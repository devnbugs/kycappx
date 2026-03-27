<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSocialProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->requiresSocialProfileCompletion()) {
            return $next($request);
        }

        return redirect()->route('social.profile.complete');
    }
}
