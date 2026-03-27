<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Inspection Operations', 'subtitle' => 'Inspection Results'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="row g-3 mb-3">
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Total</small>
                <h4 class="mb-0"><?php echo e(number_format($summary['total'] ?? 0)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Submitted</small>
                <h4 class="mb-0"><?php echo e(number_format($summary['submitted'] ?? 0)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Normal</small>
                <h4 class="mb-0 text-success"><?php echo e(number_format($summary['normal'] ?? 0)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Abnormal</small>
                <h4 class="mb-0 text-danger"><?php echo e(number_format($summary['abnormal'] ?? 0)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">With Ticket</small>
                <h4 class="mb-0 text-warning"><?php echo e(number_format($summary['with_ticket'] ?? 0)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">No Ticket</small>
                <h4 class="mb-0"><?php echo e(number_format($summary['without_ticket'] ?? 0)); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search number, officer, asset, ticket"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($statusOption); ?>" <?php if(($filters['status'] ?? null) === $statusOption): echo 'selected'; endif; ?>>
                            <?php echo e(ucfirst(str_replace('_', ' ', $statusOption))); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="final_result" class="form-select">
                    <option value="">All result</option>
                    <?php $__currentLoopData = $finalResultOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resultOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($resultOption); ?>" <?php if(($filters['final_result'] ?? null) === $resultOption): echo 'selected'; endif; ?>>
                            <?php echo e(strtoupper($resultOption)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="inspection_officer_id" class="form-select"
                    data-searchable-select data-search-placeholder="Search officer">
                    <option value="">All officer</option>
                    <?php $__currentLoopData = $officerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $officerOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($officerOption->id); ?>" <?php if((string) ($filters['inspection_officer_id'] ?? '') === (string) $officerOption->id): echo 'selected'; endif; ?>>
                            <?php echo e($officerOption->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-1">
                <select name="has_ticket" class="form-select">
                    <option value="">Ticket?</option>
                    <option value="yes" <?php if(($filters['has_ticket'] ?? null) === 'yes'): echo 'selected'; endif; ?>>Yes</option>
                    <option value="no" <?php if(($filters['has_ticket'] ?? null) === 'no'): echo 'selected'; endif; ?>>No</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="inspection_date_from" class="form-control"
                    value="<?php echo e($filters['inspection_date_from'] ?? ''); ?>" placeholder="From date">
            </div>
            <div class="col-md-2">
                <input type="date" name="inspection_date_to" class="form-control"
                    value="<?php echo e($filters['inspection_date_to'] ?? ''); ?>" placeholder="To date">
            </div>
            <div class="col-md-2 d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="<?php echo e(route('inspection-results.index')); ?>" class="btn btn-outline-light">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Inspection Task</th>
                        <th>Inspection Date</th>
                        <th>Officer</th>
                        <th>Template</th>
                        <th>Related Asset / Location</th>
                        <th>Status</th>
                        <th>Result</th>
                        <th>Findings</th>
                        <th>Evidence</th>
                        <th>Ticket</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $inspections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inspection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusClass = match ($inspection->status) {
                                \App\Models\Inspection::STATUS_SUBMITTED => 'bg-success-subtle text-success',
                                \App\Models\Inspection::STATUS_IN_PROGRESS => 'bg-warning-subtle text-warning',
                                default => 'bg-secondary-subtle text-secondary',
                            };

                            $resultClass = match ($inspection->final_result) {
                                \App\Models\Inspection::FINAL_RESULT_NORMAL => 'bg-success-subtle text-success',
                                \App\Models\Inspection::FINAL_RESULT_ABNORMAL => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo e($inspection->inspection_number); ?></div>
                                <small class="text-muted"><?php echo e($inspection->summary_notes ? \Illuminate\Support\Str::limit($inspection->summary_notes, 60) : '-'); ?></small>
                            </td>
                            <td><?php echo e(optional($inspection->inspection_date)->format('Y-m-d') ?? '-'); ?></td>
                            <td><?php echo e($inspection->officer?->name ?? '-'); ?></td>
                            <td><?php echo e($inspection->template?->name ?? '-'); ?></td>
                            <td>
                                <div><?php echo e($inspection->asset?->name ?? '-'); ?></div>
                                <small class="text-muted"><?php echo e($inspection->assetLocation?->name ?? '-'); ?></small>
                            </td>
                            <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $inspection->status))); ?></span></td>
                            <td><span class="badge <?php echo e($resultClass); ?>"><?php echo e($inspection->final_result ? strtoupper($inspection->final_result) : '-'); ?></span></td>
                            <td>
                                <small class="d-block">Pass: <?php echo e($inspection->pass_items_count); ?></small>
                                <small class="d-block">Fail: <?php echo e($inspection->fail_items_count); ?></small>
                                <small class="d-block">N/A: <?php echo e($inspection->na_items_count); ?></small>
                            </td>
                            <td><?php echo e(number_format((int) $inspection->evidences_count)); ?></td>
                            <td>
                                <?php if($inspection->ticket?->ticket_number): ?>
                                    <?php if($canOpenTicketDetail): ?>
                                        <a href="<?php echo e(route('tickets.show', $inspection->ticket)); ?>" class="link-primary">
                                            <?php echo e($inspection->ticket->ticket_number); ?>

                                        </a>
                                    <?php else: ?>
                                        <?php echo e($inspection->ticket->ticket_number); ?>

                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('inspection-results.show', $inspection)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No inspection results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($inspections->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Inspection Results'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/inspections/results/index.blade.php ENDPATH**/ ?>