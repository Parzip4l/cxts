@extends('layouts.vertical', ['subtitle' => 'My Profile'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Account', 'subtitle' => 'My Profile'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="row g-3" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="col-12">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if ($userRecord->profilePhotoUrl())
                        <img
                            src="{{ $userRecord->profilePhotoUrl() }}"
                            alt="{{ $userRecord->name }}"
                            class="rounded-circle border object-fit-cover"
                            style="width: 72px; height: 72px;">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-4"
                            style="width: 72px; height: 72px;">
                            {{ collect(explode(' ', trim($userRecord->name ?: 'NA')))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'NA' }}
                        </div>
                    @endif
                    <div>
                        <h6 class="mb-1">Profile Photo</h6>
                        <div class="text-muted small">Upload JPG, PNG, or WEBP up to 3 MB.</div>
                    </div>
                </div>
            </div>

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
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                    value="{{ old('phone_number', $userRecord->phone_number) }}" placeholder="e.g. 081234567890">
                @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="profile_photo" class="form-label">Custom Profile Photo</label>
                <input type="file" id="profile_photo" name="profile_photo"
                    class="form-control @error('profile_photo') is-invalid @enderror"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                @error('profile_photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if ($userRecord->profile_photo_path)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="remove_profile_photo" name="remove_profile_photo">
                        <label class="form-check-label" for="remove_profile_photo">Remove current profile photo</label>
                    </div>
                @endif
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
