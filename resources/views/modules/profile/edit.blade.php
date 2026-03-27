@extends('layouts.vertical', ['subtitle' => 'My Profile'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Account', 'subtitle' => 'My Profile'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="{{ $userRecord->roleRef?->name ?? $userRecord->role }}" disabled>
            </div>

            <div class="col-md-6">
                <label for="department_id" class="form-label">Department</label>
                <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                    <option value="">- None -</option>
                    @foreach ($departmentOptions as $departmentOption)
                        <option value="{{ $departmentOption->id }}" @selected((string) old('department_id', $userRecord->department_id) === (string) $departmentOption->id)>
                            {{ $departmentOption->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $userRecord->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $userRecord->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">New Password <small class="text-muted">(Optional)</small></label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror">
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="current_password" class="form-label">Current Password <small class="text-muted">(Required if changing password)</small></label>
                <input type="password" id="current_password" name="current_password"
                    class="form-control @error('current_password') is-invalid @enderror">
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

