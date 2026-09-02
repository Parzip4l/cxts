<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    public const PROCESS_TYPE_INCIDENT = 'incident';
    public const PROCESS_TYPE_SERVICE_REQUEST = 'service_request';
    public const PROCESS_TYPE_CHANGE_REQUEST = 'change_request';

    public const DETECTION_SOURCE_USER_REPORT = 'user_report';
    public const DETECTION_SOURCE_SERVICE_DESK = 'service_desk';
    public const DETECTION_SOURCE_MONITORING = 'monitoring';
    public const DETECTION_SOURCE_INSPECTION = 'inspection';

    public const RESOLUTION_CODE_FIXED = 'fixed';
    public const RESOLUTION_CODE_WORKAROUND = 'workaround';
    public const RESOLUTION_CODE_DUPLICATE = 'duplicate';
    public const RESOLUTION_CODE_NO_FAULT_FOUND = 'no_fault_found';
    public const RESOLUTION_CODE_VENDOR_ESCALATED = 'vendor_escalated';

    public const IMPACT_LOW = 'low';
    public const IMPACT_MEDIUM = 'medium';
    public const IMPACT_HIGH = 'high';

    public const SLA_STATUS_ON_TIME = 'on_time';
    public const SLA_STATUS_BREACHED = 'breached';
    public const APPROVAL_STATUS_NOT_REQUIRED = 'not_required';
    public const APPROVAL_STATUS_PENDING = 'pending';
    public const APPROVAL_STATUS_APPROVED = 'approved';
    public const APPROVAL_STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'process_type',
        'incident_detection_source',
        'is_major_incident',
        'affected_users_count',
        'service_impact_note',
        'incident_resolution_code',
        'change_reason',
        'change_risk_level',
        'change_planned_start_at',
        'change_planned_end_at',
        'change_rollback_plan',
        'change_affected_scope',
        'change_review_result',
        'change_review_notes',
        'request_form_payload',
        'requester_id',
        'requester_department_id',
        'ticket_category_id',
        'ticket_subcategory_id',
        'ticket_detail_subcategory_id',
        'ticket_priority_id',
        'service_id',
        'asset_id',
        'asset_location_id',
        'inspection_id',
        'ticket_status_id',
        'assigned_team_name',
        'assigned_engineer_id',
        'requires_approval',
        'allow_direct_assignment',
        'approval_status',
        'approval_requested_at',
        'expected_approver_id',
        'expected_approver_name_snapshot',
        'expected_approver_strategy',
        'expected_approver_role_code',
        'approved_at',
        'approved_by_id',
        'rejected_at',
        'rejected_by_id',
        'approval_notes',
        'assignment_ready_at',
        'assignment_ready_by_id',
        'flow_policy_source',
        'sla_policy_id',
        'sla_policy_name',
        'sla_name_snapshot',
        'response_due_at',
        'responded_at',
        'breached_response_at',
        'resolution_due_at',
        'source',
        'impact',
        'urgency',
        'started_at',
        'paused_at',
        'resolved_at',
        'sla_status',
        'breached_resolution_at',
        'completed_at',
        'closed_at',
        'last_status_changed_at',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'allow_direct_assignment' => 'boolean',
        'is_major_incident' => 'boolean',
        'affected_users_count' => 'integer',
        'approval_requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'assignment_ready_at' => 'datetime',
        'sla_policy_id' => 'integer',
        'response_due_at' => 'datetime',
        'responded_at' => 'datetime',
        'breached_response_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'change_planned_start_at' => 'datetime',
        'change_planned_end_at' => 'datetime',
        'request_form_payload' => 'array',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'resolved_at' => 'datetime',
        'breached_resolution_at' => 'datetime',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_status_changed_at' => 'datetime',
    ];

    public static function processTypeOptions(): array
    {
        return [
            self::PROCESS_TYPE_INCIDENT => 'Incident',
            self::PROCESS_TYPE_SERVICE_REQUEST => 'Service Request',
            self::PROCESS_TYPE_CHANGE_REQUEST => 'Change Request',
        ];
    }

    public static function normalizeProcessType(?string $processType): string
    {
        $processType = strtolower(trim((string) $processType));

        if ($processType === 'request') {
            $processType = self::PROCESS_TYPE_SERVICE_REQUEST;
        }

        if ($processType === 'change') {
            $processType = self::PROCESS_TYPE_CHANGE_REQUEST;
        }

        return array_key_exists($processType, self::processTypeOptions())
            ? $processType
            : self::PROCESS_TYPE_INCIDENT;
    }

    public static function detectionSourceOptions(): array
    {
        return [
            self::DETECTION_SOURCE_USER_REPORT => 'User Report',
            self::DETECTION_SOURCE_SERVICE_DESK => 'Service Desk',
            self::DETECTION_SOURCE_MONITORING => 'Monitoring',
            self::DETECTION_SOURCE_INSPECTION => 'Inspection',
        ];
    }

    public static function resolutionCodeOptions(): array
    {
        return [
            self::RESOLUTION_CODE_FIXED => 'Fixed',
            self::RESOLUTION_CODE_WORKAROUND => 'Workaround',
            self::RESOLUTION_CODE_DUPLICATE => 'Duplicate',
            self::RESOLUTION_CODE_NO_FAULT_FOUND => 'No Fault Found',
            self::RESOLUTION_CODE_VENDOR_ESCALATED => 'Vendor Escalated',
        ];
    }

    public static function impactUrgencyOptions(): array
    {
        return [
            self::IMPACT_LOW => 'Low',
            self::IMPACT_MEDIUM => 'Medium',
            self::IMPACT_HIGH => 'High',
        ];
    }

    public static function priorityCodeForImpactUrgency(?string $impact, ?string $urgency): string
    {
        $impact = strtolower(trim((string) $impact)) ?: self::IMPACT_MEDIUM;
        $urgency = strtolower(trim((string) $urgency)) ?: self::IMPACT_MEDIUM;

        if ($impact === self::IMPACT_HIGH && $urgency === self::IMPACT_HIGH) {
            return 'P1';
        }

        if ($impact === self::IMPACT_HIGH || $urgency === self::IMPACT_HIGH) {
            return 'P2';
        }

        if ($impact === self::IMPACT_LOW && $urgency === self::IMPACT_LOW) {
            return 'P4';
        }

        return 'P3';
    }

    public static function incidentLifecycleOptions(): array
    {
        return [
            'NEW' => 'New',
            'PENDING_APPROVAL' => 'Pending Approval',
            'ASSIGNED' => 'Assigned',
            'IN_PROGRESS' => 'In Progress',
            'PENDING_CUSTOMER' => 'Pending Customer',
            'COMPLETED' => 'Resolved / Completed',
            'CLOSED' => 'Closed',
            'REOPENED' => 'Reopened',
            'CANCELLED' => 'Cancelled',
            'REJECTED' => 'Rejected',
        ];
    }

    public static function serviceRequestLifecycleOptions(): array
    {
        return [
            'NEW' => 'Submitted',
            'PENDING_APPROVAL' => 'Pending Approval',
            'REJECTED' => 'Rejected',
            'ASSIGNED' => 'Fulfillment Assigned',
            'IN_PROGRESS' => 'Fulfillment In Progress',
            'PENDING_CUSTOMER' => 'Waiting Requester',
            'COMPLETED' => 'Fulfilled',
            'CLOSED' => 'Closed',
            'CANCELLED' => 'Cancelled',
        ];
    }

    public static function changeRiskOptions(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ];
    }

    public static function changeReviewResultOptions(): array
    {
        return [
            'successful' => 'Successful',
            'successful_with_issue' => 'Successful With Issue',
            'failed' => 'Failed',
            'rolled_back' => 'Rolled Back',
        ];
    }

    public function changeRiskLabel(): ?string
    {
        return self::changeRiskOptions()[$this->change_risk_level] ?? null;
    }

    public function changeReviewResultLabel(): ?string
    {
        return self::changeReviewResultOptions()[$this->change_review_result] ?? null;
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requesterDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requester_department_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(TicketSubcategory::class, 'ticket_subcategory_id');
    }

    public function detailSubcategory(): BelongsTo
    {
        return $this->belongsTo(TicketDetailSubcategory::class, 'ticket_detail_subcategory_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'ticket_priority_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assetLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function assignedEngineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_engineer_id');
    }

    public function assignedEngineers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_engineer_assignments', 'ticket_id', 'engineer_id')
            ->withPivot(['assigned_by_id', 'team_name', 'score_share', 'assigned_at'])
            ->withTimestamps();
    }

    public function problems(): BelongsToMany
    {
        return $this->belongsToMany(Problem::class, 'problem_ticket')->withTimestamps();
    }

    public function knowledgeArticles(): BelongsToMany
    {
        return $this->belongsToMany(KnowledgeArticle::class, 'knowledge_article_ticket')->withTimestamps();
    }

    public function slaEvents(): HasMany
    {
        return $this->hasMany(SlaEvent::class);
    }

    public function engineerAssignments(): HasMany
    {
        return $this->hasMany(TicketEngineerAssignment::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function expectedApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expected_approver_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    public function assignmentReadyBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignment_ready_by_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function worklogs(): HasMany
    {
        return $this->hasMany(TicketWorklog::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class)->latest();
    }

    public function isApprovalPending(): bool
    {
        return $this->approval_status === self::APPROVAL_STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::APPROVAL_STATUS_REJECTED;
    }

    public function statusCode(): ?string
    {
        $code = $this->relationLoaded('status')
            ? $this->status?->code
            : TicketStatus::query()->whereKey($this->ticket_status_id)->value('code');

        if (! is_string($code) || $code === '') {
            return null;
        }

        return strtoupper($code);
    }

    public function hasStatus(string ...$codes): bool
    {
        $statusCode = $this->statusCode();

        if ($statusCode === null) {
            return false;
        }

        return in_array($statusCode, array_map(fn ($code) => strtoupper((string) $code), $codes), true);
    }

    public function isAssignmentReady(): bool
    {
        if (! $this->requires_approval && $this->allow_direct_assignment) {
            return true;
        }

        if ($this->requires_approval && $this->allow_direct_assignment && $this->isApproved()) {
            return true;
        }

        return $this->assignment_ready_at !== null;
    }

    public function approvalStatusLabel(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_STATUS_PENDING => 'Pending Approval',
            self::APPROVAL_STATUS_APPROVED => 'Approved',
            self::APPROVAL_STATUS_REJECTED => 'Rejected',
            default => 'Not Required',
        };
    }

    public function processTypeLabel(): string
    {
        return self::processTypeOptions()[$this->process_type] ?? str($this->process_type ?? self::PROCESS_TYPE_INCIDENT)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    public function incidentDetectionSourceLabel(): string
    {
        return self::detectionSourceOptions()[$this->incident_detection_source] ?? '-';
    }

    public function incidentResolutionCodeLabel(): string
    {
        return self::resolutionCodeOptions()[$this->incident_resolution_code] ?? '-';
    }

    public function flowPolicySourceLabel(): string
    {
        return match ($this->flow_policy_source) {
            'ticket_sub_category' => 'Ticket Sub Category',
            'ticket_category' => 'Ticket Category',
            'ticket_type' => 'Ticket Type',
            default => 'System Default',
        };
    }

    public function expectedApproverDisplayName(): string
    {
        return $this->expectedApprover?->name
            ?? $this->expected_approver_name_snapshot
            ?? 'Supervisor/Admin Fallback';
    }

    public function canBeAssigned(): bool
    {
        if ($this->isTerminalLifecycle()) {
            return false;
        }

        if ($this->isRejected()) {
            return false;
        }

        if ($this->requires_approval && ! $this->isApproved()) {
            return false;
        }

        if (! $this->allow_direct_assignment && ! $this->isAssignmentReady()) {
            return false;
        }

        return true;
    }

    public function assignmentGateMessage(): ?string
    {
        if ($this->isCompleted()) {
            return 'Ticket ini sudah completed dan menunggu close atau reopen.';
        }

        if ($this->isClosed()) {
            return 'Ticket ini sudah closed dan tidak bisa di-assign ulang sebelum di-reopen.';
        }

        if ($this->isCancelled()) {
            return 'Ticket ini sudah dibatalkan dan tidak bisa di-assign.';
        }

        if ($this->isRejected()) {
            return 'Ticket ini sudah ditolak dan belum bisa di-assign.';
        }

        if ($this->requires_approval && $this->isApprovalPending()) {
            return 'Ticket ini masih menunggu approval sebelum bisa di-assign.';
        }

        if ($this->requires_approval && ! $this->isApproved()) {
            return 'Ticket ini membutuhkan approval sebelum bisa di-assign.';
        }

        if (! $this->allow_direct_assignment && ! $this->isAssignmentReady()) {
            return 'Ticket ini perlu diterima atau ditandai siap assign terlebih dahulu.';
        }

        return null;
    }

    public function canBeApprovedBy(?User $user): bool
    {
        if ($user === null || ! $this->requires_approval) {
            return false;
        }

        if ($this->expected_approver_strategy === TicketCategory::APPROVER_STRATEGY_ROLE_BASED
            && $this->expected_approver_role_code !== null
            && $this->expected_approver_role_code !== '') {
            return (string) $user->role === (string) $this->expected_approver_role_code;
        }

        if ($this->expected_approver_id !== null) {
            return (int) $this->expected_approver_id === (int) $user->id;
        }

        if ($this->expected_approver_role_code !== null && $this->expected_approver_role_code !== '') {
            return (string) $user->role === (string) $this->expected_approver_role_code;
        }

        return in_array((string) $user->role, ['super_admin', 'operational_admin', 'supervisor'], true);
    }

    public function isAssignedTo(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ((int) $this->assigned_engineer_id === (int) $user->id) {
            return true;
        }

        if ($this->relationLoaded('assignedEngineers')) {
            return $this->assignedEngineers->contains('id', $user->id);
        }

        return $this->assignedEngineers()->whereKey($user->id)->exists();
    }

    public function assignedEngineerNames(): string
    {
        $engineers = $this->relationLoaded('assignedEngineers')
            ? $this->assignedEngineers
            : $this->assignedEngineers()->get(['users.id', 'users.name']);

        if ($engineers->isNotEmpty()) {
            return $engineers->pluck('name')->filter()->implode(', ');
        }

        return $this->assignedEngineer?->name ?? '';
    }

    public function isPendingCustomer(): bool
    {
        return $this->hasStatus('PENDING_CUSTOMER');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null || $this->hasStatus('COMPLETED');
    }

    public function isClosed(): bool
    {
        return ($this->closed_at !== null && ! $this->isCancelled()) || $this->hasStatus('CLOSED');
    }

    public function isCancelled(): bool
    {
        return $this->hasStatus('CANCELLED');
    }

    public function isTerminalLifecycle(): bool
    {
        if ($this->completed_at !== null) {
            return true;
        }

        if ($this->closed_at !== null && ! $this->isPendingCustomer()) {
            return true;
        }

        return $this->hasStatus('COMPLETED', 'CLOSED', 'REJECTED', 'CANCELLED');
    }
}
