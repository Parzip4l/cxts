<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'Task History'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <a href="<?php echo e(route('engineer-tasks.index')); ?>" class="btn btn-outline-light">Back to Active Tasks</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Completed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($task->ticket_number); ?></td>
                            <td><?php echo e($task->title); ?></td>
                            <td><?php echo e($task->status?->name ?? '-'); ?></td>
                            <td><?php echo e($task->priority?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($task->completed_at)->format('Y-m-d H:i') ?? '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('engineer-tasks.show', $task)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No task history yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($tasks->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Task History'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/engineer-tasks/history.blade.php ENDPATH**/ ?>