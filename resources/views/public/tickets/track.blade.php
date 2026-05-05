@extends('layouts.base', ['subtitle' => 'Track Ticket'])

@section('content')
@php
    $statusCode = strtoupper((string) ($ticket?->status?->code ?? ''));
    $statusBadgeClass = match ($statusCode) {
        'CLOSED', 'COMPLETED' => 'success',
        'PENDING_CUSTOMER', 'ON_HOLD' => 'warning',
        'CANCELLED', 'REJECTED' => 'danger',
        'IN_PROGRESS', 'ASSIGNED' => 'primary',
        default => 'secondary',
    };

    $activityLabels = [
        'ticket_created' => 'Ticket dibuat',
        'ticket_approved' => 'Ticket disetujui',
        'ticket_rejected' => 'Ticket ditolak',
        'ticket_ready_for_assignment' => 'Siap ditugaskan',
        'ticket_assigned' => 'Ditugaskan ke tim teknis',
        'work_started' => 'Pekerjaan dimulai',
        'work_paused' => 'Pekerjaan dijeda',
        'work_resumed' => 'Pekerjaan dilanjutkan',
        'ticket_pending_customer' => 'Menunggu konfirmasi pelapor',
        'work_completed' => 'Pekerjaan selesai',
        'ticket_closed' => 'Ticket ditutup',
        'ticket_reopened' => 'Ticket dibuka kembali',
        'ticket_cancelled' => 'Ticket dibatalkan',
    ];

    $contextItems = collect([
        'Service' => $ticket?->service?->name,
        'Asset' => $ticket?->asset?->name,
        'Location' => $ticket?->assetLocation?->name,
    ])->filter();

    $milestones = collect([
        ['label' => 'Ticket dibuat', 'value' => $ticket?->created_at],
        ['label' => 'Target respons', 'value' => $ticket?->response_due_at],
        ['label' => 'Direspons', 'value' => $ticket?->responded_at],
        ['label' => 'Mulai dikerjakan', 'value' => $ticket?->started_at],
        ['label' => 'Selesai pekerjaan', 'value' => $ticket?->completed_at],
        ['label' => 'Ditutup', 'value' => $ticket?->closed_at],
    ])->filter(fn ($item) => filled($item['value']));
@endphp

