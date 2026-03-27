<?php

namespace App\Modules\Tickets\SlaPolicies\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSlaPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sla.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:sla_policies,name'],
            'description' => ['nullable', 'string'],
            'response_time_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'resolution_time_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'working_hours_id' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
