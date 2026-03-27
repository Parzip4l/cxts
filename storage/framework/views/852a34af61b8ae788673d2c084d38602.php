<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Inspection Task', 'subtitle' => $inspection->inspection_number], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?>

                <div class="row g-2 small mb-3">
                    <div class="col-md-6"><strong>Inspection Template:</strong> <?php echo e($inspection->template?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Related Asset:</strong> <?php echo e($inspection->asset?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Asset Location:</strong> <?php echo e($inspection->assetLocation?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Assigned Inspector:</strong> <?php echo e($inspection->officer?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Date:</strong> <?php echo e(optional($inspection->inspection_date)->format('Y-m-d')); ?></div>
                    <div class="col-md-6"><strong>Schedule:</strong> <?php echo e(strtoupper($inspection->schedule_type ?? 'none')); ?></div>
                    <div class="col-md-6"><strong>Status:</strong> <?php echo e(ucfirst(str_replace('_', ' ', $inspection->status))); ?></div>
                    <div class="col-md-6"><strong>Final Result:</strong> <?php echo e($inspection->final_result ? strtoupper($inspection->final_result) : '-'); ?></div>
                    <div class="col-md-6"><strong>Generated Ticket:</strong> <?php echo e($inspection->ticket?->ticket_number ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Submitted:</strong> <?php echo e(optional($inspection->submitted_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                </div>

                <?php if($canExecuteInspection && $inspection->status !== \App\Models\Inspection::STATUS_SUBMITTED): ?>
                    <form method="POST" action="<?php echo e(route('inspections.items.update', $inspection)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Expected</th>
                                        <th style="width: 130px;">Status</th>
                                        <th style="width: 160px;">Value</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $inspection->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[<?php echo e($index); ?>][id]" value="<?php echo e($item->id); ?>">
                                                <?php echo e($item->sequence); ?>. <?php echo e($item->item_label); ?>

                                            </td>
                                            <td><?php echo e($item->expected_value ?? '-'); ?></td>
                                            <td>
                                                <select name="items[<?php echo e($index); ?>][result_status]" class="form-select form-select-sm">
                                                    <option value="">-</option>
                                                    <?php $__currentLoopData = $resultStatusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resultStatusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($resultStatusOption); ?>" <?php if($item->result_status === $resultStatusOption): echo 'selected'; endif; ?>>
                                                            <?php echo e(strtoupper($resultStatusOption)); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="items[<?php echo e($index); ?>][result_value]"
                                                    class="form-control form-control-sm" value="<?php echo e($item->result_value); ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="items[<?php echo e($index); ?>][notes]"
                                                    class="form-control form-control-sm" value="<?php echo e($item->notes); ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Item Results</button>
                            <a href="<?php echo e(route('inspections.index')); ?>" class="btn btn-outline-light">Back</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light border">Inspection task ini dalam mode read-only.</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Expected</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $inspection->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->sequence); ?>. <?php echo e($item->item_label); ?></td>
                                        <td><?php echo e($item->expected_value ?? '-'); ?></td>
                                        <td><?php echo e(strtoupper($item->result_status ?? '-')); ?></td>
                                        <td><?php echo e($item->result_value ?? '-'); ?></td>
                                        <td><?php echo e($item->notes ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No inspection items.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo e(route('inspections.index')); ?>" class="btn btn-outline-light">Back</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <?php if($canExecuteInspection && $inspection->status !== \App\Models\Inspection::STATUS_SUBMITTED): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Submit Inspection Result</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('inspections.submit', $inspection)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="final_result" class="form-label">Final Result</label>
                            <?php $finalResult = old('final_result', $inspection->final_result ?: \App\Models\Inspection::FINAL_RESULT_NORMAL); ?>
                            <select id="final_result" name="final_result" class="form-select <?php $__errorArgs = ['final_result'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="<?php echo e(\App\Models\Inspection::FINAL_RESULT_NORMAL); ?>" <?php if($finalResult === \App\Models\Inspection::FINAL_RESULT_NORMAL): echo 'selected'; endif; ?>>Normal</option>
                                <option value="<?php echo e(\App\Models\Inspection::FINAL_RESULT_ABNORMAL); ?>" <?php if($finalResult === \App\Models\Inspection::FINAL_RESULT_ABNORMAL): echo 'selected'; endif; ?>>Abnormal</option>
                            </select>
                            <?php $__errorArgs = ['final_result'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label for="summary_notes" class="form-label">Summary Notes</label>
                            <textarea id="summary_notes" name="summary_notes" rows="3"
                                class="form-control <?php $__errorArgs = ['summary_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('summary_notes', $inspection->summary_notes)); ?></textarea>
                            <?php $__errorArgs = ['summary_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label for="supporting_files" class="form-label">Supporting Files (required if Abnormal)</label>
                            <input type="file" id="supporting_files" name="supporting_files[]"
                                class="form-control <?php $__errorArgs = ['supporting_files'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['supporting_files.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                multiple>
                            <?php $__errorArgs = ['supporting_files'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['supporting_files.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Submit Inspection Result</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if($canExecuteInspection && $inspection->status !== \App\Models\Inspection::STATUS_SUBMITTED): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload Inspection Evidence</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('inspections.evidences.store', $inspection)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-2">
                            <label for="file" class="form-label">File</label>
                            <input type="file" id="file" name="file" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label for="inspection_item_id" class="form-label">Item (Optional)</label>
                            <select id="inspection_item_id" name="inspection_item_id" class="form-select">
                                <option value="">- General evidence -</option>
                                <?php $__currentLoopData = $inspection->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($item->id); ?>"><?php echo e($item->sequence); ?>. <?php echo e($item->item_label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" name="notes" rows="2" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">Upload</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Inspection Evidence</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php $__empty_1 = true; $__currentLoopData = $inspection->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evidence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="list-group-item">
                            <a href="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($evidence->file_path)); ?>" target="_blank">
                                <?php echo e($evidence->original_name); ?>

                            </a>
                            <div class="small text-muted"><?php echo e($evidence->notes ?: '-'); ?></div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="list-group-item text-muted">No inspection evidence uploaded.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Inspection Task Detail'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/inspections/inspections/show.blade.php ENDPATH**/ ?>