<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Engineering'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php
    $dispatchControl = $dispatchControl ?? null;
    $ticketBadgeClass = function ($statusCode) {
        return match (strtolower((string) $statusCode)) {
            'new', 'assigned' => 'bg-primary-subtle text-primary',
            'in_progress' => 'bg-info-subtle text-info',
            'pending_customer', 'on_hold', 'pending_approval' => 'bg-warning-subtle text-warning',
            'completed', 'closed' => 'bg-success-subtle text-success',
            'rejected', 'cancelled' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    };
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('engineering.index')); ?>" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label for="engineering-search" class="form-label small text-muted mb-1">Search</label>
                <input
                    type="text"
                    id="engineering-search"
                    name="search"
                    class="form-control"
                    value="<?php echo e($search); ?>"
                    placeholder="Search engineer name, email, phone, or department">
            </div>
            <div class="col-lg-3">
                <label for="engineering-availability" class="form-label small text-muted mb-1">Availability</label>
                <select id="engineering-availability" name="availability" class="form-select">
                    <option value="">All statuses</option>
                    <option value="available" <?php if($availabilityFilter === 'available'): echo 'selected'; endif; ?>>Available</option>
                    <option value="busy" <?php if($availabilityFilter === 'busy'): echo 'selected'; endif; ?>>Busy</option>
                    <option value="off" <?php if($availabilityFilter === 'off'): echo 'selected'; endif; ?>>Off</option>
                    <option value="unscheduled" <?php if($availabilityFilter === 'unscheduled'): echo 'selected'; endif; ?>>Unscheduled</option>
                </select>
            </div>
            <div class="col-lg-4">
                <div class="d-flex gap-2 justify-content-lg-end">
                    <a href="<?php echo e(route('engineering.index')); ?>" class="btn btn-outline-light text-nowrap">Reset</a>
                    <button type="submit" class="btn btn-primary text-nowrap">Apply Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-primary-subtle text-primary mb-3">Today</span>
                <h3 class="mb-1"><?php echo e($summary['total_engineers']); ?></h3>
                <p class="text-muted mb-0">Engineers tracked for <?php echo e($today->format('d M Y')); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-success-subtle text-success mb-3">Available</span>
                <h3 class="mb-1"><?php echo e($summary['available']); ?></h3>
                <p class="text-muted mb-0">Ready to receive assignment</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-danger-subtle text-danger mb-3">Busy</span>
                <h3 class="mb-1"><?php echo e($summary['busy']); ?></h3>
                <p class="text-muted mb-0">Currently handling active work</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-secondary-subtle text-secondary mb-3">Unavailable</span>
                <h3 class="mb-1"><?php echo e($summary['off'] + $summary['unscheduled']); ?></h3>
                <p class="text-muted mb-0">Off duty, leave, sick, or unscheduled</p>
            </div>
        </div>
    </div>
</div>

