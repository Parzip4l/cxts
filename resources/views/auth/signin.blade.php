@extends('layouts.base', ['subtitle' => 'Sign In'])

@section('body-attribuet')
class="authentication-bg cxts-auth-page"
@endsection

@push('styles')
<style>
    .cxts-auth-page {
        --auth-navy: #011932;
        --auth-blue: #035fc7;
        background:
            radial-gradient(circle at 12% 14%, rgba(25, 197, 210, .2), transparent 28%),
            radial-gradient(circle at 88% 86%, rgba(3, 95, 199, .2), transparent 30%),
            #eef4fa !important;
        color: #142235;
    }

    .cxts-auth-page .account-pages { min-height: 100vh; padding: 2rem 0; }

    .cxts-auth-shell {
        position: relative;
        max-width: 1160px;
        min-height: 700px;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, .2);
        border-radius: 2rem;
        background: #fff;
        box-shadow: 0 30px 80px rgba(1, 25, 50, .16);
    }

    .cxts-auth-story {
        position: relative;
        isolation: isolate;
        min-height: 700px;
        padding: 3.25rem;
        overflow: hidden;
        background: linear-gradient(145deg, #011932 0%, #043b70 58%, #035fc7 130%);
        color: #fff;
    }

    .cxts-auth-story::before,
    .cxts-auth-story::after {
        position: absolute;
        z-index: -1;
        content: '';
        border-radius: 50%;
    }

    .cxts-auth-story::before {
        width: 360px;
        height: 360px;
        top: -170px;
        right: -120px;
        background: rgba(25, 197, 210, .25);
    }

    .cxts-auth-story::after {
        width: 300px;
        height: 300px;
        bottom: -150px;
        left: -100px;
        background: rgba(3, 95, 199, .34);
    }

    .cxts-auth-logo { width: 142px; height: auto; }

    .cxts-auth-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .52rem .8rem;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 999px;
        background: rgba(255, 255, 255, .09);
        color: rgba(255, 255, 255, .88);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        backdrop-filter: blur(12px);
    }

    .cxts-auth-eyebrow::before {
        width: 8px;
        height: 8px;
        content: '';
        border-radius: 50%;
        background: #4ce0c0;
        box-shadow: 0 0 0 5px rgba(76, 224, 192, .12);
    }

    .cxts-auth-headline {
        max-width: 520px;
        margin-top: 1.5rem;
        color: #fff;
        font-size: clamp(2.1rem, 4vw, 3.45rem);
        font-weight: 800;
        line-height: 1.05;
        letter-spacing: -.045em;
    }

    .cxts-auth-copy {
        max-width: 500px;
        color: rgba(255, 255, 255, .68);
        font-size: 1rem;
        line-height: 1.75;
    }

    .cxts-flow-card {
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 1.15rem;
        background: rgba(255, 255, 255, .075);
        backdrop-filter: blur(14px);
    }

    .cxts-flow-icon {
        display: grid;
        flex: 0 0 auto;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: .9rem;
        background: rgba(255, 255, 255, .12);
        color: #72e1ed;
        font-size: 1.25rem;
    }

    .cxts-auth-form-panel {
        display: flex;
        min-height: 700px;
        align-items: center;
        padding: 3.25rem clamp(1.5rem, 4vw, 4.25rem);
    }

    .cxts-auth-form-wrap { width: 100%; max-width: 430px; margin: 0 auto; }
    .cxts-mobile-logo { display: none; width: 116px; margin-bottom: 2rem; }

    .cxts-auth-title {
        color: var(--auth-navy);
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.035em;
    }

    .cxts-auth-alert {
        border: 1px solid rgba(239, 68, 68, .16);
        border-radius: 1rem;
        background: #fff4f4;
        color: #9f2424;
    }

    .cxts-field-label { margin-bottom: .55rem; color: #25354a; font-size: .82rem; font-weight: 700; }

    .cxts-input-group {
        min-height: 54px;
        overflow: hidden;
        border: 1px solid #dbe4ee;
        border-radius: 1rem;
        background: #f8fafc;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .cxts-input-group:focus-within {
        border-color: rgba(3, 95, 199, .55);
        background: #fff;
        box-shadow: 0 0 0 .25rem rgba(3, 95, 199, .09);
    }

    .cxts-input-group .input-group-text,
    .cxts-input-group .form-control,
    .cxts-input-group .btn {
        min-height: 52px;
        border: 0;
        background: transparent;
        box-shadow: none !important;
    }

    .cxts-input-group .input-group-text { padding-left: 1rem; padding-right: .55rem; color: #708198; font-size: 1.15rem; }
    .cxts-input-group .form-control { padding-left: .4rem; color: #142235; }
    .cxts-input-group .btn { color: #708198; }

    .cxts-signin-btn {
        min-height: 54px;
        border: 0;
        border-radius: 1rem;
        background: linear-gradient(135deg, var(--auth-blue), #034b9e);
        box-shadow: 0 14px 28px rgba(3, 95, 199, .23);
        color: #fff;
        font-weight: 700;
    }

    .cxts-signin-btn:hover,
    .cxts-signin-btn:focus {
        background: linear-gradient(135deg, #0870e2, #0352ad);
        color: #fff;
        box-shadow: 0 16px 32px rgba(3, 95, 199, .3);
    }

    .cxts-security-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .55rem;
        color: #718096;
        font-size: .76rem;
    }

    @media (max-width: 991.98px) {
        .cxts-auth-page .account-pages { padding: 1rem 0; }
        .cxts-auth-shell { min-height: auto; border-radius: 1.5rem; }
        .cxts-auth-story { display: none !important; }
        .cxts-auth-form-panel { min-height: calc(100vh - 2rem); padding: 2.25rem 1.5rem; }
        .cxts-mobile-logo { display: block; }
    }
</style>
@endpush

@section('content')
<main class="account-pages">
    <div class="container px-3">
        <div class="cxts-auth-shell">
            <div class="row g-0 h-100">
                <section class="col-lg-7 cxts-auth-story d-flex flex-column justify-content-between" aria-label="CXTS overview">
                    <div>
                        <img src="{{ asset('images/cxts-white.png') }}" class="cxts-auth-logo" alt="CXTS">
                        <div class="mt-5">
                            <span class="cxts-auth-eyebrow">Service operations workspace</span>
                            <h1 class="cxts-auth-headline">Satu ruang kerja untuk layanan yang lebih terukur.</h1>
                            <p class="cxts-auth-copy mt-4 mb-0">
                                Pantau ticket, approval, SLA, asset, dan inspeksi dalam alur kerja yang terhubung—dari laporan pertama hingga penyelesaian.
                            </p>
                        </div>
                    </div>

                    <div class="row g-3 mt-5">
                        <div class="col-sm-6">
                            <div class="cxts-flow-card d-flex gap-3 align-items-center h-100">
                                <span class="cxts-flow-icon"><iconify-icon icon="solar:ticket-sale-outline"></iconify-icon></span>
                                <div><div class="fw-bold">Connected workflow</div><div class="small text-white text-opacity-50 mt-1">Ticket hingga resolusi dalam satu jejak.</div></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="cxts-flow-card d-flex gap-3 align-items-center h-100">
                                <span class="cxts-flow-icon"><iconify-icon icon="solar:shield-check-outline"></iconify-icon></span>
                                <div><div class="fw-bold">Role-based access</div><div class="small text-white text-opacity-50 mt-1">Akses sesuai tanggung jawab tim.</div></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="col-lg-5 cxts-auth-form-panel">
                    <div class="cxts-auth-form-wrap">
                        <img src="{{ asset('images/cxts.png') }}" class="cxts-mobile-logo" alt="CXTS">
                        <span class="badge bg-primary-subtle text-primary mb-3 px-3 py-2">Secure workspace</span>
                        <h2 class="cxts-auth-title mb-2">Selamat datang kembali</h2>
                        <p class="text-muted mb-0">Masuk menggunakan akun kerja CXTS Anda.</p>

                        @if ($errors->any())
                            <div class="alert cxts-auth-alert d-flex gap-3 mt-4 mb-0" role="alert">
                                <iconify-icon icon="solar:danger-triangle-outline" class="fs-4 flex-shrink-0"></iconify-icon>
                                <div>
                                    <div class="fw-bold mb-1">Login belum berhasil</div>
                                    <ul class="mb-0 ps-3 small">
                                        @foreach ($errors->all() as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="mt-4">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="cxts-field-label">Email</label>
                                <div class="input-group cxts-input-group @error('email') border-danger @enderror">
                                    <span class="input-group-text"><iconify-icon icon="solar:letter-outline"></iconify-icon></span>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                                        placeholder="nama@perusahaan.com" autocomplete="username" inputmode="email" required autofocus>
                                </div>
                                @error('email')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <label for="password" class="cxts-field-label">Password</label>
                                    <a href="{{ route('second', ['auth', 'password']) }}" class="small text-decoration-none">Lupa password?</a>
                                </div>
                                <div class="input-group cxts-input-group @error('password') border-danger @enderror">
                                    <span class="input-group-text"><iconify-icon icon="solar:lock-password-outline"></iconify-icon></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Masukkan password" autocomplete="current-password" required>
                                    <button class="btn px-3" type="button" id="toggle-password" aria-label="Tampilkan password" aria-pressed="false">
                                        <iconify-icon id="password-icon" icon="solar:eye-outline" class="fs-5"></iconify-icon>
                                    </button>
                                </div>
                                @error('password')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember-me" name="remember" value="1" @checked(old('remember'))>
                                    <label class="form-check-label small" for="remember-me">Ingat saya</label>
                                </div>
                                <span class="small text-muted">Internal access</span>
                            </div>

                            <div class="d-grid">
                                <button class="btn cxts-signin-btn" type="submit">
                                    <span class="d-inline-flex align-items-center gap-2">Masuk ke workspace <iconify-icon icon="solar:arrow-right-linear" class="fs-5"></iconify-icon></span>
                                </button>
                            </div>
                        </form>

                        <div class="cxts-security-note mt-4">
                            <iconify-icon icon="solar:shield-check-outline" class="fs-5 text-success"></iconify-icon>
                            <span>Sesi dilindungi dan aktivitas dicatat untuk keamanan.</span>
                        </div>

                        <p class="text-center text-muted small mb-0 mt-5">© {{ now()->year }} {{ config('app.name', 'CXTS') }}</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('toggle-password');
        const password = document.getElementById('password');
        const icon = document.getElementById('password-icon');

        toggle?.addEventListener('click', () => {
            const isVisible = password.type === 'text';
            password.type = isVisible ? 'password' : 'text';
            toggle.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
            toggle.setAttribute('aria-pressed', String(!isVisible));
            icon?.setAttribute('icon', isVisible ? 'solar:eye-outline' : 'solar:eye-closed-outline');
            password.focus();
        });
    });
</script>
@endpush
