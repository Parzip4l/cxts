<?php

namespace App\Modules\MasterData\Departments\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('organization.manage') ?? false;
    }

    public function rules(): array
    {
        $departmentId = (int) $this->route('department')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($departmentId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'parent_department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->whereNot('id', $departmentId)],
            'head_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
