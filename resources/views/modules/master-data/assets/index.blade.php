@extends('layouts.vertical', ['subtitle' => 'Assets'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Assets'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="location_view" value="{{ $selectedLocationViewId }}">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search asset"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="asset_category_id" class="form-select">
                    <option value="">All categories</option>
                    @foreach ($categoryOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['asset_category_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="asset_status_id" class="form-select">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['asset_status_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="criticality" class="form-select">
                    <option value="">All criticality</option>
                    @foreach ($criticalityOptions as $criticalityOption)
                        <option value="{{ $criticalityOption }}" @selected(($filters['criticality'] ?? null) === $criticalityOption)>
                            {{ ucfirst($criticalityOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(($filters['is_active'] ?? null) === true)>Active</option>
                    <option value="0" @selected(($filters['is_active'] ?? null) === false)>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('master-data.assets.index') }}" class="btn btn-outline-light">Reset</a>
            </div>
            <div class="col-12 text-end">
                <a href="{{ route('master-data.assets.create') }}" class="btn btn-primary">Add Asset</a>
            </div>
        </form>

        @if ($locationViews->isNotEmpty())
            @php
                $baseQuery = request()->except(['location_view', 'page']);
                $accordionId = 'asset-location-accordion';
            @endphp
            <div class="mb-3">
                <label class="form-label mb-2">Three View by Location</label>
                <div class="accordion" id="{{ $accordionId }}">
                    @foreach ($locationViews as $locationView)
                        @php
                            $isSelectedLocationView = (int) $selectedLocationViewId === (int) $locationView->id;
                            $locationViewUrl = route(
                                'master-data.assets.index',
                                array_merge($baseQuery, ['location_view' => $locationView->id]),
                            );
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="asset-location-heading-{{ $locationView->id }}">
                                <a class="accordion-button @if (!$isSelectedLocationView) collapsed @endif"
                                    href="{{ $locationViewUrl }}"
                                    aria-expanded="{{ $isSelectedLocationView ? 'true' : 'false' }}"
                                    aria-controls="asset-location-collapse-{{ $locationView->id }}">
                                    <span>{{ $locationView->name }}</span>
                                    <span class="ms-2 badge bg-light text-dark">
                                        {{ (int) ($locationViewCounts[$locationView->id] ?? 0) }}
                                    </span>
                                </a>
                            </h2>
                            <div id="asset-location-collapse-{{ $locationView->id }}"
                                class="accordion-collapse collapse @if ($isSelectedLocationView) show @endif"
                                aria-labelledby="asset-location-heading-{{ $locationView->id }}"
                                data-bs-parent="#{{ $accordionId }}">
                                <div class="accordion-body">
                                    @if ($isSelectedLocationView)
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Name</th>
                                                        <th>Category</th>
                                                        <th>Service</th>
                                                        <th>Location</th>
                                                        <th>Status</th>
                                                        <th>Criticality</th>
                                                        <th class="text-end">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($assets as $asset)
                                                        <tr>
                                                            <td>{{ $asset->code }}</td>
                                                            <td>{{ $asset->name }}</td>
                                                            <td>{{ $asset->category?->name ?? '-' }}</td>
                                                            <td>{{ $asset->service?->name ?? '-' }}</td>
                                                            <td>{{ $asset->location?->name ?? '-' }}</td>
                                                            <td>{{ $asset->status?->name ?? '-' }}</td>
                                                            <td><span class="badge bg-info-subtle text-info">{{ ucfirst($asset->criticality) }}</span></td>
                                                            <td class="text-end">
                                                                <a href="{{ route('master-data.assets.edit', $asset) }}"
                                                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                                                <form method="POST"
                                                                    action="{{ route('master-data.assets.destroy', $asset) }}"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Delete this asset?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted py-4">No assets found.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3">
                                            {{ $assets->appends(['location_view' => $locationView->id])->links() }}
                                        </div>
                                    @else
                                        <small class="text-muted d-block">
                                            Klik lokasi ini untuk menampilkan data asset di dalam panel accordion.
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted d-block mt-2">View dibagi menjadi 3 lokasi aktif teratas sesuai kebutuhan operasional.</small>
            </div>
        @endif
    </div>
</div>
@endsection
