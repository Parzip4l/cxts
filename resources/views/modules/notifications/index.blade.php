@extends('layouts.vertical', ['subtitle' => 'Notifications'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Account', 'subtitle' => 'Notifications'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h5 class="mb-1">Recent Notifications</h5>
                <p class="text-muted mb-0 small">Update operasional terbaru yang relevan dengan akun Anda.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $unreadCount }} unread</span>
                <span class="badge bg-light text-dark border">{{ $notifications->count() }} items</span>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Mark All Read</button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-md-4">
                <div class="rounded-3 border bg-light-subtle p-3 h-100">
                    <div class="text-muted small mb-1">Approval & Escalation</div>
                    <div class="fs-4 fw-semibold">{{ $notifications->where('type', 'approval')->count() + $notifications->where('badge_class', 'danger')->count() }}</div>
                    <div class="small text-muted">Item yang butuh keputusan cepat.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 border bg-light-subtle p-3 h-100">
                    <div class="text-muted small mb-1">Unread</div>
                    <div class="fs-4 fw-semibold">{{ $notifications->where('is_read', false)->count() }}</div>
                    <div class="small text-muted">Update yang belum dibuka atau ditandai read.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 border bg-light-subtle p-3 h-100">
                    <div class="text-muted small mb-1">Acknowledged</div>
                    <div class="fs-4 fw-semibold">{{ $notifications->where('is_acknowledged', true)->count() }}</div>
                    <div class="small text-muted">Item yang sudah dikonfirmasi user.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column gap-3">
            @forelse ($notifications as $notification)
                <div class="border rounded-3 p-3 notification-item-hover {{ $notification['is_read'] ? '' : 'bg-light-subtle' }}">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle bg-{{ $notification['badge_class'] }}-subtle text-{{ $notification['badge_class'] }}">
                                    <iconify-icon icon="{{ $notification['icon'] }}"></iconify-icon>
                                </span>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    @unless ($notification['is_read'])
                                        <span class="badge bg-danger rounded-pill">Unread</span>
                                    @endunless
                                    <h6 class="mb-0 text-dark">{{ $notification['title'] }}</h6>
                                    <span class="badge bg-{{ $notification['badge_class'] }}-subtle text-{{ $notification['badge_class'] }}">
                                        {{ ucfirst(str_replace('_', ' ', $notification['type'])) }}
                                    </span>
                                    @if ($notification['is_acknowledged'])
                                        <span class="badge bg-success-subtle text-success">Acknowledged</span>
                                    @elseif ($notification['is_read'])
                                        <span class="badge bg-secondary-subtle text-secondary">Read</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-1">{{ $notification['message'] }}</p>
                                <small class="text-muted">
                                    {{ $notification['occurred_at']->format('d M Y H:i') }} · {{ $notification['occurred_at']->diffForHumans() }}
                                    @if ($notification['read_at'])
                                        · read {{ $notification['read_at']->diffForHumans() }}
                                    @endif
                                    @if ($notification['acknowledged_at'])
                                        · acknowledged {{ $notification['acknowledged_at']->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ $notification['open_url'] }}" class="btn btn-sm btn-primary">
                                Open
                            </a>
                            @unless ($notification['is_read'])
                                <form method="POST" action="{{ route('notifications.read', $notification['key']) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark Read</button>
                                </form>
                            @endunless
                            @unless ($notification['is_acknowledged'])
                                <form method="POST" action="{{ route('notifications.acknowledge', $notification['key']) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Acknowledge</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-3">
                        <span class="avatar-title rounded-circle bg-light text-muted border">
                            <iconify-icon icon="solar:bell-off-outline" class="fs-32"></iconify-icon>
                        </span>
                    </div>
                    <h5 class="mb-2">No notifications yet</h5>
                    <p class="text-muted mb-0">Notification center will populate as tickets, approvals, SLA events, and inspections move.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
