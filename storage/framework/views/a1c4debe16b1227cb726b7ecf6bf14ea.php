<?php $__env->startSection('content'); ?>
<?php echo $__env->make('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Tickets'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="card">
    <div class="card-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-3 mb-3" id="ticket-list-filter-form">
            <div class="col-lg-3">
                <input type="text" name="search" class="form-control" placeholder="Search ticket"
                    value="<?php echo e($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-lg-2">
                <select name="ticket_status_id" class="form-select">
                    <option value="">All status</option>
                    <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['ticket_status_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2">
                <select name="ticket_priority_id" class="form-select">
                    <option value="">All priority</option>
                    <?php $__currentLoopData = $priorityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['ticket_priority_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-2">
                <select name="ticket_category_id" id="ticket_list_category_id" class="form-select"
                    data-searchable-select data-search-placeholder="Search ticket type">
                    <option value="">All ticket types</option>
                    <?php $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['ticket_category_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-3">
                <select name="ticket_subcategory_id" id="ticket_list_subcategory_id" class="form-select"
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
                <select name="ticket_detail_subcategory_id" id="ticket_list_detail_subcategory_id" class="form-select"
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
                <select name="assigned_engineer_id" class="form-select"
                    data-searchable-select data-search-placeholder="Search engineer">
                    <option value="">All engineer</option>
                    <?php $__currentLoopData = $engineerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option->id); ?>" <?php if((string) ($filters['assigned_engineer_id'] ?? '') === (string) $option->id): echo 'selected'; endif; ?>>
                            <?php echo e($option->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-3">
                <select name="expected_approver_id" class="form-select"
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
                <select name="expected_approver_role_code" class="form-select">
                    <option value="">All approver roles</option>
                    <?php $__currentLoopData = $approverRoleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approverRoleCode => $approverRoleLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($approverRoleCode); ?>" <?php if((string) ($filters['expected_approver_role_code'] ?? '') === (string) $approverRoleCode): echo 'selected'; endif; ?>>
                            <?php echo e($approverRoleLabel); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-3">
                <select name="approval_status" class="form-select">
                    <option value="">All approval status</option>
                    <?php $__currentLoopData = $approvalStatusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approvalStatusCode => $approvalStatusLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($approvalStatusCode); ?>" <?php if((string) ($filters['approval_status'] ?? '') === (string) $approvalStatusCode): echo 'selected'; endif; ?>>
                            <?php echo e($approvalStatusLabel); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-lg-12 d-flex flex-wrap justify-content-between gap-2">
                <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-outline-light">Reset</a>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-secondary" type="submit">Apply Filter</button>
                    <a href="<?php echo e(route('tickets.create')); ?>" class="btn btn-primary">Create Ticket</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Title</th>
                        <th>Requester</th>
                        <th>Ticket Type</th>
                        <th>Ticket Category</th>
                        <th>Ticket Sub Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Expected Approver</th>
                        <th>Assigned Engineer</th>
                        <th>Response Due</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusCode = $ticket->status?->code;
                            $statusClass = match ($statusCode) {
                                'IN_PROGRESS' => 'bg-info-subtle text-info',
                                'ON_HOLD' => 'bg-warning-subtle text-warning',
                                'COMPLETED', 'CLOSED' => 'bg-success-subtle text-success',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                        ?>
                        <tr>
                            <td><?php echo e($ticket->ticket_number); ?></td>
                            <td><?php echo e($ticket->title); ?></td>
                            <td><?php echo e($ticket->requester?->name ?? '-'); ?></td>
                            <td><?php echo e($ticket->category?->name ?? '-'); ?></td>
                            <td><?php echo e($ticket->subcategory?->name ?? '-'); ?></td>
                            <td><?php echo e($ticket->detailSubcategory?->name ?? '-'); ?></td>
                            <td><?php echo e($ticket->priority?->name ?? '-'); ?></td>
                            <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($ticket->status?->name ?? '-'); ?></span></td>
                            <td><?php echo e(str($ticket->approval_status ?? 'not_required')->replace('_', ' ')->title()); ?></td>
                            <td>
                                <div><?php echo e($ticket->expectedApprover?->name ?? $ticket->expected_approver_name_snapshot ?? '-'); ?></div>
                                <?php if($ticket->expected_approver_role_code): ?>
                                    <div class="small text-muted"><?php echo e(\App\Models\TicketCategory::approverRoleLabel($ticket->expected_approver_role_code)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($ticket->assignedEngineer?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($ticket->response_due_at)->format('Y-m-d H:i') ?? '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">No tickets found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($tickets->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('ticket-list-filter-form');

        if (!form) {
            return;
        }

        const categorySelect = form.querySelector('[name="ticket_category_id"]');
        const subcategorySelect = form.querySelector('[name="ticket_subcategory_id"]');
        const detailSubcategorySelect = form.querySelector('[name="ticket_detail_subcategory_id"]');

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
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Tickets'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/tickets/index.blade.php ENDPATH**/ ?>