<?php
    $categoryOptions = $filterOptions['categoryOptions'] ?? collect();
    $subcategoryOptions = $filterOptions['subcategoryOptions'] ?? collect();
    $detailSubcategoryOptions = $filterOptions['detailSubcategoryOptions'] ?? collect();
    $approverOptions = $filterOptions['approverOptions'] ?? collect();
    $approverRoleOptions = $filterOptions['approverRoleOptions'] ?? [];
    $approvalStatusOptions = $filterOptions['approvalStatusOptions'] ?? [];

    $selectedCategory = filled($filters['ticket_category_id'] ?? null)
        ? $categoryOptions->firstWhere('id', (int) $filters['ticket_category_id'])?->name
        : null;
    $selectedSubcategory = filled($filters['ticket_subcategory_id'] ?? null)
        ? $subcategoryOptions->firstWhere('id', (int) $filters['ticket_subcategory_id'])?->name
        : null;
    $selectedDetailSubcategory = filled($filters['ticket_detail_subcategory_id'] ?? null)
        ? $detailSubcategoryOptions->firstWhere('id', (int) $filters['ticket_detail_subcategory_id'])?->name
        : null;
    $selectedApprover = filled($filters['expected_approver_id'] ?? null)
        ? $approverOptions->firstWhere('id', (int) $filters['expected_approver_id'])?->name
        : null;
    $selectedApproverRole = filled($filters['expected_approver_role_code'] ?? null)
        ? ($approverRoleOptions[$filters['expected_approver_role_code']] ?? null)
        : null;
    $selectedApprovalStatus = filled($filters['approval_status'] ?? null)
        ? ($approvalStatusOptions[$filters['approval_status']] ?? null)
        : null;
    $dashboardActiveFilters = array_filter([
        filled($filters['date_from'] ?? null) ? 'From: ' . $filters['date_from'] : null,
        filled($filters['date_to'] ?? null) ? 'To: ' . $filters['date_to'] : null,
        $selectedCategory ? 'Type: ' . $selectedCategory : null,
        $selectedApprovalStatus ? 'Approval: ' . $selectedApprovalStatus : null,
        $selectedSubcategory ? 'Category: ' . $selectedSubcategory : null,
        $selectedDetailSubcategory ? 'Sub Category: ' . $selectedDetailSubcategory : null,
        $selectedApprover ? 'Expected Approver: ' . $selectedApprover : null,
        $selectedApproverRole ? 'Approver Role: ' . $selectedApproverRole : null,
    ]);
    $advancedDashboardFilterApplied = filled($filters['ticket_subcategory_id'] ?? null)
        || filled($filters['ticket_detail_subcategory_id'] ?? null)
        || filled($filters['expected_approver_id'] ?? null)
        || filled($filters['expected_approver_role_code'] ?? null);
?>

