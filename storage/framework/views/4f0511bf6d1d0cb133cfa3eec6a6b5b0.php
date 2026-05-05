<?php $__env->startSection('content'); ?>
<?php
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
?>

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
                                    <a href="<?php echo e(route('public.tickets.create')); ?>" class="btn btn-light">Submit Ticket</a>
                                    <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-light">Staff Login</a>
                                </div>
                            </div>
                        </div>

                        <?php if(session('success')): ?>
                            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                        <?php endif; ?>

                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="border rounded p-3 p-lg-4 bg-light-subtle h-100">
                                    <h5 class="mb-2">Cari Status Ticket</h5>
                                    <p class="text-muted mb-4">Masukkan kombinasi yang sama dengan data saat ticket dibuat.</p>

                                    <form method="POST" action="<?php echo e(route('public.tickets.lookup')); ?>" class="row g-3">
                                        <?php echo csrf_field(); ?>
                                        <div class="col-12">
                                            <label for="ticket_number" class="form-label">Nomor Ticket</label>
                                            <input
                                                type="text"
                                                id="ticket_number"
                                                name="ticket_number"
                                                class="form-control <?php $__errorArgs = ['ticket_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                value="<?php echo e(old('ticket_number', $ticketNumber)); ?>"
                                                placeholder="Contoh: TCK-20260504-0001"
                                                required
                                            >
                                            <?php $__errorArgs = ['ticket_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <div class="col-12">
                                            <label for="requester_email" class="form-label">Email Pelapor</label>
                                            <input
                                                type="email"
                                                id="requester_email"
                                                name="requester_email"
                                                class="form-control <?php $__errorArgs = ['requester_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                value="<?php echo e(old('requester_email', $requesterEmail)); ?>"
                                                placeholder="nama@perusahaan.com"
                                                required
                                            >
                                            <?php $__errorArgs = ['requester_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                <?php if($ticket): ?>
                                    <div class="border rounded p-3 p-lg-4 mb-4">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                                            <div>
                                                <div class="text-muted small mb-1">Nomor Ticket</div>
                                                <h5 class="fw-bold mb-1"><?php echo e($ticket->ticket_number); ?></h5>
                                                <div class="text-muted"><?php echo e($ticket->title); ?></div>
                                            </div>
                                            <div>
                                                <span class="badge bg-<?php echo e($statusBadgeClass); ?> px-3 py-2"><?php echo e($ticket->status?->name ?? 'Unknown'); ?></span>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Pelapor</div>
                                                    <div class="fw-semibold"><?php echo e($ticket->requester?->name ?? '-'); ?></div>
                                                    <div class="small text-muted"><?php echo e($ticket->requesterDepartment?->name ?? '-'); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Kategori</div>
                                                    <div class="fw-semibold"><?php echo e($ticket->category?->name ?? '-'); ?></div>
                                                    <div class="small text-muted"><?php echo e($ticket->subcategory?->name ?? 'Belum ada kategori detail'); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Prioritas</div>
                                                    <div class="fw-semibold"><?php echo e($ticket->priority?->name ?? '-'); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="small text-muted mb-1">Penanganan</div>
                                                    <div class="fw-semibold"><?php echo e($ticket->assigned_engineer_id ? 'Tim teknis sudah ditugaskan' : 'Menunggu penugasan tim teknis'); ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if($contextItems->isNotEmpty()): ?>
                                            <div class="border rounded p-3 mt-3">
                                                <div class="small text-muted mb-2">Konteks Terdampak</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <?php $__currentLoopData = $contextItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="badge bg-light text-dark border"><?php echo e($label); ?>: <?php echo e($value); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($milestones->isNotEmpty()): ?>
                                        <div class="border rounded p-3 p-lg-4 mb-4">
                                            <h5 class="mb-3">Ringkasan Waktu</h5>
                                            <div class="row g-3">
                                                <?php $__currentLoopData = $milestones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $milestone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="small text-muted mb-1"><?php echo e($milestone['label']); ?></div>
                                                            <div class="fw-semibold"><?php echo e($milestone['value']->format('d M Y H:i')); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="border rounded p-3 p-lg-4">
                                        <h5 class="mb-3">Timeline Status</h5>
                                        <?php if($ticket->activities->isNotEmpty()): ?>
                                            <div class="public-track-timeline">
                                                <?php $__currentLoopData = $ticket->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="d-flex gap-3 mb-3">
                                                        <div class="public-track-dot mt-1"></div>
                                                        <div>
                                                            <div class="fw-semibold"><?php echo e($activityLabels[$activity->activity_type] ?? 'Update ticket'); ?></div>
                                                            <div class="small text-muted">
                                                                <?php echo e($activity->created_at->format('d M Y H:i')); ?>

                                                                <?php if($activity->newStatus): ?>
                                                                    · Status: <?php echo e($activity->newStatus->name); ?>

                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-light border mb-0">Timeline status belum tersedia.</div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="border rounded p-4 p-lg-5 text-center h-100 d-flex flex-column justify-content-center">
                                        <div class="mx-auto mb-3 rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                            <iconify-icon icon="solar:ticket-sale-outline" width="34" height="34"></iconify-icon>
                                        </div>
                                        <h5 class="fw-bold mb-2">Status ticket akan muncul di sini</h5>
                                        <p class="text-muted mb-0">Setelah nomor ticket dan email cocok, CXTS akan menampilkan status, ringkasan waktu, dan timeline progres yang aman untuk pelapor.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.base', ['subtitle' => 'Track Ticket'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/public/tickets/track.blade.php ENDPATH**/ ?>