@extends('layouts.base', ['subtitle' => 'Sign In'])

@section('body-attribuet')
class="authentication-bg"
@endsection

@section('content')
<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="row g-0 overflow-hidden rounded-4 shadow-lg bg-white">
                    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 text-white"
                        style="background: linear-gradient(145deg, #0f172a 0%, #1d4ed8 100%);">
                        <div>
                            <div class="mb-4">
                                <img src="/images/logo-light.png" height="30" alt="logo light">
                            </div>
                            <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 mb-3">Service Operations Platform</span>
                            <h2 class="fw-bold mb-3 text-white">Kelola ticket, approval, SLA, dan engineering execution dalam satu alur.</h2>
                            <p class="text-white text-opacity-75 mb-0">
                                CXTS dirancang untuk operasional harian yang butuh visibilitas cepat, assignment yang rapi, dan jejak audit yang jelas.
                            </p>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="rounded-3 border border-white border-opacity-10 bg-white bg-opacity-10 p-3 h-100">
                                    <div class="small text-white text-opacity-75 mb-1">Coverage</div>
                                    <div class="fw-semibold">Ticketing, Inspection, SLA</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="rounded-3 border border-white border-opacity-10 bg-white bg-opacity-10 p-3 h-100">
                                    <div class="small text-white text-opacity-75 mb-1">Control</div>
                                    <div class="fw-semibold">Approval, Assignment, Audit Trail</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="h-100 p-4 p-lg-5">
                        <div class="text-center">
                            <div class="mx-auto mb-4 text-center auth-logo">
                                <a href="{{ route('any', 'index') }}" class="logo-dark">
                                    <img src="/images/logo-dark.png" height="32" alt="logo dark">
                                </a>

                                <a href="{{ route('any', 'index') }}" class="logo-light">
                                    <img src="/images/logo-light.png" height="28" alt="logo light">
                                </a>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Masuk ke {{ config('app.name', 'CXTS') }}</h4>
                            <p class="text-muted mb-0">Platform operasional untuk ticketing, SLA, approval, asset, dan inspection.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger mt-3" role="alert">
                                <div class="fw-semibold mb-1">Login failed</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="mt-4">

                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', 'superadmin@demo.com') }}"
                                    placeholder="Enter your email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label">Password</label>
                                    <a href="{{ route('second', ['auth', 'password']) }}"
                                        class="text-decoration-none small text-muted">Forgot password?</a>
                                </div>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" value="password"
                                    placeholder="Enter your password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember-me" name="remember" value="1" @checked(old('remember'))>
                                <label class="form-check-label" for="remember-me">Remember me</label>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-dark btn-lg fw-medium" type="submit">Sign In</button>
                            </div>
                        </form>

                        <div class="rounded-3 border bg-light-subtle mt-4 p-3">
                            <div class="fw-semibold mb-2">Demo Accounts</div>
                            <div class="small text-muted mb-3">Gunakan akun berikut untuk walkthrough role-based demo.</div>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="d-flex justify-content-between gap-3"><span>Super Admin</span><code>superadmin@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Ops Admin</span><code>opsadmin@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Supervisor</span><code>supervisor@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Engineer</span><code>engineer1@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Requester</span><code>requester@demo.com / password</code></div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <p class="text-center mt-4 text-white text-opacity-50">Environment demo internal GM Tekno.</p>
            </div>
        </div>
    </div>
</div>
@endsection
