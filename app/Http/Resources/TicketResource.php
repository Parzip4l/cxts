<?php

namespace App\Http\Resources;

use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $workEndedAt = $this->completed_at ?? $this->resolved_at ?? $this->closed_at;
        $workDurationMinutes = null;
        $hasEngineerRecommendation = method_exists($this->resource, 'getAttributes')
            && array_key_exists('engineer_recommendation', $this->resource->getAttributes());

        if ($this->started_at !== null && $workEndedAt !== null) {
            $workDurationMinutes = max(0, $this->started_at->diffInMinutes($workEndedAt));
        }

        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'title' => $this->title,
            'description' => $this->description,
            'requester_id' => $this->requester_id,
            'requester_name' => $this->whenLoaded('requester', fn () => $this->requester?->name),
            'requester_department_id' => $this->requester_department_id,
            'requester_department_name' => $this->whenLoaded('requesterDepartment', fn () => $this->requesterDepartment?->name),
            'ticket_category_id' => $this->ticket_category_id,
            'ticket_category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'ticket_subcategory_id' => $this->ticket_subcategory_id,
            'ticket_subcategory_name' => $this->whenLoaded('subcategory', fn () => $this->subcategory?->name),
            'ticket_detail_subcategory_id' => $this->ticket_detail_subcategory_id,
            'ticket_detail_subcategory_name' => $this->whenLoaded('detailSubcategory', fn () => $this->detailSubcategory?->name),
            'ticket_priority_id' => $this->ticket_priority_id,
            'ticket_priority_name' => $this->whenLoaded('priority', fn () => $this->priority?->name),
            'service_id' => $this->service_id,
            'service_name' => $this->whenLoaded('service', fn () => $this->service?->name),
            'asset_id' => $this->asset_id,
            'asset_name' => $this->whenLoaded('asset', fn () => $this->asset?->name),
            'asset_location_id' => $this->asset_location_id,
            'asset_location_name' => $this->whenLoaded('assetLocation', fn () => $this->assetLocation?->name),
            'inspection_id' => $this->inspection_id,
            'inspection_number' => $this->whenLoaded('inspection', fn () => $this->inspection?->inspection_number),
            'ticket_status_id' => $this->ticket_status_id,
            'ticket_status_name' => $this->whenLoaded('status', fn () => $this->status?->name),
            'ticket_status_code' => $this->whenLoaded('status', fn () => $this->status?->code),
            'assigned_team_name' => $this->assigned_team_name,
            'assigned_engineer_id' => $this->assigned_engineer_id,
            'assigned_engineer_name' => $this->whenLoaded('assignedEngineer', fn () => $this->assignedEngineer?->name),
            'requires_approval' => (bool) $this->requires_approval,
            'allow_direct_assignment' => (bool) $this->allow_direct_assignment,
            'approval_status' => $this->approval_status,
            'approval_requested_at' => $this->approval_requested_at,
            'expected_approver_id' => $this->expected_approver_id,
            'expected_approver_name' => $this->expectedApprover?->name ?? $this->expected_approver_name_snapshot,
            'expected_approver_strategy' => $this->expected_approver_strategy,
            'expected_approver_role_code' => $this->expected_approver_role_code,
            'expected_approver_role_name' => TicketCategory::approverRoleLabel($this->expected_approver_role_code),
            'approved_at' => $this->approved_at,
            'approved_by_id' => $this->approved_by_id,
            'approved_by_name' => $this->whenLoaded('approvedBy', fn () => $this->approvedBy?->name),
            'rejected_at' => $this->rejected_at,
            'rejected_by_id' => $this->rejected_by_id,
            'rejected_by_name' => $this->whenLoaded('rejectedBy', fn () => $this->rejectedBy?->name),
            'approval_notes' => $this->approval_notes,
            'assignment_ready_at' => $this->assignment_ready_at,
            'assignment_ready_by_id' => $this->assignment_ready_by_id,
            'assignment_ready_by_name' => $this->whenLoaded('assignmentReadyBy', fn () => $this->assignmentReadyBy?->name),
            'flow_policy_source' => $this->flow_policy_source,
            'can_assign' => $this->canBeAssigned(),
            'assignment_gate_message' => $this->assignmentGateMessage(),
            'sla_policy_id' => $this->sla_policy_id,
            'sla_name_snapshot' => $this->sla_name_snapshot,
            'response_due_at' => $this->response_due_at,
            'responded_at' => $this->responded_at,
            'breached_response_at' => $this->breached_response_at,
            'resolution_due_at' => $this->resolution_due_at,
            'resolved_at' => $this->resolved_at,
            'sla_status' => $this->sla_status,
            'breached_resolution_at' => $this->breached_resolution_at,
            'source' => $this->source,
            'impact' => $this->impact,
            'urgency' => $this->urgency,
            'started_at' => $this->started_at,
            'paused_at' => $this->paused_at,
            'completed_at' => $this->completed_at,
            'closed_at' => $this->closed_at,
            'work_duration_minutes' => $workDurationMinutes,
            'can_start_work' => $this->canStartWork(),
            'can_pause_work' => $this->canPauseWork(),
            'can_resume_work' => $this->canResumeWork(),
            'can_complete_work' => $this->canCompleteWork(),
            'engineer_recommendation' => $this->when(
                $hasEngineerRecommendation || isset($this->engineer_recommendation),
                fn () => $this->engineer_recommendation
            ),
            'attachments' => TicketAttachmentResource::collection($this->whenLoaded('attachments')),
            'worklogs' => TicketWorklogResource::collection($this->whenLoaded('worklogs')),
            'activities' => TicketActivityResource::collection($this->whenLoaded('activities')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function canStartWork(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return $this->started_at === null;
    }

    private function canPauseWork(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return $this->started_at !== null && $this->paused_at === null;
    }

    private function canResumeWork(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return $this->started_at !== null && $this->paused_at !== null;
    }

    private function canCompleteWork(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return $this->started_at !== null;
    }

    private function isTerminal(): bool
    {
        if ($this->completed_at !== null || $this->closed_at !== null) {
            return true;
        }

        return in_array(strtoupper((string) ($this->status?->code ?? '')), ['COMPLETED', 'CLOSED', 'REJECTED'], true);
    }
}
