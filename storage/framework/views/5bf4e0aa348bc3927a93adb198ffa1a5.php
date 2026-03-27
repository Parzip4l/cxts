<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php
    $templateItems = old('items');
    if (! is_array($templateItems)) {
        $templateItems = $inspectionTemplate->items
            ? $inspectionTemplate->items->map(fn ($item) => [
                'sequence' => $item->sequence,
                'item_label' => $item->item_label,
                'item_type' => $item->item_type,
                'expected_value' => $item->expected_value,
                'is_required' => $item->is_required,
                'is_active' => $item->is_active,
            ])->toArray()
            : [];
    }

    if (count($templateItems) === 0) {
        $templateItems = [[
            'sequence' => 1,
            'item_label' => '',
            'item_type' => 'boolean',
            'expected_value' => '',
            'is_required' => true,
            'is_active' => true,
        ]];
    }
?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo e($action); ?>" class="row g-3" id="inspection-template-form">
            <?php echo csrf_field(); ?>
            <?php if($method !== 'POST'): ?>
                <?php echo method_field($method); ?>
            <?php endif; ?>

            <div class="col-md-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('code', $inspectionTemplate->code)); ?>" required>
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

            <div class="col-md-5">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('name', $inspectionTemplate->name)); ?>" required>
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

            <div class="col-md-4">
                <label for="asset_category_id" class="form-label">Asset Category</label>
                <select id="asset_category_id" name="asset_category_id"
                    class="form-select <?php $__errorArgs = ['asset_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">- All categories -</option>
                    <?php $__currentLoopData = $assetCategoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) old('asset_category_id', $inspectionTemplate->asset_category_id) === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['asset_category_id'];
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
                <textarea id="description" name="description" rows="3"
                    class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $inspectionTemplate->description)); ?></textarea>
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
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Template Items</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-item-row">Add Item</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" id="template-items-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Seq</th>
                                <th>Item Label</th>
                                <th style="width: 140px;">Type</th>
                                <th style="width: 180px;">Expected</th>
                                <th style="width: 120px;">Required</th>
                                <th style="width: 100px;">Active</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $templateItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <input type="number" min="1" name="items[<?php echo e($index); ?>][sequence]"
                                            class="form-control form-control-sm" value="<?php echo e($item['sequence'] ?? ($index + 1)); ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="items[<?php echo e($index); ?>][item_label]"
                                            class="form-control form-control-sm" value="<?php echo e($item['item_label'] ?? ''); ?>" required>
                                    </td>
                                    <td>
                                        <?php
                                            $itemType = $item['item_type'] ?? 'boolean';
                                        ?>
                                        <select name="items[<?php echo e($index); ?>][item_type]" class="form-select form-select-sm">
                                            <option value="boolean" <?php if($itemType === 'boolean'): echo 'selected'; endif; ?>>Boolean</option>
                                            <option value="number" <?php if($itemType === 'number'): echo 'selected'; endif; ?>>Number</option>
                                            <option value="text" <?php if($itemType === 'text'): echo 'selected'; endif; ?>>Text</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[<?php echo e($index); ?>][expected_value]"
                                            class="form-control form-control-sm" value="<?php echo e($item['expected_value'] ?? ''); ?>">
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="items[<?php echo e($index); ?>][is_required]" value="0">
                                        <input type="checkbox" name="items[<?php echo e($index); ?>][is_required]" value="1"
                                            <?php if((bool) ($item['is_required'] ?? true)): echo 'checked'; endif; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="items[<?php echo e($index); ?>][is_active]" value="0">
                                        <input type="checkbox" name="items[<?php echo e($index); ?>][is_active]" value="1"
                                            <?php if((bool) ($item['is_active'] ?? true)): echo 'checked'; endif; ?>>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-row">X</button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        <?php if((bool) old('is_active', $inspectionTemplate->is_active ?? true)): echo 'checked'; endif; ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo e(route('master-data.inspection-templates.index')); ?>" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.querySelector('#template-items-table tbody');
        const addRowButton = document.getElementById('add-item-row');

        if (!tableBody || !addRowButton) {
            return;
        }

        const reindexRows = () => {
            Array.from(tableBody.querySelectorAll('tr')).forEach((row, index) => {
                row.querySelectorAll('input, select').forEach((field) => {
                    const fieldName = field.getAttribute('name');

                    if (!fieldName) {
                        return;
                    }

                    field.setAttribute('name', fieldName.replace(/items\\[\\d+\\]/, `items[${index}]`));
                });
            });
        };

        addRowButton.addEventListener('click', () => {
            const index = tableBody.querySelectorAll('tr').length;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="number" min="1" name="items[${index}][sequence]" class="form-control form-control-sm" value="${index + 1}"></td>
                <td><input type="text" name="items[${index}][item_label]" class="form-control form-control-sm" required></td>
                <td>
                    <select name="items[${index}][item_type]" class="form-select form-select-sm">
                        <option value="boolean">Boolean</option>
                        <option value="number">Number</option>
                        <option value="text">Text</option>
                    </select>
                </td>
                <td><input type="text" name="items[${index}][expected_value]" class="form-control form-control-sm"></td>
                <td class="text-center">
                    <input type="hidden" name="items[${index}][is_required]" value="0">
                    <input type="checkbox" name="items[${index}][is_required]" value="1" checked>
                </td>
                <td class="text-center">
                    <input type="hidden" name="items[${index}][is_active]" value="0">
                    <input type="checkbox" name="items[${index}][is_active]" value="1" checked>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-row">X</button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        tableBody.addEventListener('click', (event) => {
            const target = event.target;

            if (!(target instanceof HTMLElement) || !target.classList.contains('remove-item-row')) {
                return;
            }

            target.closest('tr')?.remove();
            reindexRows();
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => $pageTitle], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/inspections/templates/form.blade.php ENDPATH**/ ?>