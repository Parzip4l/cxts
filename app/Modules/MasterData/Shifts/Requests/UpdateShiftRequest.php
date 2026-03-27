<?php

namespace App\Modules\MasterData\Shifts\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shiftId = (int) $this->route('shift')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('shifts', 'code')->ignore($shiftId)],
            'name' => ['required', 'string', 'max:150'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
            'is_overnight' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
