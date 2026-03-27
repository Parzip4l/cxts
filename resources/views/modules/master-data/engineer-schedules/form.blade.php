@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle])

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $action }}" class="row g-3">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="col-md-6">
                <label for="user_id" class="form-label">Engineer</label>
                <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search engineer" required>
                    <option value="">- Select Engineer -</option>
                    @foreach ($engineerOptions as $engineer)
                        <option value="{{ $engineer->id }}" @selected((string) old('user_id', $schedule->user_id) === (string) $engineer->id)>
                            {{ $engineer->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="shift_id" class="form-label">Shift</label>
                <select id="shift_id" name="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                    <option value="">- Select Shift -</option>
                    @foreach ($shiftOptions as $shift)
                        <option value="{{ $shift->id }}" @selected((string) old('shift_id', $schedule->shift_id) === (string) $shift->id)>
                            {{ $shift->name }} ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }})
                        </option>
                    @endforeach
                </select>
                @error('shift_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="work_date" class="form-label">Work Date</label>
                <input type="date" id="work_date" name="work_date"
                    class="form-control @error('work_date') is-invalid @enderror"
                    value="{{ old('work_date', optional($schedule->work_date)->format('Y-m-d')) }}" required>
                @error('work_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="assigned" @selected(old('status', $schedule->status ?? 'assigned') === 'assigned')>Assigned</option>
                    <option value="off" @selected(old('status', $schedule->status) === 'off')>Off</option>
                    <option value="leave" @selected(old('status', $schedule->status) === 'leave')>Leave</option>
                    <option value="sick" @selected(old('status', $schedule->status) === 'sick')>Sick</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="assigned_by_id" class="form-label">Assigned By (optional)</label>
                <input type="number" id="assigned_by_id" name="assigned_by_id"
                    class="form-control @error('assigned_by_id') is-invalid @enderror"
                    value="{{ old('assigned_by_id', $schedule->assigned_by_id) }}">
                @error('assigned_by_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $schedule->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.engineer-schedules.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
