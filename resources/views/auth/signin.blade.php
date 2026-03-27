@extends('layouts.base', ['subtitle' => 'Sign In'])

@section('body-attribuet')
class="authentication-bg"
@endsection

@section('content')
<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
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

                        <div class="alert alert-light border mt-3 mb-0">
                            <div class="fw-semibold mb-1">Demo Accounts</div>
                            <small class="d-block">superadmin@demo.com / password</small>
                            <small class="d-block">opsadmin@demo.com / password</small>
                            <small class="d-block">supervisor@demo.com / password</small>
                            <small class="d-block">engineer1@demo.com / password</small>
                            <small class="d-block">requester@demo.com / password</small>
                        </div>
                    </div>
                </div>
                <p class="text-center mt-4 text-white text-opacity-50">Environment demo internal GM Tekno.</p>
            </div>
        </div>
    </div>
</div>
@endsection
