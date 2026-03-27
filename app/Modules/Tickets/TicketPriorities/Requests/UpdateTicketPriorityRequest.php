<?php

namespace App\Modules\Tickets\TicketPriorities\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketPriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ticketPriorityId = (int) ($this->route('ticketPriority')?->id ?? $this->route('ticket_priority')?->id);

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('ticket_priorities', 'code')->ignore($ticketPriorityId)],
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'integer', 'min:1', 'max:10'],
            'response_target_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'resolution_target_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
