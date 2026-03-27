<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => $ticket->ticket_number], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php
    $workEndedAt = $ticket->completed_at ?? $ticket->resolved_at ?? $ticket->closed_at;
    $workDurationMinutes = ($ticket->started_at && $workEndedAt) ? $ticket->started_at->diffInMinutes($workEndedAt) : null;
    $currentUser = auth()->user();
    $approvalActivities = $ticket->activities->filter(fn ($activity) => in_array($activity->activity_type, ['ticket_approved', 'ticket_rejected', 'ticket_ready_for_assignment'], true))->values();
    $strategyLabels = \App\Models\TicketCategory::approverStrategies();
    $roleLabel = fn ($roleCode) => \App\Models\TicketCategory::approverRoleLabel($roleCode) ?? '-';
    $approvalPolicyLabel = function ($value, $inheritLabel = 'Follow Parent') {
        if ($value === null) {
            return $inheritLabel;
        }

        return $value ? 'Approval Required' : 'No Approval Required';
    };
    $assignmentPolicyLabel = function ($value, $inheritLabel = 'Follow Parent') {
        if ($value === null) {
            return $inheritLabel;
        }

        return $value ? 'Direct Assignment Allowed' : 'Needs Ready Flag';
    };
    $approverStrategyLabel = function ($model, $inheritLabel = 'Follow Parent') use ($strategyLabels) {
        if ($model === null) {
            return '-';
        }

        if (! $model->approver_strategy) {
            return $inheritLabel;
        }

        return $strategyLabels[$model->approver_strategy] ?? str($model->approver_strategy)->replace('_', ' ')->title()->toString();
    };
    $approverTargetLabel = function ($model, $inheritLabel = 'Follow Parent') use ($roleLabel) {
        if ($model === null) {
            return '-';
        }

        if (! $model->approver_strategy) {
            return $inheritLabel;
        }

        return $model->approver?->name
            ?? $roleLabel($model->approver_role_code)
            ?? $inheritLabel;
    };
    $policySourceBadge = fn ($source, $currentSource) => $source === $currentSource
        ? 'bg-primary-subtle text-primary'
        : 'bg-light text-muted border';
    $scoreBadgeClass = function ($score) {
        if ($score >= 80) {
            return 'bg-success-subtle text-success';
        }

        if ($score >= 55) {
            return 'bg-warning-subtle text-warning';
        }

        return 'bg-danger-subtle text-danger';
    };
    $availabilityBadgeClass = function ($status) {
        return match ($status) {
            'available' => 'bg-success-subtle text-success',
            'unavailable' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    };
    $engineerCustomProperties = function ($option) {
        return [
            'department_name' => $option->department_name ?? 'No department',
            'team_label' => $option->team_label ?? 'No team/shift',
            'availability_label' => $option->availability_label ?? 'Unknown',
            'availability_status' => $option->availability_status ?? 'unknown',
            'workload_label' => $option->workload_label ?? 'Light',
            'workload_status' => $option->workload_status ?? 'light',
            'workload_open_tickets' => (int) ($option->workload_open_tickets ?? 0),
            'recommendation_score' => (int) ($option->recommendation_score ?? 0),
        ];
    };
?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($message); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1"><?php echo e($ticket->title); ?></h5>
                        <p class="text-muted mb-0"><?php echo e($ticket->ticket_number); ?></p>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary"><?php echo e($ticket->status?->name ?? '-'); ?></span>
                </div>

                <p class="mb-3"><?php echo e($ticket->description); ?></p>

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Photo Attachments</h6>
                        <span class="badge bg-light text-muted border"><?php echo e($ticket->attachments->count()); ?> file(s)</span>
                    </div>
                    <?php if($ticket->attachments->isEmpty()): ?>
                        <div class="alert alert-light border mb-0">Belum ada lampiran foto pada ticket ini.</div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php $__currentLoopData = $ticket->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4">
                                    <a
                                        href="<?php echo e(route('tickets.attachments.show', [$ticket, $attachment])); ?>"
                                        target="_blank"
                                        class="card h-100 border text-decoration-none"
                                    >
                                        <img
                                            src="<?php echo e(route('tickets.attachments.show', [$ticket, $attachment])); ?>"
                                            alt="<?php echo e($attachment->original_name); ?>"
                                            class="card-img-top"
                                            style="height: 180px; object-fit: cover;"
                                        >
                                        <div class="card-body">
                                            <div class="fw-semibold text-dark text-truncate"><?php echo e($attachment->original_name); ?></div>
                                            <div class="small text-muted">
                                                <?php echo e(strtoupper(str_replace('image/', '', $attachment->mime_type))); ?>

                                                · <?php echo e(number_format($attachment->size_bytes / 1024, 0)); ?> KB
                                            </div>
                                            <div class="small text-muted">
                                                Uploaded by <?php echo e($attachment->uploadedBy?->name ?? 'System'); ?>

                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Requester:</strong> <?php echo e($ticket->requester?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Department:</strong> <?php echo e($ticket->requesterDepartment?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Ticket Type:</strong> <?php echo e($ticket->category?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Ticket Category:</strong> <?php echo e($ticket->subcategory?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Ticket Sub Category:</strong> <?php echo e($ticket->detailSubcategory?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Priority:</strong> <?php echo e($ticket->priority?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Related Service:</strong> <?php echo e($ticket->service?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Related Asset:</strong> <?php echo e($ticket->asset?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Asset Location:</strong> <?php echo e($ticket->assetLocation?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Linked Inspection Task:</strong> <?php echo e($ticket->inspection?->inspection_number ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Assigned Team:</strong> <?php echo e($ticket->assigned_team_name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Assigned Engineer:</strong> <?php echo e($ticket->assignedEngineer?->name ?? '-'); ?></div>
                    <div class="col-md-6">
                        <strong>Approval Rule:</strong>
                        <?php if($ticket->requires_approval): ?>
                            <span class="badge bg-warning-subtle text-warning">Approval Required</span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success">No Approval Needed</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Assignment Rule:</strong>
                        <?php if($ticket->allow_direct_assignment): ?>
                            <span class="badge bg-success-subtle text-success">Direct Assignment Allowed</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">Needs Ready Flag</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6"><strong>Approval Status:</strong> <?php echo e(str($ticket->approval_status ?? 'not_required')->replace('_', ' ')->title()); ?></div>
                    <div class="col-md-6"><strong>Rule Source:</strong> <?php echo e(str($ticket->flow_policy_source ?? 'system_default')->replace('_', ' ')->title()); ?></div>
                    <div class="col-md-6"><strong>Expected Approver:</strong> <?php echo e($ticket->expectedApprover?->name ?? $ticket->expected_approver_name_snapshot ?? 'Supervisor/Admin Fallback'); ?></div>
                    <div class="col-md-6"><strong>Approved By:</strong> <?php echo e($ticket->approvedBy?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Ready For Assignment By:</strong> <?php echo e($ticket->assignmentReadyBy?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Approval Notes:</strong> <?php echo e($ticket->approval_notes ?: '-'); ?></div>
                    <div class="col-md-6"><strong>Response Due:</strong> <?php echo e(optional($ticket->response_due_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Resolution Due:</strong> <?php echo e(optional($ticket->resolution_due_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Started At:</strong> <?php echo e(optional($ticket->started_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Completed At:</strong> <?php echo e(optional($ticket->completed_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Work Duration:</strong> <?php echo e($workDurationMinutes !== null ? $workDurationMinutes.' minute(s)' : '-'); ?></div>
                </div>

                <div class="mt-4">
                    <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-outline-light">Back</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Approval Matrix Summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Effective Approval</div>
                            <div class="fw-semibold"><?php echo e($ticket->requires_approval ? 'Approval Required' : 'No Approval Required'); ?></div>
                            <div class="small text-muted mt-1">Status: <?php echo e($ticket->approvalStatusLabel()); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Effective Assignment</div>
                            <div class="fw-semibold"><?php echo e($ticket->allow_direct_assignment ? 'Direct Assignment Allowed' : 'Needs Ready Flag'); ?></div>
                            <div class="small text-muted mt-1"><?php echo e($ticket->assignmentGateMessage() ?? 'Ticket is ready for assignment flow.'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Resolved Approver</div>
                            <div class="fw-semibold"><?php echo e($ticket->expectedApproverDisplayName()); ?></div>
                            <div class="small text-muted mt-1">
                                <?php echo e($strategyLabels[$ticket->expected_approver_strategy ?? \App\Models\TicketCategory::APPROVER_STRATEGY_FALLBACK] ?? 'Supervisor/Admin Fallback'); ?>

                                <?php if($ticket->expected_approver_role_code): ?>
                                    · <?php echo e($roleLabel($ticket->expected_approver_role_code)); ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Selected Value</th>
                                <th>Approval Policy</th>
                                <th>Assignment Policy</th>
                                <th>Approver Strategy</th>
                                <th>Approver Target</th>
                                <th>Rule Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold">Ticket Type</td>
                                <td><?php echo e($ticket->category?->name ?? '-'); ?></td>
                                <td><?php echo e($ticket->category ? $approvalPolicyLabel($ticket->category->requires_approval, 'System Default') : '-'); ?></td>
                                <td><?php echo e($ticket->category ? $assignmentPolicyLabel($ticket->category->allow_direct_assignment, 'System Default') : '-'); ?></td>
                                <td><?php echo e($approverStrategyLabel($ticket->category, 'Supervisor/Admin Fallback')); ?></td>
                                <td><?php echo e($approverTargetLabel($ticket->category, 'Supervisor/Admin Fallback')); ?></td>
                                <td><span class="badge <?php echo e($policySourceBadge('ticket_type', $ticket->flow_policy_source)); ?>"><?php echo e($ticket->flow_policy_source === 'ticket_type' ? 'Effective Source' : 'Base Rule'); ?></span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Ticket Category</td>
                                <td><?php echo e($ticket->subcategory?->name ?? '-'); ?></td>
                                <td><?php echo e($ticket->subcategory ? $approvalPolicyLabel($ticket->subcategory->requires_approval, 'Follow Ticket Type') : '-'); ?></td>
                                <td><?php echo e($ticket->subcategory ? $assignmentPolicyLabel($ticket->subcategory->allow_direct_assignment, 'Follow Ticket Type') : '-'); ?></td>
                                <td><?php echo e($approverStrategyLabel($ticket->subcategory, 'Follow Ticket Type')); ?></td>
                                <td><?php echo e($approverTargetLabel($ticket->subcategory, 'Follow Ticket Type')); ?></td>
                                <td><span class="badge <?php echo e($policySourceBadge('ticket_category', $ticket->flow_policy_source)); ?>"><?php echo e($ticket->flow_policy_source === 'ticket_category' ? 'Effective Source' : 'Override Layer'); ?></span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Ticket Sub Category</td>
                                <td><?php echo e($ticket->detailSubcategory?->name ?? '-'); ?></td>
                                <td><?php echo e($ticket->detailSubcategory ? $approvalPolicyLabel($ticket->detailSubcategory->requires_approval, 'Follow Ticket Category') : '-'); ?></td>
                                <td><?php echo e($ticket->detailSubcategory ? $assignmentPolicyLabel($ticket->detailSubcategory->allow_direct_assignment, 'Follow Ticket Category') : '-'); ?></td>
                                <td><?php echo e($approverStrategyLabel($ticket->detailSubcategory, 'Follow Ticket Category')); ?></td>
                                <td><?php echo e($approverTargetLabel($ticket->detailSubcategory, 'Follow Ticket Category')); ?></td>
                                <td><span class="badge <?php echo e($policySourceBadge('ticket_sub_category', $ticket->flow_policy_source)); ?>"><?php echo e($ticket->flow_policy_source === 'ticket_sub_category' ? 'Effective Source' : 'Override Layer'); ?></span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Snapshot Applied</td>
                                <td><?php echo e($ticket->flowPolicySourceLabel()); ?></td>
                                <td><?php echo e($ticket->requires_approval ? 'Approval Required' : 'No Approval Required'); ?></td>
                                <td><?php echo e($ticket->allow_direct_assignment ? 'Direct Assignment Allowed' : 'Needs Ready Flag'); ?></td>
                                <td><?php echo e($strategyLabels[$ticket->expected_approver_strategy ?? \App\Models\TicketCategory::APPROVER_STRATEGY_FALLBACK] ?? 'Supervisor/Admin Fallback'); ?></td>
                                <td>
                                    <?php echo e($ticket->expectedApproverDisplayName()); ?>

                                    <?php if($ticket->expected_approver_role_code): ?>
                                        <div class="small text-muted"><?php echo e($roleLabel($ticket->expected_approver_role_code)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-success-subtle text-success">Ticket Snapshot</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Approval History</h5>
            </div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $approvalActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approvalActivity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $approvalLabel = match ($approvalActivity->activity_type) {
                            'ticket_approved' => 'Approved',
                            'ticket_rejected' => 'Rejected',
                            'ticket_ready_for_assignment' => 'Marked Ready For Assignment',
                            default => str($approvalActivity->activity_type)->replace('_', ' ')->title(),
                        };
                    ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold"><?php echo e($approvalLabel); ?></div>
                                <div class="small text-muted">
                                    <?php echo e(optional($approvalActivity->created_at)->format('Y-m-d H:i')); ?>

                                    by <?php echo e($approvalActivity->actor?->name ?? 'System'); ?>

                                </div>
                            </div>
                            <span class="badge <?php echo e($approvalActivity->activity_type === 'ticket_approved' ? 'bg-success-subtle text-success' : ($approvalActivity->activity_type === 'ticket_rejected' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary')); ?>">
                                <?php echo e($approvalLabel); ?>

                            </span>
                        </div>
                        <div class="small text-muted mt-2">
                            <?php echo e(data_get($approvalActivity->metadata, 'notes') ?: 'No notes provided.'); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-muted">Belum ada approval history khusus pada ticket ini.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Worklogs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $ticket->worklogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $worklog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(optional($worklog->created_at)->format('Y-m-d H:i')); ?></td>
                                    <td><?php echo e($worklog->user?->name ?? '-'); ?></td>
                                    <td><?php echo e(ucfirst($worklog->log_type)); ?></td>
                                    <td><?php echo e($worklog->description); ?></td>
                                    <td><?php echo e($worklog->duration_minutes !== null ? $worklog->duration_minutes.' min' : '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No worklog yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Assign Engineer</h5>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 mb-3 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="fw-semibold mb-1">Assignment Gate</div>
                            <?php if($ticket->canBeAssigned()): ?>
                                <div class="text-success">Ticket ini sudah siap untuk assignment.</div>
                            <?php else: ?>
                                <div class="text-warning"><?php echo e($ticket->assignmentGateMessage()); ?></div>
                            <?php endif; ?>
                            <div class="small text-muted mt-1">
                                Policy source: <?php echo e(str($ticket->flow_policy_source ?? 'system_default')->replace('_', ' ')->title()); ?>.
                            </div>
                            <?php if($ticket->requires_approval): ?>
                                <div class="small text-muted mt-1">
                                    Approver: <?php echo e($ticket->expectedApprover?->name ?? $ticket->expected_approver_name_snapshot ?? 'Supervisor/Admin Fallback'); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if(($ticket->requires_approval && ! $ticket->isApproved() && ! $ticket->isRejected()) || (! $ticket->allow_direct_assignment && ! $ticket->isAssignmentReady() && ! $ticket->isRejected() && (! $ticket->requires_approval || $ticket->isApproved()))): ?>
                        <form method="POST" action="<?php echo e(route('tickets.approve', $ticket)); ?>" class="mt-3">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="decision" id="ticket_decision" value="approve">
                            <div class="mb-3">
                                <label for="approval_notes" class="form-label">Approval / Rejection / Readiness Notes</label>
                                <textarea id="approval_notes" name="notes" rows="3" class="form-control" placeholder="Tambahkan catatan approval, alasan reject, atau alasan ticket siap di-assign."><?php echo e(old('notes')); ?></textarea>
                                <div class="form-text">Reject dan Mark Ready sebaiknya selalu disertai alasan agar audit trail lebih jelas.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if($ticket->requires_approval && ! $ticket->isApproved() && ! $ticket->isRejected()): ?>
                                    <button type="submit" class="btn btn-success" name="decision" value="approve"
                                        <?php if(! $ticket->canBeApprovedBy($currentUser)): echo 'disabled'; endif; ?>>
                                        Approve Ticket
                                    </button>
                                    <button type="submit" class="btn btn-outline-danger" formaction="<?php echo e(route('tickets.reject', $ticket)); ?>" name="decision" value="reject"
                                        <?php if(! $ticket->canBeApprovedBy($currentUser)): echo 'disabled'; endif; ?>>
                                        Reject Ticket
                                    </button>
                                <?php endif; ?>

                                <?php if(! $ticket->allow_direct_assignment && ! $ticket->isAssignmentReady() && ! $ticket->isRejected() && (! $ticket->requires_approval || $ticket->isApproved())): ?>
                                    <button type="submit" class="btn btn-outline-primary" formaction="<?php echo e(route('tickets.mark-ready', $ticket)); ?>" name="decision" value="mark_ready">
                                        Mark Ready For Assignment
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if($ticket->requires_approval && ! $ticket->canBeApprovedBy($currentUser)): ?>
                                <div class="small text-muted mt-2">Aksi approve/reject hanya tersedia untuk approver yang ditetapkan atau fallback supervisor/admin.</div>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>

                <form method="GET" action="<?php echo e(route('tickets.show', $ticket)); ?>" class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="small text-muted">Saring shortlist engineer berdasarkan department dan shift operasional sebelum assign.</div>
                    </div>
                    <div class="col-12 col-md-12">
                        <label for="assignment_department_id" class="form-label">Filter Department</label>
                        <select id="assignment_department_id" name="assignment_department_id"
                            class="form-select" data-searchable-select data-force-searchable-select="true"
                            data-search-placeholder="Search department">
                            <option value="">- All Departments -</option>
                            <?php $__currentLoopData = $assignmentDepartmentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $departmentOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($departmentOption['id']); ?>" <?php if((string) ($assignmentFilters['department_id'] ?? '') === (string) $departmentOption['id']): echo 'selected'; endif; ?>>
                                    <?php echo e($departmentOption['name']); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-12">
                        <label for="assignment_team_label" class="form-label">Filter Team / Shift</label>
                        <select id="assignment_team_label" name="assignment_team_label"
                            class="form-select" data-searchable-select data-force-searchable-select="true"
                            data-search-placeholder="Search team or shift">
                            <option value="">- All Teams / Shifts -</option>
                            <?php $__currentLoopData = $assignmentTeamOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teamOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teamOption); ?>" <?php if((string) ($assignmentFilters['team_label'] ?? '') === (string) $teamOption): echo 'selected'; endif; ?>>
                                    <?php echo e($teamOption); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                        <?php if(($assignmentFilters['department_id'] ?? null) || ($assignmentFilters['team_label'] ?? null)): ?>
                            <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="btn btn-outline-light text-nowrap">Reset Filter</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-outline-secondary text-nowrap">Apply Filters</button>
                    </div>
                </form>

                <?php if(($engineerRecommendation['required_skill_labels'] ?? []) !== []): ?>
                    <div class="alert alert-info border">
                        <div class="fw-semibold mb-1">Recommended By Skill Match</div>
                        <div class="small text-muted mb-2">Skor rekomendasi sekarang menggabungkan skill match, availability schedule hari ini, dan workload ticket yang masih terbuka.</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php $__currentLoopData = $engineerRecommendation['required_skill_labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skillLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-primary-subtle text-primary"><?php echo e($skillLabel); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border">
                        Belum ada skill mapping yang cocok untuk ticket ini, jadi sistem masih menampilkan engineer fallback.
                    </div>
                <?php endif; ?>

                <?php if(($engineerOptions->count() ?? 0) === 0 && ($fallbackEngineerOptions->count() ?? 0) === 0): ?>
                    <div class="alert alert-warning border">
                        Tidak ada engineer yang cocok dengan filter department/team saat ini.
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('tickets.assign', $ticket)); ?>" class="row g-3">
                    <?php echo csrf_field(); ?>

                    <input type="hidden" id="assigned_engineer_id" name="assigned_engineer_id" value="<?php echo e(old('assigned_engineer_id', $ticket->assigned_engineer_id)); ?>">

                    <?php if($engineerRecommendation['has_recommendation'] ?? false): ?>
                        <div class="col-12">
                            <div class="row g-2">
                                <?php $__currentLoopData = $engineerOptions->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-12">
                                        <div class="border rounded p-2">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <div class="fw-semibold"><?php echo e($option->name); ?></div>
                                                    <div class="small text-muted">
                                                        <?php echo e($option->department_name ?? 'No department'); ?>

                                                        · <?php echo e($option->team_label ?? 'No team/shift'); ?>

                                                        · <?php echo e($option->workload_open_tickets ?? 0); ?> open ticket(s)
                                                    </div>
                                                    <div class="small text-muted">
                                                        <?php echo e($option->availability_reason ?? ($option->availability_label ?? 'Unknown availability')); ?>

                                                        <?php if(!empty($option->today_shift_name)): ?>
                                                            (<?php echo e($option->today_shift_name); ?>)
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column align-items-end gap-1">
                                                    <span class="badge <?php echo e($scoreBadgeClass((int) ($option->recommendation_score ?? 0))); ?>">Score <?php echo e($option->recommendation_score ?? 0); ?></span>
                                                    <span class="badge <?php echo e($availabilityBadgeClass($option->availability_status ?? null)); ?>"><?php echo e($option->availability_label ?? 'Unknown'); ?></span>
                                                    <span class="badge <?php echo e(($option->workload_status ?? 'light') === 'busy' ? 'bg-danger-subtle text-danger' : (($option->workload_status ?? 'light') === 'moderate' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info')); ?>"><?php echo e($option->workload_label ?? 'Light'); ?></span>
                                                </div>
                                            </div>
                                            <?php if(!empty($option->matched_skill_names)): ?>
                                                <div class="d-flex flex-wrap gap-1 mt-2">
                                                    <?php $__currentLoopData = $option->matched_skill_names; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $matchedSkillName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="badge bg-primary-subtle text-primary"><?php echo e($matchedSkillName); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-3 bg-light-subtle h-100">
                                <div class="fw-semibold mb-2">Recommended Engineers</div>
                                <label for="recommended_engineer_id_ui" class="form-label">Best Match By Score</label>
                                <select id="recommended_engineer_id_ui"
                                    data-searchable-select data-force-searchable-select="true"
                                    data-engineer-picker="true" data-search-placeholder="Search recommended engineer"
                                    class="form-select <?php $__errorArgs = ['assigned_engineer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-assignment-source="recommended">
                                    <option value="">- Select Recommended Engineer -</option>
                                    <?php $__currentLoopData = $engineerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option->id); ?>"
                                            data-custom-properties='<?php echo json_encode($engineerCustomProperties($option), 15, 512) ?>'
                                            <?php if((string) old('assigned_engineer_id', $ticket->assigned_engineer_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                            <?php echo e($option->name); ?>

                                            <?php if(!empty($option->matched_skill_names)): ?>
                                                - <?php echo e(implode(', ', $option->matched_skill_names)); ?>

                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="form-text">Daftar ini diurutkan berdasarkan total skor recommendation, bukan hanya skill yang cocok.</div>
                            </div>
                        </div>

                        <?php if(($fallbackEngineerOptions->count() ?? 0) > 0): ?>
                            <div class="col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-semibold mb-2">Fallback Engineers</div>
                                    <label for="fallback_engineer_id_ui" class="form-label">Alternative Engineer Pool</label>
                                    <select id="fallback_engineer_id_ui"
                                        data-searchable-select data-force-searchable-select="true"
                                        data-engineer-picker="true" data-search-placeholder="Search fallback engineer"
                                        class="form-select" data-assignment-source="fallback">
                                        <option value="">- Use Recommended List -</option>
                                        <?php $__currentLoopData = $fallbackEngineerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($option->id); ?>"
                                                data-custom-properties='<?php echo json_encode($engineerCustomProperties($option), 15, 512) ?>'
                                                <?php if((string) old('assigned_engineer_id', $ticket->assigned_engineer_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                                <?php echo e($option->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="form-text">Dipakai jika supervisor perlu override karena pertimbangan kapasitas, shift, atau kebutuhan lapangan.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <label for="fallback_engineer_id_ui" class="form-label">Engineer</label>
                            <select id="fallback_engineer_id_ui"
                                data-searchable-select data-force-searchable-select="true"
                                data-engineer-picker="true" data-search-placeholder="Search engineer"
                                class="form-select <?php $__errorArgs = ['assigned_engineer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-assignment-source="fallback" required>
                                <option value="">- Select Engineer -</option>
                                <?php $__currentLoopData = $fallbackEngineerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option->id); ?>"
                                        data-custom-properties='<?php echo json_encode($engineerCustomProperties($option), 15, 512) ?>'
                                        <?php if((string) old('assigned_engineer_id', $ticket->assigned_engineer_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Belum ada skill mapping yang match. Urutan fallback tetap mempertimbangkan availability schedule dan workload engineer.</div>
                        </div>
                    <?php endif; ?>

                    <?php $__errorArgs = ['assigned_engineer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="col-12">
                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <div class="col-12">
                        <label for="assigned_team_name" class="form-label">Team</label>
                        <input type="text" id="assigned_team_name" name="assigned_team_name"
                            class="form-control <?php $__errorArgs = ['assigned_team_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('assigned_team_name', $ticket->assigned_team_name)); ?>" placeholder="Ops / Field Team">
                        <?php $__errorArgs = ['assigned_team_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('notes')); ?></textarea>
                        <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100" <?php if(! $ticket->canBeAssigned()): echo 'disabled'; endif; ?>>Assign / Reassign</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Activity Timeline</h5>
            </div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $ticket->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-semibold"><?php echo e(str_replace('_', ' ', strtoupper($activity->activity_type))); ?></div>
                        <div class="small text-muted">
                            <?php echo e(optional($activity->created_at)->format('Y-m-d H:i')); ?>

                            by <?php echo e($activity->actor?->name ?? 'System'); ?>

                        </div>
                        <div class="small">
                            <?php echo e($activity->oldStatus?->name ?? '-'); ?>

                            <span class="text-muted">to</span>
                            <?php echo e($activity->newStatus?->name ?? '-'); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0">No activity yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hiddenAssignedEngineerInput = document.getElementById('assigned_engineer_id');
        const recommendedSelect = document.getElementById('recommended_engineer_id_ui');
        const fallbackSelect = document.getElementById('fallback_engineer_id_ui');

        if (!hiddenAssignedEngineerInput) {
            return;
        }

        const syncAssignmentValue = (source) => {
            if (source === 'recommended' && recommendedSelect) {
                hiddenAssignedEngineerInput.value = recommendedSelect.value || '';
                if (fallbackSelect && recommendedSelect.value) {
                    fallbackSelect.value = '';
                    if (fallbackSelect._choices) {
                        fallbackSelect._choices.removeActiveItems();
                    }
                }
                return;
            }

            if (source === 'fallback' && fallbackSelect) {
                hiddenAssignedEngineerInput.value = fallbackSelect.value || '';
                if (recommendedSelect && fallbackSelect.value) {
                    recommendedSelect.value = '';
                    if (recommendedSelect._choices) {
                        recommendedSelect._choices.removeActiveItems();
                    }
                }
            }
        };

        const bootstrapSelectedState = () => {
            const currentValue = hiddenAssignedEngineerInput.value;
            if (!currentValue) {
                return;
            }

            if (recommendedSelect && Array.from(recommendedSelect.options).some((option) => option.value === currentValue)) {
                recommendedSelect.value = currentValue;
                if (recommendedSelect._choices) {
                    recommendedSelect._choices.setChoiceByValue(currentValue);
                }
                return;
            }

            if (fallbackSelect && Array.from(fallbackSelect.options).some((option) => option.value === currentValue)) {
                fallbackSelect.value = currentValue;
                if (fallbackSelect._choices) {
                    fallbackSelect._choices.setChoiceByValue(currentValue);
                }
            }
        };

        recommendedSelect?.addEventListener('change', function() {
            syncAssignmentValue('recommended');
        });

        fallbackSelect?.addEventListener('change', function() {
            syncAssignmentValue('fallback');
        });

        bootstrapSelectedState();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Ticket Detail'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/tickets/show.blade.php ENDPATH**/ ?>