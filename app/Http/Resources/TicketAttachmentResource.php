<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'image_width' => $this->image_width,
            'image_height' => $this->image_height,
            'uploaded_by_id' => $this->uploaded_by_id,
            'uploaded_by_name' => $this->whenLoaded('uploadedBy', fn () => $this->uploadedBy?->name),
            'created_at' => $this->created_at,
            'preview_url' => route('api.v1.tickets.attachments.show', [$this->ticket_id, $this->id]),
        ];
    }
}
