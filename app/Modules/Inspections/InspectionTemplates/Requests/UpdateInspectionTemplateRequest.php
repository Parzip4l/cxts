<?php

namespace App\Modules\Inspections\InspectionTemplates\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInspectionTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $templateId = (int) ($this->route('inspectionTemplate')?->id ?? $this->route('inspection_template')?->id);

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('inspection_templates', 'code')->ignore($templateId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'asset_category_id' => ['nullable', 'integer', Rule::exists('asset_categories', 'id')],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sequence' => ['nullable', 'integer', 'min:1'],
            'items.*.item_label' => ['required', 'string', 'max:200'],
            'items.*.item_type' => ['nullable', Rule::in(['boolean', 'number', 'text'])],
            'items.*.expected_value' => ['nullable', 'string', 'max:120'],
            'items.*.is_required' => ['nullable', 'boolean'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
