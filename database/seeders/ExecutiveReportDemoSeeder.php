<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\ServiceCatalog;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAssignment;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketStatus;
use App\Models\TicketWorklog;
use App\Models\User;
use App\Services\SLA\SLAResolverService;
use App\Services\Tickets\TicketFlowPolicyResolverService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class ExecutiveReportDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeGeneratedData();

        $context = $this->context();

        if ($context === null) {
            return;
        }

        $abnormalInspectionsByPeriod = [];

        foreach ($this->profiles() as $profile) {
            $abnormalInspectionsByPeriod[$profile['key']] = $this->seedInspectionsForProfile($context, $profile);
        }

        foreach ($this->profiles() as $profile) {
            $this->seedTicketsForProfile(
                context: $context,
                profile: $profile,
                abnormalInspections: $abnormalInspectionsByPeriod[$profile['key']] ?? []
            );
        }
    }

    private function purgeGeneratedData(): void
    {
        $ticketIds = Ticket::query()
            ->where('ticket_number', 'like', 'TCK-RPT-%')
            ->pluck('id');

        if ($ticketIds->isNotEmpty()) {
            TicketActivity::query()->whereIn('ticket_id', $ticketIds)->delete();
            TicketAssignment::query()->whereIn('ticket_id', $ticketIds)->delete();
            TicketWorklog::query()->whereIn('ticket_id', $ticketIds)->delete();
            Ticket::query()->whereIn('id', $ticketIds)->delete();
        }

        $inspections = Inspection::query()
            ->where('inspection_number', 'like', 'INSP-RPT-%')
            ->get();

        foreach ($inspections as $inspection) {
            foreach ($inspection->items as $item) {
                $item->evidences()->delete();
            }

            $inspection->items()->delete();
            $inspection->evidences()->delete();
            $inspection->delete();
        }
    }

    private function seedInspectionsForProfile(array $context, array $profile): array
    {
        $abnormalInspections = [];
        $inspectorId = $context['users']['inspector@demo.com'] ?? null;
        $opsAdminId = $context['users']['opsadmin@demo.com'] ?? null;

        if ($inspectorId === null) {
            return $abnormalInspections;
        }

        $templateRotation = [
            'INSP-WIFI-DAILY',
            'INSP-CCTV-WEEKLY',
            'INSP-UPS-WEEKLY',
        ];

        for ($index = 1; $index <= $profile['inspection_count']; $index++) {
            $templateCode = $templateRotation[($index - 1) % count($templateRotation)];
            $template = $context['inspection_templates'][$templateCode] ?? null;

            if ($template === null) {
                continue;
            }

            $isAbnormal = $index <= $profile['abnormal_inspection_count'];
            $inspectionDate = $this->inspectionDate($profile, $index);
            $inspectionNumber = sprintf('INSP-RPT-%s-%03d', $profile['key'], $index);
            $assetCode = match ($templateCode) {
                'INSP-CCTV-WEEKLY' => 'AST-CCTV-002',
                'INSP-UPS-WEEKLY' => 'AST-UPS-001',
                default => 'AST-AP-002',
            };

            $asset = $context['assets'][$assetCode] ?? null;

            $status = match (true) {
                $profile['key'] === 'CUR' && $index === $profile['inspection_count'] => Inspection::STATUS_IN_PROGRESS,
                $profile['key'] === 'CUR' && $index === ($profile['inspection_count'] - 1) => Inspection::STATUS_DRAFT,
                default => Inspection::STATUS_SUBMITTED,
            };

            $inspection = new Inspection();
            $inspection->fill([
                'inspection_number' => $inspectionNumber,
                'inspection_template_id' => $template->id,
                'asset_id' => $asset?->id,
                'asset_location_id' => $asset?->asset_location_id,
                'inspection_officer_id' => $inspectorId,
                'scheduled_by_id' => $opsAdminId ?? $inspectorId,
                'inspection_date' => $inspectionDate->toDateString(),
                'schedule_type' => Inspection::SCHEDULE_TYPE_WEEKLY,
                'schedule_interval' => 1,
                'schedule_weekdays' => [$inspectionDate->dayOfWeekIso],
                'schedule_next_date' => $inspectionDate->addWeek()->toDateString(),
                'status' => $status,
                'final_result' => $status === Inspection::STATUS_SUBMITTED
                    ? ($isAbnormal ? Inspection::FINAL_RESULT_ABNORMAL : Inspection::FINAL_RESULT_NORMAL)
                    : null,
                'started_at' => $inspectionDate->setTime(8 + (($index * 2) % 4), 10),
                'submitted_at' => $status === Inspection::STATUS_SUBMITTED
                    ? $inspectionDate->setTime(9 + ($index % 3), 5 + (($index * 7) % 30))
                    : null,
                'summary_notes' => $this->inspectionSummaryNotes($templateCode, $profile['label'], $isAbnormal),
                'created_by_id' => $opsAdminId ?? $inspectorId,
                'updated_by_id' => $inspectorId,
            ]);
            $inspection->created_at = $inspectionDate->subDay()->setTime(15, 0);
            $inspection->updated_at = $inspection->submitted_at ?? $inspection->started_at ?? $inspection->created_at;
            $inspection->save();

            $this->syncInspectionItems($inspection, $template, $isAbnormal, $inspectorId);

            if ($isAbnormal && $status === Inspection::STATUS_SUBMITTED) {
                $abnormalInspections[] = $inspection;
            }
        }

        return $abnormalInspections;
    }

    private function seedTicketsForProfile(array $context, array $profile, array $abnormalInspections): void
    {
        $flowResolver = app(TicketFlowPolicyResolverService::class);
        $slaResolver = app(SLAResolverService::class);
        $stateSequence = $this->stateSequence($profile['state_mix'], $profile['key'] === 'CUR');
        $abnormalInspectionIndex = 0;

        foreach ($stateSequence as $index => $state) {
            $ticketNumber = sprintf('TCK-RPT-%s-%03d', $profile['key'], $index + 1);
            $blueprint = $this->blueprintFor($profile, $state, $index);
            $detail = $context['details'][$blueprint['detail_code']] ?? null;

            if ($detail === null) {
                continue;
            }

            $requesterEmail = $blueprint['requester_pool'][$index % count($blueprint['requester_pool'])];
            $requester = $context['requesters'][$requesterEmail] ?? null;

            if ($requester === null) {
                continue;
            }

            $createdAt = $this->ticketCreatedAt($profile, $index, $state);
            $flow = $flowResolver->resolve([
                'ticket_category_id' => $detail->category?->category?->id,
                'ticket_subcategory_id' => $detail->category?->id,
                'ticket_detail_subcategory_id' => $detail->id,
                'requester_department_id' => $requester->department_id,
                'service_id' => $context['services'][$blueprint['service_code']] ?? null,
            ]);

            $sla = $slaResolver->resolveSLA([
                'ticket_category_id' => $detail->category?->category?->id,
                'ticket_subcategory_id' => $detail->category?->id,
                'ticket_detail_subcategory_id' => $detail->id,
                'service_id' => $context['services'][$blueprint['service_code']] ?? null,
                'priority_id' => $context['priorities'][$blueprint['priority_code']] ?? null,
                'impact' => $blueprint['impact'],
                'urgency' => $blueprint['urgency'],
            ]);

            $responseDueAt = $sla->responseDueAt($createdAt);
            $resolutionDueAt = $sla->resolutionDueAt($createdAt);
            $lifecycle = $this->ticketLifecycle($state, $createdAt, $responseDueAt, $resolutionDueAt, $index, $profile['label']);
            $lifecycle = $this->normalizeApprovalLifecycle($lifecycle, $flow, $state, $createdAt);
            $assignedEngineerEmail = $blueprint['engineer_pool'][$index % count($blueprint['engineer_pool'])] ?? null;

            if (in_array($state, ['new_unassigned', 'pending_approval', 'rejected'], true)) {
                $assignedEngineerEmail = null;
            }

            $inspection = null;

            if ($blueprint['detail_code'] === 'ABNORMAL_INSPECTION' && isset($abnormalInspections[$abnormalInspectionIndex])) {
                $inspection = $abnormalInspections[$abnormalInspectionIndex];
                $abnormalInspectionIndex++;
            }

            $ticket = new Ticket();
            $ticket->fill([
                'ticket_number' => $ticketNumber,
                'title' => $this->ticketTitle($blueprint, $context, $index),
                'description' => $this->ticketDescription($blueprint, $context, $profile['label']),
                'requester_id' => $requester->id,
                'requester_department_id' => $requester->department_id,
                'ticket_category_id' => $detail->category?->category?->id,
                'ticket_subcategory_id' => $detail->category?->id,
                'ticket_detail_subcategory_id' => $detail->id,
                'ticket_priority_id' => $context['priorities'][$blueprint['priority_code']] ?? null,
                'service_id' => $context['services'][$blueprint['service_code']] ?? null,
                'asset_id' => $blueprint['asset_code'] ? ($context['assets'][$blueprint['asset_code']]->id ?? null) : null,
                'asset_location_id' => $context['locations'][$blueprint['location_code']] ?? ($blueprint['asset_code'] ? ($context['assets'][$blueprint['asset_code']]->asset_location_id ?? null) : null),
                'inspection_id' => $inspection?->id,
                'ticket_status_id' => $context['statuses'][$lifecycle['status_code']] ?? null,
                'assigned_team_name' => $blueprint['assigned_team_name'],
                'assigned_engineer_id' => $assignedEngineerEmail ? ($context['users'][$assignedEngineerEmail] ?? null) : null,
                'requires_approval' => $flow['requires_approval'],
                'allow_direct_assignment' => $flow['allow_direct_assignment'],
                'approval_status' => $lifecycle['approval_status'],
                'approval_requested_at' => $lifecycle['approval_requested_at'],
                'expected_approver_id' => $flow['approver_user_id'],
                'expected_approver_name_snapshot' => $flow['approver_name'],
                'expected_approver_strategy' => $flow['approver_strategy'],
                'expected_approver_role_code' => $flow['approver_role_code'],
                'approved_at' => $lifecycle['approved_at'],
                'approved_by_id' => $lifecycle['approved_by_email'] ? ($context['users'][$lifecycle['approved_by_email']] ?? null) : null,
                'rejected_at' => $lifecycle['rejected_at'],
                'rejected_by_id' => $lifecycle['rejected_by_email'] ? ($context['users'][$lifecycle['rejected_by_email']] ?? null) : null,
                'approval_notes' => $lifecycle['approval_notes'],
                'assignment_ready_at' => $lifecycle['assignment_ready_at'],
                'assignment_ready_by_id' => $lifecycle['assignment_ready_by_email'] ? ($context['users'][$lifecycle['assignment_ready_by_email']] ?? null) : null,
                'flow_policy_source' => $flow['source'],
                'sla_policy_id' => $sla->policyId,
                'sla_policy_name' => $sla->name,
                'sla_name_snapshot' => $sla->name,
                'response_due_at' => $responseDueAt,
                'responded_at' => $lifecycle['responded_at'],
                'breached_response_at' => $lifecycle['breached_response_at'],
                'resolution_due_at' => $resolutionDueAt,
                'source' => $blueprint['source_pool'][$index % count($blueprint['source_pool'])],
                'impact' => $blueprint['impact'],
                'urgency' => $blueprint['urgency'],
                'started_at' => $lifecycle['started_at'],
                'paused_at' => null,
                'resolved_at' => $lifecycle['resolved_at'],
                'sla_status' => $lifecycle['sla_status'],
                'breached_resolution_at' => $lifecycle['breached_resolution_at'],
                'completed_at' => $lifecycle['completed_at'],
                'closed_at' => $lifecycle['closed_at'],
                'last_status_changed_at' => $lifecycle['last_status_changed_at'],
                'created_by_id' => $requester->id,
                'updated_by_id' => $lifecycle['updated_by_email'] ? ($context['users'][$lifecycle['updated_by_email']] ?? $requester->id) : $requester->id,
            ]);
            $ticket->created_at = $createdAt;
            $ticket->updated_at = $lifecycle['last_status_changed_at'] ?? $createdAt;
            $ticket->save();

            $this->createActivities($ticket, $context, $requesterEmail, $assignedEngineerEmail, $lifecycle);
            $this->createAssignment($ticket, $context, $assignedEngineerEmail, $lifecycle, $blueprint['assigned_team_name']);
            $this->createWorklogs($ticket, $context, $assignedEngineerEmail, $lifecycle, $state, $index);
        }
    }

    private function normalizeApprovalLifecycle(array $lifecycle, array $flow, string $state, CarbonImmutable $createdAt): array
    {
        if (! ($flow['requires_approval'] ?? false)) {
            return [
                ...$lifecycle,
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'approval_requested_at' => null,
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => null,
                'rejected_by_email' => null,
                'approval_notes' => null,
                'assignment_ready_at' => $lifecycle['assignment_ready_at'] ?? $createdAt,
                'assignment_ready_by_email' => $lifecycle['assignment_ready_by_email'] ?? 'supervisor@demo.com',
            ];
        }

        if (in_array($state, ['pending_approval', 'rejected'], true)) {
            return $lifecycle;
        }

        $approvalRequestedAt = $lifecycle['approval_requested_at'] ?? $createdAt->addMinutes(10);
        $approvedByEmail = $lifecycle['approved_by_email']
            ?? match ($flow['approver_role_code'] ?? null) {
                'operational_admin' => 'opsadmin@demo.com',
                default => 'supervisor@demo.com',
            };

        return [
            ...$lifecycle,
            'approval_status' => Ticket::APPROVAL_STATUS_APPROVED,
            'approval_requested_at' => $approvalRequestedAt,
            'approved_at' => $lifecycle['approved_at'] ?? $approvalRequestedAt->addMinutes(35),
            'approved_by_email' => $approvedByEmail,
            'approval_notes' => $lifecycle['approval_notes'] ?? 'Permintaan disetujui sesuai kapasitas dan prioritas layanan.',
            'assignment_ready_at' => $lifecycle['assignment_ready_at'] ?? $approvalRequestedAt->addMinutes(40),
            'assignment_ready_by_email' => $lifecycle['assignment_ready_by_email'] ?? $approvedByEmail,
        ];
    }

    private function createActivities(Ticket $ticket, array $context, string $requesterEmail, ?string $assignedEngineerEmail, array $lifecycle): void
    {
        $created = new TicketActivity([
            'ticket_id' => $ticket->id,
            'actor_user_id' => $context['users'][$requesterEmail] ?? null,
            'activity_type' => 'ticket_created',
            'old_status_id' => null,
            'new_status_id' => $context['statuses'][$lifecycle['initial_status_code']] ?? null,
            'metadata' => ['source' => $ticket->source],
        ]);
        $created->created_at = $ticket->created_at;
        $created->updated_at = $ticket->created_at;
        $created->save();

        if ($lifecycle['approval_requested_at'] !== null && $lifecycle['initial_status_code'] !== 'PENDING_APPROVAL') {
            $activity = new TicketActivity([
                'ticket_id' => $ticket->id,
                'actor_user_id' => $context['users'][$requesterEmail] ?? null,
                'activity_type' => 'approval_requested',
                'old_status_id' => $context['statuses']['NEW'] ?? null,
                'new_status_id' => $context['statuses']['PENDING_APPROVAL'] ?? null,
                'metadata' => ['label' => 'Approval flow initiated'],
            ]);
            $activity->created_at = $lifecycle['approval_requested_at'];
            $activity->updated_at = $lifecycle['approval_requested_at'];
            $activity->save();
        }

        if ($lifecycle['approved_at'] !== null) {
            $activity = new TicketActivity([
                'ticket_id' => $ticket->id,
                'actor_user_id' => $lifecycle['approved_by_email'] ? ($context['users'][$lifecycle['approved_by_email']] ?? null) : null,
                'activity_type' => 'ticket_approved',
                'old_status_id' => $context['statuses']['PENDING_APPROVAL'] ?? null,
                'new_status_id' => $context['statuses']['NEW'] ?? null,
                'metadata' => ['notes' => $lifecycle['approval_notes']],
            ]);
            $activity->created_at = $lifecycle['approved_at'];
            $activity->updated_at = $lifecycle['approved_at'];
            $activity->save();
        }

        if ($lifecycle['rejected_at'] !== null) {
            $activity = new TicketActivity([
                'ticket_id' => $ticket->id,
                'actor_user_id' => $lifecycle['rejected_by_email'] ? ($context['users'][$lifecycle['rejected_by_email']] ?? null) : null,
                'activity_type' => 'ticket_rejected',
                'old_status_id' => $context['statuses']['PENDING_APPROVAL'] ?? null,
                'new_status_id' => $context['statuses']['REJECTED'] ?? null,
                'metadata' => ['notes' => $lifecycle['approval_notes']],
            ]);
            $activity->created_at = $lifecycle['rejected_at'];
            $activity->updated_at = $lifecycle['rejected_at'];
            $activity->save();
        }

        if ($assignedEngineerEmail !== null && $lifecycle['assigned_at'] !== null) {
            $activity = new TicketActivity([
                'ticket_id' => $ticket->id,
                'actor_user_id' => $context['users']['supervisor@demo.com'] ?? null,
                'activity_type' => 'ticket_assigned',
                'old_status_id' => $context['statuses'][$lifecycle['assignment_from_status_code']] ?? null,
                'new_status_id' => $context['statuses'][$lifecycle['assignment_to_status_code']] ?? null,
                'metadata' => ['assigned_engineer_email' => $assignedEngineerEmail],
            ]);
            $activity->created_at = $lifecycle['assigned_at'];
            $activity->updated_at = $lifecycle['assigned_at'];
            $activity->save();
        }

        if ($lifecycle['started_at'] !== null) {
            $activity = new TicketActivity([
                'ticket_id' => $ticket->id,
                'actor_user_id' => $assignedEngineerEmail ? ($context['users'][$assignedEngineerEmail] ?? null) : null,
                'activity_type' => 'work_started',
                'old_status_id' => $context['statuses']['ASSIGNED'] ?? $context['statuses']['NEW'] ?? null,
                'new_status_id' => $context['statuses']['IN_PROGRESS'] ?? null,
                'metadata' => ['phase' => 'execution'],
            ]);
            $activity->created_at = $lifecycle['started_at'];
            $activity->updated_at = $lifecycle['started_at'];
            $activity->save();
        }

        if ($lifecycle['completed_at'] !== null) {
            $activity = new TicketActivity([
                'ticket_id' => $ticket->id,
                'actor_user_id' => $assignedEngineerEmail ? ($context['users'][$assignedEngineerEmail] ?? null) : null,
                'activity_type' => 'work_completed',
                'old_status_id' => $context['statuses']['IN_PROGRESS'] ?? $context['statuses']['ASSIGNED'] ?? null,
                'new_status_id' => $context['statuses']['COMPLETED'] ?? null,
                'metadata' => ['sla_status' => $ticket->sla_status],
            ]);
            $activity->created_at = $lifecycle['completed_at'];
            $activity->updated_at = $lifecycle['completed_at'];
            $activity->save();
        }
    }

    private function createAssignment(Ticket $ticket, array $context, ?string $assignedEngineerEmail, array $lifecycle, string $teamName): void
    {
        if ($assignedEngineerEmail === null || $lifecycle['assigned_at'] === null) {
            return;
        }

        $assignment = new TicketAssignment([
            'ticket_id' => $ticket->id,
            'previous_engineer_id' => null,
            'assigned_engineer_id' => $context['users'][$assignedEngineerEmail] ?? null,
            'assigned_by_id' => $context['users']['supervisor@demo.com'] ?? $context['users']['opsadmin@demo.com'] ?? null,
            'assigned_at' => $lifecycle['assigned_at'],
            'notes' => 'Alokasi engineer dibuat untuk menjaga beban kerja tim '.$teamName.' tetap seimbang.',
        ]);
        $assignment->created_at = $lifecycle['assigned_at'];
        $assignment->updated_at = $lifecycle['assigned_at'];
        $assignment->save();
    }

    private function createWorklogs(Ticket $ticket, array $context, ?string $assignedEngineerEmail, array $lifecycle, string $state, int $index): void
    {
        if ($assignedEngineerEmail === null || ! in_array($state, ['completed_on_time', 'completed_breached', 'in_progress'], true)) {
            return;
        }

        $engineerId = $context['users'][$assignedEngineerEmail] ?? null;

        if ($engineerId === null || $lifecycle['started_at'] === null) {
            return;
        }

        $segments = match ($state) {
            'in_progress' => [
                ['type' => 'diagnosis', 'start' => $lifecycle['started_at'], 'end' => $lifecycle['started_at']->addMinutes(55 + (($index * 5) % 35)), 'text' => 'Remote diagnosis dan koordinasi awal ke site.'],
                ['type' => 'progress', 'start' => $lifecycle['started_at']->addMinutes(75), 'end' => $lifecycle['started_at']->addMinutes(135 + (($index * 3) % 40)), 'text' => 'Progress update untuk eksekusi tindak lanjut lapangan.'],
            ],
            default => [
                ['type' => 'diagnosis', 'start' => $lifecycle['started_at'], 'end' => $lifecycle['started_at']->addMinutes(45 + (($index * 4) % 25)), 'text' => 'Analisis akar masalah dan pengecekan titik gangguan utama.'],
                ['type' => 'execution', 'start' => $lifecycle['started_at']->addMinutes(60), 'end' => ($lifecycle['completed_at'] ?? $lifecycle['started_at'])->subMinutes(25), 'text' => 'Eksekusi perbaikan, validasi teknis, dan konfirmasi hasil ke operasi.'],
            ],
        };

        foreach ($segments as $segment) {
            if ($segment['end']->lte($segment['start'])) {
                continue;
            }

            $worklog = new TicketWorklog([
                'ticket_id' => $ticket->id,
                'user_id' => $engineerId,
                'log_type' => $segment['type'],
                'description' => $segment['text'],
                'started_at' => $segment['start'],
                'ended_at' => $segment['end'],
                'duration_minutes' => $segment['start']->diffInMinutes($segment['end']),
            ]);
            $worklog->created_at = $segment['start'];
            $worklog->updated_at = $segment['end'];
            $worklog->save();
        }
    }

    private function syncInspectionItems(Inspection $inspection, InspectionTemplate $template, bool $isAbnormal, int $checkedById): void
    {
        foreach ($template->items as $itemTemplate) {
            $resultStatus = match ($itemTemplate->item_type) {
                'text' => null,
                default => $isAbnormal && in_array($itemTemplate->sequence, [1, 3, 4], true) ? 'fail' : 'pass',
            };

            $resultValue = match ($itemTemplate->item_type) {
                'number' => $isAbnormal ? '87' : '42',
                'text' => $isAbnormal
                    ? 'Ditemukan anomali operasional yang butuh follow up teknis.'
                    : 'Kondisi umum stabil dan sesuai checklist.',
                default => null,
            };

            $inspection->items()->create([
                'inspection_template_item_id' => $itemTemplate->id,
                'sequence' => $itemTemplate->sequence,
                'item_label' => $itemTemplate->item_label,
                'item_type' => $itemTemplate->item_type,
                'expected_value' => $itemTemplate->expected_value,
                'result_status' => $resultStatus,
                'result_value' => $resultValue,
                'notes' => $isAbnormal && $resultStatus === 'fail'
                    ? 'Perlu tindak lanjut untuk menjaga stabilitas layanan.'
                    : 'Sesuai ekspektasi operasional.',
                'checked_at' => $inspection->submitted_at ?? $inspection->started_at,
                'checked_by_id' => $checkedById,
            ]);
        }
    }

    private function context(): ?array
    {
        $users = User::query()->pluck('id', 'email')->all();
        $requesters = User::query()->get()->keyBy('email')->all();
        $services = ServiceCatalog::query()->pluck('id', 'code')->all();
        $assets = \App\Models\Asset::query()->get()->keyBy('code')->all();
        $locations = \App\Models\AssetLocation::query()->pluck('id', 'code')->all();
        $locationNames = \App\Models\AssetLocation::query()->pluck('name', 'code')->all();
        $details = TicketDetailSubcategory::query()->with('category.category')->get()->keyBy('code')->all();
        $priorities = \App\Models\TicketPriority::query()->pluck('id', 'code')->all();
        $statuses = TicketStatus::query()->pluck('id', 'code')->all();
        $inspectionTemplates = InspectionTemplate::query()->with('items')->get()->keyBy('code')->all();

        if ($users === [] || $details === [] || $statuses === []) {
            return null;
        }

        return [
            'users' => $users,
            'requesters' => $requesters,
            'services' => $services,
            'assets' => $assets,
            'locations' => $locations,
            'location_names' => $locationNames,
            'details' => $details,
            'priorities' => $priorities,
            'statuses' => $statuses,
            'inspection_templates' => $inspectionTemplates,
        ];
    }

    private function profiles(): array
    {
        $now = CarbonImmutable::now();
        $currentFrom = $now->subDays(29)->startOfDay();
        $currentTo = $now->endOfDay();

        return [
            [
                'key' => 'CUR',
                'label' => 'periode aktif',
                'from' => $currentFrom,
                'to' => $currentTo,
                'ticket_count' => 48,
                'inspection_count' => 18,
                'abnormal_inspection_count' => 5,
                'state_mix' => [
                    'completed_on_time' => 21,
                    'completed_breached' => 9,
                    'assigned' => 4,
                    'in_progress' => 7,
                    'new_unassigned' => 4,
                    'pending_approval' => 2,
                    'rejected' => 1,
                ],
            ],
            [
                'key' => 'PMO',
                'label' => 'baseline satu bulan sebelumnya',
                'from' => $currentFrom->subMonth(),
                'to' => $currentTo->subMonth(),
                'ticket_count' => 34,
                'inspection_count' => 14,
                'abnormal_inspection_count' => 2,
                'state_mix' => [
                    'completed_on_time' => 22,
                    'completed_breached' => 4,
                    'assigned' => 2,
                    'in_progress' => 3,
                    'new_unassigned' => 1,
                    'pending_approval' => 1,
                    'rejected' => 1,
                ],
            ],
            [
                'key' => 'PYR',
                'label' => 'baseline satu tahun sebelumnya',
                'from' => $currentFrom->subYear(),
                'to' => $currentTo->subYear(),
                'ticket_count' => 24,
                'inspection_count' => 10,
                'abnormal_inspection_count' => 3,
                'state_mix' => [
                    'completed_on_time' => 11,
                    'completed_breached' => 6,
                    'assigned' => 2,
                    'in_progress' => 2,
                    'new_unassigned' => 1,
                    'pending_approval' => 1,
                    'rejected' => 1,
                ],
            ],
        ];
    }

    private function stateSequence(array $mix, bool $pressureTail = false): array
    {
        $sequence = [];

        $order = $pressureTail
            ? ['completed_on_time', 'completed_breached', 'assigned', 'in_progress', 'new_unassigned', 'pending_approval', 'rejected']
            : ['completed_on_time', 'assigned', 'completed_breached', 'in_progress', 'new_unassigned', 'pending_approval', 'rejected'];

        foreach ($order as $state) {
            for ($i = 0; $i < ($mix[$state] ?? 0); $i++) {
                $sequence[] = $state;
            }
        }

        return $sequence;
    }

    private function inspectionDate(array $profile, int $index): CarbonImmutable
    {
        $from = $profile['from'];
        $to = $profile['to'];
        $spanDays = max(1, $from->diffInDays($to));

        if ($profile['key'] === 'CUR' && $index > ($profile['inspection_count'] - 5)) {
            $offset = max(0, $spanDays - 6 + (($index * 2) % 7));
        } else {
            $offset = (($index - 1) * 2) % ($spanDays + 1);
        }

        return $from->addDays(min($offset, $spanDays));
    }

    private function ticketCreatedAt(array $profile, int $index, string $state): CarbonImmutable
    {
        $from = $profile['from'];
        $to = $profile['to'];
        $spanDays = max(1, $from->diffInDays($to));

        if ($profile['key'] === 'CUR' && in_array($state, ['completed_breached', 'in_progress', 'new_unassigned', 'pending_approval'], true)) {
            $offset = max(0, $spanDays - 6 + (($index * 3) % 7));
        } else {
            $offset = (($index * 3) + ($index % 2)) % ($spanDays + 1);
        }

        return $from
            ->addDays(min($offset, $spanDays))
            ->setTime(7 + (($index * 3) % 10), ($index * 11) % 60);
    }

    private function ticketLifecycle(
        string $state,
        CarbonImmutable $createdAt,
        ?CarbonImmutable $responseDueAt,
        ?CarbonImmutable $resolutionDueAt,
        int $index,
        string $profileLabel
    ): array {
        $defaultResponseAt = $createdAt->addMinutes(25 + (($index * 9) % 55));
        $defaultResponseAt = $responseDueAt ? $defaultResponseAt->min($responseDueAt->subMinutes(5)) : $defaultResponseAt;
        $assignedAt = $createdAt->addMinutes(20 + (($index * 7) % 45));
        $startedAt = $assignedAt->addMinutes(10 + (($index * 5) % 25));
        $safeCompletedAt = $startedAt->addHours(3 + ($index % 9));

        $completedOnTimeAt = $resolutionDueAt
            ? $safeCompletedAt->min($resolutionDueAt->subMinutes(20 + (($index * 3) % 40)))
            : $safeCompletedAt;

        if ($completedOnTimeAt->lte($startedAt)) {
            $completedOnTimeAt = $startedAt->addMinutes(45);
        }

        $completedBreachedAt = $resolutionDueAt
            ? $resolutionDueAt->addHours(2 + ($index % 8))
            : $startedAt->addHours(10 + ($index % 4));

        $respondedBreachedAt = $responseDueAt
            ? $responseDueAt->addMinutes(40 + (($index * 5) % 90))
            : $createdAt->addHours(3 + ($index % 3));

        $breachedResolutionAt = $resolutionDueAt && $completedBreachedAt->gt($resolutionDueAt) ? $resolutionDueAt : null;
        $breachedResponseAt = $responseDueAt && $respondedBreachedAt->gt($responseDueAt) ? $responseDueAt : null;

        return match ($state) {
            'completed_on_time' => [
                'initial_status_code' => 'NEW',
                'status_code' => $index % 2 === 0 ? 'CLOSED' : 'COMPLETED',
                'assignment_from_status_code' => 'NEW',
                'assignment_to_status_code' => 'ASSIGNED',
                'approval_status' => Ticket::APPROVAL_STATUS_APPROVED,
                'approval_requested_at' => null,
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => null,
                'rejected_by_email' => null,
                'approval_notes' => 'Diproses normal pada '.$profileLabel.'.',
                'assignment_ready_at' => $createdAt,
                'assignment_ready_by_email' => 'supervisor@demo.com',
                'assigned_at' => $assignedAt,
                'responded_at' => $defaultResponseAt,
                'breached_response_at' => null,
                'started_at' => $startedAt,
                'resolved_at' => $completedOnTimeAt,
                'completed_at' => $completedOnTimeAt,
                'breached_resolution_at' => null,
                'closed_at' => $index % 2 === 0 ? $completedOnTimeAt->addMinutes(40) : null,
                'last_status_changed_at' => $index % 2 === 0 ? $completedOnTimeAt->addMinutes(40) : $completedOnTimeAt,
                'sla_status' => Ticket::SLA_STATUS_ON_TIME,
                'updated_by_email' => 'supervisor@demo.com',
            ],
            'completed_breached' => [
                'initial_status_code' => 'NEW',
                'status_code' => $index % 2 === 0 ? 'CLOSED' : 'COMPLETED',
                'assignment_from_status_code' => 'NEW',
                'assignment_to_status_code' => 'ASSIGNED',
                'approval_status' => Ticket::APPROVAL_STATUS_APPROVED,
                'approval_requested_at' => null,
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => null,
                'rejected_by_email' => null,
                'approval_notes' => 'Penyelesaian memerlukan eskalasi tambahan pada '.$profileLabel.'.',
                'assignment_ready_at' => $createdAt,
                'assignment_ready_by_email' => 'supervisor@demo.com',
                'assigned_at' => $assignedAt,
                'responded_at' => $respondedBreachedAt,
                'breached_response_at' => $breachedResponseAt,
                'started_at' => $startedAt,
                'resolved_at' => $completedBreachedAt,
                'completed_at' => $completedBreachedAt,
                'breached_resolution_at' => $breachedResolutionAt,
                'closed_at' => $index % 2 === 0 ? $completedBreachedAt->addMinutes(55) : null,
                'last_status_changed_at' => $index % 2 === 0 ? $completedBreachedAt->addMinutes(55) : $completedBreachedAt,
                'sla_status' => Ticket::SLA_STATUS_BREACHED,
                'updated_by_email' => 'supervisor@demo.com',
            ],
            'assigned' => [
                'initial_status_code' => 'NEW',
                'status_code' => 'ASSIGNED',
                'assignment_from_status_code' => 'NEW',
                'assignment_to_status_code' => 'ASSIGNED',
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'approval_requested_at' => null,
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => null,
                'rejected_by_email' => null,
                'approval_notes' => null,
                'assignment_ready_at' => $createdAt,
                'assignment_ready_by_email' => 'supervisor@demo.com',
                'assigned_at' => $assignedAt,
                'responded_at' => $defaultResponseAt,
                'breached_response_at' => null,
                'started_at' => null,
                'resolved_at' => null,
                'completed_at' => null,
                'breached_resolution_at' => null,
                'closed_at' => null,
                'last_status_changed_at' => $assignedAt,
                'sla_status' => Ticket::SLA_STATUS_ON_TIME,
                'updated_by_email' => 'supervisor@demo.com',
            ],
            'in_progress' => [
                'initial_status_code' => 'NEW',
                'status_code' => 'IN_PROGRESS',
                'assignment_from_status_code' => 'NEW',
                'assignment_to_status_code' => 'ASSIGNED',
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'approval_requested_at' => null,
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => null,
                'rejected_by_email' => null,
                'approval_notes' => null,
                'assignment_ready_at' => $createdAt,
                'assignment_ready_by_email' => 'supervisor@demo.com',
                'assigned_at' => $assignedAt,
                'responded_at' => $profileLabel === 'periode aktif' && $index % 2 === 0 ? $respondedBreachedAt : $defaultResponseAt,
                'breached_response_at' => $profileLabel === 'periode aktif' && $index % 2 === 0 ? $breachedResponseAt : null,
                'started_at' => $startedAt,
                'resolved_at' => null,
                'completed_at' => null,
                'breached_resolution_at' => $resolutionDueAt && $resolutionDueAt->lt(CarbonImmutable::now()) ? $resolutionDueAt : null,
                'closed_at' => null,
                'last_status_changed_at' => $startedAt,
                'sla_status' => $resolutionDueAt && $resolutionDueAt->lt(CarbonImmutable::now())
                    ? Ticket::SLA_STATUS_BREACHED
                    : Ticket::SLA_STATUS_ON_TIME,
                'updated_by_email' => 'supervisor@demo.com',
            ],
            'pending_approval' => [
                'initial_status_code' => 'PENDING_APPROVAL',
                'status_code' => 'PENDING_APPROVAL',
                'assignment_from_status_code' => 'NEW',
                'assignment_to_status_code' => 'PENDING_APPROVAL',
                'approval_status' => Ticket::APPROVAL_STATUS_PENDING,
                'approval_requested_at' => $createdAt->addMinutes(10),
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => null,
                'rejected_by_email' => null,
                'approval_notes' => 'Masih menunggu persetujuan sesuai workflow layanan.',
                'assignment_ready_at' => null,
                'assignment_ready_by_email' => null,
                'assigned_at' => null,
                'responded_at' => null,
                'breached_response_at' => $responseDueAt && $responseDueAt->lt(CarbonImmutable::now()) ? $responseDueAt : null,
                'started_at' => null,
                'resolved_at' => null,
                'completed_at' => null,
                'breached_resolution_at' => null,
                'closed_at' => null,
                'last_status_changed_at' => $createdAt->addMinutes(10),
                'sla_status' => $responseDueAt && $responseDueAt->lt(CarbonImmutable::now())
                    ? Ticket::SLA_STATUS_BREACHED
                    : Ticket::SLA_STATUS_ON_TIME,
                'updated_by_email' => 'opsadmin@demo.com',
            ],
            'rejected' => [
                'initial_status_code' => 'PENDING_APPROVAL',
                'status_code' => 'REJECTED',
                'assignment_from_status_code' => 'PENDING_APPROVAL',
                'assignment_to_status_code' => 'REJECTED',
                'approval_status' => Ticket::APPROVAL_STATUS_REJECTED,
                'approval_requested_at' => $createdAt->addMinutes(12),
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => $createdAt->addHours(3),
                'rejected_by_email' => 'opsadmin@demo.com',
                'approval_notes' => 'Dokumen pendukung atau justifikasi kebutuhan belum lengkap.',
                'assignment_ready_at' => null,
                'assignment_ready_by_email' => null,
                'assigned_at' => null,
                'responded_at' => null,
                'breached_response_at' => null,
                'started_at' => null,
                'resolved_at' => null,
                'completed_at' => null,
                'breached_resolution_at' => null,
                'closed_at' => $createdAt->addHours(4),
                'last_status_changed_at' => $createdAt->addHours(4),
                'sla_status' => Ticket::SLA_STATUS_ON_TIME,
                'updated_by_email' => 'opsadmin@demo.com',
            ],
            default => [
                'initial_status_code' => 'NEW',
                'status_code' => 'NEW',
                'assignment_from_status_code' => 'NEW',
                'assignment_to_status_code' => 'NEW',
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'approval_requested_at' => null,
                'approved_at' => null,
                'approved_by_email' => null,
                'rejected_at' => null,
                'rejected_by_email' => null,
                'approval_notes' => null,
                'assignment_ready_at' => null,
                'assignment_ready_by_email' => null,
                'assigned_at' => null,
                'responded_at' => null,
                'breached_response_at' => $responseDueAt && $responseDueAt->lt(CarbonImmutable::now()) ? $responseDueAt : null,
                'started_at' => null,
                'resolved_at' => null,
                'completed_at' => null,
                'breached_resolution_at' => $resolutionDueAt && $resolutionDueAt->lt(CarbonImmutable::now()) ? $resolutionDueAt : null,
                'closed_at' => null,
                'last_status_changed_at' => $createdAt,
                'sla_status' => $resolutionDueAt && $resolutionDueAt->lt(CarbonImmutable::now())
                    ? Ticket::SLA_STATUS_BREACHED
                    : Ticket::SLA_STATUS_ON_TIME,
                'updated_by_email' => 'supervisor@demo.com',
            ],
        };
    }

    private function blueprintFor(array $profile, string $state, int $index): array
    {
        $approvalBlueprints = [
            [
                'detail_code' => 'TEMPORARY_ACCESS',
                'priority_code' => 'P3',
                'service_code' => 'SRV-CLOUD-GW',
                'asset_code' => null,
                'location_code' => 'LOC-JKT-001',
                'requester_pool' => ['dini.febrianti@demo.com', 'requester@demo.com'],
                'source_pool' => ['web'],
                'impact' => 'medium',
                'urgency' => 'medium',
                'assigned_team_name' => 'Security Access',
                'engineer_pool' => ['gilang.prasetyo@demo.com'],
                'subject' => 'Permintaan temporary access vendor proyek',
            ],
            [
                'detail_code' => 'PORT_ADDITION',
                'priority_code' => 'P3',
                'service_code' => 'SRV-ASSET-DEPLOY',
                'asset_code' => null,
                'location_code' => 'LOC-BKS-001',
                'requester_pool' => ['requester@demo.com', 'sarah.maharani@demo.com'],
                'source_pool' => ['web', 'internal'],
                'impact' => 'medium',
                'urgency' => 'medium',
                'assigned_team_name' => 'Field Operations',
                'engineer_pool' => ['engineer2@demo.com', 'engineer1@demo.com'],
                'subject' => 'Permintaan penambahan port dan patching baru',
            ],
            [
                'detail_code' => 'FIRMWARE_REVIEW',
                'priority_code' => 'P3',
                'service_code' => 'SRV-FIELD-MAINT',
                'asset_code' => 'AST-UPS-001',
                'location_code' => 'LOC-BKS-001',
                'requester_pool' => ['opsadmin@demo.com'],
                'source_pool' => ['internal'],
                'impact' => 'low',
                'urgency' => 'medium',
                'assigned_team_name' => 'Infrastructure Platform',
                'engineer_pool' => ['eko.saputra@demo.com'],
                'subject' => 'Review firmware perangkat lapangan',
            ],
        ];

        $operationalBlueprints = match ($profile['key']) {
            'PMO' => [
                [
                    'detail_code' => 'HEALTH_CHECK',
                    'priority_code' => 'P4',
                    'service_code' => 'SRV-FIELD-MAINT',
                    'asset_code' => 'AST-UPS-001',
                    'location_code' => 'LOC-BKS-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['internal'],
                    'impact' => 'low',
                    'urgency' => 'low',
                    'assigned_team_name' => 'Field Operations',
                    'engineer_pool' => ['engineer1@demo.com', 'eko.saputra@demo.com'],
                    'subject' => 'Preventive health check rack dan power',
                ],
                [
                    'detail_code' => 'NEW_DEVICE_INSTALL',
                    'priority_code' => 'P3',
                    'service_code' => 'SRV-ASSET-DEPLOY',
                    'asset_code' => null,
                    'location_code' => 'LOC-BDG-001',
                    'requester_pool' => ['requester@demo.com', 'sarah.maharani@demo.com'],
                    'source_pool' => ['web', 'internal'],
                    'impact' => 'medium',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Field Operations',
                    'engineer_pool' => ['engineer2@demo.com', 'engineer1@demo.com'],
                    'subject' => 'Permintaan instalasi perangkat tambahan',
                ],
                [
                    'detail_code' => 'MONITORING_ENABLEMENT',
                    'priority_code' => 'P3',
                    'service_code' => 'SRV-ACS-CONTROL',
                    'asset_code' => 'AST-ACS-001',
                    'location_code' => 'LOC-BDG-001',
                    'requester_pool' => ['sarah.maharani@demo.com'],
                    'source_pool' => ['web'],
                    'impact' => 'medium',
                    'urgency' => 'low',
                    'assigned_team_name' => 'Security Systems',
                    'engineer_pool' => ['gilang.prasetyo@demo.com'],
                    'subject' => 'Aktivasi monitoring perangkat access control',
                ],
                [
                    'detail_code' => 'WIRELESS_OUTAGE',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-WIFI-AREA',
                    'asset_code' => 'AST-AP-002',
                    'location_code' => 'LOC-CKR-001',
                    'requester_pool' => ['requester@demo.com', 'opsadmin@demo.com'],
                    'source_pool' => ['monitoring', 'web'],
                    'impact' => 'medium',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'NOC / Wireless',
                    'engineer_pool' => ['irfan.setiawan@demo.com', 'engineer1@demo.com'],
                    'subject' => 'Wireless access menurun di area operasi',
                ],
                [
                    'detail_code' => 'BATTERY_REPLACEMENT',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-UPS-POWER',
                    'asset_code' => 'AST-UPS-001',
                    'location_code' => 'LOC-BKS-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['inspection', 'internal'],
                    'impact' => 'medium',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Infrastructure Platform',
                    'engineer_pool' => ['eko.saputra@demo.com'],
                    'subject' => 'Penggantian baterai UPS menjelang end of life',
                ],
            ],
            'PYR' => [
                [
                    'detail_code' => 'BANDWIDTH_CONGESTION',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-WAN-UPLINK',
                    'asset_code' => null,
                    'location_code' => 'LOC-JKT-001',
                    'requester_pool' => ['opsadmin@demo.com', 'requester@demo.com'],
                    'source_pool' => ['monitoring'],
                    'impact' => 'high',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'NOC / Core Network',
                    'engineer_pool' => ['irfan.setiawan@demo.com'],
                    'subject' => 'Kepadatan bandwidth uplink pada jam sibuk',
                ],
                [
                    'detail_code' => 'CCTV_BLIND_SPOT',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-CCTV-MON',
                    'asset_code' => 'AST-CCTV-002',
                    'location_code' => 'LOC-MDN-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['monitoring', 'inspection'],
                    'impact' => 'high',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Security Systems',
                    'engineer_pool' => ['gilang.prasetyo@demo.com', 'engineer2@demo.com'],
                    'subject' => 'Blind spot CCTV perimeter site',
                ],
                [
                    'detail_code' => 'WIRELESS_OUTAGE',
                    'priority_code' => 'P1',
                    'service_code' => 'SRV-WIFI-AREA',
                    'asset_code' => 'AST-AP-002',
                    'location_code' => 'LOC-CKR-001',
                    'requester_pool' => ['requester@demo.com'],
                    'source_pool' => ['web', 'monitoring'],
                    'impact' => 'high',
                    'urgency' => 'high',
                    'assigned_team_name' => 'NOC / Wireless',
                    'engineer_pool' => ['irfan.setiawan@demo.com', 'engineer1@demo.com'],
                    'subject' => 'Wireless area operasi mengalami putus total',
                ],
                [
                    'detail_code' => 'BATTERY_REPLACEMENT',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-UPS-POWER',
                    'asset_code' => 'AST-UPS-001',
                    'location_code' => 'LOC-BKS-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['inspection'],
                    'impact' => 'medium',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Infrastructure Platform',
                    'engineer_pool' => ['eko.saputra@demo.com'],
                    'subject' => 'Penggantian baterai UPS yang menurun',
                ],
                [
                    'detail_code' => 'HEALTH_CHECK',
                    'priority_code' => 'P4',
                    'service_code' => 'SRV-FIELD-MAINT',
                    'asset_code' => 'AST-UPS-001',
                    'location_code' => 'LOC-BDG-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['internal'],
                    'impact' => 'low',
                    'urgency' => 'low',
                    'assigned_team_name' => 'Field Operations',
                    'engineer_pool' => ['engineer1@demo.com'],
                    'subject' => 'Health check rutin peralatan field',
                ],
            ],
            default => [
                [
                    'detail_code' => 'WIRELESS_OUTAGE',
                    'priority_code' => 'P1',
                    'service_code' => 'SRV-WIFI-AREA',
                    'asset_code' => 'AST-AP-002',
                    'location_code' => 'LOC-CKR-001',
                    'requester_pool' => ['requester@demo.com', 'opsadmin@demo.com'],
                    'source_pool' => ['monitoring', 'web'],
                    'impact' => 'high',
                    'urgency' => 'high',
                    'assigned_team_name' => 'NOC / Wireless',
                    'engineer_pool' => ['irfan.setiawan@demo.com', 'engineer1@demo.com'],
                    'subject' => 'Wireless area bongkar muat tidak stabil',
                ],
                [
                    'detail_code' => 'BANDWIDTH_CONGESTION',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-WAN-UPLINK',
                    'asset_code' => null,
                    'location_code' => 'LOC-JKT-001',
                    'requester_pool' => ['opsadmin@demo.com', 'requester@demo.com'],
                    'source_pool' => ['monitoring'],
                    'impact' => 'high',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'NOC / Core Network',
                    'engineer_pool' => ['irfan.setiawan@demo.com'],
                    'subject' => 'Lonjakan utilisasi uplink dan trafik peak hour',
                ],
                [
                    'detail_code' => 'CCTV_BLIND_SPOT',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-CCTV-MON',
                    'asset_code' => 'AST-CCTV-002',
                    'location_code' => 'LOC-MDN-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['monitoring', 'inspection'],
                    'impact' => 'high',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Security Systems',
                    'engineer_pool' => ['gilang.prasetyo@demo.com', 'engineer2@demo.com'],
                    'subject' => 'Blind spot CCTV perimeter masih muncul',
                ],
                [
                    'detail_code' => 'ABNORMAL_INSPECTION',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-CCTV-MON',
                    'asset_code' => 'AST-CCTV-002',
                    'location_code' => 'LOC-MDN-001',
                    'requester_pool' => ['inspector@demo.com'],
                    'source_pool' => ['inspection'],
                    'impact' => 'high',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Field Operations',
                    'engineer_pool' => ['engineer2@demo.com', 'gilang.prasetyo@demo.com'],
                    'subject' => 'Follow up temuan abnormal inspection site',
                ],
                [
                    'detail_code' => 'BATTERY_REPLACEMENT',
                    'priority_code' => 'P2',
                    'service_code' => 'SRV-UPS-POWER',
                    'asset_code' => 'AST-UPS-001',
                    'location_code' => 'LOC-BKS-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['inspection', 'internal'],
                    'impact' => 'medium',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Infrastructure Platform',
                    'engineer_pool' => ['eko.saputra@demo.com'],
                    'subject' => 'Penggantian baterai UPS dan verifikasi load',
                ],
                [
                    'detail_code' => 'HEALTH_CHECK',
                    'priority_code' => 'P4',
                    'service_code' => 'SRV-FIELD-MAINT',
                    'asset_code' => 'AST-UPS-001',
                    'location_code' => 'LOC-BKS-001',
                    'requester_pool' => ['opsadmin@demo.com'],
                    'source_pool' => ['internal'],
                    'impact' => 'low',
                    'urgency' => 'low',
                    'assigned_team_name' => 'Field Operations',
                    'engineer_pool' => ['engineer1@demo.com', 'eko.saputra@demo.com'],
                    'subject' => 'Preventive health check perangkat pendukung',
                ],
                [
                    'detail_code' => 'MONITORING_ENABLEMENT',
                    'priority_code' => 'P3',
                    'service_code' => 'SRV-ACS-CONTROL',
                    'asset_code' => 'AST-ACS-001',
                    'location_code' => 'LOC-BDG-001',
                    'requester_pool' => ['sarah.maharani@demo.com'],
                    'source_pool' => ['web'],
                    'impact' => 'medium',
                    'urgency' => 'low',
                    'assigned_team_name' => 'Security Systems',
                    'engineer_pool' => ['gilang.prasetyo@demo.com'],
                    'subject' => 'Aktivasi monitoring access control site',
                ],
                [
                    'detail_code' => 'NEW_DEVICE_INSTALL',
                    'priority_code' => 'P3',
                    'service_code' => 'SRV-ASSET-DEPLOY',
                    'asset_code' => null,
                    'location_code' => 'LOC-BDG-001',
                    'requester_pool' => ['requester@demo.com', 'sarah.maharani@demo.com'],
                    'source_pool' => ['web', 'internal'],
                    'impact' => 'medium',
                    'urgency' => 'medium',
                    'assigned_team_name' => 'Field Operations',
                    'engineer_pool' => ['engineer2@demo.com', 'engineer1@demo.com'],
                    'subject' => 'Permintaan instalasi perangkat tambahan',
                ],
            ],
        };

        $pool = in_array($state, ['pending_approval', 'rejected'], true) ? $approvalBlueprints : $operationalBlueprints;

        return $pool[$index % count($pool)];
    }

    private function ticketTitle(array $blueprint, array $context, int $index): string
    {
        $locationName = $context['location_names'][$blueprint['location_code']] ?? $blueprint['location_code'];

        return $blueprint['subject'].' - '.$locationName.' #'.str_pad((string) (($index % 9) + 1), 2, '0', STR_PAD_LEFT);
    }

    private function ticketDescription(array $blueprint, array $context, string $profileLabel): string
    {
        $locationName = $context['location_names'][$blueprint['location_code']] ?? $blueprint['location_code'];

        return 'Ticket historis demo untuk '.$profileLabel.' yang menggambarkan beban operasional di '.$locationName
            .'. Data ini disiapkan agar executive report menampilkan perubahan kualitas layanan, tekanan operasional, dan distribusi workload engineer secara lebih nyata.';
    }

    private function inspectionSummaryNotes(string $templateCode, string $profileLabel, bool $isAbnormal): string
    {
        $subject = match ($templateCode) {
            'INSP-CCTV-WEEKLY' => 'pemeriksaan CCTV',
            'INSP-UPS-WEEKLY' => 'pemeriksaan UPS',
            default => 'pemeriksaan wireless',
        };

        return $isAbnormal
            ? 'Hasil '.$subject.' pada '.$profileLabel.' menunjukkan anomali yang butuh follow up teknis.'
            : 'Hasil '.$subject.' pada '.$profileLabel.' masih berada dalam batas operasional yang diterima.';
    }
}
