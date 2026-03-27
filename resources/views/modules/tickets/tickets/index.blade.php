@extends('layouts.vertical', ['subtitle' => 'Tickets'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Tickets'])

@php
    $ticketCounts = [
        'total' => $tickets->total(),
        'pending_approval' => $tickets->getCollection()->filter(fn ($ticket) => $ticket->approval_status === \App\Models\Ticket::APPROVAL_STATUS_PENDING)->count(),
        'assigned' => $tickets->getCollection()->filter(fn ($ticket) => filled($ticket->assigned_engineer_id))->count(),
        'breach_risk' => $tickets->getCollection()->filter(fn ($ticket) => $ticket->response_due_at && $ticket->response_due_at->isPast() && ! $ticket->responded_at)->count(),
    ];
    $selectedStatus = filled($filters['ticket_status_id'] ?? null)
        ? $statusOptions->firstWhere('id', (int) $filters['ticket_status_id'])?->name
        : null;
    $selectedPriority = filled($filters['ticket_priority_id'] ?? null)
        ? $priorityOptions->firstWhere('id', (int) $filters['ticket_priority_id'])?->name
        : null;
    $selectedCategory = filled($filters['ticket_category_id'] ?? null)
        ? $categoryOptions->firstWhere('id', (int) $filters['ticket_category_id'])?->name
        : null;
    $selectedSubcategory = filled($filters['ticket_subcategory_id'] ?? null)
        ? $subcategoryOptions->firstWhere('id', (int) $filters['ticket_subcategory_id'])?->name
        : null;
    $selectedDetailSubcategory = filled($filters['ticket_detail_subcategory_id'] ?? null)
        ? $detailSubcategoryOptions->firstWhere('id', (int) $filters['ticket_detail_subcategory_id'])?->name
        : null;
    $selectedEngineer = filled($filters['assigned_engineer_id'] ?? null)
        ? $engineerOptions->firstWhere('id', (int) $filters['assigned_engineer_id'])?->name
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
    $activeFilterSummary = array_filter([
        filled($filters['search'] ?? null) ? 'Search: ' . $filters['search'] : null,
        $selectedStatus ? 'Status: ' . $selectedStatus : null,
        $selectedPriority ? 'Priority: ' . $selectedPriority : null,
        $selectedApprovalStatus ? 'Approval: ' . $selectedApprovalStatus : null,
        $selectedEngineer ? 'Engineer: ' . $selectedEngineer : null,
        $selectedCategory ? 'Type: ' . $selectedCategory : null,
        $selectedSubcategory ? 'Category: ' . $selectedSubcategory : null,
        $selectedDetailSubcategory ? 'Sub Category: ' . $selectedDetailSubcategory : null,
        $selectedApprover ? 'Expected Approver: ' . $selectedApprover : null,
        $selectedApproverRole ? 'Approver Role: ' . $selectedApproverRole : null,
    ]);
    $advancedFilterApplied = filled($filters['ticket_category_id'] ?? null)
        || filled($filters['ticket_subcategory_id'] ?? null)
        || filled($filters['ticket_detail_subcategory_id'] ?? null)
        || filled($filters['expected_approver_id'] ?? null)
        || filled($filters['expected_approver_role_code'] ?? null);
    $ticketStatusBadgeClass = function ($statusCode) {
        return match (strtolower((string) $statusCode)) {
            'new', 'open', 'assigned' => 'bg-primary-subtle text-primary',
            'pending_approval', 'on_hold' => 'bg-warning-subtle text-warning',
            'in_progress' => 'bg-info-subtle text-info',
            'completed', 'closed' => 'bg-success-subtle text-success',
            'rejected' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    };
    $approvalBadgeClass = function ($approvalStatus) {
        return match ($approvalStatus) {
            \App\Models\Ticket::APPROVAL_STATUS_PENDING => 'bg-warning-subtle text-warning',
            \App\Models\Ticket::APPROVAL_STATUS_APPROVED => 'bg-success-subtle text-success',
            \App\Models\Ticket::APPROVAL_STATUS_REJECTED => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    };
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Tickets In View</div>
                <div class="fs-3 fw-semibold">{{ number_format($ticketCounts['total']) }}</div>
                <div class="small text-muted">Total rows for current filter and page set.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Pending Approval</div>
                <div class="fs-3 fw-semibold">{{ number_format($ticketCounts['pending_approval']) }}</div>
                <div class="small text-muted">Ticket yang masih menunggu keputusan approver.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Assigned</div>
                <div class="fs-3 fw-semibold">{{ number_format($ticketCounts['assigned']) }}</div>
                <div class="small text-muted">Ticket yang sudah punya owner engineer.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Response Risk</div>
                <div class="fs-3 fw-semibold">{{ number_format($ticketCounts['breach_risk']) }}</div>
                <div class="small text-muted">Ticket yang sudah melewati response due tanpa response.</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 pb-0">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h5 class="mb-1">Ticket Operations</h5>
                <p class="text-muted mb-0 small">Mulai dari search, status, atau engineer. Buka filter lanjutan hanya saat perlu drill-down taxonomy atau approver.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary">Operational Queue</span>
        </div>
    </div>
    <div class="card-body pt-3">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-3" id="ticket-list-filter-form">
            <div class="rounded-3 border bg-light-subtle p-3 p-lg-4 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="fw-semibold text-dark">Quick Filters</div>
                        <div class="small text-muted">Gunakan filter inti untuk menemukan ticket lebih cepat tanpa membuka semua opsi.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedTicketFilters" aria-expanded="{{ $advancedFilterApplied ? 'true' : 'false' }}" aria-controls="advancedTicketFilters">
                            {{ $advancedFilterApplied ? 'Hide Advanced Filters' : 'Show Advanced Filters' }}
                        </button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-light">Reset</a>
                        <button class="btn btn-primary" type="submit">Apply Filters</button>
                        <a href="{{ route('tickets.create') }}" class="btn btn-dark">Create Ticket</a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Ticket number, title, requester, or service"
                            value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="ticket_status_id" class="form-select">
                            <option value="">All status</option>
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option->id }}" @selected((string) ($filters['ticket_status_id'] ?? '') === (string) $option->id)>
                                    {{ $option->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label small text-muted mb-1">Priority</label>
                        <select name="ticket_priority_id" class="form-select">
                            <option value="">All priority</option>
                            @foreach ($priorityOptions as $option)
                                <option value="{{ $option->id }}" @selected((string) ($filters['ticket_priority_id'] ?? '') === (string) $option->id)>
                                    {{ $option->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label small text-muted mb-1">Approval Status</label>
                        <select name="approval_status" class="form-select">
                            <option value="">All approval status</option>
                            @foreach ($approvalStatusOptions as $approvalStatusCode => $approvalStatusLabel)
                                <option value="{{ $approvalStatusCode }}" @selected((string) ($filters['approval_status'] ?? '') === (string) $approvalStatusCode)>
                                    {{ $approvalStatusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label small text-muted mb-1">Assigned Engineer</label>
                        <select name="assigned_engineer_id" class="form-select"
                            data-searchable-select data-search-placeholder="Search engineer">
                            <option value="">All engineer</option>
                            @foreach ($engineerOptions as $option)
                                <option value="{{ $option->id }}" @selected((string) ($filters['assigned_engineer_id'] ?? '') === (string) $option->id)>
                                    {{ $option->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if (count($activeFilterSummary))
                    <div class="mt-3">
                        <div class="small text-muted mb-2">Active filters</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($activeFilterSummary as $filterChip)
                                <span class="badge bg-white text-dark border">{{ $filterChip }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="collapse {{ $advancedFilterApplied ? 'show' : '' }}" id="advancedTicketFilters">
                <div class="rounded-3 border border-dashed p-3 p-lg-4 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div>
                            <div class="fw-semibold text-dark">Advanced Filters</div>
                            <div class="small text-muted">Gunakan saat Anda perlu menyaring berdasarkan taxonomy ticket atau jalur approver.</div>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary">{{ $advancedFilterApplied ? 'Advanced filters active' : 'Optional' }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label small text-muted mb-1">Ticket Type</label>
                            <select name="ticket_category_id" id="ticket_list_category_id" class="form-select"
                                data-searchable-select data-search-placeholder="Search ticket type">
                                <option value="">All ticket types</option>
                                @foreach ($categoryOptions as $option)
                                    <option value="{{ $option->id }}" @selected((string) ($filters['ticket_category_id'] ?? '') === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small text-muted mb-1">Ticket Category</label>
                            <select name="ticket_subcategory_id" id="ticket_list_subcategory_id" class="form-select"
                                data-searchable-select data-search-placeholder="Search ticket category">
                                <option value="">All ticket categories</option>
                                @foreach ($subcategoryOptions as $option)
                                    <option value="{{ $option->id }}" data-category-id="{{ $option->ticket_category_id }}"
                                        @selected((string) ($filters['ticket_subcategory_id'] ?? '') === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small text-muted mb-1">Ticket Sub Category</label>
                            <select name="ticket_detail_subcategory_id" id="ticket_list_detail_subcategory_id" class="form-select"
                                data-searchable-select data-search-placeholder="Search ticket sub category">
                                <option value="">All ticket sub categories</option>
                                @foreach ($detailSubcategoryOptions as $option)
                                    <option value="{{ $option->id }}" data-subcategory-id="{{ $option->ticket_subcategory_id }}"
                                        @selected((string) ($filters['ticket_detail_subcategory_id'] ?? '') === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small text-muted mb-1">Expected Approver</label>
                            <select name="expected_approver_id" class="form-select"
                                data-searchable-select data-search-placeholder="Search approver">
                                <option value="">All expected approvers</option>
                                @foreach ($approverOptions as $option)
                                    <option value="{{ $option->id }}" @selected((string) ($filters['expected_approver_id'] ?? '') === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small text-muted mb-1">Approver Role</label>
                            <select name="expected_approver_role_code" class="form-select">
                                <option value="">All approver roles</option>
                                @foreach ($approverRoleOptions as $approverRoleCode => $approverRoleLabel)
                                    <option value="{{ $approverRoleCode }}" @selected((string) ($filters['expected_approver_role_code'] ?? '') === (string) $approverRoleCode)>
                                        {{ $approverRoleLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="small text-muted">
                Menampilkan <span class="fw-semibold text-dark">{{ $tickets->count() }}</span> ticket pada halaman ini.
                Kolom inti tetap terlihat, detail tambahan bisa dinyalakan saat diperlukan.
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Column View
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3 shadow-sm border-0" style="min-width: 260px;">
                    <div class="fw-semibold mb-2">Show or hide columns</div>
                    <div class="small text-muted mb-3">Tampilan disimpan di browser Anda untuk halaman ini.</div>
                    <div class="d-flex flex-column gap-2" id="ticket-column-controls">
                        <label class="form-check m-0">
                            <input class="form-check-input" type="checkbox" data-column-toggle="requester">
                            <span class="form-check-label">Requester</span>
                        </label>
                        <label class="form-check m-0">
                            <input class="form-check-input" type="checkbox" data-column-toggle="type">
                            <span class="form-check-label">Ticket Type</span>
                        </label>
                        <label class="form-check m-0">
                            <input class="form-check-input" type="checkbox" data-column-toggle="category">
                            <span class="form-check-label">Ticket Category</span>
                        </label>
                        <label class="form-check m-0">
                            <input class="form-check-input" type="checkbox" data-column-toggle="subcategory">
                            <span class="form-check-label">Ticket Sub Category</span>
                        </label>
                        <label class="form-check m-0">
                            <input class="form-check-input" type="checkbox" data-column-toggle="priority">
                            <span class="form-check-label">Priority</span>
                        </label>
                        <label class="form-check m-0">
                            <input class="form-check-input" type="checkbox" data-column-toggle="approver">
                            <span class="form-check-label">Expected Approver</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="ticket-list-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Summary</th>
                        <th class="ticket-optional-column" data-column-key="requester" style="display: none;">Requester</th>
                        <th class="ticket-optional-column" data-column-key="type" style="display: none;">Ticket Type</th>
                        <th class="ticket-optional-column" data-column-key="category" style="display: none;">Ticket Category</th>
                        <th class="ticket-optional-column" data-column-key="subcategory" style="display: none;">Ticket Sub Category</th>
                        <th class="ticket-optional-column" data-column-key="priority" style="display: none;">Priority</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th class="ticket-optional-column" data-column-key="approver" style="display: none;">Expected Approver</th>
                        <th>Assigned Engineer</th>
                        <th>Response Due</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $ticket->ticket_number }}</div>
                                <small class="text-muted">{{ optional($ticket->created_at)->format('d M Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $ticket->title }}</div>
                                <div class="small text-muted mt-1">{{ $ticket->service?->name ?? 'No related service' }}</div>
                                <div class="d-flex flex-wrap gap-1 mt-2">
                                    <span class="badge bg-light text-dark border">{{ $ticket->category?->name ?? '-' }}</span>
                                    <span class="badge bg-light text-dark border">{{ $ticket->subcategory?->name ?? '-' }}</span>
                                    <span class="badge bg-light text-dark border">{{ $ticket->detailSubcategory?->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="ticket-optional-column" data-column-key="requester" style="display: none;">
                                <div>{{ $ticket->requester?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $ticket->requester?->department?->name ?? 'No department' }}</small>
                            </td>
                            <td class="ticket-optional-column" data-column-key="type" style="display: none;">{{ $ticket->category?->name ?? '-' }}</td>
                            <td class="ticket-optional-column" data-column-key="category" style="display: none;">{{ $ticket->subcategory?->name ?? '-' }}</td>
                            <td class="ticket-optional-column" data-column-key="subcategory" style="display: none;">{{ $ticket->detailSubcategory?->name ?? '-' }}</td>
                            <td class="ticket-optional-column" data-column-key="priority" style="display: none;">{{ $ticket->priority?->name ?? '-' }}</td>
                            <td><span class="badge {{ $ticketStatusBadgeClass($ticket->status?->code) }}">{{ $ticket->status?->name ?? '-' }}</span></td>
                            <td>
                                <span class="badge {{ $approvalBadgeClass($ticket->approval_status) }}">
                                    {{ str($ticket->approval_status ?? 'not_required')->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="ticket-optional-column" data-column-key="approver" style="display: none;">
                                <div>{{ $ticket->expectedApprover?->name ?? $ticket->expected_approver_name_snapshot ?? '-' }}</div>
                                @if ($ticket->expected_approver_role_code)
                                    <div class="small text-muted">{{ \App\Models\TicketCategory::approverRoleLabel($ticket->expected_approver_role_code) }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $ticket->assignedEngineer?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $ticket->assigned_team_name ?? 'No team' }}</small>
                            </td>
                            <td>
                                @if ($ticket->response_due_at)
                                    <div>{{ $ticket->response_due_at->format('d M Y H:i') }}</div>
                                    <small class="{{ $ticket->response_due_at->isPast() && ! $ticket->responded_at ? 'text-danger' : 'text-muted' }}">
                                        {{ $ticket->response_due_at->diffForHumans() }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #ticket-list-table td,
    #ticket-list-table th {
        vertical-align: middle;
    }

    #ticket-list-table td:first-child {
        min-width: 140px;
    }

    #ticket-list-table td:nth-child(2) {
        min-width: 300px;
    }

    #ticket-list-table td:last-child,
    #ticket-list-table th:last-child {
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('ticket-list-filter-form');
        const ticketTable = document.getElementById('ticket-list-table');
        const columnToggles = document.querySelectorAll('[data-column-toggle]');
        const columnStorageKey = 'ticket_list_optional_columns';

        if (!form) {
            return;
        }

        const categorySelect = form.querySelector('[name="ticket_category_id"]');
        const subcategorySelect = form.querySelector('[name="ticket_subcategory_id"]');
        const detailSubcategorySelect = form.querySelector('[name="ticket_detail_subcategory_id"]');
        const defaultColumns = {
            requester: false,
            type: false,
            category: false,
            subcategory: false,
            priority: false,
            approver: false,
        };

        const loadColumnPreferences = () => {
            try {
                const stored = window.localStorage.getItem(columnStorageKey);

                if (!stored) {
                    return defaultColumns;
                }

                return {
                    ...defaultColumns,
                    ...JSON.parse(stored),
                };
            } catch (error) {
                return defaultColumns;
            }
        };

        const applyColumnPreferences = (preferences) => {
            if (!ticketTable) {
                return;
            }

            Object.entries(preferences).forEach(([columnKey, isVisible]) => {
                ticketTable.querySelectorAll(`[data-column-key="${columnKey}"]`).forEach((cell) => {
                    cell.style.display = isVisible ? '' : 'none';
                });
            });

            columnToggles.forEach((toggle) => {
                toggle.checked = Boolean(preferences[toggle.dataset.columnToggle]);
            });
        };

        const persistColumnPreferences = (preferences) => {
            window.localStorage.setItem(columnStorageKey, JSON.stringify(preferences));
        };

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

        const currentColumnPreferences = loadColumnPreferences();
        applyColumnPreferences(currentColumnPreferences);

        columnToggles.forEach((toggle) => {
            toggle.addEventListener('change', () => {
                const updatedPreferences = {
                    ...loadColumnPreferences(),
                    [toggle.dataset.columnToggle]: toggle.checked,
                };

                persistColumnPreferences(updatedPreferences);
                applyColumnPreferences(updatedPreferences);
            });
        });
    });
</script>
@endpush
