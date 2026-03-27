<!-- Google Font Family link -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

<?php echo $__env->yieldContent('css'); ?>

<?php echo app('Illuminate\Foundation\Vite')([ 'resources/scss/icons.scss', 'resources/scss/style.scss']); ?>

<style>
    :root {
        --cxts-shell-radius: 1rem;
        --cxts-shell-radius-sm: 0.8rem;
        --cxts-shell-border: rgba(148, 163, 184, 0.18);
        --cxts-shell-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        --cxts-shell-shadow-hover: 0 18px 34px rgba(15, 23, 42, 0.08);
        --cxts-shell-muted: #64748b;
        --cxts-shell-bg: #f8fafc;
    }

    body {
        background-color: var(--cxts-shell-bg);
    }

    .container-fluid > .row,
    .container-fluid > .card,
    .container-fluid > .alert {
        animation: cxtsFadeUp 0.28s ease;
    }

    @keyframes cxtsFadeUp {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        border-radius: var(--cxts-shell-radius);
        border-color: var(--cxts-shell-border);
        box-shadow: var(--cxts-shell-shadow);
    }

    .card.border-0.shadow-sm,
    .card.shadow-sm {
        box-shadow: var(--cxts-shell-shadow) !important;
    }

    .card-header {
        padding-top: 1.15rem;
        padding-bottom: 1rem;
    }

    .card-body {
        padding: 1.25rem;
    }

    .rounded-3,
    .rounded-4,
    .dropdown-menu,
    .alert,
    .modal-content {
        border-radius: var(--cxts-shell-radius-sm) !important;
    }

    .dropdown-menu {
        border-color: var(--cxts-shell-border);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    .dropdown-item {
        border-radius: 0.65rem;
        margin: 0 0.35rem;
        width: auto;
    }

    .dropdown-item:active {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }

    .btn {
        border-radius: 0.8rem;
        font-weight: 500;
        letter-spacing: 0.01em;
        padding: 0.625rem 1rem;
        transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease, border-color 0.16s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-sm {
        border-radius: 0.7rem;
        padding: 0.45rem 0.8rem;
    }

    .btn-lg {
        border-radius: 0.95rem;
    }

    .btn-primary,
    .btn-dark,
    .btn-outline-secondary:hover,
    .btn-outline-light:hover {
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .btn-outline-light {
        border-color: rgba(148, 163, 184, 0.35);
        color: #334155;
        background: #fff;
    }

    .btn-outline-light:hover {
        border-color: rgba(148, 163, 184, 0.45);
        background: #f8fafc;
        color: #0f172a;
    }

    .btn-outline-secondary {
        border-color: rgba(100, 116, 139, 0.25);
        color: #334155;
        background: #fff;
    }

    .btn-outline-secondary:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .badge {
        border-radius: 999px;
        font-weight: 600;
        letter-spacing: 0.01em;
        padding: 0.45rem 0.65rem;
    }

    .badge.bg-light {
        border: 1px solid rgba(148, 163, 184, 0.22);
    }

    .form-label {
        font-weight: 600;
        color: #475569;
    }

    .form-control,
    .form-select {
        border-radius: 0.8rem;
        border-color: rgba(148, 163, 184, 0.35);
        min-height: 44px;
        background-color: #fff;
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: rgba(37, 99, 235, 0.35);
        box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.1);
    }

    .table {
        --bs-table-bg: transparent;
    }

    .table thead th {
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--cxts-shell-muted);
        border-bottom-width: 1px;
        white-space: nowrap;
    }

    .table tbody td {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .table-responsive {
        border-radius: var(--cxts-shell-radius-sm);
    }

    .text-muted,
    .small.text-muted,
    small.text-muted {
        color: var(--cxts-shell-muted) !important;
    }

    .page-title-head,
    .page-title-box {
        margin-bottom: 1rem;
    }

    .apex-charts {
        min-height: 280px;
    }
</style>

<?php echo $__env->yieldPushContent('styles'); ?>

<?php echo app('Illuminate\Foundation\Vite')([ 'resources/js/config.js']); ?>
<?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/layouts/partials/head-css.blade.php ENDPATH**/ ?>