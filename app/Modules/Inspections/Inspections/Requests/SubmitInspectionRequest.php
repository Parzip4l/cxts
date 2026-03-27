<?php

namespace App\Modules\Inspections\Inspections\Requests;

use App\Models\Inspection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'final_result' => ['required', Rule::in([Inspection::FINAL_RESULT_NORMAL, Inspection::FINAL_RESULT_ABNORMAL])],
            'summary_notes' => ['nullable', 'string'],
            'supporting_files' => ['nullable', 'array'],
            'supporting_files.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx', 'max:10240'],
        ];
    }
}
