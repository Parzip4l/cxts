@extends('layouts.base', ['subtitle' => 'Sign In'])

@section('body-attribuet')
class="authentication-bg cxts-auth-page"
@endsection

@push('styles')
<style>
    .cxts-auth-page {
        --auth-navy: #011932;
        --auth-blue: #035fc7;
        overflow: hidden;
        background: #edf1f5 !important;
        color: #17263a;
    }

    .cxts-auth-page .account-pages {
        display: flex;
        height: 100dvh;
        min-height: 0;
        align-items: center;
        padding: 20px 0;
        overflow: hidden;
    }

    .cxts-auth-shell {
        width: 100%;
        max-width: 1080px;
        height: min(680px, calc(100dvh - 40px));
        min-height: 560px;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid #dce3eb;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(1, 25, 50, .12);
    }

    .cxts-auth-story {
        position: relative;
        isolation: isolate;
        height: 100%;
        padding: 42px;
        overflow: hidden;
        background: var(--auth-navy);
        color: #fff;
    }

    .cxts-auth-story::before,
    .cxts-auth-story::after {
        position: absolute;
        z-index: -1;
        content: '';
        border-radius: 50%;
        pointer-events: none;
    }

    .cxts-auth-story::before {
        width: 360px;
        height: 360px;
        top: -180px;
        right: -135px;
        background: rgba(3, 95, 199, .28);
    }

    .cxts-auth-story::after {
        width: 420px;
        height: 420px;
        bottom: -260px;
        left: -190px;
        background: rgba(25, 197, 210, .12);
    }

    .cxts-auth-logo { width: 132px; height: auto; }

    .cxts-auth-kicker {
        color: #59cfdb;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .cxts-auth-headline {
        max-width: 440px;
        margin: 14px 0 0;
        color: #fff;
        font-size: clamp(2rem, 3vw, 2.65rem);
        font-weight: 750;
        line-height: 1.12;
        letter-spacing: -.035em;
    }

    .cxts-auth-copy {
        max-width: 430px;
        margin: 18px 0 0;
        color: rgba(255, 255, 255, .68);
        line-height: 1.65;
    }

    .cxts-auth-message { margin: auto 0; }

    .cxts-auth-form-panel {
        display: flex;
        height: 100%;
        align-items: center;
        padding: 40px clamp(32px, 5vw, 64px);
        background: #fff;
    }

    .cxts-auth-form-wrap { width: 100%; max-width: 390px; margin: 0 auto; }
    .cxts-mobile-logo { display: none; width: 110px; margin-bottom: 28px; }

    .cxts-auth-title {
        margin-bottom: 8px;
        color: var(--auth-navy);
        font-size: 1.85rem;
        font-weight: 750;
        letter-spacing: -.025em;
    }

    .cxts-auth-alert {
        margin: 20px 0 0;
        padding: 12px 14px;
        border: 1px solid #f3cdcd;
        border-radius: 10px;
        background: #fff7f7;
        color: #9f2424;
    }

    .cxts-auth-form { margin-top: 26px; }
    .cxts-field-label { margin-bottom: 7px; color: #34445a; font-size: .8rem; font-weight: 650; }

    .cxts-input-group {
        min-height: 50px;
        overflow: hidden;
        border: 1px solid #d7e0ea;
        border-radius: 10px;
        background: #fff;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .cxts-input-group:focus-within {
        border-color: rgba(3, 95, 199, .65);
        box-shadow: 0 0 0 3px rgba(3, 95, 199, .09);
    }

    .cxts-input-group .input-group-text,
    .cxts-input-group .form-control,
    .cxts-input-group .btn {
        min-height: 48px;
        border: 0;
        background: transparent;
        box-shadow: none !important;
    }

    .cxts-input-group .input-group-text { padding: 0 8px 0 14px; color: #77869a; font-size: 1.1rem; }
    .cxts-input-group .form-control { padding-left: 4px; color: #17263a; }
    .cxts-input-group .btn { color: #77869a; }

    .cxts-signin-btn {
        min-height: 50px;
        border: 0;
        border-radius: 10px;
        background: var(--auth-blue);
        color: #fff;
        font-weight: 650;
        box-shadow: 0 8px 18px rgba(3, 95, 199, .18);
    }

    .cxts-signin-btn:hover,
    .cxts-signin-btn:focus { background: #0454af; color: #fff; }

    .cxts-security-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        margin-top: 22px;
        color: #78879a;
        font-size: .74rem;
    }

    .cxts-auth-footer { margin-top: 28px; }

    @media (max-height: 680px) and (min-width: 992px) {
        .cxts-auth-shell { min-height: 520px; }
        .cxts-auth-story { padding: 30px 36px; }
        .cxts-auth-logo { width: 110px; }
        .cxts-auth-headline { font-size: 2rem; }
        .cxts-auth-copy { margin-top: 12px; font-size: .9rem; }
        .cxts-auth-form-panel { padding-top: 24px; padding-bottom: 24px; }
        .cxts-auth-form { margin-top: 18px; }
        .cxts-security-note { margin-top: 14px; }
        .cxts-auth-footer { margin-top: 16px; }
    }

    @media (max-width: 991.98px) {
        .cxts-auth-page { background: #fff !important; }
        .cxts-auth-page .account-pages { padding: 0; }
        .cxts-auth-page .container { max-width: none; padding: 0 !important; }
        .cxts-auth-shell { height: 100dvh; min-height: 0; border: 0; border-radius: 0; box-shadow: none; }
        .cxts-auth-story { display: none !important; }
        .cxts-auth-form-panel { height: 100dvh; padding: clamp(20px, 5vh, 42px) 24px; }
        .cxts-mobile-logo { display: block; }
    }

    @media (max-width: 575.98px) and (max-height: 700px) {
        .cxts-mobile-logo { width: 96px; margin-bottom: 18px; }
        .cxts-auth-title { font-size: 1.6rem; }
        .cxts-auth-form { margin-top: 18px; }
        .cxts-input-group { min-height: 46px; }
        .cxts-input-group .input-group-text,
        .cxts-input-group .form-control,
        .cxts-input-group .btn { min-height: 44px; }
        .cxts-signin-btn { min-height: 46px; }
        .cxts-security-note { margin-top: 14px; }
        .cxts-auth-footer { margin-top: 14px; }
    }
</style>
@endpush

@section('content')
<main class="account-pages">
    <div class="container">
        <div class="cxts-auth-shell">
            <div class="row g-0 h-100">
                <section class="col-lg-6 cxts-auth-story d-flex flex-column" aria-label="CXTS overview">
                    <img src="{{ asset('images/cxts-white.png') }}" class="cxts-auth-logo" alt="CXTS">

                    <div class="cxts-auth-message">
                        <div class="cxts-auth-kicker">Service operations platform</div>
                        <h1 class="cxts-auth-headline">Operasional layanan dalam satu sistem.</h1>
                        <p class="cxts-auth-copy">Kelola ticket, approval, SLA, asset, dan inspeksi dengan alur yang jelas dan mudah dipantau.</p>
                    </div>
                </section>

                <section class="col-lg-6 cxts-auth-form-panel">
                    <div class="cxts-auth-form-wrap">
                        <img src="{{ asset('images/cxts.png') }}" class="cxts-mobile-logo" alt="CXTS">
                        <h2 class="cxts-auth-title">Masuk ke CXTS</h2>
                        <p class="text-muted mb-0">Gunakan akun kerja Anda untuk melanjutkan.</p>

                        @if ($errors->any())
                            <div class="cxts-auth-alert d-flex gap-2" role="alert">
                                <iconify-icon icon="solar:danger-triangle-outline" class="fs-5 flex-shrink-0"></iconify-icon>
                                <div>
                                    <div class="fw-semibold small">Login belum berhasil</div>
                                    <ul class="mb-0 ps-3 small">
                                        @foreach ($errors->all() as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="cxts-auth-form">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="cxts-field-label">Email</label>
                                <div class="input-group cxts-input-group @error('email') border-danger @enderror">
                                    <span class="input-group-text"><iconify-icon icon="solar:letter-outline"></iconify-icon></span>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                                        placeholder="nama@perusahaan.com" autocomplete="username" inputmode="email" required autofocus>
                                </div>
                                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
                                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember-me" name="remember" value="1" @checked(old('remember'))>
                                    <label class="form-check-label small" for="remember-me">Ingat saya</label>
                                </div>
                                <span class="small text-muted">Internal access</span>
                            </div>

                            <div class="d-grid">
                                <button class="btn cxts-signin-btn" type="submit">Masuk</button>
                            </div>
                        </form>

                        <div class="cxts-security-note">
                            <iconify-icon icon="solar:shield-check-outline" class="fs-5 text-success"></iconify-icon>
                            <span>Sesi dilindungi dan aktivitas login dicatat.</span>
                        </div>

                        <p class="cxts-auth-footer text-center text-muted small mb-0">© {{ now()->year }} {{ config('app.name', 'CXTS') }}</p>
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
