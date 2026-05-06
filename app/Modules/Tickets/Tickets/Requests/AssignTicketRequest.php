<?php

namespace App\Modules\Tickets\Tickets\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission(['ticket.assign_all', 'ticket.assign_department']) ?? false;
    }

    public function rules(): array
    {
        $engineerRule = Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'engineer'));

        return [
            'assigned_engineer_id' => [
                'nullable',
                'integer',
                $engineerRule,
            ],
            'assigned_engineer_ids' => ['nullable', 'array', 'min:1'],
            'assigned_engineer_ids.*' => ['integer', 'distinct', $engineerRule],
            'assigned_team_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $ids = collect($this->input('assigned_engineer_ids', []))->filter();

            if ($ids->isEmpty() && blank($this->input('assigned_engineer_id'))) {
                $validator->errors()->add('assigned_engineer_ids', 'Minimal satu engineer harus dipilih.');
            }
        });
    }
}
