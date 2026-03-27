<?php

namespace App\Modules\MasterData\AssetLocations\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assetLocationId = (int) $this->route('asset_location')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('asset_locations', 'code')->ignore($assetLocationId)],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
