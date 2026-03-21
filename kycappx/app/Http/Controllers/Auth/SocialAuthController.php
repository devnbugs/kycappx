<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SiteSettings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class SocialAuthController extends Controller
{
    public function __construct(private SiteSettings $siteSettings)
    {
    }

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless($provider === 'google', 404);
        abort_unless($this->siteSettings->current()->google_auth_enabled, 404);

        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google sign-in is not configured yet. Please contact support.']);
        }

        return $this->googleDriver()->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless($provider === 'google', 404);
        abort_unless($this->siteSettings->current()->google_auth_enabled, 404);

        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google sign-in is not configured yet. Please contact support.']);
        }

        try {
            $socialUser = $this->googleDriver()->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google sign-in could not be completed. Please try again.']);
        }

        $email = Str::lower((string) $socialUser->getEmail());
        $linkedAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->with('user')
            ->first();

        $user = $linkedAccount?->user;

        if (! $user && $email !== '') {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            abort_unless($this->siteSettings->current()->registration_enabled, 403, 'New registrations are currently disabled.');

            $user = $this->createUserFromSocialProfile($socialUser->getName(), $email);
            event(new Registered($user));
        }

        if ($user->status !== 'active') {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'This account is currently unavailable. Please contact support.']);
        }

        SocialAccount::query()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'user_id' => $user->id,
                'provider_email' => $email ?: null,
                'avatar_url' => $socialUser->getAvatar(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'expires_at' => $socialUser->expiresIn ? now()->addSeconds((int) $socialUser->expiresIn) : null,
            ],
        );

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?? now(),
            'last_login_at' => now(),
        ])->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($user->hasTwoFactorEnabled()) {
            Auth::guard('web')->logout();
            $request->session()->put([
                'two_factor.login.id' => $user->id,
                'two_factor.remember' => true,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function createUserFromSocialProfile(?string $displayName, string $email): User
    {
        $settings = $this->siteSettings->current();
        $name = trim((string) $displayName) !== '' ? trim((string) $displayName) : 'Google User';
        $username = $this->uniqueUsername($email !== '' ? Str::before($email, '@') : Str::slug($name, '.'));

        $user = User::query()->create([
            'name' => $name,
            'username' => $username,
            'email' => $email !== '' ? $email : $username.'@google.oauth.local',
            'timezone' => 'UTC',
            'theme_preference' => $settings->default_theme,
            'status' => 'active',
            'settings' => [
                'security_alerts' => true,
                'monthly_reports' => true,
                'marketing_emails' => false,
                'login_with_google' => true,
            ],
            'password' => Hash::make(Str::password(32)),
        ]);

        Role::findOrCreate('customer');
        $user->assignRole('customer');
        $user->wallet()->create([
            'currency' => $settings->default_currency,
            'balance' => 0,
            'status' => 'active',
        ]);

        return $user;
    }

    private function uniqueUsername(string $seed): string
    {
        $base = Str::lower(preg_replace('/[^a-z0-9._-]/', '', $seed) ?: 'user');
        $candidate = $base;
        $counter = 1;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = $base.$counter;
            $counter++;

            if ($counter > 50) {
                throw new RuntimeException('Could not generate a unique username for this Google account.');
            }
        }

        return $candidate;
    }

    private function googleDriver(): AbstractProvider
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver
            ->redirectUrl($this->googleRedirectUrl())
            ->scopes(['openid', 'profile', 'email'])
            ->stateless();
    }

    private function googleIsConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    private function googleRedirectUrl(): string
    {
        return (string) (config('services.google.redirect')
            ?: route('social.callback', ['provider' => 'google'], absolute: true));
    }
}
