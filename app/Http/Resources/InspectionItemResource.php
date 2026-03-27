<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inspection_id' => $this->inspection_id,
            'inspection_template_item_id' => $this->inspection_template_item_id,
            'sequence' => $this->sequence,
            'item_label' => $this->item_label,
            'item_type' => $this->item_type,
            'expected_value' => $this->expected_value,
            'result_value' => $this->result_value,
            'result_status' => $this->result_status,
            'notes' => $this->notes,
            'checked_at' => $this->checked_at,
            'checked_by_id' => $this->checked_by_id,
            'checked_by_name' => $this->whenLoaded('checkedBy', fn () => $this->checkedBy?->name),
        ];
    }
}
