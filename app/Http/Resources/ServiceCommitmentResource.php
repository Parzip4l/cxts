<?php

namespace App\Http\Resources;

use App\Models\ServiceCommitment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCommitmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'commitment_number' => $this->commitment_number,
            'name' => $this->name,
            'commitment_type' => $this->commitment_type,
            'commitment_type_label' => ServiceCommitment::typeOptions()[$this->commitment_type] ?? $this->commitment_type,
            'service_id' => $this->service_id,
            'service_name' => $this->whenLoaded('service', fn () => $this->service?->name),
            'provider_department_id' => $this->provider_department_id,
            'provider_department_name' => $this->whenLoaded('providerDepartment', fn () => $this->providerDepartment?->name),
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->whenLoaded('vendor', fn () => $this->vendor?->name),
            'response_target_minutes' => $this->response_target_minutes,
            'resolution_target_minutes' => $this->resolution_target_minutes,
            'availability_target_percent' => $this->availability_target_percent,
            'escalation_contact' => $this->escalation_contact,
            'review_frequency' => $this->review_frequency,
            'effective_from' => $this->effective_from,
            'effective_until' => $this->effective_until,
            'status' => $this->status,
            'status_label' => ServiceCommitment::statusOptions()[$this->status] ?? $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
