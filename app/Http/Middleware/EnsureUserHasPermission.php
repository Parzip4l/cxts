<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        if (empty($permissions) || $user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
