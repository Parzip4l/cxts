<?php

namespace App\Modules\Tickets\Tickets\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            ? ($this->user()?->can('assign', $ticket) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'assigned_engineer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'engineer')),
            ],
            'assigned_team_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
