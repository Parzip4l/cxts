<?php

namespace App\Modules\Tickets\PublicAccess\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackPublicTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_number' => ['required', 'string', 'max:50'],
            'requester_email' => ['required', 'email', 'max:150'],
        ];
    }
}
