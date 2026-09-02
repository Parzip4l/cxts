<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlaPolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'response_time_minutes' => $this->response_time_minutes,
            'resolution_time_minutes' => $this->resolution_time_minutes,
            'working_hours_id' => $this->working_hours_id,
            'escalate_on_warning' => (bool) $this->escalate_on_warning,
            'escalate_on_breach' => (bool) $this->escalate_on_breach,
            'escalation_role_code' => $this->escalation_role_code,
            'escalation_note' => $this->escalation_note,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
