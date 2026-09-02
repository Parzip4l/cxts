@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Monitoring Events', 'subtitle' => $pageTitle])

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Source</label>
                    <input type="text" name="source" class="form-control" value="{{ old('source', $event->source) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select" required>
                        @foreach ($severityOptions as $severityCode => $severityLabel)
                            <option value="{{ $severityCode }}" @selected(old('severity', $event->severity) === $severityCode)>
                                {{ $severityLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Occurred At</label>
                    <input type="datetime-local" name="occurred_at" class="form-control"
                        value="{{ old('occurred_at', optional($event->occurred_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Service</label>
                    <select name="service_id" class="form-select" data-searchable-select data-search-placeholder="Search service">
                        <option value="">No service</option>
                        @foreach ($serviceOptions as $service)
                            <option value="{{ $service->id }}" @selected((string) old('service_id', $event->service_id) === (string) $service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Asset</label>
                    <select name="asset_id" class="form-select" data-searchable-select data-search-placeholder="Search asset">
                        <option value="">No asset</option>
                        @foreach ($assetOptions as $asset)
                            <option value="{{ $asset->id }}" @selected((string) old('asset_id', $event->asset_id) === (string) $asset->id)>
                                {{ $asset->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Message</label>
                    <input type="text" name="message" class="form-control" value="{{ old('message', $event->message) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Details</label>
                    <textarea name="details" rows="5" class="form-control">{{ old('details', $event->details) }}</textarea>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Deduplication Key</label>
                    <input type="text" name="deduplication_key" class="form-control" value="{{ old('deduplication_key', $event->deduplication_key) }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="hidden" name="auto_create_incident" value="0">
                        <input type="checkbox" name="auto_create_incident" value="1" class="form-check-input" id="auto-create-incident"
                            @checked((bool) old('auto_create_incident', false))>
                        <label class="form-check-label" for="auto-create-incident">Auto-create incident for high/critical</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('monitoring-events.index') }}" class="btn btn-outline-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Event</button>
        </div>
    </div>
</form>
@endsection
