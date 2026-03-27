<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Inspection Result', 'subtitle' => $inspection->inspection_number], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0"><?php echo e($inspection->inspection_number); ?></h5>
                <small class="text-muted">Inspection Date: <?php echo e(optional($inspection->inspection_date)->format('Y-m-d') ?? '-'); ?></small>
            </div>
            <div class="text-end">
                <span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $inspection->status))); ?></span>
                <span class="badge <?php echo e($resultClass); ?>"><?php echo e($inspection->final_result ? strtoupper($inspection->final_result) : '-'); ?></span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6"><strong>Officer:</strong> <?php echo e($inspection->officer?->name ?? '-'); ?></div>
            <div class="col-md-6"><strong>Officer Email:</strong> <?php echo e($inspection->officer?->email ?? '-'); ?></div>
            <div class="col-md-6"><strong>Inspection Template:</strong> <?php echo e($inspection->template?->code ? $inspection->template->code.' - '.$inspection->template->name : ($inspection->template?->name ?? '-')); ?></div>
            <div class="col-md-6"><strong>Related Asset:</strong> <?php echo e($inspection->asset?->code ? $inspection->asset->code.' - '.$inspection->asset->name : ($inspection->asset?->name ?? '-')); ?></div>
            <div class="col-md-6"><strong>Asset Location:</strong> <?php echo e($inspection->assetLocation?->code ? $inspection->assetLocation->code.' - '.$inspection->assetLocation->name : ($inspection->assetLocation?->name ?? '-')); ?></div>
            <div class="col-md-6"><strong>Asset Category:</strong> <?php echo e($inspection->asset?->category?->name ?? '-'); ?></div>
            <div class="col-md-6"><strong>Related Service:</strong> <?php echo e($inspection->asset?->service?->name ?? '-'); ?></div>
            <div class="col-md-6"><strong>Owner Department:</strong> <?php echo e($inspection->asset?->ownerDepartment?->name ?? '-'); ?></div>
            <div class="col-md-6"><strong>Vendor:</strong> <?php echo e($inspection->asset?->vendor?->name ?? '-'); ?></div>
            <div class="col-md-6"><strong>Asset Status:</strong> <?php echo e($inspection->asset?->status?->name ?? '-'); ?></div>
            <div class="col-md-6"><strong>Criticality:</strong> <?php echo e($inspection->asset?->criticality ? strtoupper($inspection->asset->criticality) : '-'); ?></div>
            <div class="col-md-6"><strong>Started At:</strong> <?php echo e(optional($inspection->started_at)->format('Y-m-d H:i') ?? '-'); ?></div>
            <div class="col-md-6"><strong>Submitted At:</strong> <?php echo e(optional($inspection->submitted_at)->format('Y-m-d H:i') ?? '-'); ?></div>
            <div class="col-md-6">
                <strong>Linked Ticket:</strong>
                <?php if($inspection->ticket?->ticket_number): ?>
                    <?php if($canOpenTicketDetail): ?>
                        <a href="<?php echo e(route('tickets.show', $inspection->ticket)); ?>" class="link-primary"><?php echo e($inspection->ticket->ticket_number); ?></a>
                    <?php else: ?>
                        <?php echo e($inspection->ticket->ticket_number); ?>

                    <?php endif; ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </div>
            <div class="col-md-6"><strong>Ticket Status:</strong> <?php echo e($inspection->ticket?->status?->name ?? '-'); ?></div>
            <div class="col-12"><strong>Summary Notes:</strong><br><?php echo e($inspection->summary_notes ?: '-'); ?></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Inspection Checklist</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Expected</th>
                        <th>Result</th>
                        <th>Value</th>
                        <th>Notes</th>
                        <th>Checked By</th>
                        <th>Checked At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $inspection->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item->sequence); ?></td>
                            <td><?php echo e($item->item_label); ?></td>
                            <td><?php echo e($item->expected_value ?? '-'); ?></td>
                            <td><?php echo e($item->result_status ? strtoupper($item->result_status) : '-'); ?></td>
                            <td><?php echo e($item->result_value ?? '-'); ?></td>
                            <td><?php echo e($item->notes ?? '-'); ?></td>
                            <td><?php echo e($item->checkedBy?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($item->checked_at)->format('Y-m-d H:i') ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No checklist item found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Inspection Evidences</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Related Item</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Uploaded By</th>
                        <th>Uploaded At</th>
                        <th>Notes</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $inspection->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evidence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($evidence->original_name); ?></td>
                            <td><?php echo e($evidence->inspectionItem?->item_label ?? '-'); ?></td>
                            <td><?php echo e($evidence->mime_type ?? '-'); ?></td>
                            <td><?php echo e($evidence->file_size ? number_format($evidence->file_size / 1024, 2).' KB' : '-'); ?></td>
                            <td><?php echo e($evidence->uploadedBy?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($evidence->created_at)->format('Y-m-d H:i') ?? '-'); ?></td>
                            <td><?php echo e($evidence->notes ?? '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($evidence->file_path)); ?>"
                                    target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No evidence uploaded.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?php echo e(route('inspection-results.index')); ?>" class="btn btn-outline-light">Back</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Inspection Result Detail'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/inspections/results/show.blade.php ENDPATH**/ ?>