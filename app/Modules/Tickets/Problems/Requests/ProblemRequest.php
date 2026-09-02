<?php

namespace App\Modules\Tickets\Problems\Requests;

use App\Models\Problem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission(['ticket.view_all', 'ticket.view_department', 'ticket.assign_all', 'ticket.approve_all']) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Problem::statusOptions()))],
            'owner_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'ticket_priority_id' => ['nullable', 'integer', Rule::exists('ticket_priorities', 'id')],
            'symptom' => ['nullable', 'string', 'max:4000'],
            'root_cause' => ['nullable', 'string', 'max:4000'],
            'workaround' => ['nullable', 'string', 'max:4000'],
            'permanent_fix' => ['nullable', 'string', 'max:4000'],
            'is_known_error' => ['nullable', 'boolean'],
            'action_item' => ['nullable', 'string', 'max:3000'],
            'target_resolution_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
            'ticket_ids' => ['nullable', 'array'],
            'ticket_ids.*' => ['integer', 'distinct', Rule::exists('tickets', 'id')],
        ];
    }
}
