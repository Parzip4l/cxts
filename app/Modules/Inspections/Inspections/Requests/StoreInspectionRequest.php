<?php

namespace App\Modules\Inspections\Inspections\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInspectionRequest extends FormRequest
{
    private const OPS_ROLES = ['super_admin', 'operational_admin', 'supervisor'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isOpsActor = in_array((string) $this->user()?->role, self::OPS_ROLES, true);

        return [
            'inspection_template_id' => ['required', 'integer', Rule::exists('inspection_templates', 'id')],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'asset_location_id' => ['nullable', 'integer', Rule::exists('asset_locations', 'id')],
            'inspection_officer_id' => [
                $isOpsActor ? 'required' : 'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['inspection_officer', 'engineer'])),
            ],
            'inspection_date' => ['required', 'date'],
            'schedule_type' => ['nullable', Rule::in(['none', 'daily', 'weekly'])],
            'schedule_interval' => ['nullable', 'integer', 'min:1', 'max:30'],
            'schedule_weekdays' => ['nullable', 'array'],
            'schedule_weekdays.*' => ['integer', 'between:1,7', 'distinct'],
            'summary_notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $scheduleType = (string) ($this->input('schedule_type') ?? 'none');
            $weekdays = $this->input('schedule_weekdays', []);

            if ($scheduleType === 'weekly' && count((array) $weekdays) === 0) {
                $validator->errors()->add('schedule_weekdays', 'Pilih minimal 1 hari untuk jadwal mingguan.');
            }
        });
    }
}
