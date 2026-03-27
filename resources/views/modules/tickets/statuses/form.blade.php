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

            <div class="col-md-4">
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $ticketStatus->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $ticketStatus->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_open" value="0">
                <input type="hidden" name="is_in_progress" value="0">
                <input type="hidden" name="is_closed" value="0">
                <input type="hidden" name="is_active" value="0">

                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_open" name="is_open" value="1"
                            @checked((bool) old('is_open', $ticketStatus->is_open ?? true))>
                        <label class="form-check-label" for="is_open">Is Open</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_in_progress" name="is_in_progress" value="1"
                            @checked((bool) old('is_in_progress', $ticketStatus->is_in_progress ?? false))>
                        <label class="form-check-label" for="is_in_progress">Is In Progress</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_closed" name="is_closed" value="1"
                            @checked((bool) old('is_closed', $ticketStatus->is_closed ?? false))>
                        <label class="form-check-label" for="is_closed">Is Closed</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            @checked((bool) old('is_active', $ticketStatus->is_active ?? true))>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.ticket-statuses.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
