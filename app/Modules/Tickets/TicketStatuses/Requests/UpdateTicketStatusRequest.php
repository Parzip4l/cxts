<?php

namespace App\Modules\Tickets\TicketStatuses\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ticketStatusId = (int) ($this->route('ticketStatus')?->id ?? $this->route('ticket_status')?->id);

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('ticket_statuses', 'code')->ignore($ticketStatusId)],
            'name' => ['required', 'string', 'max:100'],
            'is_open' => ['nullable', 'boolean'],
            'is_in_progress' => ['nullable', 'boolean'],
            'is_closed' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
