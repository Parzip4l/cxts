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
            'is_configuration_item' => (bool) $this->is_configuration_item,
            'ci_type' => $this->ci_type,
            'ci_type_label' => \App\Models\Asset::ciTypeOptions()[$this->ci_type] ?? $this->ci_type,
            'ci_lifecycle_state' => $this->ci_lifecycle_state,
            'ci_lifecycle_label' => \App\Models\Asset::ciLifecycleOptions()[$this->ci_lifecycle_state] ?? $this->ci_lifecycle_state,
            'ci_governance_note' => $this->ci_governance_note,
            'asset_status_id' => $this->asset_status_id,
            'asset_status_name' => $this->whenLoaded('status', fn () => $this->status?->name),
            'relationships' => $this->whenLoaded('relationships', fn () => $this->relationships->map(fn ($relationship) => [
                'id' => (int) $relationship->id,
                'type' => $relationship->relationship_type,
                'type_label' => \App\Models\AssetRelationship::typeOptions()[$relationship->relationship_type] ?? $relationship->relationship_type,
                'related_asset_id' => (int) $relationship->related_asset_id,
                'related_asset_code' => $relationship->relatedAsset?->code,
                'related_asset_name' => $relationship->relatedAsset?->name,
                'related_asset_criticality' => $relationship->relatedAsset?->criticality,
            ])->values()),
            'impact_view' => $this->whenLoaded('impactedByRelationships', fn () => $this->impactedByRelationships->map(fn ($relationship) => [
                'asset_id' => (int) $relationship->asset_id,
                'asset_code' => $relationship->asset?->code,
                'asset_name' => $relationship->asset?->name,
                'asset_criticality' => $relationship->asset?->criticality,
                'relationship_type' => $relationship->relationship_type,
            ])->values()),
            'ticket_count' => $this->whenCounted('tickets'),
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
