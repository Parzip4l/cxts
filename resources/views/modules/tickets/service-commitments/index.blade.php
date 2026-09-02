@extends('layouts.vertical', ['subtitle' => 'Service Commitments'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticket Setup', 'subtitle' => 'Service Commitments'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search commitment"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="commitment_type" class="form-select">
                    <option value="">All type</option>
                    @foreach ($typeOptions as $typeCode => $typeLabel)
                        <option value="{{ $typeCode }}" @selected(($filters['commitment_type'] ?? null) === $typeCode)>
                            {{ $typeLabel }}
                        </option>
                    @endforeach
                </select>
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
            <div class="col-md-3">
                <select name="service_id" class="form-select" data-searchable-select data-search-placeholder="Search service">
                    <option value="">All service</option>
                    @foreach ($serviceOptions as $service)
                        <option value="{{ $service->id }}" @selected((string) ($filters['service_id'] ?? '') === (string) $service->id)>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('service-commitments.index') }}" class="btn btn-outline-light">Reset</a>
            </div>
            <div class="col-12 text-end">
                <a href="{{ route('service-commitments.create') }}" class="btn btn-primary">Add Commitment</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Commitment</th>
                        <th>Type</th>
                        <th>Service</th>
                        <th>Provider</th>
                        <th>Targets</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commitments as $commitment)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $commitment->commitment_number }}</div>
                                <div class="small text-muted">{{ $commitment->name }}</div>
                            </td>
                            <td>{{ $commitment->typeLabel() }}</td>
                            <td>{{ $commitment->service?->name ?? '-' }}</td>
                            <td>
                                <div>{{ $commitment->providerDepartment?->name ?? $commitment->vendor?->name ?? '-' }}</div>
                                <div class="small text-muted">{{ $commitment->escalation_contact ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="small">Response: {{ $commitment->response_target_minutes ?? '-' }} min</div>
                                <div class="small">Resolution: {{ $commitment->resolution_target_minutes ?? '-' }} min</div>
                                <div class="small text-muted">Availability: {{ $commitment->availability_target_percent ?? '-' }}%</div>
                            </td>
                            <td>
                                <div>{{ optional($commitment->effective_from)->format('d M Y') ?? '-' }}</div>
                                <div class="small text-muted">to {{ optional($commitment->effective_until)->format('d M Y') ?? '-' }}</div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $commitment->statusLabel() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('service-commitments.edit', $commitment) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('service-commitments.destroy', $commitment) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this service commitment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No service commitment found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $commitments->links() }}</div>
    </div>
</div>
@endsection
