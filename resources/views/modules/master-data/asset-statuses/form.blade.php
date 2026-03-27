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
                    value="{{ old('code', $assetStatus->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $assetStatus->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $assetStatus->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <input type="hidden" name="is_operational" value="0">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="is_operational" name="is_operational" value="1"
                        @checked((bool) old('is_operational', $assetStatus->is_operational ?? true))>
                    <label class="form-check-label" for="is_operational">Operational</label>
                </div>
            </div>

            <div class="col-md-6">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $assetStatus->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.asset-statuses.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
