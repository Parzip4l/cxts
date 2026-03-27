<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlaPolicyAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sla_policy_id' => $this->sla_policy_id,
            'sla_policy_name' => $this->whenLoaded('policy', fn () => $this->policy?->name),
            'ticket_type' => $this->ticket_type,
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'subcategory_id' => $this->subcategory_id,
            'subcategory_name' => $this->whenLoaded('subcategory', fn () => $this->subcategory?->name),
            'detail_subcategory_id' => $this->detail_subcategory_id,
            'detail_subcategory_name' => $this->whenLoaded('detailSubcategory', fn () => $this->detailSubcategory?->name),
            'service_item_id' => $this->service_item_id,
            'service_item_name' => $this->whenLoaded('serviceItem', fn () => $this->serviceItem?->name),
            'priority_id' => $this->priority_id,
            'priority_name' => $this->whenLoaded('priority', fn () => $this->priority?->name),
            'impact' => $this->impact,
            'urgency' => $this->urgency,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
