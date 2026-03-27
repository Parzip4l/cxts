<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'My Schedule'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="date" name="work_date_from" class="form-control" value="<?php echo e($filters['work_date_from'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="work_date_to" class="form-control" value="<?php echo e($filters['work_date_to'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    <option value="assigned" <?php if(($filters['status'] ?? '') === 'assigned'): echo 'selected'; endif; ?>>Assigned</option>
                    <option value="off" <?php if(($filters['status'] ?? '') === 'off'): echo 'selected'; endif; ?>>Off</option>
                    <option value="leave" <?php if(($filters['status'] ?? '') === 'leave'): echo 'selected'; endif; ?>>Leave</option>
                    <option value="sick" <?php if(($filters['status'] ?? '') === 'sick'): echo 'selected'; endif; ?>>Sick</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="<?php echo e(route('engineer-tasks.schedule')); ?>" class="btn btn-outline-light">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
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
                            <td><?php echo e($schedule->notes ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No schedules found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($schedules->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'My Schedule'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/engineer-tasks/schedule.blade.php ENDPATH**/ ?>