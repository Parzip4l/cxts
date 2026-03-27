<?php

namespace App\Modules\Tickets\EngineerTasks\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class TransitionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            ? ($this->user()?->can('work', $ticket) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
        ];
    }
}
