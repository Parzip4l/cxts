<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlaEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'sla_policy_id' => $this->sla_policy_id,
            'sla_policy_name' => $this->whenLoaded('slaPolicy', fn () => $this->slaPolicy?->name),
            'event_type' => $this->event_type,
            'target' => $this->target,
            'event_at' => $this->event_at,
            'due_at' => $this->due_at,
            'threshold_percentage' => $this->threshold_percentage,
            'old_sla_status' => $this->old_sla_status,
            'new_sla_status' => $this->new_sla_status,
            'escalation_role_code' => $this->escalation_role_code,
            'actor_name' => $this->whenLoaded('actor', fn () => $this->actor?->name),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
