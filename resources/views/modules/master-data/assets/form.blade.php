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

            <div class="col-md-3">
                <label for="code" class="form-label">Asset Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $asset->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-9">
                <label for="name" class="form-label">Asset Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $asset->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="asset_category_id" class="form-label">Category</label>
                <select id="asset_category_id" name="asset_category_id"
                    class="form-select @error('asset_category_id') is-invalid @enderror" required>
                    <option value="">- Select -</option>
                    @foreach ($categoryOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('asset_category_id', $asset->asset_category_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('asset_category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="asset_status_id" class="form-label">Status</label>
                <select id="asset_status_id" name="asset_status_id" class="form-select @error('asset_status_id') is-invalid @enderror">
                    <option value="">- None -</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('asset_status_id', $asset->asset_status_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('asset_status_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="criticality" class="form-label">Criticality</label>
                <select id="criticality" name="criticality" class="form-select @error('criticality') is-invalid @enderror" required>
                    @foreach ($criticalityOptions as $criticalityOption)
                        <option value="{{ $criticalityOption }}" @selected(old('criticality', $asset->criticality ?: 'medium') === $criticalityOption)>
                            {{ ucfirst($criticalityOption) }}
                        </option>
                    @endforeach
                </select>
                @error('criticality')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="serial_number" class="form-label">Serial Number</label>
                <input type="text" id="serial_number" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror"
                    value="{{ old('serial_number', $asset->serial_number) }}">
                @error('serial_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="service_id" class="form-label">Service</label>
                <select id="service_id" name="service_id" class="form-select @error('service_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search service">
                    <option value="">- None -</option>
                    @foreach ($serviceOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('service_id', $asset->service_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="department_owner_id" class="form-label">Owner Department</label>
                <select id="department_owner_id" name="department_owner_id" class="form-select @error('department_owner_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search department">
                    <option value="">- None -</option>
                    @foreach ($departmentOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('department_owner_id', $asset->department_owner_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_owner_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="vendor_id" class="form-label">Vendor</label>
                <select id="vendor_id" name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search vendor">
                    <option value="">- None -</option>
                    @foreach ($vendorOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('vendor_id', $asset->vendor_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('vendor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="asset_location_id" class="form-label">Location</label>
                <select id="asset_location_id" name="asset_location_id" class="form-select @error('asset_location_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search asset location">
                    <option value="">- None -</option>
                    @foreach ($locationOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('asset_location_id', $asset->asset_location_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('asset_location_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="brand" class="form-label">Brand</label>
                <input type="text" id="brand" name="brand" class="form-control @error('brand') is-invalid @enderror"
                    value="{{ old('brand', $asset->brand) }}">
                @error('brand')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="model" class="form-label">Model</label>
                <input type="text" id="model" name="model" class="form-control @error('model') is-invalid @enderror"
                    value="{{ old('model', $asset->model) }}">
                @error('model')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="install_date" class="form-label">Install Date</label>
                <input type="date" id="install_date" name="install_date" class="form-control @error('install_date') is-invalid @enderror"
                    value="{{ old('install_date', optional($asset->install_date)->format('Y-m-d')) }}">
                @error('install_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="warranty_end_date" class="form-label">Warranty End</label>
                <input type="date" id="warranty_end_date" name="warranty_end_date" class="form-control @error('warranty_end_date') is-invalid @enderror"
                    value="{{ old('warranty_end_date', optional($asset->warranty_end_date)->format('Y-m-d')) }}">
                @error('warranty_end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $asset->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $asset->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.assets.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
