<?php

namespace App\Modules\MasterData\EngineerSkills\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEngineerSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:engineer_skills,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
