<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'asset_category_id' => $this->asset_category_id,
            'asset_category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'service_id' => $this->service_id,
            'service_name' => $this->whenLoaded('service', fn () => $this->service?->name),
            'department_owner_id' => $this->department_owner_id,
            'department_owner_name' => $this->whenLoaded('ownerDepartment', fn () => $this->ownerDepartment?->name),
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->whenLoaded('vendor', fn () => $this->vendor?->name),
            'asset_location_id' => $this->asset_location_id,
            'asset_location_name' => $this->whenLoaded('location', fn () => $this->location?->name),
            'serial_number' => $this->serial_number,
            'brand' => $this->brand,
            'model' => $this->model,
            'install_date' => $this->install_date,
            'warranty_end_date' => $this->warranty_end_date,
            'criticality' => $this->criticality,
            'asset_status_id' => $this->asset_status_id,
            'asset_status_name' => $this->whenLoaded('status', fn () => $this->status?->name),
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
