@extends('layouts.vertical', ['subtitle' => 'Ticket Types'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Ticket Types'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search code or name"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="is_active" class="form-select">
                    <option value="">All status</option>
                    <option value="1" @selected(($filters['is_active'] ?? null) === true)>Active</option>
                    <option value="0" @selected(($filters['is_active'] ?? null) === false)>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('master-data.ticket-categories.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.ticket-categories.create') }}" class="btn btn-primary">Add Ticket Type</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
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
                    @forelse ($ticketCategories as $ticketCategory)
                        <tr>
                            <td>{{ $ticketCategory->code }}</td>
                            <td>{{ $ticketCategory->name }}</td>
                            <td>{{ $ticketCategory->description ?: '-' }}</td>
                            <td>
                                @if ($ticketCategory->requires_approval)
                                    <span class="badge bg-warning-subtle text-warning">Required</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Not Required</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">
                                    {{ \App\Models\TicketCategory::approverStrategies()[$ticketCategory->approver_strategy ?: \App\Models\TicketCategory::APPROVER_STRATEGY_FALLBACK] ?? 'Supervisor/Admin Fallback' }}
                                </div>
                                <div class="small text-muted">
                                    {{ $ticketCategory->approver?->name
                                        ?? \App\Models\TicketCategory::approverRoleLabel($ticketCategory->approver_role_code)
                                        ?? 'Supervisor/Admin Fallback' }}
                                </div>
                            </td>
                            <td>
                                @if ($ticketCategory->allow_direct_assignment)
                                    <span class="badge bg-success-subtle text-success">Allowed</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Needs Ready Flag</span>
                                @endif
                            </td>
                            <td>
                                @if ($ticketCategory->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.ticket-categories.edit', $ticketCategory) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="{{ route('master-data.ticket-categories.destroy', $ticketCategory) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this ticket type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No ticket categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $ticketCategories->links() }}</div>
    </div>
</div>
@endsection
