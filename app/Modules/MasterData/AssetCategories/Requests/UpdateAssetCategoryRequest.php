<?php

namespace App\Modules\MasterData\AssetCategories\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assetCategoryId = (int) $this->route('asset_category')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('asset_categories', 'code')->ignore($assetCategoryId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'engineer_skill_ids' => ['nullable', 'array'],
            'engineer_skill_ids.*' => ['integer', Rule::exists('engineer_skills', 'id')->where('is_active', true)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
