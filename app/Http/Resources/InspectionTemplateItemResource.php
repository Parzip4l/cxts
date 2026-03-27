<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionTemplateItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inspection_template_id' => $this->inspection_template_id,
            'sequence' => $this->sequence,
            'item_label' => $this->item_label,
            'item_type' => $this->item_type,
            'expected_value' => $this->expected_value,
            'is_required' => $this->is_required,
            'is_active' => $this->is_active,
        ];
    }
}
