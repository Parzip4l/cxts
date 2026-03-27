<?php

namespace App\Modules\MasterData\AssetCategories\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:asset_categories,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'engineer_skill_ids' => ['nullable', 'array'],
            'engineer_skill_ids.*' => ['integer', \Illuminate\Validation\Rule::exists('engineer_skills', 'id')->where('is_active', true)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
