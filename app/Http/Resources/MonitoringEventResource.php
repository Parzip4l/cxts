<?php

namespace App\Http\Resources;

use App\Models\MonitoringEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_number' => $this->event_number,
            'source' => $this->source,
            'severity' => $this->severity,
            'severity_label' => MonitoringEvent::severityOptions()[$this->severity] ?? $this->severity,
            'service_id' => $this->service_id,
            'service_name' => $this->whenLoaded('service', fn () => $this->service?->name),
            'asset_id' => $this->asset_id,
            'asset_name' => $this->whenLoaded('asset', fn () => $this->asset?->name),
            'message' => $this->message,
            'details' => $this->details,
            'occurred_at' => $this->occurred_at,
            'status' => $this->status,
            'status_label' => MonitoringEvent::statusOptions()[$this->status] ?? $this->status,
            'deduplication_key' => $this->deduplication_key,
            'duplicate_count' => $this->duplicate_count,
            'last_seen_at' => $this->last_seen_at,
            'converted_ticket_id' => $this->converted_ticket_id,
            'converted_ticket' => new TicketResource($this->whenLoaded('convertedTicket')),
            'converted_at' => $this->converted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
