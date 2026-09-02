<?php

namespace App\Modules\Tickets\Tickets\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            ? ($this->user()?->can('update', $ticket) ?? false)
            : false;
    }

    public function rules(): array
    {
        $engineerRule = Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'engineer'));

        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'process_type' => ['nullable', 'string', Rule::in(array_keys(Ticket::processTypeOptions()))],
            'incident_detection_source' => ['nullable', 'string', Rule::in(array_keys(Ticket::detectionSourceOptions()))],
            'is_major_incident' => ['nullable', 'boolean'],
            'affected_users_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'service_impact_note' => ['nullable', 'string', 'max:2000'],
            'incident_resolution_code' => ['nullable', 'string', Rule::in(array_keys(Ticket::resolutionCodeOptions()))],
            'change_reason' => ['nullable', 'string', 'max:2000'],
            'change_risk_level' => ['nullable', 'string', Rule::in(array_keys(Ticket::changeRiskOptions()))],
            'change_planned_start_at' => ['nullable', 'date'],
            'change_planned_end_at' => ['nullable', 'date', 'after_or_equal:change_planned_start_at'],
            'change_rollback_plan' => ['nullable', 'string', 'max:4000'],
            'change_affected_scope' => ['nullable', 'string', 'max:3000'],
            'change_review_result' => ['nullable', 'string', Rule::in(array_keys(Ticket::changeReviewResultOptions()))],
            'change_review_notes' => ['nullable', 'string', 'max:3000'],
            'assigned_engineer_ids' => ['nullable', 'array', 'min:1'],
            'assigned_engineer_ids.*' => ['integer', 'distinct', $engineerRule],
            'assigned_team_name' => ['nullable', 'string', 'max:100'],
            'assignment_notes' => ['nullable', 'string'],
        ];
    }
}
