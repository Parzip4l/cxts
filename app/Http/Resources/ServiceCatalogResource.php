<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'service_category' => $this->service_category,
            'description' => $this->description,
            'ownership_model' => $this->ownership_model,
            'department_owner_id' => $this->department_owner_id,
            'department_owner_name' => $this->whenLoaded('ownerDepartment', fn () => $this->ownerDepartment?->name),
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->whenLoaded('vendor', fn () => $this->vendor?->name),
            'service_manager_user_id' => $this->service_manager_user_id,
            'service_manager_user_name' => $this->whenLoaded('manager', fn () => $this->manager?->name),
            'is_active' => $this->is_active,
            'is_requestable' => $this->is_requestable,
            'default_request_approval_required' => $this->default_request_approval_required,
            'default_request_sla_policy_id' => $this->default_request_sla_policy_id,
            'default_request_sla_policy_name' => $this->whenLoaded('defaultRequestSlaPolicy', fn () => $this->defaultRequestSlaPolicy?->name),
            'fulfillment_team_name' => $this->fulfillment_team_name,
            'request_form_schema' => $this->request_form_schema,
            'asset_count' => $this->whenCounted('assets'),
            'ticket_count' => $this->whenCounted('tickets'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
