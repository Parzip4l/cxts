<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Account', 'subtitle' => 'Notifications'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h5 class="mb-1">Recent Notifications</h5>
                <p class="text-muted mb-0 small">Update operasional terbaru yang relevan dengan akun Anda.</p>
            </div>
            <span class="badge bg-light text-dark border"><?php echo e($notifications->count()); ?> items</span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="rounded-3 border bg-light-subtle p-3 h-100">
                    <div class="text-muted small mb-1">Approval & Escalation</div>
                    <div class="fs-4 fw-semibold"><?php echo e($notifications->where('type', 'approval')->count() + $notifications->where('badge_class', 'danger')->count()); ?></div>
                    <div class="small text-muted">Item yang butuh keputusan cepat.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 border bg-light-subtle p-3 h-100">
                    <div class="text-muted small mb-1">Assignments</div>
                    <div class="fs-4 fw-semibold"><?php echo e($notifications->where('type', 'assignment')->count()); ?></div>
                    <div class="small text-muted">Task dan ticket yang sudah masuk ke engineer.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 border bg-light-subtle p-3 h-100">
                    <div class="text-muted small mb-1">Latest Update</div>
                    <div class="fs-6 fw-semibold"><?php echo e(optional($notifications->first()['occurred_at'] ?? null)->diffForHumans() ?? 'No recent update'); ?></div>
                    <div class="small text-muted">Konteks update paling baru di akun ini.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column gap-3">
            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e($notification['url']); ?>" class="text-decoration-none">
                    <div class="border rounded-3 p-3 notification-item-hover">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="avatar-sm">
                                    <span class="avatar-title rounded-circle bg-<?php echo e($notification['badge_class']); ?>-subtle text-<?php echo e($notification['badge_class']); ?>">
                                        <iconify-icon icon="<?php echo e($notification['icon']); ?>"></iconify-icon>
                                    </span>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <h6 class="mb-0 text-dark"><?php echo e($notification['title']); ?></h6>
                                        <span class="badge bg-<?php echo e($notification['badge_class']); ?>-subtle text-<?php echo e($notification['badge_class']); ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $notification['type']))); ?>

                                        </span>
                                    </div>
                                    <p class="text-muted mb-1"><?php echo e($notification['message']); ?></p>
                                    <small class="text-muted"><?php echo e($notification['occurred_at']->format('d M Y H:i')); ?> · <?php echo e($notification['occurred_at']->diffForHumans()); ?></small>
                                </div>
                            </div>
                            <iconify-icon icon="solar:alt-arrow-right-outline" class="fs-20 text-muted"></iconify-icon>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-3">
                        <span class="avatar-title rounded-circle bg-light text-muted border">
                            <iconify-icon icon="solar:bell-off-outline" class="fs-32"></iconify-icon>
                        </span>
                    </div>
                    <h5 class="mb-2">No notifications yet</h5>
                    <p class="text-muted mb-0">Notification center will populate as tickets, approvals, SLA events, and inspections move.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Notifications'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/notifications/index.blade.php ENDPATH**/ ?>