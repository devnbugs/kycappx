<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\Kyc\KycStrengthService;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactorService,
        private KycStrengthService $kycStrength,
    ) {
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->loadMissing('socialAccounts'),
            'twoFactorQrCode' => $request->user()->two_factor_secret
                ? $this->twoFactorService->qrCodeSvg($request->user(), $request->user()->two_factor_secret)
                : null,
            'recoveryCodes' => $request->user()->two_factor_recovery_codes ?? [],
            'kycSnapshot' => $this->kycStrength->snapshot($request->user()),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $googleLinked = $request->user()->hasSocialProvider('google');
        $validated['username'] = strtolower($validated['username']);
        $validated['email'] = strtolower($validated['email']);
        $validated['settings'] = $request->user()->mergeSettings([
            'security_alerts' => (bool) data_get($validated, 'settings.security_alerts', false),
            'monthly_reports' => (bool) data_get($validated, 'settings.monthly_reports', false),
            'marketing_emails' => (bool) data_get($validated, 'settings.marketing_emails', false),
            'login_with_google' => $googleLinked
                ? (bool) data_get($validated, 'settings.login_with_google', $request->user()->googleLoginEnabled())
                : false,
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateTheme(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'theme_preference' => ['required', 'in:light,dark,system'],
        ]);

        $request->user()->forceFill([
            'theme_preference' => $validated['theme_preference'],
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'theme_preference' => $validated['theme_preference'],
            ]);
        }

        return Redirect::back()->with('status', 'theme-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
