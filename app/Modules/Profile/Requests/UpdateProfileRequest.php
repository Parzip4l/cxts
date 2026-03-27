<?php

namespace App\Modules\Profile\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = (int) $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'profile_photo' => ['nullable', 'file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'extensions:jpg,jpeg,png,webp', 'max:3072'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'current_password' => [
                'required_with:password',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->filled('password')) {
                        return;
                    }

                    $user = $this->user();
                    if ($user === null || ! Hash::check((string) $value, (string) $user->password)) {
                        $fail('Current password is invalid.');
                    }
                },
            ],
        ];
    }
}
