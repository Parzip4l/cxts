<?php

namespace App\Modules\Tickets\Tickets\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            ? ($this->user()?->can('update', $ticket) ?? false)
            : false;
    }

    public function rules(): array
    {
        $engineerRule = Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'engineer'));

        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'assigned_engineer_ids' => ['nullable', 'array', 'min:1'],
            'assigned_engineer_ids.*' => ['integer', 'distinct', $engineerRule],
            'assigned_team_name' => ['nullable', 'string', 'max:100'],
            'assignment_notes' => ['nullable', 'string'],
        ];
    }
}
