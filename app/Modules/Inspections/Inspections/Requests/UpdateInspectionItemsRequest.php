<?php

namespace App\Modules\Inspections\Inspections\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInspectionItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', Rule::exists('inspection_items', 'id')],
            'items.*.result_status' => ['nullable', Rule::in(['pass', 'fail', 'na'])],
            'items.*.result_value' => ['nullable', 'string', 'max:120'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
