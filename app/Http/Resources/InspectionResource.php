<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $workDurationMinutes = null;
        if ($this->started_at !== null && $this->submitted_at !== null) {
            $workDurationMinutes = max(0, $this->started_at->diffInMinutes($this->submitted_at));
        }

        return [
            'id' => $this->id,
            'inspection_number' => $this->inspection_number,
            'inspection_template_id' => $this->inspection_template_id,
            'inspection_template_name' => $this->whenLoaded('template', fn () => $this->template?->name),
            'asset_id' => $this->asset_id,
            'asset_code' => $this->whenLoaded('asset', fn () => $this->asset?->code),
            'asset_name' => $this->whenLoaded('asset', fn () => $this->asset?->name),
            'asset_category_name' => $this->whenLoaded('asset', fn () => $this->asset?->category?->name),
            'asset_service_name' => $this->whenLoaded('asset', fn () => $this->asset?->service?->name),
            'asset_owner_department_name' => $this->whenLoaded('asset', fn () => $this->asset?->ownerDepartment?->name),
            'asset_vendor_name' => $this->whenLoaded('asset', fn () => $this->asset?->vendor?->name),
            'asset_status_name' => $this->whenLoaded('asset', fn () => $this->asset?->status?->name),
            'asset_criticality' => $this->whenLoaded('asset', fn () => $this->asset?->criticality),
            'asset_location_id' => $this->asset_location_id,
            'asset_location_name' => $this->whenLoaded('assetLocation', fn () => $this->assetLocation?->name),
            'inspection_officer_id' => $this->inspection_officer_id,
            'inspection_officer_name' => $this->whenLoaded('officer', fn () => $this->officer?->name),
            'inspection_officer_email' => $this->whenLoaded('officer', fn () => $this->officer?->email),
            'scheduled_by_id' => $this->scheduled_by_id,
            'scheduled_by_name' => $this->whenLoaded('scheduledBy', fn () => $this->scheduledBy?->name),
            'inspection_date' => $this->inspection_date,
            'schedule_type' => $this->schedule_type,
            'schedule_interval' => $this->schedule_interval,
            'schedule_weekdays' => $this->schedule_weekdays,
            'schedule_next_date' => $this->schedule_next_date,
            'parent_inspection_id' => $this->parent_inspection_id,
            'status' => $this->status,
            'final_result' => $this->final_result,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
            'work_duration_minutes' => $workDurationMinutes,
            'summary_notes' => $this->summary_notes,
            'linked_ticket_id' => $this->whenLoaded('ticket', fn () => $this->ticket?->id),
            'linked_ticket_number' => $this->whenLoaded('ticket', fn () => $this->ticket?->ticket_number),
            'linked_ticket_status_id' => $this->whenLoaded('ticket', fn () => $this->ticket?->ticket_status_id),
            'linked_ticket_status_name' => $this->whenLoaded('ticket', fn () => $this->ticket?->status?->name),
            'items_count' => $this->when(isset($this->items_count), $this->items_count),
            'pass_items_count' => $this->when(isset($this->pass_items_count), $this->pass_items_count),
            'fail_items_count' => $this->when(isset($this->fail_items_count), $this->fail_items_count),
            'na_items_count' => $this->when(isset($this->na_items_count), $this->na_items_count),
            'evidences_count' => $this->when(isset($this->evidences_count), $this->evidences_count),
            'items' => InspectionItemResource::collection($this->whenLoaded('items')),
            'evidences' => InspectionEvidenceResource::collection($this->whenLoaded('evidences')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
