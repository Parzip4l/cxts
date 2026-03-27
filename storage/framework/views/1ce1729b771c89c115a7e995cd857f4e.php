<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Ticket Categories'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search code or name"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="ticket_category_id" class="form-select">
                    <option value="">All ticket types</option>
                    <?php $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['ticket_category_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

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
                <a href="<?php echo e(route('master-data.ticket-subcategories.index')); ?>" class="btn btn-outline-light">Reset</a>
                <a href="<?php echo e(route('master-data.ticket-subcategories.create')); ?>" class="btn btn-primary">Add Ticket Category</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket Type</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Approval</th>
                        <th>Approver Matrix</th>
                        <th>Direct Assign</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $ticketSubcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketSubcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($ticketSubcategory->category?->name ?? '-'); ?></td>
                            <td><?php echo e($ticketSubcategory->code); ?></td>
                            <td><?php echo e($ticketSubcategory->name); ?></td>
                            <td><?php echo e($ticketSubcategory->description ?: '-'); ?></td>
                            <td>
                                <?php if($ticketSubcategory->requires_approval === null): ?>
                                    <span class="badge bg-light text-muted border">Follow Type</span>
                                <?php elseif($ticketSubcategory->requires_approval): ?>
                                    <span class="badge bg-warning-subtle text-warning">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success">Not Required</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-medium">
                                    <?php if($ticketSubcategory->approver_strategy): ?>
                                        <?php echo e(\App\Models\TicketCategory::approverStrategies()[$ticketSubcategory->approver_strategy] ?? 'Follow Type'); ?>

                                    <?php else: ?>
                                        Follow Ticket Type
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted">
                                    <?php echo e($ticketSubcategory->approver?->name
                                        ?? \App\Models\TicketCategory::approverRoleLabel($ticketSubcategory->approver_role_code)
                                        ?? 'Follow Ticket Type'); ?>

                                </div>
                            </td>
                            <td>
                                <?php if($ticketSubcategory->allow_direct_assignment === null): ?>
                                    <span class="badge bg-light text-muted border">Follow Type</span>
                                <?php elseif($ticketSubcategory->allow_direct_assignment): ?>
                                    <span class="badge bg-success-subtle text-success">Allowed</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Needs Ready Flag</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($ticketSubcategory->is_active): ?>
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.ticket-subcategories.edit', $ticketSubcategory)); ?>"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="<?php echo e(route('master-data.ticket-subcategories.destroy', $ticketSubcategory)); ?>"
                                    class="d-inline" onsubmit="return confirm('Delete this ticket category?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No ticket subcategories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($ticketSubcategories->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Ticket Categories'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/subcategories/index.blade.php ENDPATH**/ ?>