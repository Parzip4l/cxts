<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Edit ' . $ticket->ticket_number], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php
    $activeAssignedEngineers = $ticket->assignedEngineers->isNotEmpty()
        ? $ticket->assignedEngineers
        : collect([$ticket->assignedEngineer])->filter();
    $selectedAssignedEngineerIds = collect(old('assigned_engineer_ids', $activeAssignedEngineers->pluck('id')->all()))
        ->map(fn ($id) => (string) $id)
        ->all();
    $latestAssignmentNotes = old('assignment_notes', $ticket->assignments->first()?->notes);
    $groupedEngineerOptions = $engineerOptions->groupBy(
        fn ($engineer) => $engineer->department?->name ?? 'No Engineer Team'
    );
    $engineerTeamNameOptions = $groupedEngineerOptions->keys()->filter()->values();
?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small text-muted fw-semibold mb-1">Ticket Adjustment</div>
                <h4 class="mb-1">Edit ticket tanpa cancel</h4>
                <p class="text-muted mb-0">Ubah nama ticket, deskripsi, dan assignment agar score engineer tidak terdampak oleh cancel yang tidak perlu.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="btn btn-outline-light">Back to Detail</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="<?php echo e(route('tickets.update', $ticket)); ?>" class="row g-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="col-lg-7">
                <div class="border rounded-3 p-4 h-100 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Ticket Core</div>
                            <h5 class="mb-1">Summary & Description</h5>
                        </div>
                        <span class="badge bg-primary-subtle text-primary"><?php echo e($ticket->ticket_number); ?></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Nama Ticket</label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('title', $ticket->title)); ?>"
                                maxlength="200"
                                required
                            >
                            <?php $__errorArgs = ['title'];
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

                        <div class="col-12">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="8"
                                class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                required
                            ><?php echo e(old('description', $ticket->description)); ?></textarea>
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
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border rounded-3 p-4 h-100 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Assignment</div>
                            <h5 class="mb-1">Engineer & Team</h5>
                        </div>
                        <span class="badge bg-info-subtle text-info"><?php echo e($activeAssignedEngineers->count()); ?> assigned</span>
                    </div>

                    <?php if($canManageAssignment): ?>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="assigned_engineer_ids" class="form-label">Engineer</label>
                                <select
                                    id="assigned_engineer_ids"
                                    name="assigned_engineer_ids[]"
                                    class="form-select <?php $__errorArgs = ['assigned_engineer_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['assigned_engineer_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    data-searchable-select
                                    data-force-searchable-select="true"
                                    data-search-placeholder="Search engineer"
                                    multiple
                                >
                                    <?php $__currentLoopData = $groupedEngineerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $picTeamLabel => $groupedOptions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <optgroup label="<?php echo e($picTeamLabel); ?>">
                                            <?php $__currentLoopData = $groupedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option->id); ?>" <?php if(in_array((string) $option->id, $selectedAssignedEngineerIds, true)): echo 'selected'; endif; ?>>
                                                    <?php echo e($option->name); ?>

                                                    <?php if($option->department): ?>
                                                        - <?php echo e($option->department->name); ?>

                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </optgroup>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="form-text">Pilih ulang engineer aktif dengan grouping Engineer Team agar assignment lebih stabil daripada mengikuti shift.</div>
                                <?php $__errorArgs = ['assigned_engineer_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['assigned_engineer_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12">
                                <label for="assigned_team_name" class="form-label">Team Assigned</label>
                                <input
                                    type="text"
                                    id="assigned_team_name"
                                    name="assigned_team_name"
                                    class="form-control <?php $__errorArgs = ['assigned_team_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('assigned_team_name', $ticket->assigned_team_name)); ?>"
                                    placeholder="Ops / Field Team"
                                    list="ticket-edit-engineer-team-options"
                                >
                                <datalist id="ticket-edit-engineer-team-options">
                                    <?php $__currentLoopData = $engineerTeamNameOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teamName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teamName); ?>"></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </datalist>
                                <?php $__errorArgs = ['assigned_team_name'];
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

                            <div class="col-12">
                                <label for="assignment_notes" class="form-label">Assignment Notes</label>
                                <textarea
                                    id="assignment_notes"
                                    name="assignment_notes"
                                    rows="4"
                                    class="form-control <?php $__errorArgs = ['assignment_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    placeholder="Catatan perubahan assignment"
                                ><?php echo e($latestAssignmentNotes); ?></textarea>
                                <?php $__errorArgs = ['assignment_notes'];
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
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">
                            Anda bisa mengubah nama dan deskripsi ticket, tetapi perubahan assignment hanya tersedia untuk user yang memiliki otorisasi dispatch engineer.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="small text-muted">Perubahan disimpan pada ticket yang sama, jadi histori dan score engineer tetap konsisten.</div>
                    <div class="d-flex gap-2">
                        <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="btn btn-outline-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Edit Ticket'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/tickets/edit.blade.php ENDPATH**/ ?>