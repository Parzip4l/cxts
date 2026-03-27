<?php

namespace App\Modules\Tickets\EngineerTasks\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskWorklogRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            ? ($this->user()?->can('addWorklog', $ticket) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'log_type' => ['nullable', Rule::in(['note', 'progress', 'resolution'])],
            'description' => ['required', 'string'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }
}
