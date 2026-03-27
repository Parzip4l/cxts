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
                    value="{{ old('code', $department->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $department->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="parent_department_id" class="form-label">Parent Department</label>
                <select id="parent_department_id" name="parent_department_id"
                    data-searchable-select data-search-placeholder="Search parent department"
                    class="form-select @error('parent_department_id') is-invalid @enderror">
                    <option value="">- None -</option>
                    @foreach ($parentOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('parent_department_id', $department->parent_department_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_department_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="head_user_id" class="form-label">Head / PIC</label>
                <select id="head_user_id" name="head_user_id" class="form-select @error('head_user_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search head or PIC">
                    <option value="">- None -</option>
                    @foreach ($headOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('head_user_id', $department->head_user_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('head_user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $department->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $department->is_active ?? true))>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.departments.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
