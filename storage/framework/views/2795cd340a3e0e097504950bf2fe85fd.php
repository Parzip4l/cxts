<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo e($action); ?>" class="row g-3">
            <?php echo csrf_field(); ?>
            <?php if($method !== 'POST'): ?>
                <?php echo method_field($method); ?>
            <?php endif; ?>

            <div class="col-md-4">
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('code', $ticketCategory->code)); ?>" required>
                <?php $__errorArgs = ['code'];
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

            <div class="col-md-8">
                <label for="name" class="form-label">Type Name</label>
                <input type="text" id="name" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('name', $ticketCategory->name)); ?>" required>
                <?php $__errorArgs = ['name'];
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
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $ticketCategory->description)); ?></textarea>
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

            <div class="col-12">
                <div class="border rounded p-3 bg-light-subtle" data-approval-config-root>
                    <?php echo $__env->make('modules.tickets.partials.approval-matrix', [
                        'scopeLabel' => 'Ticket Type',
                        'inheritLabel' => null,
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="hidden" name="requires_approval" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requires_approval" name="requires_approval" value="1"
                                    <?php if((bool) old('requires_approval', $ticketCategory->requires_approval ?? false)): echo 'checked'; endif; ?>>
                                <label class="form-check-label" for="requires_approval">Requires approval before assignment</label>
                            </div>
                            <div class="form-text">Gunakan untuk jenis ticket yang perlu kontrol sebelum operasional bergerak.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="hidden" name="allow_direct_assignment" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_direct_assignment" name="allow_direct_assignment" value="1"
                                    <?php if((bool) old('allow_direct_assignment', $ticketCategory->allow_direct_assignment ?? true)): echo 'checked'; endif; ?>>
                                <label class="form-check-label" for="allow_direct_assignment">Allow direct assignment</label>
                            </div>
                            <div class="form-text">Jika nonaktif, ticket harus ditandai siap assign lebih dulu walau approval tidak dibutuhkan.</div>
                        </div>
                        <div class="col-12">
                            <label for="approver_strategy" class="form-label">Approver Strategy</label>
                            <select id="approver_strategy" name="approver_strategy" class="form-select <?php $__errorArgs = ['approver_strategy'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">- Supervisor/Admin Fallback -</option>
                                <?php $__currentLoopData = $approverStrategyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $strategyCode => $strategyLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($strategyCode); ?>" <?php if((string) old('approver_strategy', $ticketCategory->approver_strategy) === (string) $strategyCode): echo 'selected'; endif; ?>>
                                        <?php echo e($strategyLabel); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['approver_strategy'];
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
                        <div class="col-md-6" data-approver-user-field>
                            <label for="approver_user_id" class="form-label">Specific Approver</label>
                            <select id="approver_user_id" name="approver_user_id" class="form-select <?php $__errorArgs = ['approver_user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-searchable-select data-force-searchable-select="true" data-search-placeholder="Search approver">
                                <option value="">- Supervisor/Admin Fallback -</option>
                                <?php $__currentLoopData = $approverOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approverOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($approverOption->id); ?>" <?php if((string) old('approver_user_id', $ticketCategory->approver_user_id) === (string) $approverOption->id): echo 'selected'; endif; ?>>
                                        <?php echo e($approverOption->name); ?> - <?php echo e(str($approverOption->role)->replace('_', ' ')->title()); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Jika dikosongkan, approval bisa dilakukan oleh supervisor atau admin sesuai akses existing.</div>
                            <?php $__errorArgs = ['approver_user_id'];
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
                        <div class="col-md-6" data-approver-role-field>
                            <label for="approver_role_code" class="form-label">Role-Based Approver</label>
                            <select id="approver_role_code" name="approver_role_code" class="form-select <?php $__errorArgs = ['approver_role_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">- Select Role -</option>
                                <?php $__currentLoopData = $approverRoleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approverRoleCode => $approverRoleLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($approverRoleCode); ?>" <?php if((string) old('approver_role_code', $ticketCategory->approver_role_code) === (string) $approverRoleCode): echo 'selected'; endif; ?>>
                                        <?php echo e($approverRoleLabel); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['approver_role_code'];
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

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        <?php if((bool) old('is_active', $ticketCategory->is_active ?? true)): echo 'checked'; endif; ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo e(route('master-data.ticket-categories.index')); ?>" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => $pageTitle], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/categories/form.blade.php ENDPATH**/ ?>