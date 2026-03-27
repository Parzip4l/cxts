<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'actor_user_id' => $this->actor_user_id,
            'actor_user_name' => $this->whenLoaded('actor', fn () => $this->actor?->name),
            'activity_type' => $this->activity_type,
            'old_status_id' => $this->old_status_id,
            'old_status_name' => $this->whenLoaded('oldStatus', fn () => $this->oldStatus?->name),
            'new_status_id' => $this->new_status_id,
            'new_status_name' => $this->whenLoaded('newStatus', fn () => $this->newStatus?->name),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
