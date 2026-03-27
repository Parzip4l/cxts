<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'SLA Rules'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search assignment"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="sla_policy_id" class="form-select">
                    <option value="">All policies</option>
                    <?php $__currentLoopData = $policyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policyOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($policyOption->id); ?>" <?php if((string) ($filters['sla_policy_id'] ?? '') === (string) $policyOption->id): echo 'selected'; endif; ?>>
                            <?php echo e($policyOption->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="">All status</option>
                    <option value="1" <?php if(($filters['is_active'] ?? null) === true): echo 'selected'; endif; ?>>Active</option>
                    <option value="0" <?php if(($filters['is_active'] ?? null) === false): echo 'selected'; endif; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="<?php echo e(route('master-data.sla-policy-assignments.index')); ?>" class="btn btn-outline-light">Reset</a>
                <a href="<?php echo e(route('master-data.sla-policy-assignments.create')); ?>" class="btn btn-primary">Add Rule</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Policy</th>
                        <th>Rule Match</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $slaPolicyAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($assignment->policy?->name ?? '-'); ?></td>
                            <td>
                                <div class="small">
                                    <div><strong>Process Type Key:</strong> <?php echo e($assignment->ticket_type ?? 'Any'); ?></div>
                                    <div><strong>Ticket Type:</strong> <?php echo e($assignment->category?->name ?? 'Any'); ?></div>
                                    <div><strong>Ticket Category:</strong> <?php echo e($assignment->subcategory?->name ?? 'Any'); ?></div>
                                    <div><strong>Ticket Sub Category:</strong> <?php echo e($assignment->detailSubcategory?->name ?? 'Any'); ?></div>
                                    <div><strong>Service:</strong> <?php echo e($assignment->serviceItem?->name ?? 'Any'); ?></div>
                                    <div><strong>Priority:</strong> <?php echo e($assignment->priority?->name ?? 'Any'); ?></div>
                                    <div><strong>Impact/Urgency:</strong> <?php echo e($assignment->impact ?? 'Any'); ?> / <?php echo e($assignment->urgency ?? 'Any'); ?></div>
                                </div>
                            </td>
                            <td><?php echo e($assignment->sort_order); ?></td>
                            <td>
                                <?php if($assignment->is_active): ?>
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.sla-policy-assignments.edit', $assignment)); ?>"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="<?php echo e(route('master-data.sla-policy-assignments.destroy', $assignment)); ?>"
                                    class="d-inline" onsubmit="return confirm('Delete this SLA rule?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No SLA policy assignments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($slaPolicyAssignments->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'SLA Rules'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/sla-policy-assignments/index.blade.php ENDPATH**/ ?>