<?php

namespace App\Modules\Inspections\Inspections;

use App\Models\Inspection;
use App\Models\InspectionEvidence;
use App\Models\InspectionItem;
use App\Models\InspectionTemplate;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Modules\Tickets\Tickets\TicketService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InspectionService
{
    private const OPS_ROLES = ['super_admin', 'operational_admin', 'supervisor'];

    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function paginateMyInspections(User $officer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $dueOnly = (bool) ($filters['due_only'] ?? true);

        return Inspection::query()
            ->with([
                'template:id,name',
                'asset:id,name',
                'assetLocation:id,name',
                'officer:id,name',
                'scheduledBy:id,name',
                'ticket:id,inspection_id,ticket_number',
            ])
            ->where('inspection_officer_id', $officer->id)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('inspection_number', 'like', "%{$search}%")
                        ->orWhereHas('asset', fn ($assetQuery) => $assetQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('status', $status))
            ->when($status === null || $status === '', fn ($query) => $query->whereIn('status', [Inspection::STATUS_DRAFT, Inspection::STATUS_IN_PROGRESS]))
            ->when($dueOnly && ($status === null || $status === ''), function ($query): void {
                $today = now()->toDateString();
                $query->where(function ($subQuery) use ($today): void {
                    $subQuery
                        ->where('status', Inspection::STATUS_IN_PROGRESS)
                        ->orWhereDate('inspection_date', '<=', $today);
                });
            })
            ->when($filters['inspection_date'] ?? null, fn ($query, $inspectionDate) => $query->whereDate('inspection_date', $inspectionDate))
            ->orderBy('inspection_date')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateInspectionTasksForOps(User $actor, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (! $this->isOpsRole($actor)) {
            throw new AccessDeniedHttpException('You are not allowed to access inspection task management.');
        }

        $status = $filters['status'] ?? null;

        return Inspection::query()
            ->with([
                'template:id,name',
                'asset:id,name',
                'assetLocation:id,name',
                'officer:id,name',
                'scheduledBy:id,name',
                'ticket:id,inspection_id,ticket_number',
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('inspection_number', 'like', "%{$search}%")
                        ->orWhereHas('asset', fn ($assetQuery) => $assetQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('officer', fn ($officerQuery) => $officerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('status', $status))
            ->when($status === null || $status === '', fn ($query) => $query->whereIn('status', [Inspection::STATUS_DRAFT, Inspection::STATUS_IN_PROGRESS]))
            ->when($filters['inspection_officer_id'] ?? null, fn ($query, $officerId) => $query->where('inspection_officer_id', $officerId))
            ->when($filters['inspection_date'] ?? null, fn ($query, $inspectionDate) => $query->whereDate('inspection_date', $inspectionDate))
            ->when($filters['schedule_type'] ?? null, fn ($query, $scheduleType) => $query->where('schedule_type', $scheduleType))
            ->orderBy('inspection_date')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateInspectionResults(User $actor, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->inspectionResultBaseQuery($actor, $filters)
            ->orderByDesc('inspection_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summarizeInspectionResults(User $actor, array $filters = []): array
    {
        $query = $this->inspectionResultBaseQuery($actor, $filters);

        return [
            'total' => (clone $query)->count(),
            'submitted' => (clone $query)->where('status', Inspection::STATUS_SUBMITTED)->count(),
            'normal' => (clone $query)->where('final_result', Inspection::FINAL_RESULT_NORMAL)->count(),
            'abnormal' => (clone $query)->where('final_result', Inspection::FINAL_RESULT_ABNORMAL)->count(),
            'with_ticket' => (clone $query)->whereHas('ticket')->count(),
            'without_ticket' => (clone $query)->doesntHave('ticket')->count(),
        ];
    }

    public function resolveInspectionResultDetail(Inspection $inspection, User $actor): Inspection
    {
        $this->ensureCanViewInspectionResult($inspection, $actor);

        return $inspection->load($this->inspectionResultRelations());
    }

    public function createForOfficer(array $data, User $officer): Inspection
    {
        return DB::transaction(function () use ($data, $officer): Inspection {
            /** @var InspectionTemplate $template */
            $template = InspectionTemplate::query()
                ->with(['items' => fn ($query) => $query->where('is_active', true)->orderBy('sequence')])
                ->findOrFail($data['inspection_template_id']);

            $assignedOfficerId = (int) ($data['inspection_officer_id'] ?? $officer->id);
            $inspectionDate = CarbonImmutable::parse((string) $data['inspection_date'])->startOfDay();
            $schedulePayload = $this->normalizeSchedulePayload($data, $inspectionDate);

            $inspection = Inspection::query()->create([
                'inspection_number' => $this->generateInspectionNumber(),
                'inspection_template_id' => $template->id,
                'asset_id' => $data['asset_id'] ?? null,
                'asset_location_id' => $data['asset_location_id'] ?? null,
                'inspection_officer_id' => $assignedOfficerId,
                'scheduled_by_id' => $officer->id,
                'inspection_date' => $inspectionDate->toDateString(),
                'schedule_next_date' => $schedulePayload['schedule_next_date'],
                'status' => Inspection::STATUS_DRAFT,
                'schedule_type' => $schedulePayload['schedule_type'],
                'schedule_interval' => $schedulePayload['schedule_interval'],
                'schedule_weekdays' => $schedulePayload['schedule_weekdays'],
                'summary_notes' => $data['summary_notes'] ?? null,
                'created_by_id' => $officer->id,
                'updated_by_id' => $officer->id,
            ]);

            foreach ($template->items as $templateItem) {
                $inspection->items()->create([
                    'inspection_template_item_id' => $templateItem->id,
                    'sequence' => $templateItem->sequence,
                    'item_label' => $templateItem->item_label,
                    'item_type' => $templateItem->item_type,
                    'expected_value' => $templateItem->expected_value,
                ]);
            }

            return $inspection->fresh($this->inspectionRelations());
        });
    }

    public function createAndSubmitPublic(array $data, User $officer, mixed $supportingFiles = []): Inspection
    {
        return DB::transaction(function () use ($data, $officer, $supportingFiles): Inspection {
            /** @var InspectionTemplate $template */
            $template = InspectionTemplate::query()
                ->with(['items' => fn ($query) => $query->where('is_active', true)->orderBy('sequence')])
                ->findOrFail($data['inspection_template_id']);

            $payloadByTemplateItemId = collect($data['items'] ?? [])
                ->keyBy(fn (array $item): int => (int) $item['inspection_template_item_id']);

            $reporterName = trim((string) ($data['reporter_name'] ?? 'Public Reporter'));
            $reporterEmail = trim((string) ($data['reporter_email'] ?? '-'));
            $reporterInfo = "Reporter: {$reporterName} ({$reporterEmail})";
            $summaryNotes = trim((string) ($data['summary_notes'] ?? ''));
            $combinedSummary = $summaryNotes !== '' ? "{$summaryNotes}\n\n{$reporterInfo}" : $reporterInfo;
            $finalResult = (string) ($data['final_result'] ?? Inspection::FINAL_RESULT_NORMAL);
            $normalizedFiles = $this->normalizeSupportingFiles($supportingFiles);

            if ($finalResult === Inspection::FINAL_RESULT_ABNORMAL && count($normalizedFiles) === 0) {
                throw ValidationException::withMessages([
                    'supporting_files' => 'File pendukung wajib diunggah jika hasil akhir Abnormal.',
                ]);
            }

            $inspection = Inspection::query()->create([
                'inspection_number' => $this->generateInspectionNumber(),
                'inspection_template_id' => $template->id,
                'asset_id' => $data['asset_id'] ?? null,
                'asset_location_id' => $data['asset_location_id'] ?? null,
                'inspection_officer_id' => $officer->id,
                'inspection_date' => $data['inspection_date'],
                'status' => Inspection::STATUS_SUBMITTED,
                'final_result' => $finalResult,
                'started_at' => now(),
                'submitted_at' => now(),
                'summary_notes' => $combinedSummary,
                'created_by_id' => $officer->id,
                'updated_by_id' => $officer->id,
            ]);

            foreach ($template->items as $templateItem) {
                $itemPayload = $payloadByTemplateItemId->get((int) $templateItem->id, []);

                $inspection->items()->create([
                    'inspection_template_item_id' => $templateItem->id,
                    'sequence' => $templateItem->sequence,
                    'item_label' => $templateItem->item_label,
                    'item_type' => $templateItem->item_type,
                    'expected_value' => $templateItem->expected_value,
                    'result_status' => $itemPayload['result_status'] ?? null,
                    'result_value' => $itemPayload['result_value'] ?? null,
                    'notes' => $itemPayload['notes'] ?? null,
                    'checked_at' => now(),
                    'checked_by_id' => $officer->id,
                ]);
            }

            foreach ($normalizedFiles as $file) {
                $this->createEvidenceRecord($inspection, $officer, $file);
            }

            $this->autoCreateAbnormalTicketIfNeeded($inspection, $officer);

            return $inspection->fresh($this->inspectionRelations());
        });
    }

    public function ensureOwnedByOfficer(Inspection $inspection, User $officer): void
    {
        if ((int) $inspection->inspection_officer_id !== (int) $officer->id) {
            throw new AccessDeniedHttpException('Inspection is not assigned to this officer.');
        }
    }

    public function updateItems(Inspection $inspection, User $officer, array $items): Inspection
    {
        $this->ensureOwnedByOfficer($inspection, $officer);

        if ($inspection->status === Inspection::STATUS_SUBMITTED) {
            throw ValidationException::withMessages([
                'status' => 'Inspection yang sudah disubmit tidak dapat diubah lagi.',
            ]);
        }

        DB::transaction(function () use ($inspection, $officer, $items): void {
            foreach ($items as $itemPayload) {
                /** @var InspectionItem $inspectionItem */
                $inspectionItem = InspectionItem::query()
                    ->where('inspection_id', $inspection->id)
                    ->whereKey($itemPayload['id'])
                    ->firstOrFail();

                $inspectionItem->update([
                    'result_status' => $itemPayload['result_status'] ?? null,
                    'result_value' => $itemPayload['result_value'] ?? null,
                    'notes' => $itemPayload['notes'] ?? null,
                    'checked_at' => now(),
                    'checked_by_id' => $officer->id,
                ]);
            }

            $inspection->update([
                'status' => Inspection::STATUS_IN_PROGRESS,
                'started_at' => $inspection->started_at ?? now(),
                'updated_by_id' => $officer->id,
            ]);
        });

        return $inspection->fresh($this->inspectionRelations());
    }

    public function submit(
        Inspection $inspection,
        User $officer,
        string $finalResult,
        ?string $summaryNotes = null,
        mixed $supportingFiles = [],
    ): Inspection
    {
        $this->ensureOwnedByOfficer($inspection, $officer);

        if ($inspection->status === Inspection::STATUS_SUBMITTED) {
            throw ValidationException::withMessages([
                'status' => 'Inspection ini sudah pernah disubmit.',
            ]);
        }

        $normalizedFiles = $this->normalizeSupportingFiles($supportingFiles);
        foreach ($normalizedFiles as $file) {
            $this->createEvidenceRecord($inspection, $officer, $file);
        }

        if ($finalResult === Inspection::FINAL_RESULT_ABNORMAL && $inspection->evidences()->count() === 0) {
            throw ValidationException::withMessages([
                'supporting_files' => 'File pendukung wajib diunggah jika hasil akhir Abnormal.',
            ]);
        }

        $inspection->update([
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => $finalResult,
            'started_at' => $inspection->started_at ?? now(),
            'submitted_at' => now(),
            'summary_notes' => $summaryNotes ?? $inspection->summary_notes,
            'updated_by_id' => $officer->id,
        ]);

        $this->autoCreateAbnormalTicketIfNeeded($inspection, $officer);
        $this->createNextScheduledInspectionIfNeeded($inspection, $officer);

        return $inspection->fresh($this->inspectionRelations());
    }

    public function addEvidence(
        Inspection $inspection,
        User $officer,
        UploadedFile $file,
        ?string $notes = null,
        ?int $inspectionItemId = null,
    ): InspectionEvidence {
        $this->ensureOwnedByOfficer($inspection, $officer);

        if ($inspectionItemId !== null) {
            InspectionItem::query()
                ->where('inspection_id', $inspection->id)
                ->whereKey($inspectionItemId)
                ->firstOrFail();
        }

        return $this->createEvidenceRecord($inspection, $officer, $file, $notes, $inspectionItemId);
    }

    public function deleteEvidence(InspectionEvidence $evidence): void
    {
        if (Storage::disk('public')->exists($evidence->file_path)) {
            Storage::disk('public')->delete($evidence->file_path);
        }

        $evidence->delete();
    }

    private function generateInspectionNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $base = "INSP-{$datePrefix}";

        $lastInspectionNumber = Inspection::query()
            ->where('inspection_number', 'like', "{$base}-%")
            ->orderByDesc('inspection_number')
            ->value('inspection_number');

        $lastSequence = 0;
        if (is_string($lastInspectionNumber)) {
            $segments = explode('-', $lastInspectionNumber);
            $lastSequence = (int) end($segments);
        }

        $nextSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

        return "{$base}-{$nextSequence}";
    }

    public function inspectionRelations(): array
    {
        return [
            'template:id,name',
            'asset:id,name,service_id,department_owner_id',
            'assetLocation:id,name',
            'officer:id,name',
            'scheduledBy:id,name',
            'ticket:id,inspection_id,ticket_number,ticket_status_id',
            'items.checkedBy:id,name',
            'evidences.uploadedBy:id,name',
        ];
    }

    public function inspectionResultRelations(): array
    {
        return [
            'template:id,code,name',
            'asset:id,code,name,asset_category_id,service_id,department_owner_id,vendor_id,asset_status_id,criticality',
            'asset.category:id,name',
            'asset.service:id,name',
            'asset.ownerDepartment:id,name',
            'asset.vendor:id,name',
            'asset.status:id,name',
            'assetLocation:id,code,name',
            'officer:id,name,email',
            'scheduledBy:id,name,email',
            'ticket:id,inspection_id,ticket_number,ticket_status_id',
            'ticket.status:id,name,code',
            'items.checkedBy:id,name',
            'evidences.inspectionItem:id,item_label',
            'evidences.uploadedBy:id,name',
        ];
    }

    private function inspectionResultBaseQuery(User $actor, array $filters = []): Builder
    {
        $query = Inspection::query()
            ->with([
                'template:id,name',
                'asset:id,name',
                'assetLocation:id,name',
                'officer:id,name',
                'ticket:id,inspection_id,ticket_number',
            ])
            ->withCount([
                'items',
                'items as pass_items_count' => fn (Builder $builder) => $builder->where('result_status', 'pass'),
                'items as fail_items_count' => fn (Builder $builder) => $builder->where('result_status', 'fail'),
                'items as na_items_count' => fn (Builder $builder) => $builder->where('result_status', 'na'),
                'evidences',
            ]);

        $this->applyInspectionAccessScope($query, $actor);

        $query
            ->when($filters['search'] ?? null, function (Builder $builder, string $search): void {
                $builder->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('inspection_number', 'like', "%{$search}%")
                        ->orWhere('summary_notes', 'like', "%{$search}%")
                        ->orWhereHas('asset', fn (Builder $assetQuery) => $assetQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('template', fn (Builder $templateQuery) => $templateQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('officer', fn (Builder $officerQuery) => $officerQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('ticket', fn (Builder $ticketQuery) => $ticketQuery->where('ticket_number', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when(
                $filters['final_result'] ?? null,
                fn (Builder $builder, string $finalResult) => $builder->where('final_result', $finalResult)
            )
            ->when(
                $filters['inspection_officer_id'] ?? null,
                fn (Builder $builder, int|string $officerId) => $builder->where('inspection_officer_id', $officerId)
            )
            ->when(
                $filters['inspection_date_from'] ?? null,
                fn (Builder $builder, string $dateFrom) => $builder->whereDate('inspection_date', '>=', $dateFrom)
            )
            ->when(
                $filters['inspection_date_to'] ?? null,
                fn (Builder $builder, string $dateTo) => $builder->whereDate('inspection_date', '<=', $dateTo)
            )
            ->when($filters['has_ticket'] ?? null, function (Builder $builder, string $hasTicket): void {
                if ($hasTicket === 'yes') {
                    $builder->whereHas('ticket');
                }

                if ($hasTicket === 'no') {
                    $builder->doesntHave('ticket');
                }
            });

        return $query;
    }

    private function applyInspectionAccessScope(Builder $query, User $actor): void
    {
        if ($this->isOpsRole($actor)) {
            return;
        }

        if (in_array($actor->role, ['inspection_officer', 'engineer'], true)) {
            $query->where('inspection_officer_id', $actor->id);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function ensureCanViewInspectionResult(Inspection $inspection, User $actor): void
    {
        if ($this->isOpsRole($actor)) {
            return;
        }

        if (in_array($actor->role, ['inspection_officer', 'engineer'], true)
            && (int) $inspection->inspection_officer_id === (int) $actor->id) {
            return;
        }

        throw new AccessDeniedHttpException('You are not allowed to access this inspection result.');
    }

    private function autoCreateAbnormalTicketIfNeeded(Inspection $inspection, User $officer): void
    {
        $inspection->refresh();

        if ($inspection->final_result !== Inspection::FINAL_RESULT_ABNORMAL) {
            return;
        }

        $existingTicket = Ticket::query()
            ->where('inspection_id', $inspection->id)
            ->exists();

        if ($existingTicket) {
            return;
        }

        $inspection->loadMissing([
            'template:id,name',
            'asset:id,name,service_id,department_owner_id',
            'assetLocation:id,name',
        ]);

        $priority = TicketPriority::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->first();

        $category = TicketCategory::query()
            ->where('is_active', true)
            ->where('code', 'INCIDENT')
            ->first()
            ?? TicketCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->first();

        $title = "Abnormal Inspection {$inspection->inspection_number}";
        $description = implode("\n", array_filter([
            'Ticket ini dibuat otomatis dari hasil inspeksi abnormal.',
            "Inspection Number: {$inspection->inspection_number}",
            'Inspection Date: '.$inspection->inspection_date?->format('Y-m-d'),
            'Template: '.($inspection->template?->name ?? '-'),
            'Asset: '.($inspection->asset?->name ?? '-'),
            'Location: '.($inspection->assetLocation?->name ?? '-'),
            'Summary: '.($inspection->summary_notes ?: '-'),
        ]));

        $this->ticketService->create([
            'title' => Str::limit($title, 200),
            'description' => $description,
            'requester_id' => $inspection->inspection_officer_id ?? $officer->id,
            'requester_department_id' => $officer->department_id ?? $inspection->asset?->department_owner_id,
            'ticket_category_id' => $category?->id,
            'ticket_priority_id' => $priority?->id,
            'service_id' => $inspection->asset?->service_id,
            'asset_id' => $inspection->asset_id,
            'asset_location_id' => $inspection->asset_location_id,
            'inspection_id' => $inspection->id,
            'source' => 'inspection_auto',
            'impact' => 'high',
            'urgency' => 'high',
            'sla_policy_name' => 'AUTO_ABNORMAL_INSPECTION',
        ], $officer);
    }

    private function createNextScheduledInspectionIfNeeded(Inspection $inspection, User $actor): void
    {
        $scheduleType = (string) ($inspection->schedule_type ?? Inspection::SCHEDULE_TYPE_NONE);
        if ($scheduleType === Inspection::SCHEDULE_TYPE_NONE) {
            return;
        }

        $baseDate = CarbonImmutable::parse((string) $inspection->inspection_date)->startOfDay();
        $nextDate = $this->resolveNextInspectionDate(
            fromDate: $baseDate,
            scheduleType: $scheduleType,
            scheduleInterval: (int) ($inspection->schedule_interval ?? 1),
            scheduleWeekdays: is_array($inspection->schedule_weekdays) ? $inspection->schedule_weekdays : null,
        );

        if ($nextDate === null) {
            return;
        }

        $existingChild = Inspection::query()
            ->where('parent_inspection_id', $inspection->id)
            ->whereDate('inspection_date', $nextDate->toDateString())
            ->exists();

        if ($existingChild) {
            return;
        }

        $inspection->loadMissing(['items']);

        $nextScheduleDate = $this->resolveNextInspectionDate(
            fromDate: $nextDate,
            scheduleType: $scheduleType,
            scheduleInterval: (int) ($inspection->schedule_interval ?? 1),
            scheduleWeekdays: is_array($inspection->schedule_weekdays) ? $inspection->schedule_weekdays : null,
        );

        $nextInspection = Inspection::query()->create([
            'inspection_number' => $this->generateInspectionNumber(),
            'inspection_template_id' => $inspection->inspection_template_id,
            'asset_id' => $inspection->asset_id,
            'asset_location_id' => $inspection->asset_location_id,
            'inspection_officer_id' => $inspection->inspection_officer_id,
            'scheduled_by_id' => $inspection->scheduled_by_id ?? $actor->id,
            'inspection_date' => $nextDate->toDateString(),
            'schedule_next_date' => $nextScheduleDate?->toDateString(),
            'status' => Inspection::STATUS_DRAFT,
            'schedule_type' => $scheduleType,
            'schedule_interval' => (int) ($inspection->schedule_interval ?? 1),
            'schedule_weekdays' => $inspection->schedule_weekdays,
            'final_result' => null,
            'started_at' => null,
            'submitted_at' => null,
            'summary_notes' => null,
            'parent_inspection_id' => $inspection->id,
            'created_by_id' => $actor->id,
            'updated_by_id' => $actor->id,
        ]);

        foreach ($inspection->items as $item) {
            $nextInspection->items()->create([
                'inspection_template_item_id' => $item->inspection_template_item_id,
                'sequence' => $item->sequence,
                'item_label' => $item->item_label,
                'item_type' => $item->item_type,
                'expected_value' => $item->expected_value,
            ]);
        }
    }

    private function normalizeSchedulePayload(array $data, CarbonImmutable $inspectionDate): array
    {
        $scheduleType = strtolower((string) ($data['schedule_type'] ?? Inspection::SCHEDULE_TYPE_NONE));
        if (! in_array($scheduleType, [Inspection::SCHEDULE_TYPE_NONE, Inspection::SCHEDULE_TYPE_DAILY, Inspection::SCHEDULE_TYPE_WEEKLY], true)) {
            $scheduleType = Inspection::SCHEDULE_TYPE_NONE;
        }

        $scheduleInterval = max(1, (int) ($data['schedule_interval'] ?? 1));
        $scheduleWeekdays = null;

        if ($scheduleType === Inspection::SCHEDULE_TYPE_WEEKLY) {
            $scheduleWeekdays = collect($data['schedule_weekdays'] ?? [])
                ->map(fn ($day) => (int) $day)
                ->filter(fn (int $day) => $day >= 1 && $day <= 7)
                ->unique()
                ->sort()
                ->values()
                ->all();

            if ($scheduleWeekdays === []) {
                $scheduleWeekdays = [$inspectionDate->dayOfWeekIso];
            }
        }

        if ($scheduleType === Inspection::SCHEDULE_TYPE_NONE) {
            $scheduleInterval = 1;
        }

        $nextDate = $this->resolveNextInspectionDate(
            fromDate: $inspectionDate,
            scheduleType: $scheduleType,
            scheduleInterval: $scheduleInterval,
            scheduleWeekdays: $scheduleWeekdays,
        );

        return [
            'schedule_type' => $scheduleType,
            'schedule_interval' => $scheduleInterval,
            'schedule_weekdays' => $scheduleWeekdays,
            'schedule_next_date' => $nextDate?->toDateString(),
        ];
    }

    private function resolveNextInspectionDate(
        CarbonImmutable $fromDate,
        string $scheduleType,
        int $scheduleInterval,
        ?array $scheduleWeekdays = null,
    ): ?CarbonImmutable {
        if ($scheduleType === Inspection::SCHEDULE_TYPE_NONE) {
            return null;
        }

        if ($scheduleType === Inspection::SCHEDULE_TYPE_DAILY) {
            return $fromDate->addDays(max(1, $scheduleInterval));
        }

        if ($scheduleType === Inspection::SCHEDULE_TYPE_WEEKLY) {
            $weekdays = collect($scheduleWeekdays ?? [])
                ->map(fn ($day) => (int) $day)
                ->filter(fn (int $day) => $day >= 1 && $day <= 7)
                ->unique()
                ->sort()
                ->values()
                ->all();

            if ($weekdays === []) {
                $weekdays = [$fromDate->dayOfWeekIso];
            }

            $currentWeekday = $fromDate->dayOfWeekIso;
            foreach ($weekdays as $weekday) {
                if ($weekday > $currentWeekday) {
                    return $fromDate->addDays($weekday - $currentWeekday);
                }
            }

            $firstWeekday = $weekdays[0];
            $daysToNextCycle = $firstWeekday - $currentWeekday + (7 * max(1, $scheduleInterval));

            return $fromDate->addDays($daysToNextCycle);
        }

        return null;
    }

    private function isOpsRole(User $actor): bool
    {
        return in_array($actor->role, self::OPS_ROLES, true);
    }

    private function normalizeSupportingFiles(mixed $supportingFiles): array
    {
        if ($supportingFiles instanceof UploadedFile) {
            return [$supportingFiles];
        }

        if (is_array($supportingFiles)) {
            return array_values(array_filter($supportingFiles, fn ($file) => $file instanceof UploadedFile));
        }

        return [];
    }

    private function createEvidenceRecord(
        Inspection $inspection,
        User $officer,
        UploadedFile $file,
        ?string $notes = null,
        ?int $inspectionItemId = null,
    ): InspectionEvidence {
        $storedPath = $file->store('inspection-evidences', 'public');

        return InspectionEvidence::query()->create([
            'inspection_id' => $inspection->id,
            'inspection_item_id' => $inspectionItemId,
            'uploaded_by_id' => $officer->id,
            'file_path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'notes' => $notes,
        ])->fresh(['uploadedBy:id,name']);
    }
}
