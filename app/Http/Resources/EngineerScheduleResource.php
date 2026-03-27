<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'engineer_name' => $this->whenLoaded('engineer', fn () => $this->engineer?->name),
            'shift_id' => $this->shift_id,
            'shift_name' => $this->whenLoaded('shift', fn () => $this->shift?->name),
            'shift_start_time' => $this->whenLoaded('shift', fn () => $this->shift?->start_time),
            'shift_end_time' => $this->whenLoaded('shift', fn () => $this->shift?->end_time),
            'work_date' => $this->work_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'assigned_by_id' => $this->assigned_by_id,
            'assigned_by_name' => $this->whenLoaded('assignedBy', fn () => $this->assignedBy?->name),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
