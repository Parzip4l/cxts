<?php

namespace App\Modules\MasterData\Assets\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assetId = (int) $this->route('asset')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('assets', 'code')->ignore($assetId)],
            'name' => ['required', 'string', 'max:150'],
            'asset_category_id' => ['required', 'integer', Rule::exists('asset_categories', 'id')],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')],
            'department_owner_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')],
            'asset_location_id' => ['nullable', 'integer', Rule::exists('asset_locations', 'id')],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'install_date' => ['nullable', 'date'],
            'warranty_end_date' => ['nullable', 'date', 'after_or_equal:install_date'],
            'criticality' => ['required', Rule::in(Asset::criticalityOptions())],
            'is_configuration_item' => ['nullable', 'boolean'],
            'ci_type' => ['nullable', 'string', Rule::in(array_keys(Asset::ciTypeOptions()))],
            'ci_lifecycle_state' => ['nullable', 'string', Rule::in(array_keys(Asset::ciLifecycleOptions()))],
            'ci_governance_note' => ['nullable', 'string', 'max:2000'],
            'asset_status_id' => ['nullable', 'integer', Rule::exists('asset_statuses', 'id')],
            'depends_on_asset_ids' => ['nullable', 'array'],
            'depends_on_asset_ids.*' => ['integer', 'distinct', Rule::exists('assets', 'id')->where(fn ($query) => $query->where('id', '!=', $assetId))],
            'supports_asset_ids' => ['nullable', 'array'],
            'supports_asset_ids.*' => ['integer', 'distinct', Rule::exists('assets', 'id')->where(fn ($query) => $query->where('id', '!=', $assetId))],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->boolean('is_configuration_item')) {
                return;
            }

            if (blank($this->input('ci_type'))) {
                $validator->errors()->add('ci_type', 'CI type is required when asset is marked as Configuration Item.');
            }

            $hasServiceOrRelationship = filled($this->input('service_id'))
                || collect($this->input('depends_on_asset_ids', []))->filter()->isNotEmpty()
                || collect($this->input('supports_asset_ids', []))->filter()->isNotEmpty();

            if (! $hasServiceOrRelationship) {
                $validator->errors()->add('service_id', 'Configuration Item should be linked to a service or CMDB relationship.');
            }
        });
    }
}