<?php if($dispatchControl): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h5 class="mb-1">Dispatch Control Tower</h5>
                    <p class="text-muted mb-0 small">Queue assignment, follow-up, dan closure yang perlu dikawal shift ini.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e($dispatchControl['links']['ready_queue']); ?>" class="btn btn-outline-primary btn-sm text-nowrap">
                        <iconify-icon icon="solar:plain-2-outline" class="me-1"></iconify-icon>
                        Ready Queue
                    </a>
                    <a href="<?php echo e($dispatchControl['links']['pending_customer_queue']); ?>" class="btn btn-outline-warning btn-sm text-nowrap">
                        <iconify-icon icon="solar:user-block-outline" class="me-1"></iconify-icon>
                        Pending Customer
                    </a>
                    <a href="<?php echo e($dispatchControl['links']['completed_queue']); ?>" class="btn btn-outline-success btn-sm text-nowrap">
                        <iconify-icon icon="solar:check-read-outline" class="me-1"></iconify-icon>
                        Awaiting Close
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="rounded-3 border bg-light-subtle p-3 h-100">
                        <div class="text-muted small mb-1">Ready Assignment</div>
                        <div class="fs-4 fw-semibold"><?php echo e(number_format($dispatchControl['summary']['ready_assignment'])); ?></div>
                        <div class="small text-muted">Belum punya engineer owner</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="rounded-3 border bg-light-subtle p-3 h-100">
                        <div class="text-muted small mb-1">Pending Customer</div>
                        <div class="fs-4 fw-semibold"><?php echo e(number_format($dispatchControl['summary']['pending_customer'])); ?></div>
                        <div class="small text-muted">Butuh follow-up requester</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="rounded-3 border bg-light-subtle p-3 h-100">
                        <div class="text-muted small mb-1">Awaiting Close</div>
                        <div class="fs-4 fw-semibold"><?php echo e(number_format($dispatchControl['summary']['completed_waiting_close'])); ?></div>
                        <div class="small text-muted">Completed belum closed</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="rounded-3 border bg-light-subtle p-3 h-100">
                        <div class="text-muted small mb-1">High Load Engineers</div>
                        <div class="fs-4 fw-semibold"><?php echo e(number_format($dispatchControl['summary']['high_load_engineers'])); ?></div>
                        <div class="small text-muted">Kandidat rebalance</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">Ready For Assignment</div>
                                <div class="small text-muted">Prioritas ticket yang perlu owner engineer.</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary"><?php echo e($dispatchControl['ready_tickets']->count()); ?></span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <?php $__empty_1 = true; $__currentLoopData = $dispatchControl['ready_tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="rounded-3 border bg-white p-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="fw-semibold text-dark text-decoration-none"><?php echo e($ticket->ticket_number); ?></a>
                                            <div class="small text-muted text-truncate"><?php echo e($ticket->title); ?></div>
                                            <div class="small text-muted"><?php echo e($ticket->requesterDepartment?->name ?? 'No department'); ?></div>
                                        </div>
                                        <span class="badge <?php echo e($ticketBadgeClass($ticket->status?->code)); ?>"><?php echo e($ticket->status?->name ?? '-'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-muted small">Tidak ada ticket yang menunggu assignment.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">Follow-up Queue</div>
                                <div class="small text-muted">Ticket pending customer yang perlu dikejar.</div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning"><?php echo e($dispatchControl['pending_customer_tickets']->count()); ?></span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <?php $__empty_1 = true; $__currentLoopData = $dispatchControl['pending_customer_tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="rounded-3 border bg-white p-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="fw-semibold text-dark text-decoration-none"><?php echo e($ticket->ticket_number); ?></a>
                                            <div class="small text-muted text-truncate"><?php echo e($ticket->title); ?></div>
                                            <div class="small text-muted">Owner: <?php echo e($ticket->assignedEngineer?->name ?? 'Unassigned'); ?></div>
                                        </div>
                                        <span class="badge <?php echo e($ticketBadgeClass($ticket->status?->code)); ?>"><?php echo e($ticket->status?->name ?? '-'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-muted small">Tidak ada ticket pending customer.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">Completed Awaiting Close</div>
                                <div class="small text-muted">Ticket selesai kerja tapi belum ditutup ops.</div>
                            </div>
                            <span class="badge bg-success-subtle text-success"><?php echo e($dispatchControl['completed_waiting_close_tickets']->count()); ?></span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <?php $__empty_1 = true; $__currentLoopData = $dispatchControl['completed_waiting_close_tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="rounded-3 border bg-white p-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="fw-semibold text-dark text-decoration-none"><?php echo e($ticket->ticket_number); ?></a>
                                            <div class="small text-muted text-truncate"><?php echo e($ticket->title); ?></div>
                                            <div class="small text-muted">Owner: <?php echo e($ticket->assignedEngineer?->name ?? 'Unassigned'); ?></div>
                                        </div>
                                        <span class="badge <?php echo e($ticketBadgeClass($ticket->status?->code)); ?>"><?php echo e($ticket->status?->name ?? '-'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-muted small">Tidak ada completed ticket yang menunggu close.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-xl-6">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold mb-3">Fallback Engineer Pool</div>
                        <div class="d-flex flex-column gap-2">
                            <?php $__empty_1 = true; $__currentLoopData = $dispatchControl['available_engineers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between align-items-center gap-3 border-bottom pb-2">
                                    <div>
                                        <div class="fw-semibold"><?php echo e($card['engineer']->name); ?></div>
                                        <div class="small text-muted"><?php echo e($card['engineer']->department?->name ?? 'No department'); ?> · <?php echo e($card['shift_label']); ?></div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success"><?php echo e($card['active_ticket_count']); ?> active</span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-muted small">Tidak ada engineer available pada filter saat ini.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold mb-3">Rebalance Watchlist</div>
                        <div class="d-flex flex-column gap-2">
                            <?php $__empty_1 = true; $__currentLoopData = $dispatchControl['busy_engineers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between align-items-center gap-3 border-bottom pb-2">
                                    <div>
                                        <div class="fw-semibold"><?php echo e($card['engineer']->name); ?></div>
                                        <div class="small text-muted"><?php echo e($card['engineer']->department?->name ?? 'No department'); ?> · <?php echo e($card['workload_label']); ?></div>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger"><?php echo e($card['active_ticket_count']); ?> active</span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-muted small">Tidak ada engineer high load pada filter saat ini.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 pt-4 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Department Workload</h5>
                <p class="text-muted mb-0 small">Ringkasan kapasitas dan beban engineer per department.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border"><?php echo e($departmentSummary->count()); ?> Departments</span>
                <button
                    class="btn btn-outline-light btn-sm text-nowrap"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#departmentWorkloadPanel"
                    aria-expanded="true"
                    aria-controls="departmentWorkloadPanel">
                    Tampilkan / Sembunyikan
                </button>
            </div>
        </div>
    </div>
    <div class="collapse show" id="departmentWorkloadPanel">
    <div class="card-body pt-3">
        <div class="row g-3">
            <?php $__empty_1 = true; $__currentLoopData = $departmentSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-md-6 col-xl-4">
                    <div class="rounded-3 border bg-light-subtle p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h6 class="mb-1"><?php echo e($department['department']); ?></h6>
                                <div class="text-muted small"><?php echo e($department['engineer_count']); ?> engineers</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary"><?php echo e($department['active_ticket_count']); ?> active</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-success-subtle text-success"><?php echo e($department['available_count']); ?> available</span>
                            <span class="badge bg-danger-subtle text-danger"><?php echo e($department['busy_count']); ?> busy</span>
                            <span class="badge bg-info-subtle text-info"><?php echo e($department['in_progress_ticket_count']); ?> in progress</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <div class="text-muted small">No department workload data available for the current filter.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
</div>

<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <?php if($card['profile_photo_url']): ?>
                                <img
                                    src="<?php echo e($card['profile_photo_url']); ?>"
                                    alt="<?php echo e($card['engineer']->name); ?>"
                                    class="rounded-circle object-fit-cover border"
                                    style="width: 56px; height: 56px;">
                            <?php else: ?>
                                <div class="avatar-md rounded-circle bg-<?php echo e($card['avatar_class']); ?> bg-opacity-10 text-<?php echo e($card['avatar_class']); ?> d-flex align-items-center justify-content-center fw-bold fs-5">
                                    <?php echo e($card['avatar_initials']); ?>

                                </div>
                            <?php endif; ?>
                            <div>
                                <h5 class="mb-1"><?php echo e($card['engineer']->name); ?></h5>
                                <p class="text-muted mb-0"><?php echo e($card['engineer']->department?->name ?? 'No department'); ?></p>
                            </div>
                        </div>
                        <span class="badge bg-<?php echo e($card['availability_class']); ?>-subtle text-<?php echo e($card['availability_class']); ?>">
                            <?php echo e($card['availability_label']); ?>

                        </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border"><?php echo e($card['schedule_status_label']); ?></span>
                        <span class="badge bg-<?php echo e($card['schedule_status_class']); ?>-subtle text-<?php echo e($card['schedule_status_class']); ?>">
                            <?php echo e($card['shift_label']); ?>

                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="rounded-3 bg-light p-3 h-100">
                                <div class="text-muted small mb-1">Active Tickets</div>
                                <div class="fs-4 fw-semibold"><?php echo e($card['active_ticket_count']); ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 bg-light p-3 h-100">
                                <div class="text-muted small mb-1">In Progress</div>
                                <div class="fs-4 fw-semibold"><?php echo e($card['in_progress_ticket_count']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted small text-uppercase fw-semibold">Workload</div>
                            <span class="small fw-semibold"><?php echo e($card['workload_label']); ?> · <?php echo e($card['workload_percent']); ?>%</span>
                        </div>
                        <div class="progress bg-light" style="height: 10px;">
                            <div
                                class="progress-bar bg-<?php echo e($card['availability_class']); ?>"
                                role="progressbar"
                                style="width: <?php echo e($card['workload_percent']); ?>%;"
                                aria-valuenow="<?php echo e($card['workload_percent']); ?>"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-2">Skills</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php $__empty_2 = true; $__currentLoopData = $card['skill_names']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skillName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <span class="badge bg-info-subtle text-info"><?php echo e($skillName); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <span class="text-muted small">No skills mapped yet</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-auto pt-2 border-top">
                        <div class="small text-muted mb-1"><?php echo e($card['engineer']->email); ?></div>
                        <?php if($card['engineer']->phone_number): ?>
                            <div class="small text-muted mb-1"><?php echo e($card['engineer']->phone_number); ?></div>
                        <?php endif; ?>
                        <?php if($card['schedule_notes']): ?>
                            <div class="small text-muted">Notes: <?php echo e($card['schedule_notes']); ?></div>
                        <?php endif; ?>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <?php if($card['whatsapp_url']): ?>
                                <a href="<?php echo e($card['whatsapp_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm text-nowrap">
                                    WhatsApp
                                </a>
                            <?php endif; ?>
                            <?php if($card['tel_url']): ?>
                                <a href="<?php echo e($card['tel_url']); ?>" class="btn btn-outline-primary btn-sm text-nowrap">
                                    Telepon
                                </a>
                            <?php elseif(! $card['whatsapp_url']): ?>
                                <a href="mailto:<?php echo e($card['engineer']->email); ?>" class="btn btn-outline-primary btn-sm text-nowrap">
                                    Email
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <h5 class="mb-2">No engineer data found</h5>
                    <p class="text-muted mb-0">Add engineer users and schedules first to populate this board.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if($cards->hasPages()): ?>
    <div class="mt-4 d-flex justify-content-end">
        <?php echo e($cards->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Engineering'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/engineering/index.blade.php ENDPATH**/ ?>