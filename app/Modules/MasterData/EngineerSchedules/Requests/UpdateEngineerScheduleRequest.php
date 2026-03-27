<?php

namespace App\Modules\MasterData\EngineerSchedules\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEngineerScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $engineerScheduleId = (int) $this->route('engineer_schedule')?->id;

        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'engineer')),
            ],
            'shift_id' => ['required', 'integer', Rule::exists('shifts', 'id')],
            'work_date' => [
                'required',
                'date',
                Rule::unique('engineer_schedules', 'work_date')
                    ->ignore($engineerScheduleId)
                    ->where(fn ($query) => $query->where('user_id', $this->input('user_id'))),
            ],
            'status' => ['nullable', Rule::in(['assigned', 'off', 'leave', 'sick'])],
            'notes' => ['nullable', 'string'],
            'assigned_by_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
