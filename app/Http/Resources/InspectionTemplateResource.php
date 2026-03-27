<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'asset_category_id' => $this->asset_category_id,
            'asset_category_name' => $this->whenLoaded('assetCategory', fn () => $this->assetCategory?->name),
            'is_active' => $this->is_active,
            'items' => InspectionTemplateItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
