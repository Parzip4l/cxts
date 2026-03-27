<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Inspection Operations', 'subtitle' => $isOpsActor ? 'Inspection Tasks' : 'My Inspection Tasks'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="vstack gap-3 mb-4">
            <div class="row g-3">
                <div class="col-12 col-xl-4">
                    <label for="inspection-search" class="form-label small text-muted mb-1">Search</label>
                    <input
                        id="inspection-search"
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search task number or asset"
                        value="<?php echo e($filters['search'] ?? ''); ?>"
                    >
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label for="inspection-status" class="form-label small text-muted mb-1">Status</label>
                    <select id="inspection-status" name="status" class="form-select">
                        <option value="">All status</option>
                        <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($statusOption); ?>" <?php if(($filters['status'] ?? null) === $statusOption): echo 'selected'; endif; ?>>
                                <?php echo e(ucfirst(str_replace('_', ' ', $statusOption))); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label for="inspection-date" class="form-label small text-muted mb-1">Inspection date</label>
                    <input
                        id="inspection-date"
                        type="date"
                        name="inspection_date"
                        class="form-control"
                        value="<?php echo e($filters['inspection_date'] ?? ''); ?>"
                    >
                </div>
                <?php if($isOpsActor): ?>
                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="inspection-officer" class="form-label small text-muted mb-1">Officer</label>
                        <select
                            id="inspection-officer"
                            name="inspection_officer_id"
                            class="form-select"
                            data-searchable-select
                            data-search-placeholder="Search officer"
                        >
                            <option value="">All officers</option>
                            <?php $__currentLoopData = $officerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $officer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($officer->id); ?>" <?php if((string) ($filters['inspection_officer_id'] ?? '') === (string) $officer->id): echo 'selected'; endif; ?>>
                                    <?php echo e($officer->name); ?> (<?php echo e(strtoupper($officer->role)); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="inspection-schedule" class="form-label small text-muted mb-1">Schedule</label>
                        <select id="inspection-schedule" name="schedule_type" class="form-select">
                            <option value="">All schedule</option>
                            <?php $__currentLoopData = $scheduleTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scheduleTypeOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($scheduleTypeOption); ?>" <?php if(($filters['schedule_type'] ?? '') === $scheduleTypeOption): echo 'selected'; endif; ?>>
                                    <?php echo e(ucfirst($scheduleTypeOption)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="small text-muted">
                    Filter inspection tasks by status, date, officer, or schedule pattern before opening the task.
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-md-end gap-2">
                    <button class="btn btn-outline-secondary text-nowrap" type="submit">Apply Filter</button>
                    <a href="<?php echo e(route('inspections.index')); ?>" class="btn btn-outline-light text-nowrap">Reset</a>
                    <?php if($isOpsActor): ?>
                        <a href="<?php echo e(route('inspections.create')); ?>" class="btn btn-primary text-nowrap">Schedule Task</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Task Number</th>
                        <th>Inspection Template</th>
                        <th>Related Asset</th>
                        <th>Inspection Date</th>
                        <th>Officer</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Final Result</th>
                        <th>Linked Ticket</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $inspections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inspection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($inspection->inspection_number); ?></td>
                            <td><?php echo e($inspection->template?->name ?? '-'); ?></td>
                            <td><?php echo e($inspection->asset?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($inspection->inspection_date)->format('Y-m-d')); ?></td>
                            <td><?php echo e($inspection->officer?->name ?? '-'); ?></td>
                            <td>
                                <?php echo e(strtoupper($inspection->schedule_type ?? 'none')); ?>

                                <?php if(($inspection->schedule_type ?? 'none') !== 'none'): ?>
                                    <div class="small text-muted">
                                        Interval <?php echo e($inspection->schedule_interval ?? 1); ?>

                                        <?php if(($inspection->schedule_type ?? 'none') === 'daily'): ?>
                                            day(s)
                                        <?php else: ?>
                                            week(s)
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info-subtle text-info"><?php echo e(ucfirst(str_replace('_', ' ', $inspection->status))); ?></span></td>
                            <td><?php echo e($inspection->final_result ? strtoupper($inspection->final_result) : '-'); ?></td>
                            <td><?php echo e($inspection->ticket?->ticket_number ?? '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('inspections.show', $inspection)); ?>" class="btn btn-sm btn-outline-primary">Open</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No inspections found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($inspections->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => $isOpsActor ? 'Inspection Tasks' : 'My Inspection Tasks'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/inspections/inspections/index.blade.php ENDPATH**/ ?>