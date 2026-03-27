<?php

namespace App\Modules\Inspections\PublicAccess\Requests;

use App\Models\Inspection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StorePublicInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reporter_name' => ['required', 'string', 'max:150'],
            'reporter_email' => ['required', 'email', 'max:150'],
            'inspection_template_id' => ['required', 'integer', Rule::exists('inspection_templates', 'id')],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'asset_location_id' => ['nullable', 'integer', Rule::exists('asset_locations', 'id')],
            'inspection_date' => ['required', 'date'],
            'final_result' => ['required', Rule::in([Inspection::FINAL_RESULT_NORMAL, Inspection::FINAL_RESULT_ABNORMAL])],
            'summary_notes' => ['nullable', 'string'],
            'supporting_files' => ['nullable', 'array'],
            'supporting_files.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inspection_template_item_id' => ['required', 'integer', Rule::exists('inspection_template_items', 'id')],
            'items.*.result_status' => ['nullable', Rule::in(['pass', 'fail', 'na'])],
            'items.*.result_value' => ['nullable', 'string', 'max:120'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $finalResult = (string) $this->input('final_result', '');
            if ($finalResult !== Inspection::FINAL_RESULT_ABNORMAL) {
                return;
            }

            $files = $this->file('supporting_files', []);
            if (is_array($files) && count($files) > 0) {
                return;
            }

            $validator->errors()->add('supporting_files', 'File pendukung wajib diunggah jika hasil akhir Abnormal.');
        });
    }
}
