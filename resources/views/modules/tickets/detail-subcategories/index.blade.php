@extends('layouts.vertical', ['subtitle' => 'Ticket Sub Categories'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Ticket Sub Categories'])

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
                <select name="ticket_subcategory_id" class="form-select">
                    <option value="">All ticket categories</option>
                    @foreach ($categoryOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['ticket_subcategory_id'] ?? '') === (string) $option->id)>
                            {{ $option->category?->name ? $option->category->name.' / ' : '' }}{{ $option->name }}
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
                <a href="{{ route('master-data.ticket-detail-subcategories.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.ticket-detail-subcategories.create') }}" class="btn btn-primary">Add Ticket Sub Category</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket Type</th>
                        <th>Ticket Category</th>
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
                    @forelse ($ticketDetailSubcategories as $ticketDetailSubcategory)
                        <tr>
                            <td>{{ $ticketDetailSubcategory->category?->category?->name ?? '-' }}</td>
                            <td>{{ $ticketDetailSubcategory->category?->name ?? '-' }}</td>
                            <td>{{ $ticketDetailSubcategory->code }}</td>
                            <td>{{ $ticketDetailSubcategory->name }}</td>
                            <td>{{ $ticketDetailSubcategory->description ?: '-' }}</td>
                            <td>
                                @if ($ticketDetailSubcategory->requires_approval === null)
                                    <span class="badge bg-light text-muted border">Follow Parent</span>
                                @elseif ($ticketDetailSubcategory->requires_approval)
                                    <span class="badge bg-warning-subtle text-warning">Required</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Not Required</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">
                                    @if ($ticketDetailSubcategory->approver_strategy)
                                        {{ \App\Models\TicketCategory::approverStrategies()[$ticketDetailSubcategory->approver_strategy] ?? 'Follow Parent' }}
                                    @else
                                        Follow Parent
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    {{ $ticketDetailSubcategory->approver?->name
                                        ?? \App\Models\TicketCategory::approverRoleLabel($ticketDetailSubcategory->approver_role_code)
                                        ?? 'Follow Parent' }}
                                </div>
                            </td>
                            <td>
                                @if ($ticketDetailSubcategory->allow_direct_assignment === null)
                                    <span class="badge bg-light text-muted border">Follow Parent</span>
                                @elseif ($ticketDetailSubcategory->allow_direct_assignment)
                                    <span class="badge bg-success-subtle text-success">Allowed</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Needs Ready Flag</span>
                                @endif
                            </td>
                            <td>
                                @if ($ticketDetailSubcategory->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.ticket-detail-subcategories.edit', $ticketDetailSubcategory) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="{{ route('master-data.ticket-detail-subcategories.destroy', $ticketDetailSubcategory) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this ticket sub category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No ticket sub categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $ticketDetailSubcategories->links() }}</div>
    </div>
</div>
@endsection
