@extends('layouts.vertical', ['subtitle' => $isOpsActor ? 'Inspection Tasks' : 'My Inspection Tasks'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Inspection Operations', 'subtitle' => $isOpsActor ? 'Inspection Tasks' : 'My Inspection Tasks'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="vstack gap-3 mb-4">
            <div class="row g-3">
                <div class="col-12 col-xl-4">
                    <label for="inspection-search" class="form-label small text-muted mb-1">Search</label>
                    <input
                        id="inspection-search"
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search task number or asset"
                        value="{{ $filters['search'] ?? '' }}"
                    >
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label for="inspection-status" class="form-label small text-muted mb-1">Status</label>
                    <select id="inspection-status" name="status" class="form-select">
                        <option value="">All status</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}" @selected(($filters['status'] ?? null) === $statusOption)>
                                {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label for="inspection-date" class="form-label small text-muted mb-1">Inspection date</label>
                    <input
                        id="inspection-date"
                        type="date"
                        name="inspection_date"
                        class="form-control"
                        value="{{ $filters['inspection_date'] ?? '' }}"
                    >
                </div>
                @if ($isOpsActor)
                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="inspection-officer" class="form-label small text-muted mb-1">Officer</label>
                        <select
                            id="inspection-officer"
                            name="inspection_officer_id"
                            class="form-select"
                            data-searchable-select
                            data-search-placeholder="Search officer"
                        >
                            <option value="">All officers</option>
                            @foreach ($officerOptions as $officer)
                                <option value="{{ $officer->id }}" @selected((string) ($filters['inspection_officer_id'] ?? '') === (string) $officer->id)>
                                    {{ $officer->name }} ({{ strtoupper($officer->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="inspection-schedule" class="form-label small text-muted mb-1">Schedule</label>
                        <select id="inspection-schedule" name="schedule_type" class="form-select">
                            <option value="">All schedule</option>
                            @foreach ($scheduleTypeOptions as $scheduleTypeOption)
                                <option value="{{ $scheduleTypeOption }}" @selected(($filters['schedule_type'] ?? '') === $scheduleTypeOption)>
                                    {{ ucfirst($scheduleTypeOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="small text-muted">
                    Filter inspection tasks by status, date, officer, or schedule pattern before opening the task.
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-md-end gap-2">
                    <button class="btn btn-outline-secondary text-nowrap" type="submit">Apply Filter</button>
                    <a href="{{ route('inspections.index') }}" class="btn btn-outline-light text-nowrap">Reset</a>
                    @if ($isOpsActor)
                        <a href="{{ route('inspections.create') }}" class="btn btn-primary text-nowrap">Schedule Task</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Task Number</th>
                        <th>Inspection Template</th>
                        <th>Related Asset</th>
                        <th>Inspection Date</th>
                        <th>Officer</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Final Result</th>
                        <th>Linked Ticket</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspections as $inspection)
                        <tr>
                            <td>{{ $inspection->inspection_number }}</td>
                            <td>{{ $inspection->template?->name ?? '-' }}</td>
                            <td>{{ $inspection->asset?->name ?? '-' }}</td>
                            <td>{{ optional($inspection->inspection_date)->format('Y-m-d') }}</td>
                            <td>{{ $inspection->officer?->name ?? '-' }}</td>
                            <td>
                                {{ strtoupper($inspection->schedule_type ?? 'none') }}
                                @if (($inspection->schedule_type ?? 'none') !== 'none')
                                    <div class="small text-muted">
                                        Interval {{ $inspection->schedule_interval ?? 1 }}
                                        @if (($inspection->schedule_type ?? 'none') === 'daily')
                                            day(s)
                                        @else
                                            week(s)
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge bg-info-subtle text-info">{{ ucfirst(str_replace('_', ' ', $inspection->status)) }}</span></td>
                            <td>{{ $inspection->final_result ? strtoupper($inspection->final_result) : '-' }}</td>
                            <td>{{ $inspection->ticket?->ticket_number ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-sm btn-outline-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No inspections found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $inspections->links() }}</div>
    </div>
</div>
@endsection
