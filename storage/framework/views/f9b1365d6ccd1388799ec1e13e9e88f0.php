<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Permissions'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search code or name"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="group_name" class="form-select">
                    <option value="">All groups</option>
                    <?php $__currentLoopData = $groupOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($groupOption); ?>" <?php if((string) ($filters['group_name'] ?? '') === (string) $groupOption): echo 'selected'; endif; ?>>
                            <?php echo e(str($groupOption)->replace('_', ' ')->title()); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="<?php echo e(route('master-data.permissions.index')); ?>" class="btn btn-outline-light">Reset</a>
                <a href="<?php echo e(route('master-data.permissions.create')); ?>" class="btn btn-primary">Add Permission</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($permission->code); ?></td>
                            <td><?php echo e($permission->name); ?></td>
                            <td><?php echo e($permission->group_name ? str($permission->group_name)->replace('_', ' ')->title() : '-'); ?></td>
                            <td>
                                <span class="badge <?php echo e($permission->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'); ?>">
                                    <?php echo e($permission->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.permissions.edit', $permission)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="<?php echo e(route('master-data.permissions.destroy', $permission)); ?>" class="d-inline"
                                    onsubmit="return confirm('Delete this permission?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No permissions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($permissions->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Permissions'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/master-data/permissions/index.blade.php ENDPATH**/ ?>