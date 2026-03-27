@extends('layouts.vertical', ['subtitle' => 'Inspection Task Detail'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Inspection Task', 'subtitle' => $inspection->inspection_number])

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row g-2 small mb-3">
                    <div class="col-md-6"><strong>Inspection Template:</strong> {{ $inspection->template?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Related Asset:</strong> {{ $inspection->asset?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Asset Location:</strong> {{ $inspection->assetLocation?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Assigned Inspector:</strong> {{ $inspection->officer?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Date:</strong> {{ optional($inspection->inspection_date)->format('Y-m-d') }}</div>
                    <div class="col-md-6"><strong>Schedule:</strong> {{ strtoupper($inspection->schedule_type ?? 'none') }}</div>
                    <div class="col-md-6"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $inspection->status)) }}</div>
                    <div class="col-md-6"><strong>Final Result:</strong> {{ $inspection->final_result ? strtoupper($inspection->final_result) : '-' }}</div>
                    <div class="col-md-6"><strong>Generated Ticket:</strong> {{ $inspection->ticket?->ticket_number ?? '-' }}</div>
                    <div class="col-md-6"><strong>Submitted:</strong> {{ optional($inspection->submitted_at)->format('Y-m-d H:i') ?? '-' }}</div>
                </div>

                @if ($canExecuteInspection && $inspection->status !== \App\Models\Inspection::STATUS_SUBMITTED)
                    <form method="POST" action="{{ route('inspections.items.update', $inspection) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Expected</th>
                                        <th style="width: 130px;">Status</th>
                                        <th style="width: 160px;">Value</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inspection->items as $index => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                {{ $item->sequence }}. {{ $item->item_label }}
                                            </td>
                                            <td>{{ $item->expected_value ?? '-' }}</td>
                                            <td>
                                                <select name="items[{{ $index }}][result_status]" class="form-select form-select-sm">
                                                    <option value="">-</option>
                                                    @foreach ($resultStatusOptions as $resultStatusOption)
                                                        <option value="{{ $resultStatusOption }}" @selected($item->result_status === $resultStatusOption)>
                                                            {{ strtoupper($resultStatusOption) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][result_value]"
                                                    class="form-control form-control-sm" value="{{ $item->result_value }}">
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][notes]"
                                                    class="form-control form-control-sm" value="{{ $item->notes }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Item Results</button>
                            <a href="{{ route('inspections.index') }}" class="btn btn-outline-light">Back</a>
                        </div>
                    </form>
                @else
                    <div class="alert alert-light border">Inspection task ini dalam mode read-only.</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Expected</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inspection->items as $item)
                                    <tr>
                                        <td>{{ $item->sequence }}. {{ $item->item_label }}</td>
                                        <td>{{ $item->expected_value ?? '-' }}</td>
                                        <td>{{ strtoupper($item->result_status ?? '-') }}</td>
                                        <td>{{ $item->result_value ?? '-' }}</td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No inspection items.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('inspections.index') }}" class="btn btn-outline-light">Back</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if ($canExecuteInspection && $inspection->status !== \App\Models\Inspection::STATUS_SUBMITTED)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Submit Inspection Result</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inspections.submit', $inspection) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="final_result" class="form-label">Final Result</label>
                            @php $finalResult = old('final_result', $inspection->final_result ?: \App\Models\Inspection::FINAL_RESULT_NORMAL); @endphp
                            <select id="final_result" name="final_result" class="form-select @error('final_result') is-invalid @enderror" required>
                                <option value="{{ \App\Models\Inspection::FINAL_RESULT_NORMAL }}" @selected($finalResult === \App\Models\Inspection::FINAL_RESULT_NORMAL)>Normal</option>
                                <option value="{{ \App\Models\Inspection::FINAL_RESULT_ABNORMAL }}" @selected($finalResult === \App\Models\Inspection::FINAL_RESULT_ABNORMAL)>Abnormal</option>
                            </select>
                            @error('final_result')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="summary_notes" class="form-label">Summary Notes</label>
                            <textarea id="summary_notes" name="summary_notes" rows="3"
                                class="form-control @error('summary_notes') is-invalid @enderror">{{ old('summary_notes', $inspection->summary_notes) }}</textarea>
                            @error('summary_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="supporting_files" class="form-label">Supporting Files (required if Abnormal)</label>
                            <input type="file" id="supporting_files" name="supporting_files[]"
                                class="form-control @error('supporting_files') is-invalid @enderror @error('supporting_files.*') is-invalid @enderror"
                                multiple>
                            @error('supporting_files')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('supporting_files.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success w-100">Submit Inspection Result</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($canExecuteInspection && $inspection->status !== \App\Models\Inspection::STATUS_SUBMITTED)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload Inspection Evidence</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inspections.evidences.store', $inspection) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label for="file" class="form-label">File</label>
                            <input type="file" id="file" name="file" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label for="inspection_item_id" class="form-label">Item (Optional)</label>
                            <select id="inspection_item_id" name="inspection_item_id" class="form-select">
                                <option value="">- General evidence -</option>
                                @foreach ($inspection->items as $item)
                                    <option value="{{ $item->id }}">{{ $item->sequence }}. {{ $item->item_label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" name="notes" rows="2" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">Upload</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Inspection Evidence</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($inspection->evidences as $evidence)
                        <li class="list-group-item">
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($evidence->file_path) }}" target="_blank">
                                {{ $evidence->original_name }}
                            </a>
                            <div class="small text-muted">{{ $evidence->notes ?: '-' }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No inspection evidence uploaded.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
