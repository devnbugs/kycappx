<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) abort(401);

        if (!$user->hasAnyRole(['super-admin', 'admin', 'support'])) {
            abort(403);
        }

        return $next($request);
    }
}