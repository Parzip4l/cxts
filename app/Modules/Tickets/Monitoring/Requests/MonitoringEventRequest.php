<?php

namespace App\Modules\Tickets\Monitoring\Requests;

use App\Models\MonitoringEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MonitoringEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission(['ticket.view_all', 'ticket.view_department', 'ticket.assign_all', 'ticket.approve_all']) ?? false;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'max:100'],
            'severity' => ['required', Rule::in(array_keys(MonitoringEvent::severityOptions()))],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'message' => ['required', 'string', 'max:500'],
            'details' => ['nullable', 'string', 'max:4000'],
            'occurred_at' => ['nullable', 'date'],
            'deduplication_key' => ['nullable', 'string', 'max:160'],
            'auto_create_incident' => ['nullable', 'boolean'],
        ];
    }
}
