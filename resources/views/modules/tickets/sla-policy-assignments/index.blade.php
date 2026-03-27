@extends('layouts.vertical', ['subtitle' => 'SLA Rules'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'SLA Rules'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search assignment"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="sla_policy_id" class="form-select">
                    <option value="">All policies</option>
                    @foreach ($policyOptions as $policyOption)
                        <option value="{{ $policyOption->id }}" @selected((string) ($filters['sla_policy_id'] ?? '') === (string) $policyOption->id)>
                            {{ $policyOption->name }}
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
                <a href="{{ route('master-data.sla-policy-assignments.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.sla-policy-assignments.create') }}" class="btn btn-primary">Add Rule</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Policy</th>
                        <th>Rule Match</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slaPolicyAssignments as $assignment)
                        <tr>
                            <td>{{ $assignment->policy?->name ?? '-' }}</td>
                            <td>
                                <div class="small">
                                    <div><strong>Process Type Key:</strong> {{ $assignment->ticket_type ?? 'Any' }}</div>
                                    <div><strong>Ticket Type:</strong> {{ $assignment->category?->name ?? 'Any' }}</div>
                                    <div><strong>Ticket Category:</strong> {{ $assignment->subcategory?->name ?? 'Any' }}</div>
                                    <div><strong>Ticket Sub Category:</strong> {{ $assignment->detailSubcategory?->name ?? 'Any' }}</div>
                                    <div><strong>Service:</strong> {{ $assignment->serviceItem?->name ?? 'Any' }}</div>
                                    <div><strong>Priority:</strong> {{ $assignment->priority?->name ?? 'Any' }}</div>
                                    <div><strong>Impact/Urgency:</strong> {{ $assignment->impact ?? 'Any' }} / {{ $assignment->urgency ?? 'Any' }}</div>
                                </div>
                            </td>
                            <td>{{ $assignment->sort_order }}</td>
                            <td>
                                @if ($assignment->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.sla-policy-assignments.edit', $assignment) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="{{ route('master-data.sla-policy-assignments.destroy', $assignment) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this SLA rule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No SLA policy assignments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $slaPolicyAssignments->links() }}</div>
    </div>
</div>
@endsection
