<?php

namespace App\Modules\Tickets\TicketStatuses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:ticket_statuses,code'],
            'name' => ['required', 'string', 'max:100'],
            'is_open' => ['nullable', 'boolean'],
            'is_in_progress' => ['nullable', 'boolean'],
            'is_closed' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
