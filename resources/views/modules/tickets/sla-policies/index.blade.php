@extends('layouts.vertical', ['subtitle' => 'SLA Policies'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'SLA Policies'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search SLA policy"
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
                <a href="{{ route('master-data.sla-policies.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.sla-policies.create') }}" class="btn btn-primary">Add SLA Policy</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Response (min)</th>
                        <th>Resolution (min)</th>
                        <th>Working Hours ID</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slaPolicies as $slaPolicy)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $slaPolicy->name }}</div>
                                <div class="text-muted small">{{ $slaPolicy->description ?: '-' }}</div>
                            </td>
                            <td>{{ $slaPolicy->response_time_minutes ?? '-' }}</td>
                            <td>{{ $slaPolicy->resolution_time_minutes ?? '-' }}</td>
                            <td>{{ $slaPolicy->working_hours_id ?? '-' }}</td>
                            <td>
                                @if ($slaPolicy->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.sla-policies.edit', $slaPolicy) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="{{ route('master-data.sla-policies.destroy', $slaPolicy) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this SLA policy?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No SLA policies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $slaPolicies->links() }}</div>
    </div>
</div>
@endsection
