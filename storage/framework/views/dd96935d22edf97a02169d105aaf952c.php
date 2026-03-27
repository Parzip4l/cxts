<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Engineer Skills'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('master-data.engineer-skills.index')); ?>" class="row g-2 align-items-end mb-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" id="search" name="search" class="form-control" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search code, name, or description">
            </div>

            <div class="col-md-3">
                <label for="is_active" class="form-label">Status</label>
                <select id="is_active" name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" <?php if(($filters['is_active'] ?? null) === true): echo 'selected'; endif; ?>>Active</option>
                    <option value="0" <?php if(($filters['is_active'] ?? null) === false): echo 'selected'; endif; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?php echo e(route('master-data.engineer-skills.index')); ?>" class="btn btn-outline-light">Reset</a>
                <a href="<?php echo e(route('master-data.engineer-skills.create')); ?>" class="btn btn-outline-primary">Add Skill</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $engineerSkills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($skill->code); ?></td>
                            <td><?php echo e($skill->name); ?></td>
                            <td><?php echo e($skill->description ?: '-'); ?></td>
                            <td>
                                <?php if($skill->is_active): ?>
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('master-data.engineer-skills.edit', $skill)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="<?php echo e(route('master-data.engineer-skills.destroy', $skill)); ?>" class="d-inline" onsubmit="return confirm('Delete this skill?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No engineer skill found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?php echo e($engineerSkills->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Engineer Skills'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/master-data/engineer-skills/index.blade.php ENDPATH**/ ?>