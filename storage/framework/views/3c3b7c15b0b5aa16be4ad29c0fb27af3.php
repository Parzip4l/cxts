<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Users'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name or email"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All roles</option>
                    <?php $__currentLoopData = $roleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($roleOption->code); ?>" <?php if(($filters['role'] ?? null) === $roleOption->code): echo 'selected'; endif; ?>>
                            <?php echo e($roleOption->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All departments</option>
                    <?php $__currentLoopData = $departmentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $departmentOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($departmentOption->id); ?>" <?php if((string) ($filters['department_id'] ?? '') === (string) $departmentOption->id): echo 'selected'; endif; ?>>
                            <?php echo e($departmentOption->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </div>
            <div class="col-12 d-flex justify-content-between">
                <a href="<?php echo e(route('master-data.users.index')); ?>" class="btn btn-outline-light">Reset</a>
                <div class="d-flex gap-2">
                    <a href="<?php echo e(route('master-data.users.create')); ?>" class="btn btn-primary">Add User</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if($userItem->profilePhotoUrl()): ?>
                                        <img
                                            src="<?php echo e($userItem->profilePhotoUrl()); ?>"
                                            alt="<?php echo e($userItem->name); ?>"
                                            class="rounded-circle object-fit-cover border"
                                            style="width: 36px; height: 36px;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                            style="width: 36px; height: 36px;">
                                            <?php echo e(collect(explode(' ', trim($userItem->name ?: 'NA')))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'NA'); ?>

                                        </div>
                                    <?php endif; ?>
                                    <span><?php echo e($userItem->name); ?></span>
                                </div>
                            </td>
                            <td><?php echo e($userItem->email); ?></td>
                            <td><?php echo e($userItem->phone_number ?? '-'); ?></td>
                            <td>
                                <?php if($userItem->role === 'engineer'): ?>
                                    <span class="badge bg-info-subtle text-info"><?php echo e($userItem->roleRef?->name ?? $userItem->role); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?php echo e($userItem->roleRef?->name ?? $userItem->role); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($userItem->department?->name ?? '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.users.edit', $userItem)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="<?php echo e(route('master-data.users.destroy', $userItem)); ?>"
                                    class="d-inline" onsubmit="return confirm('Delete this user?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($users->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Users'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/master-data/users/index.blade.php ENDPATH**/ ?>