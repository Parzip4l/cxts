<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Engineer Schedules'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="vstack gap-3 mb-4">
            <div class="row g-3">
                <div class="col-12 col-xl-4">
                    <label for="schedule-search" class="form-label small text-muted mb-1">Search</label>
                    <input
                        id="schedule-search"
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search engineer"
                        value="<?php echo e($filters['search'] ?? ''); ?>"
                    >
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="schedule-engineer" class="form-label small text-muted mb-1">Engineer</label>
                    <select
                        id="schedule-engineer"
                        name="user_id"
                        class="form-select"
                        data-searchable-select
                        data-search-placeholder="Search engineer"
                    >
                        <option value="">All engineers</option>
                        <?php $__currentLoopData = $engineerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $engineer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($engineer->id); ?>" <?php if((string) ($filters['user_id'] ?? '') === (string) $engineer->id): echo 'selected'; endif; ?>>
                                <?php echo e($engineer->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="schedule-date" class="form-label small text-muted mb-1">Work date</label>
                    <input
                        id="schedule-date"
                        type="date"
                        name="work_date"
                        class="form-control"
                        value="<?php echo e($filters['work_date'] ?? ''); ?>"
                    >
                </div>
                <div class="col-6 col-md-3 col-xl-3">
                    <label for="schedule-status" class="form-label small text-muted mb-1">Status</label>
                    <select id="schedule-status" name="status" class="form-select">
                        <option value="">All status</option>
                        <option value="assigned" <?php if(($filters['status'] ?? '') === 'assigned'): echo 'selected'; endif; ?>>Assigned</option>
                        <option value="off" <?php if(($filters['status'] ?? '') === 'off'): echo 'selected'; endif; ?>>Off</option>
                        <option value="leave" <?php if(($filters['status'] ?? '') === 'leave'): echo 'selected'; endif; ?>>Leave</option>
                        <option value="sick" <?php if(($filters['status'] ?? '') === 'sick'): echo 'selected'; endif; ?>>Sick</option>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="small text-muted">
                    Filter engineer schedules by name, date, or availability status before opening the roster detail.
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-md-end gap-2">
                    <button class="btn btn-outline-secondary text-nowrap" type="submit">Apply Filter</button>
                    <a href="<?php echo e(route('master-data.engineer-schedules.index')); ?>" class="btn btn-outline-light text-nowrap">Reset</a>
                    <a href="<?php echo e(route('master-data.engineer-schedules.create')); ?>" class="btn btn-primary text-nowrap">Add Schedule</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-nowrap align-middle mb-0" id="engineer-schedule-table">
                <thead class="table-light">
                    <tr>
                        <th width="60">No</th>
                        <th>Engineer</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $rowNumber = ($schedules->firstItem() ?? 1) + $loop->index;
                        ?>
                        <tr>
                            <td><?php echo e($rowNumber); ?></td>
                            <td><?php echo e($schedule->engineer?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($schedule->work_date)->format('Y-m-d')); ?></td>
                            <td>
                                <?php echo e($schedule->shift?->name ?? '-'); ?>

                                <?php if($schedule->shift): ?>
                                    <div class="text-muted small">
                                        <?php echo e(substr($schedule->shift->start_time, 0, 5)); ?> - <?php echo e(substr($schedule->shift->end_time, 0, 5)); ?>

                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info-subtle text-info text-uppercase"><?php echo e($schedule->status); ?></span></td>
                            <td><?php echo e($schedule->assignedBy?->name ?? '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.engineer-schedules.edit', $schedule)); ?>"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="<?php echo e(route('master-data.engineer-schedules.destroy', $schedule)); ?>"
                                    class="d-inline" onsubmit="return confirm('Delete this schedule?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No schedules found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($schedules->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Engineer Schedules'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/master-data/engineer-schedules/index.blade.php ENDPATH**/ ?>