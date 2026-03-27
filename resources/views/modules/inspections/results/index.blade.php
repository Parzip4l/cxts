@extends('layouts.vertical', ['subtitle' => 'Inspection Results'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Inspection Operations', 'subtitle' => 'Inspection Results'])

<div class="row g-3 mb-3">
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Total</small>
                <h4 class="mb-0">{{ number_format($summary['total'] ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Submitted</small>
                <h4 class="mb-0">{{ number_format($summary['submitted'] ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Normal</small>
                <h4 class="mb-0 text-success">{{ number_format($summary['normal'] ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">Abnormal</small>
                <h4 class="mb-0 text-danger">{{ number_format($summary['abnormal'] ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">With Ticket</small>
                <h4 class="mb-0 text-warning">{{ number_format($summary['with_ticket'] ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card mb-0">
            <div class="card-body py-3">
                <small class="text-muted d-block">No Ticket</small>
                <h4 class="mb-0">{{ number_format($summary['without_ticket'] ?? 0) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search number, officer, asset, ticket"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}" @selected(($filters['status'] ?? null) === $statusOption)>
                            {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="final_result" class="form-select">
                    <option value="">All result</option>
                    @foreach ($finalResultOptions as $resultOption)
                        <option value="{{ $resultOption }}" @selected(($filters['final_result'] ?? null) === $resultOption)>
                            {{ strtoupper($resultOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="inspection_officer_id" class="form-select"
                    data-searchable-select data-search-placeholder="Search officer">
                    <option value="">All officer</option>
                    @foreach ($officerOptions as $officerOption)
                        <option value="{{ $officerOption->id }}" @selected((string) ($filters['inspection_officer_id'] ?? '') === (string) $officerOption->id)>
                            {{ $officerOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select name="has_ticket" class="form-select">
                    <option value="">Ticket?</option>
                    <option value="yes" @selected(($filters['has_ticket'] ?? null) === 'yes')>Yes</option>
                    <option value="no" @selected(($filters['has_ticket'] ?? null) === 'no')>No</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="inspection_date_from" class="form-control"
                    value="{{ $filters['inspection_date_from'] ?? '' }}" placeholder="From date">
            </div>
            <div class="col-md-2">
                <input type="date" name="inspection_date_to" class="form-control"
                    value="{{ $filters['inspection_date_to'] ?? '' }}" placeholder="To date">
            </div>
            <div class="col-md-2 d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('inspection-results.index') }}" class="btn btn-outline-light">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Inspection Task</th>
                        <th>Inspection Date</th>
                        <th>Officer</th>
                        <th>Template</th>
                        <th>Related Asset / Location</th>
                        <th>Status</th>
                        <th>Result</th>
                        <th>Findings</th>
                        <th>Evidence</th>
                        <th>Ticket</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspections as $inspection)
                        @php
                            $statusClass = match ($inspection->status) {
                                \App\Models\Inspection::STATUS_SUBMITTED => 'bg-success-subtle text-success',
                                \App\Models\Inspection::STATUS_IN_PROGRESS => 'bg-warning-subtle text-warning',
                                default => 'bg-secondary-subtle text-secondary',
                            };

                            $resultClass = match ($inspection->final_result) {
                                \App\Models\Inspection::FINAL_RESULT_NORMAL => 'bg-success-subtle text-success',
                                \App\Models\Inspection::FINAL_RESULT_ABNORMAL => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $inspection->inspection_number }}</div>
                                <small class="text-muted">{{ $inspection->summary_notes ? \Illuminate\Support\Str::limit($inspection->summary_notes, 60) : '-' }}</small>
                            </td>
                            <td>{{ optional($inspection->inspection_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $inspection->officer?->name ?? '-' }}</td>
                            <td>{{ $inspection->template?->name ?? '-' }}</td>
                            <td>
                                <div>{{ $inspection->asset?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $inspection->assetLocation?->name ?? '-' }}</small>
                            </td>
                            <td><span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $inspection->status)) }}</span></td>
                            <td><span class="badge {{ $resultClass }}">{{ $inspection->final_result ? strtoupper($inspection->final_result) : '-' }}</span></td>
                            <td>
                                <small class="d-block">Pass: {{ $inspection->pass_items_count }}</small>
                                <small class="d-block">Fail: {{ $inspection->fail_items_count }}</small>
                                <small class="d-block">N/A: {{ $inspection->na_items_count }}</small>
                            </td>
                            <td>{{ number_format((int) $inspection->evidences_count) }}</td>
                            <td>
                                @if ($inspection->ticket?->ticket_number)
                                    @if ($canOpenTicketDetail)
                                        <a href="{{ route('tickets.show', $inspection->ticket) }}" class="link-primary">
                                            {{ $inspection->ticket->ticket_number }}
                                        </a>
                                    @else
                                        {{ $inspection->ticket->ticket_number }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('inspection-results.show', $inspection) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No inspection results found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $inspections->links() }}</div>
    </div>
</div>
@endsection
