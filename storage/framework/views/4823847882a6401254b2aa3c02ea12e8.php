<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Assets'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="location_view" value="<?php echo e($selectedLocationViewId); ?>">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search asset"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <select name="asset_category_id" class="form-select">
                    <option value="">All categories</option>
                    <?php $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['asset_category_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="asset_status_id" class="form-select">
                    <option value="">All statuses</option>
                    <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['asset_status_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="criticality" class="form-select">
                    <option value="">All criticality</option>
                    <?php $__currentLoopData = $criticalityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criticalityOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($criticalityOption); ?>" <?php if(($filters['criticality'] ?? null) === $criticalityOption): echo 'selected'; endif; ?>>
                            <?php echo e(ucfirst($criticalityOption)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-1">
                <select name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" <?php if(($filters['is_active'] ?? null) === true): echo 'selected'; endif; ?>>Active</option>
                    <option value="0" <?php if(($filters['is_active'] ?? null) === false): echo 'selected'; endif; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="<?php echo e(route('master-data.assets.index')); ?>" class="btn btn-outline-light">Reset</a>
            </div>
            <div class="col-12 text-end">
                <a href="<?php echo e(route('master-data.assets.create')); ?>" class="btn btn-primary">Add Asset</a>
            </div>
        </form>

        <?php if($locationViews->isNotEmpty()): ?>
            <?php
                $baseQuery = request()->except(['location_view', 'page']);
                $accordionId = 'asset-location-accordion';
            ?>
            <div class="mb-3">
                <label class="form-label mb-2">Three View by Location</label>
                <div class="accordion" id="<?php echo e($accordionId); ?>">
                    <?php $__currentLoopData = $locationViews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locationView): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isSelectedLocationView = (int) $selectedLocationViewId === (int) $locationView->id;
                            $locationViewUrl = route(
                                'master-data.assets.index',
                                array_merge($baseQuery, ['location_view' => $locationView->id]),
                            );
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="asset-location-heading-<?php echo e($locationView->id); ?>">
                                <a class="accordion-button <?php if(!$isSelectedLocationView): ?> collapsed <?php endif; ?>"
                                    href="<?php echo e($locationViewUrl); ?>"
                                    aria-expanded="<?php echo e($isSelectedLocationView ? 'true' : 'false'); ?>"
                                    aria-controls="asset-location-collapse-<?php echo e($locationView->id); ?>">
                                    <span><?php echo e($locationView->name); ?></span>
                                    <span class="ms-2 badge bg-light text-dark">
                                        <?php echo e((int) ($locationViewCounts[$locationView->id] ?? 0)); ?>

                                    </span>
                                </a>
                            </h2>
                            <div id="asset-location-collapse-<?php echo e($locationView->id); ?>"
                                class="accordion-collapse collapse <?php if($isSelectedLocationView): ?> show <?php endif; ?>"
                                aria-labelledby="asset-location-heading-<?php echo e($locationView->id); ?>"
                                data-bs-parent="#<?php echo e($accordionId); ?>">
                                <div class="accordion-body">
                                    <?php if($isSelectedLocationView): ?>
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Name</th>
                                                        <th>Category</th>
                                                        <th>Service</th>
                                                        <th>Location</th>
                                                        <th>Status</th>
                                                        <th>Criticality</th>
                                                        <th class="text-end">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <tr>
                                                            <td><?php echo e($asset->code); ?></td>
                                                            <td><?php echo e($asset->name); ?></td>
                                                            <td><?php echo e($asset->category?->name ?? '-'); ?></td>
                                                            <td><?php echo e($asset->service?->name ?? '-'); ?></td>
                                                            <td><?php echo e($asset->location?->name ?? '-'); ?></td>
                                                            <td><?php echo e($asset->status?->name ?? '-'); ?></td>
                                                            <td><span class="badge bg-info-subtle text-info"><?php echo e(ucfirst($asset->criticality)); ?></span></td>
                                                            <td class="text-end">
                                                                <a href="<?php echo e(route('master-data.assets.edit', $asset)); ?>"
                                                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                                                <form method="POST"
                                                                    action="<?php echo e(route('master-data.assets.destroy', $asset)); ?>"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Delete this asset?')">
                                                                    <?php echo csrf_field(); ?>
                                                                    <?php echo method_field('DELETE'); ?>
                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted py-4">No assets found.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3">
                                            <?php echo e($assets->appends(['location_view' => $locationView->id])->links()); ?>

                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted d-block">
                                            Klik lokasi ini untuk menampilkan data asset di dalam panel accordion.
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <small class="text-muted d-block mt-2">View dibagi menjadi 3 lokasi aktif teratas sesuai kebutuhan operasional.</small>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Assets'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/master-data/assets/index.blade.php ENDPATH**/ ?>