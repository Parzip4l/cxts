@extends('layouts.vertical', ['subtitle' => 'Tickets'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Tickets'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-3 mb-3" id="ticket-list-filter-form">
            <div class="col-lg-3">
                <input type="text" name="search" class="form-control" placeholder="Search ticket"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-lg-2">
                <select name="ticket_status_id" class="form-select">
                    <option value="">All status</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['ticket_status_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <select name="ticket_priority_id" class="form-select">
                    <option value="">All priority</option>
                    @foreach ($priorityOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['ticket_priority_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
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
            <div class="col-lg-3">
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
                <select name="expected_approver_role_code" class="form-select">
                    <option value="">All approver roles</option>
                    @foreach ($approverRoleOptions as $approverRoleCode => $approverRoleLabel)
                        <option value="{{ $approverRoleCode }}" @selected((string) ($filters['expected_approver_role_code'] ?? '') === (string) $approverRoleCode)>
                            {{ $approverRoleLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <select name="approval_status" class="form-select">
                    <option value="">All approval status</option>
                    @foreach ($approvalStatusOptions as $approvalStatusCode => $approvalStatusLabel)
                        <option value="{{ $approvalStatusCode }}" @selected((string) ($filters['approval_status'] ?? '') === (string) $approvalStatusCode)>
                            {{ $approvalStatusLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-12 d-flex flex-wrap justify-content-between gap-2">
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-light">Reset</a>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-secondary" type="submit">Apply Filter</button>
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary">Create Ticket</a>
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
                    @forelse ($tickets as $ticket)
                        @php
                            $statusCode = $ticket->status?->code;
                            $statusClass = match ($statusCode) {
                                'IN_PROGRESS' => 'bg-info-subtle text-info',
                                'ON_HOLD' => 'bg-warning-subtle text-warning',
                                'COMPLETED', 'CLOSED' => 'bg-success-subtle text-success',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                        @endphp
                        <tr>
                            <td>{{ $ticket->ticket_number }}</td>
                            <td>{{ $ticket->title }}</td>
                            <td>{{ $ticket->requester?->name ?? '-' }}</td>
                            <td>{{ $ticket->category?->name ?? '-' }}</td>
                            <td>{{ $ticket->subcategory?->name ?? '-' }}</td>
                            <td>{{ $ticket->detailSubcategory?->name ?? '-' }}</td>
                            <td>{{ $ticket->priority?->name ?? '-' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $ticket->status?->name ?? '-' }}</span></td>
                            <td>{{ str($ticket->approval_status ?? 'not_required')->replace('_', ' ')->title() }}</td>
                            <td>
                                <div>{{ $ticket->expectedApprover?->name ?? $ticket->expected_approver_name_snapshot ?? '-' }}</div>
                                @if ($ticket->expected_approver_role_code)
                                    <div class="small text-muted">{{ \App\Models\TicketCategory::approverRoleLabel($ticket->expected_approver_role_code) }}</div>
                                @endif
                            </td>
                            <td>{{ $ticket->assignedEngineer?->name ?? '-' }}</td>
                            <td>{{ optional($ticket->response_due_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
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
@endpush
