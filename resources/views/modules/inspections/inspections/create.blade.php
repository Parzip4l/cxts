@extends('layouts.vertical', ['subtitle' => 'Schedule Inspection Task'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Inspection Operations', 'subtitle' => 'Schedule Inspection Task'])

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('inspections.store') }}" class="row g-3">
            @csrf

            <div class="col-md-4">
                <label for="inspection_template_id" class="form-label">Inspection Template</label>
                <select id="inspection_template_id" name="inspection_template_id"
                    data-searchable-select data-search-placeholder="Search inspection template"
                    class="form-select @error('inspection_template_id') is-invalid @enderror" required>
                    <option value="">- Select -</option>
                    @foreach ($templateOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('inspection_template_id') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('inspection_template_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="asset_id" class="form-label">Related Asset</label>
                <select id="asset_id" name="asset_id" class="form-select @error('asset_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search asset">
                    <option value="">- Optional -</option>
                    @foreach ($assetOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('asset_id') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Pilih aset yang akan diperiksa jika task terkait perangkat tertentu.</div>
                @error('asset_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="asset_location_id" class="form-label">Asset Location</label>
                <select id="asset_location_id" name="asset_location_id"
                    data-searchable-select data-search-placeholder="Search asset location"
                    class="form-select @error('asset_location_id') is-invalid @enderror">
                    <option value="">- Optional -</option>
                    @foreach ($locationOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('asset_location_id') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Pilih site atau area jika inspeksi lebih relevan ke lokasi daripada aset spesifik.</div>
                @error('asset_location_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="inspection_officer_id" class="form-label">Assigned Inspector</label>
                <select id="inspection_officer_id" name="inspection_officer_id"
                    data-searchable-select data-search-placeholder="Search inspector"
                    class="form-select @error('inspection_officer_id') is-invalid @enderror" required>
                    <option value="">- Select -</option>
                    @foreach ($officerOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('inspection_officer_id') === (string) $option->id)>
                            {{ $option->name }} ({{ strtoupper($option->role) }})
                        </option>
                    @endforeach
                </select>
                @error('inspection_officer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="inspection_date" class="form-label">Inspection Date</label>
                <input type="date" id="inspection_date" name="inspection_date"
                    class="form-control @error('inspection_date') is-invalid @enderror"
                    value="{{ old('inspection_date', now()->format('Y-m-d')) }}" required>
                @error('inspection_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="schedule_type" class="form-label">Schedule Type</label>
                @php $selectedScheduleType = old('schedule_type', \App\Models\Inspection::SCHEDULE_TYPE_NONE); @endphp
                <select id="schedule_type" name="schedule_type" class="form-select @error('schedule_type') is-invalid @enderror">
                    @foreach ($scheduleTypeOptions as $option)
                        <option value="{{ $option }}" @selected($selectedScheduleType === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                @error('schedule_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="schedule_interval" class="form-label">Schedule Interval</label>
                <input type="number" min="1" max="30" id="schedule_interval" name="schedule_interval"
                    class="form-control @error('schedule_interval') is-invalid @enderror"
                    value="{{ old('schedule_interval', 1) }}">
                <div class="form-text">Daily: every N days, Weekly: every N weeks.</div>
                @error('schedule_interval')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label class="form-label d-block">Weekly Days (used when Schedule Type = Weekly)</label>
                @php
                    $selectedWeekdays = collect(old('schedule_weekdays', []))->map(fn ($day) => (int) $day)->all();
                    $weekdayLabels = [
                        1 => 'Mon',
                        2 => 'Tue',
                        3 => 'Wed',
                        4 => 'Thu',
                        5 => 'Fri',
                        6 => 'Sat',
                        7 => 'Sun',
                    ];
                @endphp
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($weekdayLabels as $weekdayValue => $weekdayLabel)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="schedule_weekdays_{{ $weekdayValue }}"
                                name="schedule_weekdays[]" value="{{ $weekdayValue }}" @checked(in_array($weekdayValue, $selectedWeekdays, true))>
                            <label class="form-check-label" for="schedule_weekdays_{{ $weekdayValue }}">{{ $weekdayLabel }}</label>
                        </div>
                    @endforeach
                </div>
                @error('schedule_weekdays')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('schedule_weekdays.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="summary_notes" class="form-label">Initial Notes</label>
                <textarea id="summary_notes" name="summary_notes" rows="3"
                    class="form-control @error('summary_notes') is-invalid @enderror">{{ old('summary_notes') }}</textarea>
                @error('summary_notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Schedule Task</button>
                <a href="{{ route('inspections.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
