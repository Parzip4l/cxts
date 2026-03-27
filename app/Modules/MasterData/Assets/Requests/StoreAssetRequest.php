<?php

namespace App\Modules\MasterData\Assets\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:assets,code'],
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
            'asset_status_id' => ['nullable', 'integer', Rule::exists('asset_statuses', 'id')],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
