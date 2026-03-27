<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'profile_photo_url' => $this->profilePhotoUrl(),
            'whatsapp_url' => $this->whatsappUrl(),
            'tel_url' => $this->telUrl(),
            'role' => $this->role,
            'role_name' => $this->whenLoaded('roleRef', fn () => $this->roleRef?->name),
            'department_id' => $this->department_id,
            'department_name' => $this->whenLoaded('department', fn () => $this->department?->name),
        ];
    }
}
