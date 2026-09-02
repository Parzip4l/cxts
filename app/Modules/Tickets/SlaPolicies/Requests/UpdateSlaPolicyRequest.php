<?php

namespace App\Modules\Tickets\SlaPolicies\Requests;

use App\Models\SlaPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSlaPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sla.manage') ?? false;
    }

    public function rules(): array
    {
        /** @var SlaPolicy $slaPolicy */
        $slaPolicy = $this->route('sla_policy');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('sla_policies', 'name')->ignore($slaPolicy)],
            'description' => ['nullable', 'string'],
            'response_time_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'resolution_time_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'working_hours_id' => ['nullable', 'integer', 'min:1'],
            'escalate_on_warning' => ['nullable', 'boolean'],
            'escalate_on_breach' => ['nullable', 'boolean'],
            'escalation_role_code' => ['nullable', 'string', 'max:60'],
            'escalation_note' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
