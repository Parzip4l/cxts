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
                    value="{{ old('code', $vendor->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $vendor->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="contact_person_name" class="form-label">Contact Person</label>
                <input type="text" id="contact_person_name" name="contact_person_name"
                    class="form-control @error('contact_person_name') is-invalid @enderror"
                    value="{{ old('contact_person_name', $vendor->contact_person_name) }}">
                @error('contact_person_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="contact_phone" class="form-label">Contact Phone</label>
                <input type="text" id="contact_phone" name="contact_phone"
                    class="form-control @error('contact_phone') is-invalid @enderror"
                    value="{{ old('contact_phone', $vendor->contact_phone) }}">
                @error('contact_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="contact_email" class="form-label">Contact Email</label>
                <input type="email" id="contact_email" name="contact_email"
                    class="form-control @error('contact_email') is-invalid @enderror"
                    value="{{ old('contact_email', $vendor->contact_email) }}">
                @error('contact_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $vendor->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $vendor->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $vendor->is_active ?? true))>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.vendors.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
