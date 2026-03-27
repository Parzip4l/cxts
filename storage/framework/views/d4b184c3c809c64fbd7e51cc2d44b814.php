<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'SLA Policies'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search SLA policy"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="is_active" class="form-select">
                    <option value="">All status</option>
                    <option value="1" <?php if(($filters['is_active'] ?? null) === true): echo 'selected'; endif; ?>>Active</option>
                    <option value="0" <?php if(($filters['is_active'] ?? null) === false): echo 'selected'; endif; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="<?php echo e(route('master-data.sla-policies.index')); ?>" class="btn btn-outline-light">Reset</a>
                <a href="<?php echo e(route('master-data.sla-policies.create')); ?>" class="btn btn-primary">Add SLA Policy</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Response (min)</th>
                        <th>Resolution (min)</th>
                        <th>Working Hours ID</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $slaPolicies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slaPolicy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo e($slaPolicy->name); ?></div>
                                <div class="text-muted small"><?php echo e($slaPolicy->description ?: '-'); ?></div>
                            </td>
                            <td><?php echo e($slaPolicy->response_time_minutes ?? '-'); ?></td>
                            <td><?php echo e($slaPolicy->resolution_time_minutes ?? '-'); ?></td>
                            <td><?php echo e($slaPolicy->working_hours_id ?? '-'); ?></td>
                            <td>
                                <?php if($slaPolicy->is_active): ?>
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.sla-policies.edit', $slaPolicy)); ?>"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="<?php echo e(route('master-data.sla-policies.destroy', $slaPolicy)); ?>"
                                    class="d-inline" onsubmit="return confirm('Delete this SLA policy?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No SLA policies found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($slaPolicies->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'SLA Policies'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/sla-policies/index.blade.php ENDPATH**/ ?>