@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle])

@php
    $selectedRole = old('role', $userRecord->role ?: request('role'));
    $selectedEngineerSkillIds = collect(old('engineer_skill_ids', $userRecord->relationLoaded('engineerSkills') ? $userRecord->engineerSkills->pluck('id')->all() : $userRecord->engineerSkills()->pluck('engineer_skills.id')->all()))
        ->map(fn ($value) => (string) $value)
        ->all();
@endphp

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $action }}" class="row g-3">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

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
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">- Select Role -</option>
                    @foreach ($roleOptions as $roleOption)
                        <option value="{{ $roleOption->code }}" @selected((string) $selectedRole === (string) $roleOption->code)>
                            {{ $roleOption->name }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="department_id" class="form-label">Department</label>
                <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search department">
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

            <div class="col-12 @if ($selectedRole !== 'engineer') d-none @endif" data-engineer-skill-panel>
                <label for="engineer_skill_ids" class="form-label">Engineer Skills</label>
                <select id="engineer_skill_ids" name="engineer_skill_ids[]"
                    class="form-select @error('engineer_skill_ids') is-invalid @enderror @error('engineer_skill_ids.*') is-invalid @enderror"
                    data-searchable-select data-force-searchable-select="true"
                    data-search-placeholder="Search engineer skill" multiple>
                    @foreach ($engineerSkillOptions as $skillOption)
                        <option value="{{ $skillOption->id }}" @selected(in_array((string) $skillOption->id, $selectedEngineerSkillIds, true))>
                            {{ $skillOption->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Pilih keahlian inti engineer agar assignment ticket bisa lebih terarah.</div>
                @error('engineer_skill_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('engineer_skill_ids.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">
                    Password
                    @if ($method !== 'POST')
                        <small class="text-muted">(Leave blank to keep current password)</small>
                    @endif
                </label>
                <input type="password" id="password" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    @if ($method === 'POST') required @endif>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    @if ($method === 'POST') required @endif>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.users.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const engineerSkillPanel = document.querySelector('[data-engineer-skill-panel]');
        const engineerSkillSelect = document.getElementById('engineer_skill_ids');

        const toggleEngineerSkills = () => {
            if (!roleSelect || !engineerSkillPanel) {
                return;
            }

            const isEngineer = roleSelect.value === 'engineer';
            engineerSkillPanel.classList.toggle('d-none', !isEngineer);

            if (!isEngineer && engineerSkillSelect) {
                Array.from(engineerSkillSelect.options).forEach((option) => {
                    option.selected = false;
                });

                if (engineerSkillSelect._choices) {
                    engineerSkillSelect._choices.removeActiveItems();
                }
            }
        };

        roleSelect?.addEventListener('change', toggleEngineerSkills);
        toggleEngineerSkills();
    });
</script>
@endpush
