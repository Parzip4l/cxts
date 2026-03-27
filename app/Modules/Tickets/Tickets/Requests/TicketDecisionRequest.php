<?php

namespace App\Modules\Tickets\Tickets\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class TicketDecisionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('decision')) {
            return;
        }

        $routeName = (string) $this->route()?->getName();

        $decision = match (true) {
            str_ends_with($routeName, '.approve') => 'approve',
            str_ends_with($routeName, '.reject') => 'reject',
            str_ends_with($routeName, '.mark-ready') => 'mark_ready',
            default => null,
        };

        if ($decision !== null) {
            $this->merge(['decision' => $decision]);
        }
    }

    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        if (! $ticket instanceof Ticket || $this->user() === null) {
            return false;
        }

        $routeName = (string) $this->route()?->getName();
        $ability = match (true) {
            str_ends_with($routeName, '.approve') => 'approve',
            str_ends_with($routeName, '.reject') => 'reject',
            str_ends_with($routeName, '.mark-ready') => 'markReady',
            default => null,
        };

        return $ability !== null
            ? $this->user()->can($ability, $ticket)
            : false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'required_if:decision,reject,mark_ready'],
        ];
    }
}
