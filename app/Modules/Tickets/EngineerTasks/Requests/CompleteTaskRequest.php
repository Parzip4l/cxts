<?php

namespace App\Modules\Tickets\EngineerTasks\Requests;

class CompleteTaskRequest extends TransitionTaskRequest
{
    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'max:5000'],
            'completion_evidences' => ['required', 'array', 'min:1', 'max:5'],
            'completion_evidences.*' => ['bail', 'file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'Resolution notes wajib diisi sebelum menyelesaikan task.',
            'completion_evidences.required' => 'Minimal satu foto evidence wajib diupload sebelum menyelesaikan task.',
            'completion_evidences.min' => 'Minimal satu foto evidence wajib diupload sebelum menyelesaikan task.',
            'completion_evidences.*.image' => 'Evidence harus berupa file gambar.',
            'completion_evidences.*.mimetypes' => 'Evidence harus berformat JPG, PNG, atau WEBP.',
            'completion_evidences.*.extensions' => 'Evidence harus berformat JPG, PNG, atau WEBP.',
            'completion_evidences.*.max' => 'Ukuran setiap evidence maksimal 5MB.',
        ];
    }
}
