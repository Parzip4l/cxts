@extends('layouts.vertical', ['subtitle' => 'Edit Ticket'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Edit ' . $ticket->ticket_number])

@php
    $activeAssignedEngineers = $ticket->assignedEngineers->isNotEmpty()
        ? $ticket->assignedEngineers
        : collect([$ticket->assignedEngineer])->filter();
    $selectedAssignedEngineerIds = collect(old('assigned_engineer_ids', $activeAssignedEngineers->pluck('id')->all()))
        ->map(fn ($id) => (string) $id)
        ->all();
    $latestAssignmentNotes = old('assignment_notes', $ticket->assignments->first()?->notes);
    $groupedEngineerOptions = $engineerOptions->groupBy(
        fn ($engineer) => $engineer->department?->name ?? 'No Engineer Team'
    );
    $engineerTeamNameOptions = $groupedEngineerOptions->keys()->filter()->values();
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small text-muted fw-semibold mb-1">Ticket Adjustment</div>
                <h4 class="mb-1">Edit ticket tanpa cancel</h4>
                <p class="text-muted mb-0">Ubah nama ticket, deskripsi, dan assignment agar score engineer tidak terdampak oleh cancel yang tidak perlu.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-light">Back to Detail</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-lg-7">
                <div class="border rounded-3 p-4 h-100 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Ticket Core</div>
                            <h5 class="mb-1">Summary & Description</h5>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">{{ $ticket->ticket_number }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Nama Ticket</label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $ticket->title) }}"
                                maxlength="200"
                                required
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="8"
                                class="form-control @error('description') is-invalid @enderror"
                                required
                            >{{ old('description', $ticket->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border rounded-3 p-4 h-100 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Assignment</div>
                            <h5 class="mb-1">Engineer & Team</h5>
                        </div>
                        <span class="badge bg-info-subtle text-info">{{ $activeAssignedEngineers->count() }} assigned</span>
                    </div>

                    @if ($canManageAssignment)
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="assigned_engineer_ids" class="form-label">Engineer</label>
                                <select
                                    id="assigned_engineer_ids"
                                    name="assigned_engineer_ids[]"
                                    class="form-select @error('assigned_engineer_ids') is-invalid @enderror @error('assigned_engineer_ids.*') is-invalid @enderror"
                                    data-searchable-select
                                    data-force-searchable-select="true"
                                    data-search-placeholder="Search engineer"
                                    multiple
                                >
                                    @foreach ($groupedEngineerOptions as $picTeamLabel => $groupedOptions)
                                        <optgroup label="{{ $picTeamLabel }}">
                                            @foreach ($groupedOptions as $option)
                                                <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedAssignedEngineerIds, true))>
                                                    {{ $option->name }}
                                                    @if ($option->department)
                                                        - {{ $option->department->name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih ulang engineer aktif dengan grouping Engineer Team agar assignment lebih stabil daripada mengikuti shift.</div>
                                @error('assigned_engineer_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('assigned_engineer_ids.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="assigned_team_name" class="form-label">Team Assigned</label>
                                <input
                                    type="text"
                                    id="assigned_team_name"
                                    name="assigned_team_name"
                                    class="form-control @error('assigned_team_name') is-invalid @enderror"
                                    value="{{ old('assigned_team_name', $ticket->assigned_team_name) }}"
                                    placeholder="Ops / Field Team"
                                    list="ticket-edit-engineer-team-options"
                                >
                                <datalist id="ticket-edit-engineer-team-options">
                                    @foreach ($engineerTeamNameOptions as $teamName)
                                        <option value="{{ $teamName }}"></option>
                                    @endforeach
                                </datalist>
                                @error('assigned_team_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="assignment_notes" class="form-label">Assignment Notes</label>
                                <textarea
                                    id="assignment_notes"
                                    name="assignment_notes"
                                    rows="4"
                                    class="form-control @error('assignment_notes') is-invalid @enderror"
                                    placeholder="Catatan perubahan assignment"
                                >{{ $latestAssignmentNotes }}</textarea>
                                @error('assignment_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            Anda bisa mengubah nama dan deskripsi ticket, tetapi perubahan assignment hanya tersedia untuk user yang memiliki otorisasi dispatch engineer.
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="small text-muted">Perubahan disimpan pada ticket yang sama, jadi histori dan score engineer tetap konsisten.</div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
