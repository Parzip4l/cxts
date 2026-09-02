@extends('layouts.vertical', ['subtitle' => 'Problem Management'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Problem Management'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search problem, RCA, title"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    @foreach ($statusOptions as $statusCode => $statusLabel)
                        <option value="{{ $statusCode }}" @selected(($filters['status'] ?? null) === $statusCode)>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_known_error" class="form-select">
                    <option value="">All KEDB</option>
                    <option value="1" @selected(($filters['is_known_error'] ?? null) === true)>Known Error</option>
                    <option value="0" @selected(($filters['is_known_error'] ?? null) === false)>Not Known Error</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="owner_user_id" class="form-select" data-searchable-select data-search-placeholder="Search owner">
                    <option value="">All owner</option>
                    @foreach ($ownerOptions as $owner)
                        <option value="{{ $owner->id }}" @selected((string) ($filters['owner_user_id'] ?? '') === (string) $owner->id)>
                            {{ $owner->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('problems.index') }}" class="btn btn-outline-light">Reset</a>
            </div>
            <div class="col-12 text-end">
                <a href="{{ route('problems.create') }}" class="btn btn-primary">Create Problem</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Problem</th>
                        <th>Status</th>
                        <th>Owner</th>
                        <th>Priority</th>
                        <th>Known Error</th>
                        <th>Linked Tickets</th>
                        <th>Target</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($problems as $problem)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $problem->problem_number }}</div>
                                <div class="small text-muted">{{ $problem->title }}</div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $statusOptions[$problem->status] ?? $problem->status }}</span></td>
                            <td>{{ $problem->owner?->name ?? '-' }}</td>
                            <td>{{ $problem->priority?->name ?? '-' }}</td>
                            <td>{{ $problem->is_known_error ? 'Yes' : 'No' }}</td>
                            <td>{{ number_format((int) ($problem->tickets_count ?? 0)) }}</td>
                            <td>{{ optional($problem->target_resolution_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('problems.show', $problem) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="{{ route('problems.edit', $problem) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('problems.destroy', $problem) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this problem?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No problem found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $problems->links() }}
        </div>
    </div>
</div>
@endsection
