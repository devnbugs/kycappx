<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function store(Request $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => $twoFactorService->generateSecret(),
            'two_factor_recovery_codes' => $twoFactorService->recoveryCodes(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-started');
    }

    public function update(Request $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:16'],
        ]);

        abort_unless($request->user()->two_factor_secret, 403);

        if (! $twoFactorService->verify($request->user()->two_factor_secret, $validated['code'])) {
            return back()->withErrors(['two_factor' => 'The authentication code could not be confirmed.']);
        }

        $request->user()->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('status', 'two-factor-enabled');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-disabled');
    }
}
