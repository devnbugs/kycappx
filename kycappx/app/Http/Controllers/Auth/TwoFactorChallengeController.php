<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $user = User::query()->findOrFail((int) $request->session()->get('two_factor.login.id'));
        $code = trim($validated['code']);
        $recoveryCodes = collect($user->two_factor_recovery_codes ?? []);

        $validRecoveryCode = $recoveryCodes->contains($code);
        $validTotp = $user->two_factor_secret && $twoFactorService->verify($user->two_factor_secret, $code);

        if (! $validRecoveryCode && ! $validTotp) {
            return back()->withErrors(['code' => 'The provided authentication code was invalid.']);
        }

        if ($validRecoveryCode) {
            $user->forceFill([
                'two_factor_recovery_codes' => $recoveryCodes
                    ->reject(fn ($recoveryCode) => hash_equals($recoveryCode, $code))
                    ->values()
                    ->all(),
            ])->save();
        }

        Auth::login($user, (bool) $request->session()->pull('two_factor.remember', false));
        $request->session()->forget('two_factor.login.id');
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'two_factor.login.id',
            'two_factor.remember',
        ]);

        return redirect()->route('login');
    }
}
