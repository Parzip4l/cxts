<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Role Permission Matrix'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Assigned Permissions</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($role->name); ?></td>
                            <td><?php echo e($role->description ?: '-'); ?></td>
                            <td><?php echo e($role->permissions_count); ?></td>
                            <td>
                                <span class="badge <?php echo e($role->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'); ?>">
                                    <?php echo e($role->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.role-permissions.edit', $role)); ?>" class="btn btn-sm btn-outline-primary">Manage Matrix</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Role Permission Matrix'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/master-data/role-permissions/index.blade.php ENDPATH**/ ?>