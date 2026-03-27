<?php

namespace App\Modules\MasterData\EngineerSkills\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEngineerSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $engineerSkillId = (int) $this->route('engineer_skill')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('engineer_skills', 'code')->ignore($engineerSkillId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
