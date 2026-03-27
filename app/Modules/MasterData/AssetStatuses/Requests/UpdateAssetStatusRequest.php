<?php

namespace App\Modules\MasterData\AssetStatuses\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assetStatusId = (int) $this->route('asset_status')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('asset_statuses', 'code')->ignore($assetStatusId)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_operational' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
