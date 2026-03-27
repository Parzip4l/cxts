<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => $ticket->ticket_number], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php
    $statusCode = strtoupper((string) ($ticket->status?->code ?? ''));
    $isTerminal = $ticket->completed_at !== null || $ticket->closed_at !== null || in_array($statusCode, ['COMPLETED', 'CLOSED'], true);
    $canStart = !$isTerminal && $ticket->started_at === null;
    $canPause = !$isTerminal && $ticket->started_at !== null && $ticket->paused_at === null;
    $canResume = !$isTerminal && $ticket->started_at !== null && $ticket->paused_at !== null;
    $canComplete = !$isTerminal && $ticket->started_at !== null;
    $workEndedAt = $ticket->completed_at ?? $ticket->resolved_at ?? $ticket->closed_at;
    $workDurationMinutes = ($ticket->started_at && $workEndedAt) ? $ticket->started_at->diffInMinutes($workEndedAt) : null;
?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?>

                <h5 class="mb-1"><?php echo e($ticket->title); ?></h5>
                <p class="text-muted mb-3"><?php echo e($ticket->ticket_number); ?></p>

                <p class="mb-3"><?php echo e($ticket->description); ?></p>

                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Status:</strong> <?php echo e($ticket->status?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Priority:</strong> <?php echo e($ticket->priority?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Ticket Type:</strong> <?php echo e($ticket->category?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Ticket Category:</strong> <?php echo e($ticket->subcategory?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Ticket Sub Category:</strong> <?php echo e($ticket->detailSubcategory?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Related Service:</strong> <?php echo e($ticket->service?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Related Asset:</strong> <?php echo e($ticket->asset?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Asset Location:</strong> <?php echo e($ticket->assetLocation?->name ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Started At:</strong> <?php echo e(optional($ticket->started_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Paused At:</strong> <?php echo e(optional($ticket->paused_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Completed At:</strong> <?php echo e(optional($ticket->completed_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Work Duration:</strong> <?php echo e($workDurationMinutes !== null ? $workDurationMinutes.' minute(s)' : '-'); ?></div>
                    <div class="col-md-6"><strong>Response Due:</strong> <?php echo e(optional($ticket->response_due_at)->format('Y-m-d H:i') ?? '-'); ?></div>
                </div>

                <div class="mt-4">
                    <a href="<?php echo e(route('engineer-tasks.index')); ?>" class="btn btn-outline-light">Back to My Tasks</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Worklog</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('engineer-tasks.worklogs.store', $ticket)); ?>" class="row g-3">
                    <?php echo csrf_field(); ?>
                    <div class="col-md-3">
                        <label for="log_type" class="form-label">Type</label>
                        <select id="log_type" name="log_type" class="form-select <?php $__errorArgs = ['log_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $type = old('log_type', 'note'); ?>
                            <option value="note" <?php if($type === 'note'): echo 'selected'; endif; ?>>Note</option>
                            <option value="progress" <?php if($type === 'progress'): echo 'selected'; endif; ?>>Progress</option>
                            <option value="resolution" <?php if($type === 'resolution'): echo 'selected'; endif; ?>>Resolution</option>
                        </select>
                        <?php $__errorArgs = ['log_type'];
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
                    <div class="col-md-3">
                        <label for="started_at" class="form-label">Started At</label>
                        <input type="datetime-local" id="started_at" name="started_at"
                            value="<?php echo e(old('started_at')); ?>" class="form-control <?php $__errorArgs = ['started_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['started_at'];
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
                    <div class="col-md-3">
                        <label for="ended_at" class="form-label">Ended At</label>
                        <input type="datetime-local" id="ended_at" name="ended_at"
                            value="<?php echo e(old('ended_at')); ?>" class="form-control <?php $__errorArgs = ['ended_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['ended_at'];
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
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Save Worklog</button>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
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
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Worklogs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $ticket->worklogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $worklog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(optional($worklog->created_at)->format('Y-m-d H:i')); ?></td>
                                    <td><?php echo e(ucfirst($worklog->log_type)); ?></td>
                                    <td><?php echo e($worklog->description); ?></td>
                                    <td><?php echo e($worklog->duration_minutes !== null ? $worklog->duration_minutes.' min' : '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No worklog yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Task Actions</h5>
            </div>
            <div class="card-body">
                <?php if($errors->has('action')): ?>
                    <div class="alert alert-danger"><?php echo e($errors->first('action')); ?></div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <?php if($canStart): ?>
                        <form method="POST" action="<?php echo e(route('engineer-tasks.start', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="notes" value="Start work from web panel">
                            <button type="submit" class="btn btn-info w-100">Start Work</button>
                        </form>
                    <?php endif; ?>

                    <?php if($canPause): ?>
                        <form method="POST" action="<?php echo e(route('engineer-tasks.pause', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="notes" value="Pause work from web panel">
                            <button type="submit" class="btn btn-warning w-100">Pause Work</button>
                        </form>
                    <?php endif; ?>

                    <?php if($canResume): ?>
                        <form method="POST" action="<?php echo e(route('engineer-tasks.resume', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="notes" value="Resume work from web panel">
                            <button type="submit" class="btn btn-primary w-100">Resume Work</button>
                        </form>
                    <?php endif; ?>

                    <?php if($canComplete): ?>
                        <form method="POST" action="<?php echo e(route('engineer-tasks.complete', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="notes" value="Complete work from web panel">
                            <button type="submit" class="btn btn-success w-100">Complete Work</button>
                        </form>
                    <?php endif; ?>

                    <?php if(! $canStart && ! $canPause && ! $canResume && ! $canComplete): ?>
                        <div class="alert alert-light border mb-0">
                            Tidak ada aksi transisi yang tersedia untuk status task saat ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Activity Timeline</h5>
            </div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $ticket->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-semibold"><?php echo e(str_replace('_', ' ', strtoupper($activity->activity_type))); ?></div>
                        <div class="small text-muted"><?php echo e(optional($activity->created_at)->format('Y-m-d H:i')); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0">No activity yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Task Detail'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/engineer-tasks/show.blade.php ENDPATH**/ ?>