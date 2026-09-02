@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => $pageTitle])

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $action }}" class="row g-3">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="col-md-8">
                <label for="title" class="form-label">Problem Title</label>
                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $problem->title) }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach ($statusOptions as $statusCode => $statusLabel)
                        <option value="{{ $statusCode }}" @selected(old('status', $problem->status ?: 'open') === $statusCode)>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="owner_user_id" class="form-label">Owner</label>
                <select id="owner_user_id" name="owner_user_id" class="form-select @error('owner_user_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search owner">
                    <option value="">- None -</option>
                    @foreach ($ownerOptions as $owner)
                        <option value="{{ $owner->id }}" @selected((string) old('owner_user_id', $problem->owner_user_id) === (string) $owner->id)>
                            {{ $owner->name }}
                        </option>
                    @endforeach
                </select>
                @error('owner_user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="ticket_priority_id" class="form-label">Priority</label>
                <select id="ticket_priority_id" name="ticket_priority_id" class="form-select @error('ticket_priority_id') is-invalid @enderror">
                    <option value="">- None -</option>
                    @foreach ($priorityOptions as $priority)
                        <option value="{{ $priority->id }}" @selected((string) old('ticket_priority_id', $problem->ticket_priority_id) === (string) $priority->id)>
                            {{ $priority->name }}
                        </option>
                    @endforeach
                </select>
                @error('ticket_priority_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="target_resolution_at" class="form-label">Target Resolution</label>
                <input type="datetime-local" id="target_resolution_at" name="target_resolution_at"
                    class="form-control @error('target_resolution_at') is-invalid @enderror"
                    value="{{ old('target_resolution_at', optional($problem->target_resolution_at)->format('Y-m-d\\TH:i')) }}">
                @error('target_resolution_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="ticket_ids" class="form-label">Linked Incident/Tickets</label>
                <select id="ticket_ids" name="ticket_ids[]" class="form-select @error('ticket_ids') is-invalid @enderror @error('ticket_ids.*') is-invalid @enderror"
                    data-searchable-select data-force-searchable-select="true" data-search-placeholder="Search ticket" multiple>
                    @foreach ($ticketOptions as $ticket)
                        <option value="{{ $ticket->id }}" @selected(in_array((string) $ticket->id, old('ticket_ids', $selectedTicketIds), true))>
                            {{ $ticket->ticket_number }} - {{ $ticket->title }}
                        </option>
                    @endforeach
                </select>
                @error('ticket_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('ticket_ids.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $problem->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="symptom" class="form-label">Symptom</label>
                <textarea id="symptom" name="symptom" rows="3" class="form-control @error('symptom') is-invalid @enderror">{{ old('symptom', $problem->symptom) }}</textarea>
                @error('symptom')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="root_cause" class="form-label">Root Cause</label>
                <textarea id="root_cause" name="root_cause" rows="3" class="form-control @error('root_cause') is-invalid @enderror">{{ old('root_cause', $problem->root_cause) }}</textarea>
                @error('root_cause')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="workaround" class="form-label">Workaround</label>
                <textarea id="workaround" name="workaround" rows="3" class="form-control @error('workaround') is-invalid @enderror">{{ old('workaround', $problem->workaround) }}</textarea>
                @error('workaround')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="permanent_fix" class="form-label">Permanent Fix</label>
                <textarea id="permanent_fix" name="permanent_fix" rows="3" class="form-control @error('permanent_fix') is-invalid @enderror">{{ old('permanent_fix', $problem->permanent_fix) }}</textarea>
                @error('permanent_fix')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="action_item" class="form-label">Action Item</label>
                <textarea id="action_item" name="action_item" rows="2" class="form-control @error('action_item') is-invalid @enderror">{{ old('action_item', $problem->action_item) }}</textarea>
                @error('action_item')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="resolved_at" class="form-label">Resolved At</label>
                <input type="datetime-local" id="resolved_at" name="resolved_at"
                    class="form-control @error('resolved_at') is-invalid @enderror"
                    value="{{ old('resolved_at', optional($problem->resolved_at)->format('Y-m-d\\TH:i')) }}">
                @error('resolved_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <input type="hidden" name="is_known_error" value="0">
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="is_known_error" name="is_known_error" value="1"
                        @checked((bool) old('is_known_error', $problem->is_known_error))>
                    <label class="form-check-label" for="is_known_error">Known Error</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('problems.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
