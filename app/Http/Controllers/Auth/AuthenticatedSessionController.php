<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\Audit\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly AuditTrailService $auditTrailService)
    {
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.signin');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        $request->authenticate();

        $request->session()->regenerate();

        $this->auditTrailService->recordManual(
            actor: $request->user(),
            module: 'Authentication',
            action: 'login / store',
            path: '/login',
            request: $request,
        );

        return redirect()->to($this->resolveRedirectTarget($request));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $actor = $request->user();

        $this->auditTrailService->recordManual(
            actor: $actor,
            module: 'Authentication',
            action: 'logout / destroy',
            path: '/logout',
            request: $request,
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function resolveRedirectTarget(Request $request): string
    {
        $intendedUrl = $request->session()->pull('url.intended');

        if ($this->shouldIgnoreIntendedUrl($intendedUrl, $request)) {
            return RouteServiceProvider::HOME;
        }

        if (is_string($intendedUrl) && $intendedUrl !== '') {
            return $intendedUrl;
        }

        return RouteServiceProvider::HOME;
    }

    private function shouldIgnoreIntendedUrl(?string $intendedUrl, Request $request): bool
    {
        if (! is_string($intendedUrl) || $intendedUrl === '') {
            return true;
        }

        $parsed = parse_url($intendedUrl);
        $path = $parsed['path'] ?? '/';
        $host = $parsed['host'] ?? null;

        if ($host !== null && $host !== $request->getHost()) {
            return true;
        }

        if (str_starts_with($path, '/.well-known/')) {
            return true;
        }

        if (str_contains($path, 'com.chrome.devtools.json')) {
            return true;
        }

        return false;
    }
}
