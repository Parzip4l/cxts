@extends('layouts.vertical', ['subtitle' => 'Task Detail'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => $ticket->ticket_number])

@php
    $statusCode = strtoupper((string) ($ticket->status?->code ?? ''));
    $isTerminal = $ticket->completed_at !== null || $ticket->closed_at !== null || in_array($statusCode, ['COMPLETED', 'CLOSED'], true);
    $canStart = !$isTerminal && $ticket->started_at === null;
    $canPause = !$isTerminal && $ticket->started_at !== null && $ticket->paused_at === null;
    $canResume = !$isTerminal && $ticket->started_at !== null && $ticket->paused_at !== null;
    $canComplete = !$isTerminal && $ticket->started_at !== null;
    $workEndedAt = $ticket->completed_at ?? $ticket->resolved_at ?? $ticket->closed_at;
    $workDurationMinutes = ($ticket->started_at && $workEndedAt) ? $ticket->started_at->diffInMinutes($workEndedAt) : null;
@endphp

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <h5 class="mb-1">{{ $ticket->title }}</h5>
                <p class="text-muted mb-3">{{ $ticket->ticket_number }}</p>

                <p class="mb-3">{{ $ticket->description }}</p>

                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Status:</strong> {{ $ticket->status?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Priority:</strong> {{ $ticket->priority?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Ticket Type:</strong> {{ $ticket->category?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Ticket Category:</strong> {{ $ticket->subcategory?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Ticket Sub Category:</strong> {{ $ticket->detailSubcategory?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Related Service:</strong> {{ $ticket->service?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Related Asset:</strong> {{ $ticket->asset?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Asset Location:</strong> {{ $ticket->assetLocation?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Started At:</strong> {{ optional($ticket->started_at)->format('Y-m-d H:i') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Paused At:</strong> {{ optional($ticket->paused_at)->format('Y-m-d H:i') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Completed At:</strong> {{ optional($ticket->completed_at)->format('Y-m-d H:i') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Work Duration:</strong> {{ $workDurationMinutes !== null ? $workDurationMinutes.' minute(s)' : '-' }}</div>
                    <div class="col-md-6"><strong>Response Due:</strong> {{ optional($ticket->response_due_at)->format('Y-m-d H:i') ?? '-' }}</div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('engineer-tasks.index') }}" class="btn btn-outline-light">Back to My Tasks</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Worklog</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('engineer-tasks.worklogs.store', $ticket) }}" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label for="log_type" class="form-label">Type</label>
                        <select id="log_type" name="log_type" class="form-select @error('log_type') is-invalid @enderror">
                            @php $type = old('log_type', 'note'); @endphp
                            <option value="note" @selected($type === 'note')>Note</option>
                            <option value="progress" @selected($type === 'progress')>Progress</option>
                            <option value="resolution" @selected($type === 'resolution')>Resolution</option>
                        </select>
                        @error('log_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="started_at" class="form-label">Started At</label>
                        <input type="datetime-local" id="started_at" name="started_at"
                            value="{{ old('started_at') }}" class="form-control @error('started_at') is-invalid @enderror">
                        @error('started_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="ended_at" class="form-label">Ended At</label>
                        <input type="datetime-local" id="ended_at" name="ended_at"
                            value="{{ old('ended_at') }}" class="form-control @error('ended_at') is-invalid @enderror">
                        @error('ended_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Save Worklog</button>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Worklogs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ticket->worklogs as $worklog)
                                <tr>
                                    <td>{{ optional($worklog->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ ucfirst($worklog->log_type) }}</td>
                                    <td>{{ $worklog->description }}</td>
                                    <td>{{ $worklog->duration_minutes !== null ? $worklog->duration_minutes.' min' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No worklog yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Task Actions</h5>
            </div>
            <div class="card-body">
                @if ($errors->has('action'))
                    <div class="alert alert-danger">{{ $errors->first('action') }}</div>
                @endif

                <div class="d-grid gap-2">
                    @if ($canStart)
                        <form method="POST" action="{{ route('engineer-tasks.start', $ticket) }}">
                            @csrf
                            <input type="hidden" name="notes" value="Start work from web panel">
                            <button type="submit" class="btn btn-info w-100">Start Work</button>
                        </form>
                    @endif

                    @if ($canPause)
                        <form method="POST" action="{{ route('engineer-tasks.pause', $ticket) }}">
                            @csrf
                            <input type="hidden" name="notes" value="Pause work from web panel">
                            <button type="submit" class="btn btn-warning w-100">Pause Work</button>
                        </form>
                    @endif

                    @if ($canResume)
                        <form method="POST" action="{{ route('engineer-tasks.resume', $ticket) }}">
                            @csrf
                            <input type="hidden" name="notes" value="Resume work from web panel">
                            <button type="submit" class="btn btn-primary w-100">Resume Work</button>
                        </form>
                    @endif

                    @if ($canComplete)
                        <form method="POST" action="{{ route('engineer-tasks.complete', $ticket) }}">
                            @csrf
                            <input type="hidden" name="notes" value="Complete work from web panel">
                            <button type="submit" class="btn btn-success w-100">Complete Work</button>
                        </form>
                    @endif

                    @if (! $canStart && ! $canPause && ! $canResume && ! $canComplete)
                        <div class="alert alert-light border mb-0">
                            Tidak ada aksi transisi yang tersedia untuk status task saat ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Activity Timeline</h5>
            </div>
            <div class="card-body">
                @forelse ($ticket->activities as $activity)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-semibold">{{ str_replace('_', ' ', strtoupper($activity->activity_type)) }}</div>
                        <div class="small text-muted">{{ optional($activity->created_at)->format('Y-m-d H:i') }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
