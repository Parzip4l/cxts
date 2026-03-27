<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketWorklogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'log_type' => $this->log_type,
            'description' => $this->description,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'duration_minutes' => $this->duration_minutes,
            'created_at' => $this->created_at,
        ];
    }
}
