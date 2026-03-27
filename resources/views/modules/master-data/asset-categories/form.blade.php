@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle])

@php
    $selectedEngineerSkillIds = collect(old('engineer_skill_ids', $assetCategory->relationLoaded('engineerSkills') ? $assetCategory->engineerSkills->pluck('id')->all() : $assetCategory->engineerSkills()->pluck('engineer_skills.id')->all()))
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

            <div class="col-md-4">
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $assetCategory->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $assetCategory->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="engineer_skill_ids" class="form-label">Related Engineer Skills</label>
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
                <div class="form-text">Skill ini dipakai saat ticket berkaitan dengan asset category ini.</div>
                @error('engineer_skill_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('engineer_skill_ids.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $assetCategory->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $assetCategory->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.asset-categories.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
