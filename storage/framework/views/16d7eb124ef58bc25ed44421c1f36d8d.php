<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Ticket Sub Categories'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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
                <select name="ticket_subcategory_id" class="form-select">
                    <option value="">All ticket categories</option>
                    <?php $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['ticket_subcategory_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->category?->name ? $option->category->name.' / ' : ''); ?><?php echo e($option->name); ?>

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
                <a href="<?php echo e(route('master-data.ticket-detail-subcategories.index')); ?>" class="btn btn-outline-light">Reset</a>
                <a href="<?php echo e(route('master-data.ticket-detail-subcategories.create')); ?>" class="btn btn-primary">Add Ticket Sub Category</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket Type</th>
                        <th>Ticket Category</th>
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
                    <?php $__empty_1 = true; $__currentLoopData = $ticketDetailSubcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketDetailSubcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($ticketDetailSubcategory->category?->category?->name ?? '-'); ?></td>
                            <td><?php echo e($ticketDetailSubcategory->category?->name ?? '-'); ?></td>
                            <td><?php echo e($ticketDetailSubcategory->code); ?></td>
                            <td><?php echo e($ticketDetailSubcategory->name); ?></td>
                            <td><?php echo e($ticketDetailSubcategory->description ?: '-'); ?></td>
                            <td>
                                <?php if($ticketDetailSubcategory->requires_approval === null): ?>
                                    <span class="badge bg-light text-muted border">Follow Parent</span>
                                <?php elseif($ticketDetailSubcategory->requires_approval): ?>
                                    <span class="badge bg-warning-subtle text-warning">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success">Not Required</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-medium">
                                    <?php if($ticketDetailSubcategory->approver_strategy): ?>
                                        <?php echo e(\App\Models\TicketCategory::approverStrategies()[$ticketDetailSubcategory->approver_strategy] ?? 'Follow Parent'); ?>

                                    <?php else: ?>
                                        Follow Parent
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted">
                                    <?php echo e($ticketDetailSubcategory->approver?->name
                                        ?? \App\Models\TicketCategory::approverRoleLabel($ticketDetailSubcategory->approver_role_code)
                                        ?? 'Follow Parent'); ?>

                                </div>
                            </td>
                            <td>
                                <?php if($ticketDetailSubcategory->allow_direct_assignment === null): ?>
                                    <span class="badge bg-light text-muted border">Follow Parent</span>
                                <?php elseif($ticketDetailSubcategory->allow_direct_assignment): ?>
                                    <span class="badge bg-success-subtle text-success">Allowed</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Needs Ready Flag</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($ticketDetailSubcategory->is_active): ?>
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.ticket-detail-subcategories.edit', $ticketDetailSubcategory)); ?>"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="<?php echo e(route('master-data.ticket-detail-subcategories.destroy', $ticketDetailSubcategory)); ?>"
                                    class="d-inline" onsubmit="return confirm('Delete this ticket sub category?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No ticket sub categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($ticketDetailSubcategories->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Ticket Sub Categories'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/detail-subcategories/index.blade.php ENDPATH**/ ?>