@extends('layouts.vertical', ['subtitle' => 'Inspection Result Detail'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Inspection Result', 'subtitle' => $inspection->inspection_number])

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

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">{{ $inspection->inspection_number }}</h5>
                <small class="text-muted">Inspection Date: {{ optional($inspection->inspection_date)->format('Y-m-d') ?? '-' }}</small>
            </div>
            <div class="text-end">
                <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $inspection->status)) }}</span>
                <span class="badge {{ $resultClass }}">{{ $inspection->final_result ? strtoupper($inspection->final_result) : '-' }}</span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6"><strong>Officer:</strong> {{ $inspection->officer?->name ?? '-' }}</div>
            <div class="col-md-6"><strong>Officer Email:</strong> {{ $inspection->officer?->email ?? '-' }}</div>
            <div class="col-md-6"><strong>Inspection Template:</strong> {{ $inspection->template?->code ? $inspection->template->code.' - '.$inspection->template->name : ($inspection->template?->name ?? '-') }}</div>
            <div class="col-md-6"><strong>Related Asset:</strong> {{ $inspection->asset?->code ? $inspection->asset->code.' - '.$inspection->asset->name : ($inspection->asset?->name ?? '-') }}</div>
            <div class="col-md-6"><strong>Asset Location:</strong> {{ $inspection->assetLocation?->code ? $inspection->assetLocation->code.' - '.$inspection->assetLocation->name : ($inspection->assetLocation?->name ?? '-') }}</div>
            <div class="col-md-6"><strong>Asset Category:</strong> {{ $inspection->asset?->category?->name ?? '-' }}</div>
            <div class="col-md-6"><strong>Related Service:</strong> {{ $inspection->asset?->service?->name ?? '-' }}</div>
            <div class="col-md-6"><strong>Owner Department:</strong> {{ $inspection->asset?->ownerDepartment?->name ?? '-' }}</div>
            <div class="col-md-6"><strong>Vendor:</strong> {{ $inspection->asset?->vendor?->name ?? '-' }}</div>
            <div class="col-md-6"><strong>Asset Status:</strong> {{ $inspection->asset?->status?->name ?? '-' }}</div>
            <div class="col-md-6"><strong>Criticality:</strong> {{ $inspection->asset?->criticality ? strtoupper($inspection->asset->criticality) : '-' }}</div>
            <div class="col-md-6"><strong>Started At:</strong> {{ optional($inspection->started_at)->format('Y-m-d H:i') ?? '-' }}</div>
            <div class="col-md-6"><strong>Submitted At:</strong> {{ optional($inspection->submitted_at)->format('Y-m-d H:i') ?? '-' }}</div>
            <div class="col-md-6">
                <strong>Linked Ticket:</strong>
                @if ($inspection->ticket?->ticket_number)
                    @if ($canOpenTicketDetail)
                        <a href="{{ route('tickets.show', $inspection->ticket) }}" class="link-primary">{{ $inspection->ticket->ticket_number }}</a>
                    @else
                        {{ $inspection->ticket->ticket_number }}
                    @endif
                @else
                    -
                @endif
            </div>
            <div class="col-md-6"><strong>Ticket Status:</strong> {{ $inspection->ticket?->status?->name ?? '-' }}</div>
            <div class="col-12"><strong>Summary Notes:</strong><br>{{ $inspection->summary_notes ?: '-' }}</div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Inspection Checklist</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Expected</th>
                        <th>Result</th>
                        <th>Value</th>
                        <th>Notes</th>
                        <th>Checked By</th>
                        <th>Checked At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspection->items as $item)
                        <tr>
                            <td>{{ $item->sequence }}</td>
                            <td>{{ $item->item_label }}</td>
                            <td>{{ $item->expected_value ?? '-' }}</td>
                            <td>{{ $item->result_status ? strtoupper($item->result_status) : '-' }}</td>
                            <td>{{ $item->result_value ?? '-' }}</td>
                            <td>{{ $item->notes ?? '-' }}</td>
                            <td>{{ $item->checkedBy?->name ?? '-' }}</td>
                            <td>{{ optional($item->checked_at)->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No checklist item found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Inspection Evidences</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Related Item</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Uploaded By</th>
                        <th>Uploaded At</th>
                        <th>Notes</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspection->evidences as $evidence)
                        <tr>
                            <td>{{ $evidence->original_name }}</td>
                            <td>{{ $evidence->inspectionItem?->item_label ?? '-' }}</td>
                            <td>{{ $evidence->mime_type ?? '-' }}</td>
                            <td>{{ $evidence->file_size ? number_format($evidence->file_size / 1024, 2).' KB' : '-' }}</td>
                            <td>{{ $evidence->uploadedBy?->name ?? '-' }}</td>
                            <td>{{ optional($evidence->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $evidence->notes ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($evidence->file_path) }}"
                                    target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No evidence uploaded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('inspection-results.index') }}" class="btn btn-outline-light">Back</a>
</div>
@endsection
