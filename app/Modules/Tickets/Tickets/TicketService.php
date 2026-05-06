<?php

namespace App\Modules\Tickets\Tickets;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAttachment;
use App\Models\TicketAssignment;
use App\Models\TicketEngineerAssignment;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\TicketWorklog;
use App\Models\User;
use App\Services\SLA\SLAResolverService;
use App\Services\SLA\SLATrackingService;
use App\Services\Notifications\FirebasePushService;
use App\Services\Tickets\TicketFlowPolicyResolverService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_ASSIGNED = 'ASSIGNED';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_ON_HOLD = 'ON_HOLD';
    public const STATUS_PENDING_CUSTOMER = 'PENDING_CUSTOMER';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public function __construct(
        private readonly SLAResolverService $slaResolver,
        private readonly SLATrackingService $slaTracking,
        private readonly TicketFlowPolicyResolverService $ticketFlowPolicyResolver,
        private readonly FirebasePushService $firebasePushService,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15, ?User $actor = null): LengthAwarePaginator
    {
        $query = Ticket::query()
            ->with([
                'requester:id,name',
                'requesterDepartment:id,name',
                'category:id,name',
                'subcategory:id,name',
                'detailSubcategory:id,name',
                'priority:id,name',
                'status:id,name,code',
                'service:id,name',
                'asset:id,name',
                'assetLocation:id,name',
                'inspection:id,inspection_number',
                'assignedEngineer:id,name',
                'assignedEngineers:id,name',
                'expectedApprover:id,name',
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['ticket_status_id'] ?? null, fn ($query, $statusId) => $query->where('ticket_status_id', $statusId))
            ->when($filters['ticket_priority_id'] ?? null, fn ($query, $priorityId) => $query->where('ticket_priority_id', $priorityId))
            ->when($filters['ticket_category_id'] ?? null, fn ($query, $categoryId) => $query->where('ticket_category_id', $categoryId))
            ->when($filters['ticket_subcategory_id'] ?? null, fn ($query, $subcategoryId) => $query->where('ticket_subcategory_id', $subcategoryId))
            ->when($filters['ticket_detail_subcategory_id'] ?? null, fn ($query, $detailSubcategoryId) => $query->where('ticket_detail_subcategory_id', $detailSubcategoryId))
            ->when($filters['assigned_engineer_id'] ?? null, function ($query, $engineerId): void {
                $query->where(function ($scopedQuery) use ($engineerId): void {
                    $scopedQuery->where('assigned_engineer_id', $engineerId)
                        ->orWhereHas('assignedEngineers', fn ($engineerQuery) => $engineerQuery->whereKey($engineerId));
                });
            })
            ->when($filters['expected_approver_id'] ?? null, fn ($query, $approverId) => $query->where('expected_approver_id', $approverId))
            ->when($filters['expected_approver_role_code'] ?? null, fn ($query, $roleCode) => $query->where('expected_approver_role_code', $roleCode))
            ->when($filters['approval_status'] ?? null, fn ($query, $approvalStatus) => $query->where('approval_status', $approvalStatus))
            ->when(($filters['assignment_queue'] ?? null) === 'ready', function ($query) {
                $query->whereNull('assigned_engineer_id')
                    ->whereDoesntHave('assignedEngineers')
                    ->whereNotNull('assignment_ready_at');
            });

        $this->applyAccessScope($query, $actor);
        $this->applyApprovalQueueScope($query, $filters, $actor);

        return $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?User $actor = null): Ticket
    {
        return DB::transaction(function () use ($data, $actor): Ticket {
            $now = CarbonImmutable::now();
            $flowPolicy = $this->ticketFlowPolicyResolver->resolve($data);
            $initialApprovalStatus = $flowPolicy['requires_approval']
                ? Ticket::APPROVAL_STATUS_PENDING
                : Ticket::APPROVAL_STATUS_NOT_REQUIRED;
            $initialStatus = $flowPolicy['requires_approval']
                ? ($this->findStatusByCode(self::STATUS_PENDING_APPROVAL) ?? $this->findStatusByCode(self::STATUS_NEW))
                : $this->findStatusByCode(self::STATUS_NEW);
            $resolvedSla = $this->slaResolver->resolveSLA($this->slaContext($data));
            $ticketData = Arr::except($data, ['ticket_type', 'service_item_id', 'sla_policy_id', 'attachments']);

            $ticket = Ticket::query()->create([
                ...$ticketData,
                'ticket_number' => $this->generateTicketNumber(),
                'ticket_status_id' => $initialStatus?->id,
                'requires_approval' => $flowPolicy['requires_approval'],
                'allow_direct_assignment' => $flowPolicy['allow_direct_assignment'],
                'approval_status' => $initialApprovalStatus,
                'approval_requested_at' => $flowPolicy['requires_approval'] ? $now : null,
                'expected_approver_id' => $flowPolicy['requires_approval'] ? $flowPolicy['approver_user_id'] : null,
                'expected_approver_name_snapshot' => $flowPolicy['requires_approval'] ? $flowPolicy['approver_name'] : null,
                'expected_approver_strategy' => $flowPolicy['requires_approval'] ? $flowPolicy['approver_strategy'] : null,
                'expected_approver_role_code' => $flowPolicy['requires_approval'] ? $flowPolicy['approver_role_code'] : null,
                'flow_policy_source' => $flowPolicy['source'],
                'sla_policy_id' => $resolvedSla->policyId,
                'sla_policy_name' => $resolvedSla->name,
                'sla_name_snapshot' => $resolvedSla->name,
                'response_due_at' => $resolvedSla->responseDueAt($now),
                'resolution_due_at' => $resolvedSla->resolutionDueAt($now),
                'sla_status' => $resolvedSla->hasTargets() ? Ticket::SLA_STATUS_ON_TIME : null,
                'created_by_id' => $actor?->id,
                'updated_by_id' => $actor?->id,
                'last_status_changed_at' => $now,
            ]);

            $this->logActivity(
                ticket: $ticket,
                actor: $actor,
                type: 'ticket_created',
                oldStatusId: null,
                newStatusId: $initialStatus?->id,
                metadata: [
                    'source' => $ticket->source,
                    'sla_source' => $resolvedSla->source,
                    'sla_policy_id' => $ticket->sla_policy_id,
                    'sla_name' => $ticket->sla_name_snapshot,
                    'requires_approval' => $ticket->requires_approval,
                    'allow_direct_assignment' => $ticket->allow_direct_assignment,
                    'approval_status' => $ticket->approval_status,
                    'expected_approver_id' => $ticket->expected_approver_id,
                    'expected_approver_name_snapshot' => $ticket->expected_approver_name_snapshot,
                    'expected_approver_strategy' => $ticket->expected_approver_strategy,
                    'expected_approver_role_code' => $ticket->expected_approver_role_code,
                    'flow_policy_source' => $ticket->flow_policy_source,
                ]
            );

            $this->storeAttachments($ticket, $data['attachments'] ?? [], $actor);

            DB::afterCommit(fn () => $this->firebasePushService->sendTicketCreated($ticket->fresh($this->ticketRelations())));

            return $this->syncSlaState($ticket, $actor, $now)->fresh($this->ticketRelations());
        });
    }

    public function assign(Ticket $ticket, User|iterable $assignedEngineers, ?User $actor = null, ?string $teamName = null, ?string $notes = null): Ticket
    {
        $this->ensureCanAssign($ticket);
        $engineers = $this->normalizeAssignedEngineers($assignedEngineers);

        if ($engineers->isEmpty()) {
            throw ValidationException::withMessages([
                'assigned_engineer_ids' => ['Minimal satu engineer harus dipilih.'],
            ]);
        }

        return DB::transaction(function () use ($ticket, $engineers, $actor, $teamName, $notes): Ticket {
            $assignedStatus = $this->findStatusByCode(self::STATUS_ASSIGNED);
            $oldStatusId = $ticket->ticket_status_id;
            $previousEngineerId = $ticket->assigned_engineer_id;
            $primaryEngineer = $engineers->first();
            $scoreShare = round(1 / max(1, $engineers->count()), 4);
            $assignedAt = now();
            $engineerIds = $engineers->pluck('id')->map(fn ($id) => (int) $id)->values();

            $ticket->update([
                'assigned_engineer_id' => $primaryEngineer->id,
                'assigned_team_name' => $teamName,
                'ticket_status_id' => $assignedStatus?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor?->id,
                'last_status_changed_at' => now(),
            ]);

            TicketEngineerAssignment::query()
                ->where('ticket_id', $ticket->id)
                ->whereNotIn('engineer_id', $engineerIds->all())
                ->delete();

            foreach ($engineers as $engineer) {
                TicketEngineerAssignment::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'engineer_id' => $engineer->id,
                    ],
                    [
                        'assigned_by_id' => $actor?->id,
                        'team_name' => $teamName,
                        'score_share' => $scoreShare,
                        'assigned_at' => $assignedAt,
                    ]
                );

                TicketAssignment::query()->create([
                    'ticket_id' => $ticket->id,
                    'previous_engineer_id' => $previousEngineerId,
                    'assigned_engineer_id' => $engineer->id,
                    'assigned_by_id' => $actor?->id,
                    'assigned_at' => $assignedAt,
                    'notes' => $notes,
                ]);
            }

            $this->logActivity(
                ticket: $ticket,
                actor: $actor,
                type: 'ticket_assigned',
                oldStatusId: $oldStatusId,
                newStatusId: $assignedStatus?->id,
                metadata: [
                    'assigned_engineer_id' => $primaryEngineer->id,
                    'assigned_engineer_ids' => $engineerIds->all(),
                    'score_share_per_engineer' => $scoreShare,
                    'assigned_team_name' => $teamName,
                    'notes' => $notes,
                ]
            );

            DB::afterCommit(fn () => $this->firebasePushService->sendTicketAssigned($ticket->fresh($this->ticketRelations())));

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function approve(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanApprove($ticket, $actor);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $newStatus = $ticket->allow_direct_assignment
                ? ($this->findStatusByCode(self::STATUS_NEW) ?? $ticket->status)
                : ($this->findStatusByCode(self::STATUS_NEW) ?? $ticket->status);
            $oldStatusId = $ticket->ticket_status_id;
            $approvedAt = CarbonImmutable::now();

            $ticket->update([
                'approval_status' => Ticket::APPROVAL_STATUS_APPROVED,
                'approved_at' => $approvedAt,
                'approved_by_id' => $actor->id,
                'approval_notes' => $notes,
                'ticket_status_id' => $newStatus?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $approvedAt,
            ]);

            $this->logActivity($ticket, $actor, 'ticket_approved', $oldStatusId, $newStatus?->id, [
                'notes' => $notes,
                'requires_approval' => $ticket->requires_approval,
                'allow_direct_assignment' => $ticket->allow_direct_assignment,
            ]);

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function reject(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanReject($ticket, $actor);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $rejectedStatus = $this->findStatusByCode(self::STATUS_REJECTED) ?? $ticket->status;
            $oldStatusId = $ticket->ticket_status_id;
            $rejectedAt = CarbonImmutable::now();

            $ticket->update([
                'approval_status' => Ticket::APPROVAL_STATUS_REJECTED,
                'rejected_at' => $rejectedAt,
                'rejected_by_id' => $actor->id,
                'approval_notes' => $notes,
                'ticket_status_id' => $rejectedStatus?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $rejectedAt,
            ]);

            $this->logActivity($ticket, $actor, 'ticket_rejected', $oldStatusId, $rejectedStatus?->id, [
                'notes' => $notes,
            ]);

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function markReadyForAssignment(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanMarkReadyForAssignment($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $oldStatusId = $ticket->ticket_status_id;
            $readyAt = CarbonImmutable::now();
            $newStatus = $this->findStatusByCode(self::STATUS_NEW) ?? $ticket->status;

            $ticket->update([
                'assignment_ready_at' => $readyAt,
                'assignment_ready_by_id' => $actor->id,
                'updated_by_id' => $actor->id,
                'ticket_status_id' => $newStatus?->id ?? $ticket->ticket_status_id,
                'last_status_changed_at' => $readyAt,
            ]);

            $this->logActivity($ticket, $actor, 'ticket_ready_for_assignment', $oldStatusId, $newStatus?->id, [
                'notes' => $notes,
            ]);

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function startWork(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanStartWork($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $inProgress = $this->findStatusByCode(self::STATUS_IN_PROGRESS);
            $oldStatusId = $ticket->ticket_status_id;
            $startedAt = CarbonImmutable::now();
            $startedAt = $ticket->started_at !== null
                ? CarbonImmutable::instance($ticket->started_at)
                : $startedAt;
            $responseBreachAt = $this->resolveResponseBreachAt($ticket, $startedAt);

            $ticket->update([
                'started_at' => $startedAt,
                'paused_at' => null,
                'responded_at' => $ticket->responded_at ?? $startedAt,
                'breached_response_at' => $ticket->breached_response_at ?? $responseBreachAt,
                'sla_status' => $this->mergeSlaStatus($ticket, $responseBreachAt !== null),
                'ticket_status_id' => $inProgress?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => now(),
            ]);

            if ($notes !== null && $notes !== '') {
                $this->addWorklog($ticket, $actor, [
                    'log_type' => 'progress',
                    'description' => $notes,
                ]);
            }

            $this->logActivity($ticket, $actor, 'work_started', $oldStatusId, $inProgress?->id, ['notes' => $notes]);

            return $this->syncSlaState($ticket, $actor, $startedAt)->fresh($this->ticketRelations());
        });
    }

    public function pauseWork(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanPauseWork($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $onHold = $this->findStatusByCode(self::STATUS_ON_HOLD);
            $oldStatusId = $ticket->ticket_status_id;
            $pausedAt = CarbonImmutable::now();

            $ticket = $this->syncSlaState($ticket, $actor, $pausedAt);

            $ticket->update([
                'paused_at' => $pausedAt,
                'ticket_status_id' => $onHold?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $pausedAt,
            ]);

            if ($notes !== null && $notes !== '') {
                $this->addWorklog($ticket, $actor, [
                    'log_type' => 'progress',
                    'description' => $notes,
                ]);
            }

            $this->logActivity($ticket, $actor, 'work_paused', $oldStatusId, $onHold?->id, [
                'notes' => $notes,
                'paused_at' => $pausedAt->toIso8601String(),
            ]);

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function resumeWork(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanResumeWork($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $inProgress = $this->findStatusByCode(self::STATUS_IN_PROGRESS);
            $oldStatusId = $ticket->ticket_status_id;
            $resumedAt = CarbonImmutable::now();
            $resumePayload = $this->slaTracking->buildResumePayload($ticket, $resumedAt);

            $ticket->update([
                'paused_at' => null,
                ...$resumePayload['ticket_updates'],
                'ticket_status_id' => $inProgress?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $resumedAt,
            ]);

            if ($notes !== null && $notes !== '') {
                $this->addWorklog($ticket, $actor, [
                    'log_type' => 'progress',
                    'description' => $notes,
                ]);
            }

            $this->logActivity($ticket, $actor, 'work_resumed', $oldStatusId, $inProgress?->id, [
                'notes' => $notes,
                ...$resumePayload['activity_metadata'],
            ]);

            return $this->syncSlaState($ticket, $actor, $resumedAt)->fresh($this->ticketRelations());
        });
    }

    public function completeWork(Ticket $ticket, User $actor, ?string $notes = null, array $completionEvidences = []): Ticket
    {
        $this->ensureCanCompleteWork($ticket);

        if ($notes === null || trim($notes) === '') {
            throw ValidationException::withMessages([
                'notes' => ['Resolution notes wajib diisi sebelum menyelesaikan task.'],
            ]);
        }

        if ($completionEvidences === []) {
            throw ValidationException::withMessages([
                'completion_evidences' => ['Minimal satu foto evidence wajib diupload sebelum menyelesaikan task.'],
            ]);
        }

        return DB::transaction(function () use ($ticket, $actor, $notes, $completionEvidences): Ticket {
            $completed = $this->findStatusByCode(self::STATUS_COMPLETED);
            $oldStatusId = $ticket->ticket_status_id;
            $completedAt = CarbonImmutable::now();
            $respondedAt = $ticket->responded_at !== null
                ? CarbonImmutable::instance($ticket->responded_at)
                : ($ticket->started_at !== null ? CarbonImmutable::instance($ticket->started_at) : $completedAt);
            $responseBreachAt = $ticket->breached_response_at !== null
                ? CarbonImmutable::instance($ticket->breached_response_at)
                : $this->resolveResponseBreachAt($ticket, $respondedAt);
            $resolutionBreachAt = $this->resolveResolutionBreachAt($ticket, $completedAt);

            $ticket->update([
                'responded_at' => $respondedAt,
                'breached_response_at' => $responseBreachAt,
                'completed_at' => $completedAt,
                'resolved_at' => $completedAt,
                'breached_resolution_at' => $ticket->breached_resolution_at ?? $resolutionBreachAt,
                'sla_status' => $this->mergeSlaStatus($ticket, $responseBreachAt !== null || $resolutionBreachAt !== null),
                'ticket_status_id' => $completed?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => now(),
            ]);

            $this->addWorklog($ticket, $actor, [
                'log_type' => 'resolution',
                'description' => $notes,
            ]);

            $this->storeAttachments($ticket, $completionEvidences, $actor);

            $this->logActivity($ticket, $actor, 'work_completed', $oldStatusId, $completed?->id, [
                'notes' => $notes,
                'evidence_count' => count($completionEvidences),
            ]);

            DB::afterCommit(fn () => $this->firebasePushService->sendTicketCompleted($ticket->fresh($this->ticketRelations())));

            return $this->syncSlaState($ticket, $actor, $completedAt)->fresh($this->ticketRelations());
        });
    }

    public function markPendingCustomer(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanMarkPendingCustomer($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $pendingCustomer = $this->findStatusByCode(self::STATUS_PENDING_CUSTOMER);
            $oldStatusId = $ticket->ticket_status_id;
            $pendingAt = CarbonImmutable::now();
            $pausedAt = $ticket->paused_at !== null
                ? CarbonImmutable::instance($ticket->paused_at)
                : $pendingAt;

            $ticket = $this->syncSlaState($ticket, $actor, $pendingAt);

            $ticket->update([
                'paused_at' => $pausedAt,
                'ticket_status_id' => $pendingCustomer?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $pendingAt,
            ]);

            if ($notes !== null && $notes !== '') {
                $this->addWorklog($ticket, $actor, [
                    'log_type' => 'progress',
                    'description' => $notes,
                ]);
            }

            $this->logActivity($ticket, $actor, 'ticket_pending_customer', $oldStatusId, $pendingCustomer?->id, [
                'notes' => $notes,
                'paused_at' => $pausedAt->toIso8601String(),
            ]);

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function close(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanClose($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $closed = $this->findStatusByCode(self::STATUS_CLOSED);
            $oldStatusId = $ticket->ticket_status_id;
            $closedAt = CarbonImmutable::now();

            $ticket = $this->syncSlaState($ticket, $actor, $closedAt);

            $ticket->update([
                'closed_at' => $closedAt,
                'ticket_status_id' => $closed?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $closedAt,
            ]);

            $this->logActivity($ticket, $actor, 'ticket_closed', $oldStatusId, $closed?->id, [
                'notes' => $notes,
                'closed_at' => $closedAt->toIso8601String(),
            ]);

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function reopen(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanReopen($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $reopenedAt = CarbonImmutable::now();
            $reopenedStatus = $ticket->assigned_engineer_id !== null
                ? $this->findStatusByCode(self::STATUS_ASSIGNED)
                : $this->findStatusByCode(self::STATUS_NEW);
            $oldStatusId = $ticket->ticket_status_id;

            $ticket->update([
                'started_at' => null,
                'completed_at' => null,
                'closed_at' => null,
                'resolved_at' => null,
                'paused_at' => null,
                'ticket_status_id' => $reopenedStatus?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $reopenedAt,
            ]);

            $this->logActivity($ticket, $actor, 'ticket_reopened', $oldStatusId, $reopenedStatus?->id, [
                'notes' => $notes,
                'reopen_target_status' => $reopenedStatus?->code,
            ]);

            return $this->syncSlaState($ticket, $actor, $reopenedAt)->fresh($this->ticketRelations());
        });
    }

    public function cancel(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $this->ensureCanCancel($ticket);

        return DB::transaction(function () use ($ticket, $actor, $notes): Ticket {
            $cancelled = $this->findStatusByCode(self::STATUS_CANCELLED);
            $oldStatusId = $ticket->ticket_status_id;
            $cancelledAt = CarbonImmutable::now();

            $ticket = $this->syncSlaState($ticket, $actor, $cancelledAt);

            $ticket->update([
                'closed_at' => $cancelledAt,
                'ticket_status_id' => $cancelled?->id ?? $ticket->ticket_status_id,
                'updated_by_id' => $actor->id,
                'last_status_changed_at' => $cancelledAt,
            ]);

            $this->logActivity($ticket, $actor, 'ticket_cancelled', $oldStatusId, $cancelled?->id, [
                'notes' => $notes,
                'cancelled_at' => $cancelledAt->toIso8601String(),
            ]);

            return $ticket->fresh($this->ticketRelations());
        });
    }

    public function syncSlaState(Ticket $ticket, ?User $actor = null, ?CarbonImmutable $referenceAt = null): Ticket
    {
        return $this->slaTracking->sync($ticket, $actor, $referenceAt);
    }

    public function monitorSla(int $limit = 500): array
    {
        $processed = 0;
        $openTicketQuery = Ticket::query()
            ->where(function ($query): void {
                $query->whereNotNull('response_due_at')
                    ->orWhereNotNull('resolution_due_at');
            })
            ->whereNull('closed_at')
            ->whereNull('completed_at')
            ->orderBy('id')
            ->limit($limit);

        $openTicketQuery->get()->each(function (Ticket $ticket) use (&$processed): void {
            $this->syncSlaState($ticket, referenceAt: CarbonImmutable::now());
            $processed++;
        });

        return [
            'processed' => $processed,
            'limit' => $limit,
        ];
    }

    public function addWorklog(Ticket $ticket, User $actor, array $data): TicketWorklog
    {
        $startedAt = isset($data['started_at']) ? CarbonImmutable::parse($data['started_at']) : null;
        $endedAt = isset($data['ended_at']) ? CarbonImmutable::parse($data['ended_at']) : null;

        $durationMinutes = null;
        if ($startedAt !== null && $endedAt !== null) {
            $durationMinutes = max(0, $startedAt->diffInMinutes($endedAt));
        }

        $worklog = TicketWorklog::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $actor->id,
            'log_type' => $data['log_type'] ?? 'note',
            'description' => $data['description'],
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes,
        ]);

        $this->logActivity(
            ticket: $ticket,
            actor: $actor,
            type: 'worklog_added',
            oldStatusId: $ticket->ticket_status_id,
            newStatusId: $ticket->ticket_status_id,
            metadata: ['worklog_id' => $worklog->id, 'log_type' => $worklog->log_type]
        );

        return $worklog->fresh('user:id,name');
    }

    public function findStatusByCode(string $code): ?TicketStatus
    {
        return TicketStatus::query()
            ->where('code', $code)
            ->first();
    }

    public function findStatusOrFail(string $code): TicketStatus
    {
        $status = $this->findStatusByCode($code);

        if ($status === null) {
            throw new ModelNotFoundException("Ticket status {$code} not found");
        }

        return $status;
    }

    private function normalizeAssignedEngineers(User|iterable $assignedEngineers): \Illuminate\Support\Collection
    {
        $engineers = $assignedEngineers instanceof User
            ? collect([$assignedEngineers])
            : collect($assignedEngineers);

        return $engineers
            ->filter(fn ($engineer) => $engineer instanceof User && $engineer->role === 'engineer')
            ->unique('id')
            ->values();
    }

    private function ensureCanStartWork(Ticket $ticket): void
    {
        if ($this->isTerminalStatus($ticket)) {
            $this->throwInvalidAction('Task sudah selesai, aksi start tidak tersedia.');
        }

        if ($ticket->started_at !== null) {
            $this->throwInvalidAction('Task sudah pernah dimulai. Gunakan pause/resume atau complete.');
        }
    }

    private function ensureCanAssign(Ticket $ticket): void
    {
        if (! $ticket->canBeAssigned()) {
            $this->throwInvalidAction($ticket->assignmentGateMessage() ?? 'Ticket ini belum bisa di-assign.');
        }
    }

    private function ensureCanApprove(Ticket $ticket, User $actor): void
    {
        if (! $ticket->requires_approval) {
            $this->throwInvalidAction('Ticket ini tidak membutuhkan approval.');
        }

        if (! $ticket->canBeApprovedBy($actor)) {
            $this->throwInvalidAction('Anda bukan approver yang ditetapkan untuk ticket ini.');
        }

        if ($ticket->isApproved()) {
            $this->throwInvalidAction('Ticket ini sudah disetujui.');
        }

        if ($ticket->isRejected()) {
            $this->throwInvalidAction('Ticket ini sudah ditolak.');
        }
    }

    private function ensureCanReject(Ticket $ticket, User $actor): void
    {
        if (! $ticket->requires_approval) {
            $this->throwInvalidAction('Ticket ini tidak membutuhkan approval.');
        }

        if (! $ticket->canBeApprovedBy($actor)) {
            $this->throwInvalidAction('Anda bukan approver yang ditetapkan untuk ticket ini.');
        }

        if ($ticket->isApproved()) {
            $this->throwInvalidAction('Ticket ini sudah disetujui dan tidak bisa ditolak lewat aksi ini.');
        }

        if ($ticket->isRejected()) {
            $this->throwInvalidAction('Ticket ini sudah ditolak.');
        }
    }

    private function ensureCanMarkReadyForAssignment(Ticket $ticket): void
    {
        if ($ticket->isRejected()) {
            $this->throwInvalidAction('Ticket yang ditolak tidak bisa ditandai siap assign.');
        }

        if ($ticket->requires_approval && ! $ticket->isApproved()) {
            $this->throwInvalidAction('Ticket ini harus disetujui terlebih dahulu sebelum ditandai siap assign.');
        }

        if ($ticket->allow_direct_assignment) {
            $this->throwInvalidAction('Ticket ini sudah boleh langsung di-assign tanpa penanda siap assign.');
        }

        if ($ticket->assignment_ready_at !== null) {
            $this->throwInvalidAction('Ticket ini sudah ditandai siap assign.');
        }
    }

    private function ensureCanMarkPendingCustomer(Ticket $ticket): void
    {
        if ($this->isTerminalStatus($ticket)) {
            $this->throwInvalidAction('Ticket sudah selesai, aksi pending customer tidak tersedia.');
        }

        if ($ticket->started_at === null) {
            $this->throwInvalidAction('Ticket belum dikerjakan. Mulai pekerjaan terlebih dahulu sebelum menunggu customer.');
        }

        if ($this->resolveStatusCode($ticket) === self::STATUS_PENDING_CUSTOMER) {
            $this->throwInvalidAction('Ticket ini sudah berada pada status pending customer.');
        }
    }

    private function ensureCanPauseWork(Ticket $ticket): void
    {
        if ($this->isTerminalStatus($ticket)) {
            $this->throwInvalidAction('Task sudah selesai, aksi pause tidak tersedia.');
        }

        if ($ticket->started_at === null) {
            $this->throwInvalidAction('Task belum dimulai. Silakan start terlebih dahulu.');
        }

        if ($ticket->paused_at !== null) {
            $this->throwInvalidAction('Task sudah dalam status pause.');
        }
    }

    private function ensureCanResumeWork(Ticket $ticket): void
    {
        if ($this->isTerminalStatus($ticket)) {
            $this->throwInvalidAction('Task sudah selesai, aksi resume tidak tersedia.');
        }

        if ($ticket->started_at === null) {
            $this->throwInvalidAction('Task belum dimulai. Silakan start terlebih dahulu.');
        }

        if ($ticket->paused_at === null) {
            $this->throwInvalidAction('Task tidak sedang pause.');
        }
    }

    private function ensureCanCompleteWork(Ticket $ticket): void
    {
        if ($this->isTerminalStatus($ticket)) {
            $this->throwInvalidAction('Task sudah selesai.');
        }

        if ($ticket->started_at === null) {
            $this->throwInvalidAction('Task belum dimulai. Silakan start terlebih dahulu.');
        }
    }

    private function ensureCanClose(Ticket $ticket): void
    {
        if ($this->resolveStatusCode($ticket) === self::STATUS_CANCELLED) {
            $this->throwInvalidAction('Ticket yang sudah dibatalkan tidak bisa di-close.');
        }

        if ($this->resolveStatusCode($ticket) === self::STATUS_CLOSED || $ticket->closed_at !== null) {
            $this->throwInvalidAction('Ticket ini sudah closed.');
        }

        if ($ticket->completed_at === null && $this->resolveStatusCode($ticket) !== self::STATUS_COMPLETED) {
            $this->throwInvalidAction('Ticket hanya bisa di-close setelah engineer menyelesaikan pekerjaan.');
        }
    }

    private function ensureCanReopen(Ticket $ticket): void
    {
        if ($ticket->completed_at === null
            && $ticket->closed_at === null
            && ! in_array($this->resolveStatusCode($ticket), [self::STATUS_COMPLETED, self::STATUS_CLOSED, self::STATUS_CANCELLED], true)) {
            $this->throwInvalidAction('Ticket ini belum berada pada status yang bisa di-reopen.');
        }
    }

    private function ensureCanCancel(Ticket $ticket): void
    {
        $statusCode = $this->resolveStatusCode($ticket);

        if ($statusCode === self::STATUS_CANCELLED) {
            $this->throwInvalidAction('Ticket ini sudah dibatalkan.');
        }

        if ($statusCode === self::STATUS_REJECTED) {
            $this->throwInvalidAction('Ticket yang sudah ditolak tidak bisa dibatalkan lewat aksi ini.');
        }

        if ($statusCode === self::STATUS_CLOSED || $ticket->closed_at !== null) {
            $this->throwInvalidAction('Ticket yang sudah closed tidak bisa dibatalkan.');
        }

        if ($ticket->completed_at !== null || $statusCode === self::STATUS_COMPLETED) {
            $this->throwInvalidAction('Ticket yang sudah completed perlu di-close atau di-reopen, bukan dibatalkan.');
        }
    }

    private function isTerminalStatus(Ticket $ticket): bool
    {
        if ($ticket->completed_at !== null || $ticket->closed_at !== null) {
            return true;
        }

        return in_array($this->resolveStatusCode($ticket), [self::STATUS_COMPLETED, self::STATUS_CLOSED, self::STATUS_REJECTED, self::STATUS_CANCELLED], true);
    }

    private function resolveStatusCode(Ticket $ticket): ?string
    {
        $code = $ticket->relationLoaded('status')
            ? $ticket->status?->code
            : TicketStatus::query()->whereKey($ticket->ticket_status_id)->value('code');

        if ($code === null || $code === '') {
            return null;
        }

        return strtoupper((string) $code);
    }

    private function throwInvalidAction(string $message): never
    {
        throw ValidationException::withMessages([
            'action' => $message,
        ]);
    }

    private function applyAccessScope(Builder $query, ?User $actor): void
    {
        if ($actor === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($actor->hasPermission('ticket.view_all')) {
            return;
        }

        $query->where(function (Builder $scopedQuery) use ($actor): void {
            $hasScope = false;

            if ($actor->hasAnyPermission(['ticket.approve_all', 'ticket.approve_department'])) {
                if ($actor->id !== null) {
                    $scopedQuery->orWhere('tickets.expected_approver_id', $actor->id);
                    $hasScope = true;
                }

                if (filled($actor->role)) {
                    $scopedQuery->orWhere('tickets.expected_approver_role_code', $actor->role);
                    $hasScope = true;
                }
            }

            if ($actor->hasPermission('ticket.view_department') && $actor->department_id !== null) {
                $scopedQuery->orWhere('tickets.requester_department_id', $actor->department_id);
                $hasScope = true;
            }

            if ($actor->hasPermission('ticket.view_assigned')) {
                $scopedQuery->orWhere('tickets.assigned_engineer_id', $actor->id)
                    ->orWhereHas('assignedEngineers', fn ($engineerQuery) => $engineerQuery->whereKey($actor->id));
                $hasScope = true;
            }

            if ($actor->hasPermission('ticket.view_own')) {
                $scopedQuery->orWhere('tickets.requester_id', $actor->id);
                $hasScope = true;
            }

            if (! $hasScope) {
                $scopedQuery->whereRaw('1 = 0');
            }
        });
    }

    private function applyApprovalQueueScope(Builder $query, array $filters, ?User $actor): void
    {
        if (($filters['approval_queue'] ?? null) !== 'my') {
            return;
        }

        if ($actor === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('tickets.approval_status', Ticket::APPROVAL_STATUS_PENDING);

        $query->where(function (Builder $approvalQuery) use ($actor): void {
            $hasScope = false;

            if ($actor->id !== null) {
                $approvalQuery->orWhere('tickets.expected_approver_id', $actor->id);
                $hasScope = true;
            }

            if (filled($actor->role)) {
                $approvalQuery->orWhere('tickets.expected_approver_role_code', $actor->role);
                $hasScope = true;
            }

            if (! $hasScope) {
                $approvalQuery->whereRaw('1 = 0');
            }
        });
    }

    private function slaContext(array $data): array
    {
        return [
            'ticket_type' => $data['ticket_type'] ?? $this->deriveTicketTypeFromCategoryId($data['ticket_category_id'] ?? null),
            'category_id' => $data['ticket_category_id'] ?? null,
            'subcategory_id' => $data['ticket_subcategory_id'] ?? null,
            'detail_subcategory_id' => $data['ticket_detail_subcategory_id'] ?? null,
            'service_item_id' => $data['service_item_id'] ?? $data['service_id'] ?? null,
            'priority_id' => $data['ticket_priority_id'] ?? null,
            'impact' => $data['impact'] ?? null,
            'urgency' => $data['urgency'] ?? null,
            'sla_policy_id' => $data['sla_policy_id'] ?? null,
            'sla_policy_name' => $data['sla_policy_name'] ?? null,
        ];
    }

    private function deriveTicketTypeFromCategoryId(int|string|null $categoryId): ?string
    {
        if ($categoryId === null || $categoryId === '') {
            return null;
        }

        $categoryCode = TicketCategory::query()
            ->whereKey($categoryId)
            ->value('code');

        if (! is_string($categoryCode) || $categoryCode === '') {
            return null;
        }

        return match (strtoupper($categoryCode)) {
            'INCIDENT' => 'incident',
            'REQUEST' => 'service_request',
            'MAINTENANCE' => 'change_request',
            default => strtolower($categoryCode),
        };
    }

    private function resolveResponseBreachAt(Ticket $ticket, CarbonImmutable $respondedAt): ?CarbonImmutable
    {
        if ($ticket->response_due_at === null) {
            return null;
        }

        $responseDueAt = CarbonImmutable::instance($ticket->response_due_at);

        return $respondedAt->gt($responseDueAt)
            ? $responseDueAt
            : null;
    }

    private function resolveResolutionBreachAt(Ticket $ticket, CarbonImmutable $resolvedAt): ?CarbonImmutable
    {
        if ($ticket->resolution_due_at === null) {
            return null;
        }

        $resolutionDueAt = CarbonImmutable::instance($ticket->resolution_due_at);

        return $resolvedAt->gt($resolutionDueAt)
            ? $resolutionDueAt
            : null;
    }

    private function mergeSlaStatus(Ticket $ticket, bool $isBreached): ?string
    {
        if ($isBreached || $ticket->breached_response_at !== null || $ticket->breached_resolution_at !== null) {
            return Ticket::SLA_STATUS_BREACHED;
        }

        if ($ticket->response_due_at !== null || $ticket->resolution_due_at !== null) {
            return Ticket::SLA_STATUS_ON_TIME;
        }

        return $ticket->sla_status;
    }

    private function generateTicketNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $base = "TCK-{$datePrefix}";

        $lastTicketNumber = Ticket::query()
            ->where('ticket_number', 'like', "{$base}-%")
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        $lastSequence = 0;
        if (is_string($lastTicketNumber)) {
            $segments = explode('-', $lastTicketNumber);
            $lastSequence = (int) end($segments);
        }

        $nextSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

        return "{$base}-{$nextSequence}";
    }

    private function logActivity(
        Ticket $ticket,
        ?User $actor,
        string $type,
        ?int $oldStatusId,
        ?int $newStatusId,
        ?array $metadata = null,
    ): void {
        TicketActivity::query()->create([
            'ticket_id' => $ticket->id,
            'actor_user_id' => $actor?->id,
            'activity_type' => $type,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'metadata' => $metadata,
        ]);
    }

    private function ticketRelations(): array
    {
        return [
            'requester:id,name',
            'requesterDepartment:id,name',
            'category:id,name',
            'subcategory:id,name',
            'detailSubcategory:id,name',
            'priority:id,name',
            'status:id,name,code',
            'service:id,name',
            'asset:id,name',
            'assetLocation:id,name',
            'inspection:id,inspection_number',
            'assignedEngineer:id,name',
            'assignedEngineers:id,name',
            'expectedApprover:id,name',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'assignmentReadyBy:id,name',
            'slaPolicy:id,name',
            'attachments',
            'attachments.uploadedBy:id,name',
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    private function storeAttachments(Ticket $ticket, array $attachments, ?User $actor = null): void
    {
        foreach ($attachments as $attachment) {
            if (! $attachment instanceof UploadedFile) {
                continue;
            }

            $imageSize = @getimagesize($attachment->getRealPath());
            if ($imageSize === false) {
                throw ValidationException::withMessages([
                    'attachments' => ['One of the uploaded files is not a valid image.'],
                ]);
            }

            $mimeType = (string) $attachment->getMimeType();
            if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw ValidationException::withMessages([
                    'attachments' => ['Only JPG, PNG, and WEBP images are allowed.'],
                ]);
            }

            $extension = strtolower((string) ($attachment->guessExtension() ?: $attachment->extension() ?: 'jpg'));
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $extension = 'jpg';
            }

            $storedName = (string) Str::uuid().'.'.$extension;
            $storedPath = $attachment->storeAs(
                'tickets/'.$ticket->ticket_number.'/attachments',
                $storedName,
                'local'
            );

            TicketAttachment::query()->create([
                'ticket_id' => $ticket->id,
                'disk' => 'local',
                'file_path' => $storedPath,
                'original_name' => $attachment->getClientOriginalName(),
                'stored_name' => $storedName,
                'mime_type' => $mimeType,
                'size_bytes' => $attachment->getSize() ?: 0,
                'sha256_checksum' => hash_file('sha256', $attachment->getRealPath()) ?: null,
                'image_width' => isset($imageSize[0]) ? (int) $imageSize[0] : null,
                'image_height' => isset($imageSize[1]) ? (int) $imageSize[1] : null,
                'uploaded_by_id' => $actor?->id,
            ]);

            $this->logActivity(
                ticket: $ticket,
                actor: $actor,
                type: 'ticket_attachment_added',
                oldStatusId: $ticket->ticket_status_id,
                newStatusId: $ticket->ticket_status_id,
                metadata: [
                    'original_name' => $attachment->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size_bytes' => $attachment->getSize(),
                ],
            );
        }
    }
}
