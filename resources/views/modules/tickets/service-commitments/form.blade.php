@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Service Commitments', 'subtitle' => $pageTitle])

<form method="POST" action="{{ $action }}" id="service-commitment-form">
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
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $commitment->name) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="commitment_type" class="form-select" id="commitment-type" required>
                        @foreach ($typeOptions as $typeCode => $typeLabel)
                            <option value="{{ $typeCode }}" @selected(old('commitment_type', $commitment->commitment_type) === $typeCode)>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach ($statusOptions as $statusCode => $statusLabel)
                            <option value="{{ $statusCode }}" @selected(old('status', $commitment->status) === $statusCode)>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Service</label>
                    <select name="service_id" class="form-select" data-searchable-select data-search-placeholder="Search service">
                        <option value="">No service</option>
                        @foreach ($serviceOptions as $service)
                            <option value="{{ $service->id }}" @selected((string) old('service_id', $commitment->service_id) === (string) $service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 commitment-provider commitment-provider-ola">
                    <label class="form-label">Provider Department</label>
                    <select name="provider_department_id" class="form-select" data-searchable-select data-search-placeholder="Search department">
                        <option value="">No department</option>
                        @foreach ($departmentOptions as $department)
                            <option value="{{ $department->id }}" @selected((string) old('provider_department_id', $commitment->provider_department_id) === (string) $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 commitment-provider commitment-provider-uc">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select" data-searchable-select data-search-placeholder="Search vendor">
                        <option value="">No vendor</option>
                        @foreach ($vendorOptions as $vendor)
                            <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $commitment->vendor_id) === (string) $vendor->id)>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Escalation Contact</label>
                    <input type="text" name="escalation_contact" class="form-control"
                        value="{{ old('escalation_contact', $commitment->escalation_contact) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Response Target (min)</label>
                    <input type="number" min="1" name="response_target_minutes" class="form-control"
                        value="{{ old('response_target_minutes', $commitment->response_target_minutes) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Resolution Target (min)</label>
                    <input type="number" min="1" name="resolution_target_minutes" class="form-control"
                        value="{{ old('resolution_target_minutes', $commitment->resolution_target_minutes) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Availability Target (%)</label>
                    <input type="number" min="0" max="100" step="0.01" name="availability_target_percent" class="form-control"
                        value="{{ old('availability_target_percent', $commitment->availability_target_percent) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Review Frequency</label>
                    <input type="text" name="review_frequency" class="form-control"
                        value="{{ old('review_frequency', $commitment->review_frequency) }}" placeholder="Monthly">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Effective From</label>
                    <input type="date" name="effective_from" class="form-control"
                        value="{{ old('effective_from', optional($commitment->effective_from)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Effective Until</label>
                    <input type="date" name="effective_until" class="form-control"
                        value="{{ old('effective_until', optional($commitment->effective_until)->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="4" class="form-control">{{ old('notes', $commitment->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('service-commitments.index') }}" class="btn btn-outline-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Commitment</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('commitment-type');
            const olaFields = document.querySelectorAll('.commitment-provider-ola');
            const ucFields = document.querySelectorAll('.commitment-provider-uc');

            const syncProviderFields = function () {
                const isUc = typeSelect.value === 'underpinning_contract';
                olaFields.forEach((field) => field.classList.toggle('d-none', isUc));
                ucFields.forEach((field) => field.classList.toggle('d-none', !isUc));
            };

            typeSelect.addEventListener('change', syncProviderFields);
            syncProviderFields();
        });
    </script>
@endpush
