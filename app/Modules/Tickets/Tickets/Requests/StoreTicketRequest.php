<?php

namespace App\Modules\Tickets\Tickets\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ticket::class) ?? false;
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
            'request_form_payload' => ['nullable', 'array'],
            'request_form_payload.*' => ['nullable', 'string', 'max:1000'],
            'requester_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'requester_department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'ticket_type' => ['nullable', 'string', 'max:50'],
            'ticket_category_id' => ['required', 'integer', Rule::exists('ticket_categories', 'id')],
            'ticket_subcategory_id' => ['nullable', 'integer', Rule::exists('ticket_subcategories', 'id')],
            'ticket_detail_subcategory_id' => ['nullable', 'integer', Rule::exists('ticket_detail_subcategories', 'id')],
            'ticket_priority_id' => ['nullable', 'integer', Rule::exists('ticket_priorities', 'id')],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'asset_location_id' => ['nullable', 'integer', Rule::exists('asset_locations', 'id')],
            'source' => ['nullable', 'string', 'max:30'],
            'impact' => ['nullable', 'string', Rule::in(array_keys(Ticket::impactUrgencyOptions()))],
            'urgency' => ['nullable', 'string', Rule::in(array_keys(Ticket::impactUrgencyOptions()))],
            'assigned_engineer_ids' => ['nullable', 'array', 'min:1'],
            'assigned_engineer_ids.*' => ['integer', 'distinct', $engineerRule],
            'assigned_team_name' => ['nullable', 'string', 'max:100'],
            'assignment_notes' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['bail', 'file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $categoryId = $this->input('ticket_category_id');
            $subcategoryId = $this->input('ticket_subcategory_id');
            $detailSubcategoryId = $this->input('ticket_detail_subcategory_id');

            if ($categoryId && $subcategoryId) {
                $belongsToType = \App\Models\TicketSubcategory::query()
                    ->whereKey($subcategoryId)
                    ->where('ticket_category_id', $categoryId)
                    ->exists();

                if (! $belongsToType) {
                    $validator->errors()->add('ticket_subcategory_id', 'The selected ticket category does not belong to the selected ticket type.');
                }
            }

            if ($subcategoryId && $detailSubcategoryId) {
                $belongsToCategory = \App\Models\TicketDetailSubcategory::query()
                    ->whereKey($detailSubcategoryId)
                    ->where('ticket_subcategory_id', $subcategoryId)
                    ->exists();

                if (! $belongsToCategory) {
                    $validator->errors()->add('ticket_detail_subcategory_id', 'The selected ticket sub category does not belong to the selected ticket category.');
                }
            }
        });
    }
}
