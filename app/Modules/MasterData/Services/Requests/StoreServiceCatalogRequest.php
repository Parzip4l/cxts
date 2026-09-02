<?php

namespace App\Modules\MasterData\Services\Requests;

use App\Models\ServiceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('organization.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:services,code'],
            'name' => ['required', 'string', 'max:150'],
            'service_category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'ownership_model' => ['required', Rule::in(ServiceCatalog::ownershipOptions())],
            'department_owner_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')],
            'service_manager_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_requestable' => ['nullable', 'boolean'],
            'default_request_approval_required' => ['nullable', 'boolean'],
            'default_request_sla_policy_id' => ['nullable', 'integer', Rule::exists('sla_policies', 'id')->where('is_active', true)],
            'fulfillment_team_name' => ['nullable', 'string', 'max:100'],
            'request_form_schema' => ['nullable', 'string', 'json'],
            'engineer_skill_ids' => ['nullable', 'array'],
            'engineer_skill_ids.*' => ['integer', Rule::exists('engineer_skills', 'id')->where('is_active', true)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
