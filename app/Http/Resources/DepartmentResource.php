<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'parent_department_id' => $this->parent_department_id,
            'parent_department_name' => $this->whenLoaded('parentDepartment', fn () => $this->parentDepartment?->name),
            'head_user_id' => $this->head_user_id,
            'head_user_name' => $this->whenLoaded('head', fn () => $this->head?->name),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
