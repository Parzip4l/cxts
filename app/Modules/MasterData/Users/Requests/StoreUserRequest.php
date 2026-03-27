<?php

namespace App\Modules\MasterData\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('organization.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', 'string', Rule::exists('roles', 'code')->where('is_active', true)],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'engineer_skill_ids' => ['nullable', 'array'],
            'engineer_skill_ids.*' => ['integer', Rule::exists('engineer_skills', 'id')->where('is_active', true)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
