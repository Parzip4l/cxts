<form method="GET" class="row g-3 mb-3" id="dashboard-ticket-filter-form">
    <div class="col-lg-2">
        <label for="date_from" class="form-label">Date From</label>
        <input id="date_from" type="date" name="date_from" class="form-control"
            value="{{ $filters['date_from'] ?? '' }}">
    </div>
    <div class="col-lg-2">
        <label for="date_to" class="form-label">Date To</label>
        <input id="date_to" type="date" name="date_to" class="form-control"
            value="{{ $filters['date_to'] ?? '' }}">
    </div>
    <div class="col-lg-2">
        <label for="dashboard_ticket_category_id" class="form-label">Ticket Type</label>
        <select id="dashboard_ticket_category_id" name="ticket_category_id" class="form-select"
            data-searchable-select data-search-placeholder="Search ticket type">
            <option value="">All ticket types</option>
            @foreach (($filterOptions['categoryOptions'] ?? collect()) as $option)
                <option value="{{ $option->id }}" @selected((string) ($filters['ticket_category_id'] ?? '') === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_ticket_subcategory_id" class="form-label">Ticket Category</label>
        <select id="dashboard_ticket_subcategory_id" name="ticket_subcategory_id" class="form-select"
            data-searchable-select data-search-placeholder="Search ticket category">
            <option value="">All ticket categories</option>
            @foreach (($filterOptions['subcategoryOptions'] ?? collect()) as $option)
                <option value="{{ $option->id }}" data-category-id="{{ $option->ticket_category_id }}"
                    @selected((string) ($filters['ticket_subcategory_id'] ?? '') === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_ticket_detail_subcategory_id" class="form-label">Ticket Sub Category</label>
        <select id="dashboard_ticket_detail_subcategory_id" name="ticket_detail_subcategory_id" class="form-select"
            data-searchable-select data-search-placeholder="Search ticket sub category">
            <option value="">All ticket sub categories</option>
            @foreach (($filterOptions['detailSubcategoryOptions'] ?? collect()) as $option)
                <option value="{{ $option->id }}" data-subcategory-id="{{ $option->ticket_subcategory_id }}"
                    @selected((string) ($filters['ticket_detail_subcategory_id'] ?? '') === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_expected_approver_id" class="form-label">Expected Approver</label>
        <select id="dashboard_expected_approver_id" name="expected_approver_id" class="form-select"
            data-searchable-select data-search-placeholder="Search approver">
            <option value="">All expected approvers</option>
            @foreach (($filterOptions['approverOptions'] ?? collect()) as $option)
                <option value="{{ $option->id }}" @selected((string) ($filters['expected_approver_id'] ?? '') === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_expected_approver_role_code" class="form-label">Approver Role</label>
        <select id="dashboard_expected_approver_role_code" name="expected_approver_role_code" class="form-select">
            <option value="">All approver roles</option>
            @foreach (($filterOptions['approverRoleOptions'] ?? []) as $approverRoleCode => $approverRoleLabel)
                <option value="{{ $approverRoleCode }}" @selected((string) ($filters['expected_approver_role_code'] ?? '') === (string) $approverRoleCode)>
                    {{ $approverRoleLabel }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label for="dashboard_approval_status" class="form-label">Approval Status</label>
        <select id="dashboard_approval_status" name="approval_status" class="form-select">
            <option value="">All approval status</option>
            @foreach (($filterOptions['approvalStatusOptions'] ?? []) as $approvalStatusCode => $approvalStatusLabel)
                <option value="{{ $approvalStatusCode }}" @selected((string) ($filters['approval_status'] ?? '') === (string) $approvalStatusCode)>
                    {{ $approvalStatusLabel }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12 d-flex flex-wrap justify-content-between gap-2">
        <a href="{{ route($routeName) }}" class="btn btn-outline-light">Reset</a>
        <button type="submit" class="btn btn-outline-secondary">Apply Filter</button>
    </div>
</form>

@once
    @push('scripts')
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
    @endpush
@endonce
