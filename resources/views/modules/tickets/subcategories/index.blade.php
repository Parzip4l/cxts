@extends('layouts.vertical', ['subtitle' => 'Ticket Categories'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Ticket Categories'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search code or name"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="ticket_category_id" class="form-select">
                    <option value="">All ticket types</option>
                    @foreach ($categoryOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['ticket_category_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="">All status</option>
                    <option value="1" @selected(($filters['is_active'] ?? null) === true)>Active</option>
                    <option value="0" @selected(($filters['is_active'] ?? null) === false)>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('master-data.ticket-subcategories.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.ticket-subcategories.create') }}" class="btn btn-primary">Add Ticket Category</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket Type</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Approval</th>
                        <th>Approver Matrix</th>
                        <th>Direct Assign</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ticketSubcategories as $ticketSubcategory)
                        <tr>
                            <td>{{ $ticketSubcategory->category?->name ?? '-' }}</td>
                            <td>{{ $ticketSubcategory->code }}</td>
                            <td>{{ $ticketSubcategory->name }}</td>
                            <td>{{ $ticketSubcategory->description ?: '-' }}</td>
                            <td>
                                @if ($ticketSubcategory->requires_approval === null)
                                    <span class="badge bg-light text-muted border">Follow Type</span>
                                @elseif ($ticketSubcategory->requires_approval)
                                    <span class="badge bg-warning-subtle text-warning">Required</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Not Required</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">
                                    @if ($ticketSubcategory->approver_strategy)
                                        {{ \App\Models\TicketCategory::approverStrategies()[$ticketSubcategory->approver_strategy] ?? 'Follow Type' }}
                                    @else
                                        Follow Ticket Type
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    {{ $ticketSubcategory->approver?->name
                                        ?? \App\Models\TicketCategory::approverRoleLabel($ticketSubcategory->approver_role_code)
                                        ?? 'Follow Ticket Type' }}
                                </div>
                            </td>
                            <td>
                                @if ($ticketSubcategory->allow_direct_assignment === null)
                                    <span class="badge bg-light text-muted border">Follow Type</span>
                                @elseif ($ticketSubcategory->allow_direct_assignment)
                                    <span class="badge bg-success-subtle text-success">Allowed</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Needs Ready Flag</span>
                                @endif
                            </td>
                            <td>
                                @if ($ticketSubcategory->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.ticket-subcategories.edit', $ticketSubcategory) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="{{ route('master-data.ticket-subcategories.destroy', $ticketSubcategory) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this ticket category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No ticket subcategories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $ticketSubcategories->links() }}</div>
    </div>
</div>
@endsection
