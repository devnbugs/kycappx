<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Security\TurnstileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private TurnstileService $turnstile)
    {
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $this->ensureTurnstile($request, 'login');
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if ($user?->hasTwoFactorEnabled()) {
            Auth::guard('web')->logout();

            $request->session()->put([
                'two_factor.login.id' => $user->id,
                'two_factor.remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('two-factor.challenge');
        }

        $user?->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function ensureTurnstile(Request $request, string $action): void
    {
        $result = $this->turnstile->verify(
            token: $request->string('cf-turnstile-response')->value(),
            remoteIp: $request->ip(),
            expectedAction: $action,
        );

        if (! $result['success']) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => $result['message'],
            ]);
        }
    }
}
