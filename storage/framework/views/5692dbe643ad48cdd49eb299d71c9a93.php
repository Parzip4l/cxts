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
                    value="<?php echo e(old('code', $ticketStatus->code)); ?>" required>
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
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('name', $ticketStatus->name)); ?>" required>
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
                <input type="hidden" name="is_open" value="0">
                <input type="hidden" name="is_in_progress" value="0">
                <input type="hidden" name="is_closed" value="0">
                <input type="hidden" name="is_active" value="0">

                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_open" name="is_open" value="1"
                            <?php if((bool) old('is_open', $ticketStatus->is_open ?? true)): echo 'checked'; endif; ?>>
                        <label class="form-check-label" for="is_open">Is Open</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_in_progress" name="is_in_progress" value="1"
                            <?php if((bool) old('is_in_progress', $ticketStatus->is_in_progress ?? false)): echo 'checked'; endif; ?>>
                        <label class="form-check-label" for="is_in_progress">Is In Progress</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_closed" name="is_closed" value="1"
                            <?php if((bool) old('is_closed', $ticketStatus->is_closed ?? false)): echo 'checked'; endif; ?>>
                        <label class="form-check-label" for="is_closed">Is Closed</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            <?php if((bool) old('is_active', $ticketStatus->is_active ?? true)): echo 'checked'; endif; ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo e(route('master-data.ticket-statuses.index')); ?>" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => $pageTitle], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/statuses/form.blade.php ENDPATH**/ ?>