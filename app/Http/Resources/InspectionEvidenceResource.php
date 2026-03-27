<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InspectionEvidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inspection_id' => $this->inspection_id,
            'inspection_item_id' => $this->inspection_item_id,
            'uploaded_by_id' => $this->uploaded_by_id,
            'uploaded_by_name' => $this->whenLoaded('uploadedBy', fn () => $this->uploadedBy?->name),
            'file_path' => $this->file_path,
            'file_url' => Storage::disk('public')->url($this->file_path),
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
