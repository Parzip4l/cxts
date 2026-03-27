<form method="GET" class="row g-3 mb-3" id="dashboard-ticket-filter-form">
    <div class="col-lg-2">
        <label for="date_from" class="form-label">Date From</label>
        <input id="date_from" type="date" name="date_from" class="form-control"
            value="<?php echo e($filters['date_from'] ?? ''); ?>">
    </div>
    <div class="col-lg-2">
        <label for="date_to" class="form-label">Date To</label>
        <input id="date_to" type="date" name="date_to" class="form-control"
            value="<?php echo e($filters['date_to'] ?? ''); ?>">
    </div>
    <div class="col-lg-2">
        <label for="dashboard_ticket_category_id" class="form-label">Ticket Type</label>
        <select id="dashboard_ticket_category_id" name="ticket_category_id" class="form-select"
            data-searchable-select data-search-placeholder="Search ticket type">
            <option value="">All ticket types</option>
            <?php $__currentLoopData = ($filterOptions['categoryOptions'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['ticket_category_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                    <?php echo e($option->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_ticket_subcategory_id" class="form-label">Ticket Category</label>
        <select id="dashboard_ticket_subcategory_id" name="ticket_subcategory_id" class="form-select"
            data-searchable-select data-search-placeholder="Search ticket category">
            <option value="">All ticket categories</option>
            <?php $__currentLoopData = ($filterOptions['subcategoryOptions'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option->id); ?>" data-category-id="<?php echo e($option->ticket_category_id); ?>"
                    <?php if((string) ($filters['ticket_subcategory_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                    <?php echo e($option->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_ticket_detail_subcategory_id" class="form-label">Ticket Sub Category</label>
        <select id="dashboard_ticket_detail_subcategory_id" name="ticket_detail_subcategory_id" class="form-select"
            data-searchable-select data-search-placeholder="Search ticket sub category">
            <option value="">All ticket sub categories</option>
            <?php $__currentLoopData = ($filterOptions['detailSubcategoryOptions'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option->id); ?>" data-subcategory-id="<?php echo e($option->ticket_subcategory_id); ?>"
                    <?php if((string) ($filters['ticket_detail_subcategory_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                    <?php echo e($option->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_expected_approver_id" class="form-label">Expected Approver</label>
        <select id="dashboard_expected_approver_id" name="expected_approver_id" class="form-select"
            data-searchable-select data-search-placeholder="Search approver">
            <option value="">All expected approvers</option>
            <?php $__currentLoopData = ($filterOptions['approverOptions'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['expected_approver_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                    <?php echo e($option->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_expected_approver_role_code" class="form-label">Approver Role</label>
        <select id="dashboard_expected_approver_role_code" name="expected_approver_role_code" class="form-select">
            <option value="">All approver roles</option>
            <?php $__currentLoopData = ($filterOptions['approverRoleOptions'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approverRoleCode => $approverRoleLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($approverRoleCode); ?>" <?php if((string) ($filters['expected_approver_role_code'] ?? '') === (string) $approverRoleCode): echo 'selected'; endif; ?>>
                    <?php echo e($approverRoleLabel); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_approval_status" class="form-label">Approval Status</label>
        <select id="dashboard_approval_status" name="approval_status" class="form-select">
            <option value="">All approval status</option>
            <?php $__currentLoopData = ($filterOptions['approvalStatusOptions'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approvalStatusCode => $approvalStatusLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($approvalStatusCode); ?>" <?php if((string) ($filters['approval_status'] ?? '') === (string) $approvalStatusCode): echo 'selected'; endif; ?>>
                    <?php echo e($approvalStatusLabel); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-12 d-flex flex-wrap justify-content-between gap-2">
        <a href="<?php echo e(route($routeName)); ?>" class="btn btn-outline-light">Reset</a>
        <button type="submit" class="btn btn-outline-secondary">Apply Filter</button>
    </div>
</form>

<?php if (! $__env->hasRenderedOnce('ad15726d-3a79-40e9-9b2e-1542675f2326')): $__env->markAsRenderedOnce('ad15726d-3a79-40e9-9b2e-1542675f2326'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const forms = document.querySelectorAll('#dashboard-ticket-filter-form, #ticket-list-filter-form');

                const refreshChoices = (select) => {
                    if (select?._choices) {
                        select._choices.removeActiveItems();
                        select._choices.setChoices(
                            Array.from(select.options).map((option) => ({
                                value: option.value,
                                label: option.textContent.trim(),
                                selected: option.selected,
                                disabled: option.disabled,
                            })),
                            'value',
                            'label',
                            true
                        );
                    }
                };

                forms.forEach((form) => {
                    const categorySelect = form.querySelector('[name="ticket_category_id"]');
                    const subcategorySelect = form.querySelector('[name="ticket_subcategory_id"]');
                    const detailSubcategorySelect = form.querySelector('[name="ticket_detail_subcategory_id"]');

                    const toggleDetailSubcategories = () => {
                        if (!subcategorySelect || !detailSubcategorySelect) {
                            return;
                        }

                        const selectedSubcategoryId = subcategorySelect.value;
                        let hasVisibleOption = false;

                        Array.from(detailSubcategorySelect.options).forEach((option, index) => {
                            if (index === 0) {
                                option.hidden = false;
                                return;
                            }

                            const matches = selectedSubcategoryId === '' || option.dataset.subcategoryId === selectedSubcategoryId;
                            option.hidden = !matches;
                            hasVisibleOption ||= matches;
                        });

                        if (selectedSubcategoryId !== '' && detailSubcategorySelect.selectedOptions[0]?.hidden) {
                            detailSubcategorySelect.value = '';
                        }

                        if (!hasVisibleOption) {
                            detailSubcategorySelect.value = '';
                        }

                        refreshChoices(detailSubcategorySelect);
                    };

                    const toggleSubcategories = () => {
                        if (!categorySelect || !subcategorySelect) {
                            return;
                        }

                        const selectedCategoryId = categorySelect.value;
                        let hasVisibleOption = false;

                        Array.from(subcategorySelect.options).forEach((option, index) => {
                            if (index === 0) {
                                option.hidden = false;
                                return;
                            }

                            const matches = selectedCategoryId === '' || option.dataset.categoryId === selectedCategoryId;
                            option.hidden = !matches;
                            hasVisibleOption ||= matches;
                        });

                        if (selectedCategoryId !== '' && subcategorySelect.selectedOptions[0]?.hidden) {
                            subcategorySelect.value = '';
                        }

                        if (!hasVisibleOption) {
                            subcategorySelect.value = '';
                        }

                        refreshChoices(subcategorySelect);
                        toggleDetailSubcategories();
                    };

                    categorySelect?.addEventListener('change', toggleSubcategories);
                    subcategorySelect?.addEventListener('change', toggleDetailSubcategories);
                    toggleSubcategories();
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/dashboard/operations/partials/filter.blade.php ENDPATH**/ ?>