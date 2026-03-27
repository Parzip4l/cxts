<?php

namespace App\Http\Resources;

use App\Models\TicketCategory as TicketCategoryModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'requires_approval' => (bool) $this->requires_approval,
            'allow_direct_assignment' => (bool) $this->allow_direct_assignment,
            'approver_user_id' => $this->approver_user_id,
            'approver_name' => $this->whenLoaded('approver', fn () => $this->approver?->name),
            'approver_strategy' => $this->approver_strategy,
            'approver_role_code' => $this->approver_role_code,
            'approver_role_name' => TicketCategoryModel::approverRoleLabel($this->approver_role_code),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
