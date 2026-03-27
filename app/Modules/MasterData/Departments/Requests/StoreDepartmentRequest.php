<?php

namespace App\Modules\MasterData\Departments\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('organization.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'parent_department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'head_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
