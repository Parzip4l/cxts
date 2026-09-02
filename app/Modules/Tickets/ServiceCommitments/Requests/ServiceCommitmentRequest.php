<?php

namespace App\Modules\Tickets\ServiceCommitments\Requests;

use App\Models\ServiceCommitment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceCommitmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sla.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'commitment_type' => ['required', Rule::in(array_keys(ServiceCommitment::typeOptions()))],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')],
            'provider_department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')],
            'response_target_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'resolution_target_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'availability_target_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'escalation_contact' => ['nullable', 'string', 'max:150'],
            'review_frequency' => ['nullable', 'string', 'max:60'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', Rule::in(array_keys(ServiceCommitment::statusOptions()))],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('commitment_type') === ServiceCommitment::TYPE_OLA && blank($this->input('provider_department_id'))) {
                $validator->errors()->add('provider_department_id', 'Provider department is required for OLA.');
            }

            if ($this->input('commitment_type') === ServiceCommitment::TYPE_UNDERPINNING_CONTRACT && blank($this->input('vendor_id'))) {
                $validator->errors()->add('vendor_id', 'Vendor is required for Underpinning Contract.');
            }
        });
    }
}