<style>
    .public-track-hero {
        background: linear-gradient(135deg, #111827 0%, #1f2937 48%, #0f766e 100%);
        border-radius: 8px;
    }

    .public-track-timeline {
        position: relative;
    }

    .public-track-timeline::before {
        background: #d9dee8;
        bottom: 10px;
        content: "";
        left: 10px;
        position: absolute;
        top: 10px;
        width: 2px;
    }

    .public-track-dot {
        background: #ffffff;
        border: 3px solid #0d6efd;
        border-radius: 999px;
        flex: 0 0 22px;
        height: 22px;
        position: relative;
        width: 22px;
        z-index: 1;
    }
</style>

<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-lg-5">
                        <div class="public-track-hero text-white p-4 p-lg-5 mb-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <div class="text-uppercase small fw-semibold opacity-75 mb-2">CXTS Public Portal</div>
                                    <h4 class="fw-bold mb-2">Track Ticket</h4>
                                    <p class="mb-0 opacity-75">Gunakan nomor ticket dan email pelapor untuk melihat status terbaru tanpa login.</p>
                                </div>
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    <a href="{{ route('public.tickets.create') }}" class="btn btn-light">Submit Ticket</a>
                                    <a href="{{ route('login') }}" class="btn btn-outline-light">Staff Login</a>
                                </div>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="border rounded p-3 p-lg-4 bg-light-subtle h-100">
                                    <h5 class="mb-2">Cari Status Ticket</h5>
                                    <p class="text-muted mb-4">Masukkan kombinasi yang sama dengan data saat ticket dibuat.</p>

                                    <form method="POST" action="{{ route('public.tickets.lookup') }}" class="row g-3">
                                        @csrf
                                        <div class="col-12">
                                            <label for="ticket_number" class="form-label">Nomor Ticket</label>
                                            <input
                                                type="text"
                                                id="ticket_number"
                                                name="ticket_number"
                                                class="form-control @error('ticket_number') is-invalid @enderror"
                                                value="{{ old('ticket_number', $ticketNumber) }}"
                                                placeholder="Contoh: TCK-20260504-0001"
                                                required
                                            >
                                            @error('ticket_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label for="requester_email" class="form-label">Email Pelapor</label>
                                            <input
                                                type="email"
                                                id="requester_email"
                                                name="requester_email"
                                                class="form-control @error('requester_email') is-invalid @enderror"
                                                value="{{ old('requester_email', $requesterEmail) }}"
                                                placeholder="nama@perusahaan.com"
                                                required
                                            >
                                            @error('requester_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-dark w-100">Check Status</button>
                                        </div>
                                    </form>

                                    <div class="alert alert-info border mt-4 mb-0">
                                        Nomor ticket juga dikirim melalui email jika konfigurasi mail server aktif.
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-7">
                                @if ($ticket)
                                    <div class="border rounded p-3 p-lg-4 mb-4">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                                            <div>
                                                <div class="text-muted small mb-1">Nomor Ticket</div>
                                                <h5 class="fw-bold mb-1">{{ $ticket->ticket_number }}</h5>
                                                <div class="text-muted">{{ $ticket->title }}</div>
                                            </div>
                                            <div>
                                                <span class="badge bg-{{ $statusBadgeClass }} px-3 py-2">{{ $ticket->status?->name ?? 'Unknown' }}</span>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Pelapor</div>
                                                    <div class="fw-semibold">{{ $ticket->requester?->name ?? '-' }}</div>
                                                    <div class="small text-muted">{{ $ticket->requesterDepartment?->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Kategori</div>
                                                    <div class="fw-semibold">{{ $ticket->category?->name ?? '-' }}</div>
                                                    <div class="small text-muted">{{ $ticket->subcategory?->name ?? 'Belum ada kategori detail' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Prioritas</div>
                                                    <div class="fw-semibold">{{ $ticket->priority?->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Penanganan</div>
                                                    <div class="fw-semibold">{{ $ticket->assigned_engineer_id ? 'Tim teknis sudah ditugaskan' : 'Menunggu penugasan tim teknis' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($contextItems->isNotEmpty())
                                            <div class="border rounded p-3 mt-3">
                                                <div class="small text-muted mb-2">Konteks Terdampak</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($contextItems as $label => $value)
                                                        <span class="badge bg-light text-dark border">{{ $label }}: {{ $value }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($milestones->isNotEmpty())
                                        <div class="border rounded p-3 p-lg-4 mb-4">
                                            <h5 class="mb-3">Ringkasan Waktu</h5>
                                            <div class="row g-3">
                                                @foreach ($milestones as $milestone)
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="small text-muted mb-1">{{ $milestone['label'] }}</div>
                                                            <div class="fw-semibold">{{ $milestone['value']->format('d M Y H:i') }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="border rounded p-3 p-lg-4">
                                        <h5 class="mb-3">Timeline Status</h5>
                                        @if ($ticket->activities->isNotEmpty())
                                            <div class="public-track-timeline">
                                                @foreach ($ticket->activities as $activity)
                                                    <div class="d-flex gap-3 mb-3">
                                                        <div class="public-track-dot mt-1"></div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $activityLabels[$activity->activity_type] ?? 'Update ticket' }}</div>
                                                            <div class="small text-muted">
                                                                {{ $activity->created_at->format('d M Y H:i') }}
                                                                @if ($activity->newStatus)
                                                                    · Status: {{ $activity->newStatus->name }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-light border mb-0">Timeline status belum tersedia.</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="border rounded p-4 p-lg-5 text-center h-100 d-flex flex-column justify-content-center">
                                        <div class="mx-auto mb-3 rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                            <iconify-icon icon="solar:ticket-sale-outline" width="34" height="34"></iconify-icon>
                                        </div>
                                        <h5 class="fw-bold mb-2">Status ticket akan muncul di sini</h5>
                                        <p class="text-muted mb-0">Setelah nomor ticket dan email cocok, CXTS akan menampilkan status, ringkasan waktu, dan timeline progres yang aman untuk pelapor.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
