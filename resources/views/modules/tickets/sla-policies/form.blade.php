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
                <label for="name" class="form-label">Policy Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $slaPolicy->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="response_time_minutes" class="form-label">Response (min)</label>
                <input type="number" min="1" id="response_time_minutes" name="response_time_minutes"
                    class="form-control @error('response_time_minutes') is-invalid @enderror"
                    value="{{ old('response_time_minutes', $slaPolicy->response_time_minutes) }}">
                @error('response_time_minutes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="resolution_time_minutes" class="form-label">Resolution (min)</label>
                <input type="number" min="1" id="resolution_time_minutes" name="resolution_time_minutes"
                    class="form-control @error('resolution_time_minutes') is-invalid @enderror"
                    value="{{ old('resolution_time_minutes', $slaPolicy->resolution_time_minutes) }}">
                @error('resolution_time_minutes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="working_hours_id" class="form-label">Working Hours ID</label>
                <input type="number" min="1" id="working_hours_id" name="working_hours_id"
                    class="form-control @error('working_hours_id') is-invalid @enderror"
                    value="{{ old('working_hours_id', $slaPolicy->working_hours_id) }}">
                @error('working_hours_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-9">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $slaPolicy->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $slaPolicy->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.sla-policies.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
