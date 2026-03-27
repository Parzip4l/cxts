@extends('layouts.vertical', ['subtitle' => 'Workflow Statuses'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Workflow Statuses'])

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
                <a href="{{ route('master-data.ticket-statuses.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.ticket-statuses.create') }}" class="btn btn-primary">Add Workflow Status</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Open</th>
                        <th>In Progress</th>
                        <th>Closed</th>
                        <th>Active</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ticketStatuses as $ticketStatus)
                        <tr>
                            <td>{{ $ticketStatus->code }}</td>
                            <td>{{ $ticketStatus->name }}</td>
                            <td>
                                @if ($ticketStatus->is_open)
                                    <span class="badge bg-info-subtle text-info">Yes</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($ticketStatus->is_in_progress)
                                    <span class="badge bg-warning-subtle text-warning">Yes</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($ticketStatus->is_closed)
                                    <span class="badge bg-success-subtle text-success">Yes</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($ticketStatus->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.ticket-statuses.edit', $ticketStatus) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="{{ route('master-data.ticket-statuses.destroy', $ticketStatus) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this workflow status?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No ticket statuses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $ticketStatuses->links() }}</div>
    </div>
</div>
@endsection
