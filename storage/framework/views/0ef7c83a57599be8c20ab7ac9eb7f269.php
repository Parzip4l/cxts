<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Role Permission Matrix'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php
    $selectedPermissionIds = $roleRecord->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
?>

<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <h5 class="mb-1"><?php echo e($roleRecord->name); ?></h5>
            <p class="text-muted mb-0"><?php echo e($roleRecord->description ?: 'No role description.'); ?></p>
        </div>

        <form method="POST" action="<?php echo e(route('master-data.role-permissions.update', $roleRecord)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="row g-3">
                <?php $__currentLoopData = $permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-2"><?php echo e(str($groupName)->replace('_', ' ')->title()); ?></div>
                            <div class="d-flex flex-column gap-2">
                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="form-check border rounded px-3 py-2">
                                        <input class="form-check-input me-2" type="checkbox" name="permission_ids[]"
                                            value="<?php echo e($permission->id); ?>" <?php if(in_array((string) $permission->id, $selectedPermissionIds, true)): echo 'checked'; endif; ?>>
                                        <span class="fw-medium"><?php echo e($permission->name); ?></span>
                                        <span class="d-block small text-muted"><?php echo e($permission->code); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Matrix</button>
                <a href="<?php echo e(route('master-data.role-permissions.index')); ?>" class="btn btn-outline-light">Back</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Manage Role Permission Matrix'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/master-data/role-permissions/form.blade.php ENDPATH**/ ?>