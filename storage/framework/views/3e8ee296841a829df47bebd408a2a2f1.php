<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Administration', 'subtitle' => 'Audit Trail'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Records In View</div>
                <div class="fs-3 fw-semibold"><?php echo e(number_format($logs->total())); ?></div>
                <div class="small text-muted">Total audit rows matching the current filter.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Current Page</div>
                <div class="fs-3 fw-semibold"><?php echo e(number_format($logs->count())); ?></div>
                <div class="small text-muted">Rows visible right now on this page.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Latest Event</div>
                <div class="fs-6 fw-semibold"><?php echo e(optional(optional($logs->first())->created_at)->diffForHumans() ?? 'No data yet'); ?></div>
                <div class="small text-muted">Recency helps judge whether the system is actively logging.</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('audit-trail.index')); ?>" class="row g-3 align-items-end">
            <div class="col-lg-3">
                <label for="audit-search" class="form-label small text-muted mb-1">Search</label>
                <input type="text" id="audit-search" name="search" class="form-control"
                    value="<?php echo e($filters['search']); ?>" placeholder="Action, path, actor, subject">
            </div>
            <div class="col-lg-3">
                <label for="audit-module" class="form-label small text-muted mb-1">Module</label>
                <select id="audit-module" name="module" class="form-select">
                    <option value="">All modules</option>
                    <?php $__currentLoopData = $moduleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $moduleOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($moduleOption); ?>" <?php if($filters['module'] === $moduleOption): echo 'selected'; endif; ?>><?php echo e($moduleOption); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2">
                <label for="audit-actor" class="form-label small text-muted mb-1">Actor</label>
                <select id="audit-actor" name="actor_user_id" class="form-select" data-searchable-select data-search-placeholder="Search actor">
                    <option value="">All actors</option>
                    <?php $__currentLoopData = $actorOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actorOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($actorOption->id); ?>" <?php if((string) $filters['actor_user_id'] === (string) $actorOption->id): echo 'selected'; endif; ?>>
                            <?php echo e($actorOption->name); ?> (<?php echo e($actorOption->email); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2">
                <label for="audit-date-from" class="form-label small text-muted mb-1">Date From</label>
                <input type="date" id="audit-date-from" name="date_from" class="form-control" value="<?php echo e($filters['date_from']); ?>">
            </div>
            <div class="col-lg-2">
                <label for="audit-date-to" class="form-label small text-muted mb-1">Date To</label>
                <input type="date" id="audit-date-to" name="date_to" class="form-control" value="<?php echo e($filters['date_to']); ?>">
            </div>
            <div class="col-12 d-flex justify-content-between gap-2 flex-wrap">
                <a href="<?php echo e(route('audit-trail.index')); ?>" class="btn btn-outline-light">Reset</a>
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Actor</th>
                        <th>Method</th>
                        <th>Path</th>
                        <th>Subject</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div><?php echo e($log->created_at?->format('d M Y H:i')); ?></div>
                                <small class="text-muted"><?php echo e($log->created_at?->diffForHumans()); ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo e($log->module); ?></span></td>
                            <td>
                                <div class="fw-semibold"><?php echo e($log->action); ?></div>
                                <?php if($log->route_name): ?>
                                    <small class="text-muted"><?php echo e($log->route_name); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($log->actor): ?>
                                    <div><?php echo e($log->actor->name); ?></div>
                                    <small class="text-muted"><?php echo e($log->actor->email); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">System / Guest</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?php echo e($log->method); ?></span></td>
                            <td><code><?php echo e($log->path); ?></code></td>
                            <td>
                                <?php if($log->subject_type || $log->subject_id): ?>
                                    <div><?php echo e(class_basename((string) $log->subject_type) ?: '-'); ?></div>
                                    <small class="text-muted"><?php echo e($log->subject_id ?? '-'); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo e(($log->status_code ?? 500) < 300 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'); ?>">
                                    <?php echo e($log->status_code ?? '-'); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="avatar-lg mx-auto mb-3">
                                    <span class="avatar-title rounded-circle bg-light text-muted border">
                                        <iconify-icon icon="solar:document-text-outline" class="fs-32"></iconify-icon>
                                    </span>
                                </div>
                                No audit records yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?php echo e($logs->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Audit Trail'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/audit-trail/index.blade.php ENDPATH**/ ?>