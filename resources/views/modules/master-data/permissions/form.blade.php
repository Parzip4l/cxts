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
                <label for="code" class="form-label">Permission Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $permissionRecord->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label">Permission Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $permissionRecord->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="group_name" class="form-label">Group</label>
                <input type="text" id="group_name" name="group_name" class="form-control @error('group_name') is-invalid @enderror"
                    value="{{ old('group_name', $permissionRecord->group_name) }}">
                @error('group_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $permissionRecord->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $permissionRecord->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.permissions.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
