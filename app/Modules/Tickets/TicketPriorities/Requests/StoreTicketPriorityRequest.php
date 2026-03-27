<?php

namespace App\Modules\Tickets\TicketPriorities\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketPriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:ticket_priorities,code'],
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'integer', 'min:1', 'max:10'],
            'response_target_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'resolution_target_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
