<?php

namespace App\Modules\Tickets\Monitoring;

use App\Models\MonitoringEvent;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Modules\Tickets\Tickets\TicketService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonitoringEventService
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->orderByDesc('occurred_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?User $actor = null): MonitoringEvent
    {
        return DB::transaction(function () use ($data, $actor): MonitoringEvent {
            $payload = $this->preparePayload($data, $actor);
            $event = null;

            if (! empty($payload['deduplication_key'])) {
                $event = MonitoringEvent::query()
                    ->where('deduplication_key', $payload['deduplication_key'])
                    ->where('status', MonitoringEvent::STATUS_OPEN)
                    ->latest('occurred_at')
                    ->lockForUpdate()
                    ->first();
            }

            if ($event !== null) {
                $event->update([
                    'severity' => $payload['severity'],
                    'message' => $payload['message'],
                    'details' => $payload['details'] ?? $event->details,
                    'occurred_at' => $payload['occurred_at'],
                    'last_seen_at' => $payload['occurred_at'],
                    'duplicate_count' => $event->duplicate_count + 1,
                    'updated_by_id' => $actor?->id,
                ]);
            } else {
                $event = MonitoringEvent::query()->create([
                    ...$payload,
                    'event_number' => $this->generateEventNumber(),
                    'status' => MonitoringEvent::STATUS_OPEN,
                    'duplicate_count' => 1,
                    'last_seen_at' => $payload['occurred_at'],
                    'created_by_id' => $actor?->id,
                    'updated_by_id' => $actor?->id,
                ]);
            }

            if (($data['auto_create_incident'] ?? false) && $this->canAutoConvert($event)) {
                $event = $this->convertToIncident($event, $actor);
            }

            return $event->fresh($this->relations());
        });
    }

    public function convertToIncident(MonitoringEvent $event, ?User $actor = null): MonitoringEvent
    {
        if ($event->status === MonitoringEvent::STATUS_CONVERTED) {
            return $event->fresh($this->relations());
        }

        if ($event->status !== MonitoringEvent::STATUS_OPEN) {
            throw ValidationException::withMessages([
                'status' => ['Only open monitoring event can be converted to incident.'],
            ]);
        }

        return DB::transaction(function () use ($event, $actor): MonitoringEvent {
            $mappedPriority = $this->severityToImpactUrgency($event->severity);
            $ticket = $this->ticketService->create([
                'title' => 'Monitoring event: '.$event->message,
                'description' => trim(collect([
                    'Source: '.$event->source,
                    'Severity: '.$event->severityLabel(),
                    'Occurred At: '.optional($event->occurred_at)->format('Y-m-d H:i:s'),
                    'Duplicate Count: '.$event->duplicate_count,
                    '',
                    $event->details ?: $event->message,
                ])->implode("\n")),
                'process_type' => Ticket::PROCESS_TYPE_INCIDENT,
                'incident_detection_source' => Ticket::DETECTION_SOURCE_MONITORING,
                'ticket_category_id' => $this->incidentCategoryId(),
                'service_id' => $event->service_id,
                'asset_id' => $event->asset_id,
                'source' => 'monitoring',
                'impact' => $mappedPriority['impact'],
                'urgency' => $mappedPriority['urgency'],
                'service_impact_note' => $event->message,
            ], $actor);

            $event->update([
                'status' => MonitoringEvent::STATUS_CONVERTED,
                'converted_ticket_id' => $ticket->id,
                'converted_at' => now(),
                'updated_by_id' => $actor?->id,
            ]);

            return $event->fresh($this->relations());
        });
    }

    public function ignore(MonitoringEvent $event, ?User $actor = null): MonitoringEvent
    {
        if ($event->status !== MonitoringEvent::STATUS_OPEN) {
            return $event->fresh($this->relations());
        }

        $event->update([
            'status' => MonitoringEvent::STATUS_IGNORED,
            'updated_by_id' => $actor?->id,
        ]);

        return $event->fresh($this->relations());
    }

    public function severityToImpactUrgency(string $severity): array
    {
        return match ($severity) {
            MonitoringEvent::SEVERITY_CRITICAL => ['impact' => Ticket::IMPACT_HIGH, 'urgency' => Ticket::IMPACT_HIGH],
            MonitoringEvent::SEVERITY_HIGH => ['impact' => Ticket::IMPACT_HIGH, 'urgency' => Ticket::IMPACT_MEDIUM],
            MonitoringEvent::SEVERITY_LOW => ['impact' => Ticket::IMPACT_LOW, 'urgency' => Ticket::IMPACT_LOW],
            default => ['impact' => Ticket::IMPACT_MEDIUM, 'urgency' => Ticket::IMPACT_MEDIUM],
        };
    }

    private function query(array $filters = []): Builder
    {
        return MonitoringEvent::query()
            ->with($this->relations())
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('event_number', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('details', 'like', "%{$search}%");
                });
            })
            ->when($filters['severity'] ?? null, fn (Builder $query, string $severity) => $query->where('severity', $severity))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source));
    }

    private function relations(): array
    {
        return [
            'service:id,name',
            'asset:id,name',
            'convertedTicket:id,ticket_number,title,process_type,incident_detection_source,impact,urgency,ticket_status_id',
            'convertedTicket.status:id,name,code',
        ];
    }

    private function preparePayload(array $data, ?User $actor): array
    {
        $payload = Arr::except($data, ['auto_create_incident']);
        $payload['occurred_at'] = $payload['occurred_at'] ?? now();
        $payload['deduplication_key'] = filled($payload['deduplication_key'] ?? null)
            ? $payload['deduplication_key']
            : $this->buildDeduplicationKey($payload);
        $payload['created_by_id'] = $actor?->id;
        $payload['updated_by_id'] = $actor?->id;

        return $payload;
    }

    private function buildDeduplicationKey(array $payload): string
    {
        $parts = [
            strtolower((string) ($payload['source'] ?? '')),
            (string) ($payload['service_id'] ?? ''),
            (string) ($payload['asset_id'] ?? ''),
            strtolower(trim(preg_replace('/\s+/', ' ', (string) ($payload['message'] ?? '')))),
        ];

        return sha1(implode('|', $parts));
    }

    private function canAutoConvert(MonitoringEvent $event): bool
    {
        return in_array($event->severity, [MonitoringEvent::SEVERITY_CRITICAL, MonitoringEvent::SEVERITY_HIGH], true)
            && $event->status === MonitoringEvent::STATUS_OPEN;
    }

    private function incidentCategoryId(): ?int
    {
        $category = TicketCategory::query()
            ->where('code', 'INCIDENT')
            ->first();

        if ($category === null) {
            $category = TicketCategory::query()->create([
                'code' => 'INCIDENT',
                'name' => 'Incident',
                'is_active' => true,
            ]);
        }

        return $category->id;
    }

    private function generateEventNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $base = "EVT-{$datePrefix}";
        $lastNumber = MonitoringEvent::query()
            ->where('event_number', 'like', "{$base}-%")
            ->orderByDesc('event_number')
            ->value('event_number');

        $lastSequence = 0;
        if (is_string($lastNumber)) {
            $segments = explode('-', $lastNumber);
            $lastSequence = (int) end($segments);
        }

        return $base.'-'.str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    }
}
