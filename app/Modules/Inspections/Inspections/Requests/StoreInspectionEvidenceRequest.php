<?php

namespace App\Modules\Inspections\Inspections\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInspectionEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx', 'max:10240'],
            'inspection_item_id' => ['nullable', 'integer', Rule::exists('inspection_items', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