<form method="GET" class="mb-0" id="dashboard-ticket-filter-form">
    <div class="rounded-3 border bg-light-subtle p-3 p-lg-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="fw-semibold text-dark">Quick Filters</div>
                <div class="small text-muted">Atur rentang waktu dan dimensi utama dulu. Filter lanjutan hanya dibuka jika perlu drill-down.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedDashboardFilters" aria-expanded="<?php echo e($advancedDashboardFilterApplied ? 'true' : 'false'); ?>" aria-controls="advancedDashboardFilters">
                    <?php echo e($advancedDashboardFilterApplied ? 'Hide Advanced Filters' : 'Show Advanced Filters'); ?>

                </button>
                <a href="<?php echo e(route($routeName)); ?>" class="btn btn-outline-light">Reset</a>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <label for="date_from" class="form-label small text-muted mb-1">Date From</label>
                <input id="date_from" type="date" name="date_from" class="form-control"
                    value="<?php echo e($filters['date_from'] ?? ''); ?>">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="date_to" class="form-label small text-muted mb-1">Date To</label>
                <input id="date_to" type="date" name="date_to" class="form-control"
                    value="<?php echo e($filters['date_to'] ?? ''); ?>">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="dashboard_ticket_category_id" class="form-label small text-muted mb-1">Ticket Type</label>
                <select id="dashboard_ticket_category_id" name="ticket_category_id" class="form-select"
                    data-searchable-select data-search-placeholder="Search ticket type">
                    <option value="">All ticket types</option>
                    <?php $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['ticket_category_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="dashboard_approval_status" class="form-label small text-muted mb-1">Approval Status</label>
                <select id="dashboard_approval_status" name="approval_status" class="form-select">
                    <option value="">All approval status</option>
                    <?php $__currentLoopData = $approvalStatusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approvalStatusCode => $approvalStatusLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($approvalStatusCode); ?>" <?php if((string) ($filters['approval_status'] ?? '') === (string) $approvalStatusCode): echo 'selected'; endif; ?>>
                            <?php echo e($approvalStatusLabel); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <?php if(count($dashboardActiveFilters)): ?>
            <div class="mt-3">
                <div class="small text-muted mb-2">Active filters</div>
                <div class="d-flex flex-wrap gap-2">
                    <?php $__currentLoopData = $dashboardActiveFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filterChip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge bg-white text-dark border"><?php echo e($filterChip); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="collapse <?php echo e($advancedDashboardFilterApplied ? 'show' : ''); ?>" id="advancedDashboardFilters">
        <div class="rounded-3 border border-dashed p-3 p-lg-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <div class="fw-semibold text-dark">Advanced Filters</div>
                    <div class="small text-muted">Dipakai saat Anda butuh analisis taxonomy lebih detail atau melihat jalur approver tertentu.</div>
                </div>
                <span class="badge bg-secondary-subtle text-secondary"><?php echo e($advancedDashboardFilterApplied ? 'Advanced filters active' : 'Optional'); ?></span>
            </div>

            <div class="row g-3">
                <div class="col-lg-3">
                    <label for="dashboard_ticket_subcategory_id" class="form-label small text-muted mb-1">Ticket Category</label>
                    <select id="dashboard_ticket_subcategory_id" name="ticket_subcategory_id" class="form-select"
                        data-searchable-select data-search-placeholder="Search ticket category">
                        <option value="">All ticket categories</option>
                        <?php $__currentLoopData = $subcategoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->id); ?>" data-category-id="<?php echo e($option->ticket_category_id); ?>"
                                <?php if((string) ($filters['ticket_subcategory_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                                <?php echo e($option->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label for="dashboard_ticket_detail_subcategory_id" class="form-label small text-muted mb-1">Ticket Sub Category</label>
                    <select id="dashboard_ticket_detail_subcategory_id" name="ticket_detail_subcategory_id" class="form-select"
                        data-searchable-select data-search-placeholder="Search ticket sub category">
                        <option value="">All ticket sub categories</option>
                        <?php $__currentLoopData = $detailSubcategoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->id); ?>" data-subcategory-id="<?php echo e($option->ticket_subcategory_id); ?>"
                                <?php if((string) ($filters['ticket_detail_subcategory_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                                <?php echo e($option->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label for="dashboard_expected_approver_id" class="form-label small text-muted mb-1">Expected Approver</label>
                    <select id="dashboard_expected_approver_id" name="expected_approver_id" class="form-select"
                        data-searchable-select data-search-placeholder="Search approver">
                        <option value="">All expected approvers</option>
                        <?php $__currentLoopData = $approverOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['expected_approver_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                                <?php echo e($option->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label for="dashboard_expected_approver_role_code" class="form-label small text-muted mb-1">Approver Role</label>
                    <select id="dashboard_expected_approver_role_code" name="expected_approver_role_code" class="form-select">
                        <option value="">All approver roles</option>
                        <?php $__currentLoopData = $approverRoleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approverRoleCode => $approverRoleLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($approverRoleCode); ?>" <?php if((string) ($filters['expected_approver_role_code'] ?? '') === (string) $approverRoleCode): echo 'selected'; endif; ?>>
                                <?php echo e($approverRoleLabel); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if (! $__env->hasRenderedOnce('6bef1135-1e17-42c1-accc-578f19e674fa')): $__env->markAsRenderedOnce('6bef1135-1e17-42c1-accc-578f19e674fa'); ?>
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