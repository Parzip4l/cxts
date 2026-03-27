<?php

namespace App\Modules\Tickets\TicketCategories\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('taxonomy.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:ticket_categories,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'requires_approval' => ['nullable', 'boolean'],
            'allow_direct_assignment' => ['nullable', 'boolean'],
            'approver_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])),
            ],
            'approver_strategy' => ['nullable', Rule::in(array_keys(\App\Models\TicketCategory::approverStrategies()))],
            'approver_role_code' => ['nullable', Rule::in(['super_admin', 'operational_admin', 'supervisor'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validateApproverConfiguration($validator);
        });
    }

    private function validateApproverConfiguration($validator): void
    {
        $strategy = $this->input('approver_strategy');

        if ($strategy === \App\Models\TicketCategory::APPROVER_STRATEGY_SPECIFIC_USER && ! $this->filled('approver_user_id')) {
            $validator->errors()->add('approver_user_id', 'Specific approver must be selected when using Specific User strategy.');
        }

        if ($strategy === \App\Models\TicketCategory::APPROVER_STRATEGY_ROLE_BASED && ! $this->filled('approver_role_code')) {
            $validator->errors()->add('approver_role_code', 'Approver role must be selected when using Role Based strategy.');
        }
    }
}
