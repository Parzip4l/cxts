<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken === null || $bearerToken === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $hashedToken = hash('sha256', $bearerToken);

        $apiToken = ApiToken::query()
            ->with('user.department')
            ->where('token', $hashedToken)
            ->first();

        if ($apiToken === null || $apiToken->isExpired()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        $request->setUserResolver(fn () => $apiToken->user);
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }
}
