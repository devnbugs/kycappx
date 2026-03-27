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
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class SocialAuthController extends Controller
{
    private const FLOW_SESSION_KEY = 'social_auth.flow';

    public function __construct(private SiteSettings $siteSettings)
    {
    }

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        abort_unless($provider === 'google', 404);
        abort_unless($this->siteSettings->current()->google_auth_enabled, 404);

        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route($request->user() ? 'profile.edit' : 'login')
                ->withErrors(['login' => 'Google sign-in is not configured yet. Please contact support.']);
        }

        $intent = $this->resolveIntent($request);

        if ($intent === 'link' && ! $request->user()) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Please sign in to your account before linking Google.']);
        }

        $request->session()->put(self::FLOW_SESSION_KEY, [
            'provider' => $provider,
            'intent' => $intent,
            'user_id' => $intent === 'link' ? $request->user()?->id : null,
        ]);

        return $this->googleDriver()->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless($provider === 'google', 404);
        abort_unless($this->siteSettings->current()->google_auth_enabled, 404);
        $flow = $this->pullFlow($request, $provider);

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

        $intent = (string) ($flow['intent'] ?? ($request->user() ? 'link' : 'login'));
        $email = Str::lower((string) $socialUser->getEmail());

        if ($intent === 'link' || $request->user()) {
            return $this->completeLinkingFlow($request, $provider, $socialUser, $email, $flow);
        }

        return $this->completeSignInFlow($request, $provider, $socialUser, $email);
    }

    public function completeProfile(Request $request): View|RedirectResponse
    {
        $user = $request->user()->loadMissing('socialAccounts');

        if (! $user->hasSocialProvider('google')) {
            return redirect()->route('profile.edit');
        }

        if (! $user->requiresSocialProfileCompletion()) {
            return redirect()->route('dashboard');
        }

        return view('auth.social-complete', [
            'user' => $user,
        ]);
    }

    public function storeCompletedProfile(Request $request): RedirectResponse
    {
        $user = $request->user()->loadMissing('socialAccounts');

        if (! $user->hasSocialProvider('google')) {
            return redirect()->route('profile.edit');
        }

        if (! $user->requiresSocialProfileCompletion()) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username,'.$user->id],
            'phone' => ['required', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'preferred_funding_provider' => ['nullable', 'in:paystack,kora,squad'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'username' => Str::lower($validated['username']),
            'phone' => $validated['phone'],
            'company_name' => $validated['company_name'] ?? null,
            'timezone' => $validated['timezone'],
            'preferred_funding_provider' => $validated['preferred_funding_provider'] ?? ($user->preferred_funding_provider ?: $this->siteSettings->current()->default_funding_provider),
            'settings' => $user->mergeSettings([
                'login_with_google' => true,
                'social_signup_completed' => true,
            ]),
        ])->save();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Google signup completed successfully. Your workspace is ready.');
    }

    private function completeSignInFlow(Request $request, string $provider, mixed $socialUser, string $email): RedirectResponse
    {
        $linkedAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->with('user')
            ->first();

        $user = $linkedAccount?->user;

        if (! $user && $email !== '') {
            $user = User::query()->where('email', $email)->first();
        }

        if ($user && $user->status !== 'active') {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'This account is currently unavailable. Please contact support.']);
        }

        if ($user && ! $user->googleLoginEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'login' => 'Google sign-in is turned off for this account. Sign in with your password and re-enable it from settings first.',
                ]);
        }

        if (! $user) {
            abort_unless($this->siteSettings->current()->registration_enabled, 403, 'New registrations are currently disabled.');

            $user = $this->createUserFromSocialProfile($socialUser->getName(), $email);
            event(new Registered($user));
        }

        $this->syncSocialAccount($user, $provider, $socialUser, $email);

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?? now(),
            'last_login_at' => now(),
            'settings' => $user->mergeSettings([
                'login_with_google' => true,
            ]),
        ])->save();

        return $this->finishLogin($request, $user, true);
    }

    private function completeLinkingFlow(Request $request, string $provider, mixed $socialUser, string $email, array $flow): RedirectResponse
    {
        $user = $request->user()
            ?? User::query()->find((int) ($flow['user_id'] ?? 0));

        if (! $user) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Your Google linking session expired. Please sign in and try again.']);
        }

        $linkedAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->with('user')
            ->first();

        if ($linkedAccount && ! $linkedAccount->user?->is($user)) {
            return redirect()
                ->route('profile.edit')
                ->withErrors([
                    'settings.login_with_google' => 'This Google account is already linked to another user.',
                ]);
        }

        $this->syncSocialAccount($user, $provider, $socialUser, $email);

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?? now(),
            'settings' => $user->mergeSettings([
                'login_with_google' => true,
            ]),
        ])->save();

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Google sign-in linked successfully.');
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
                'social_signup_completed' => false,
            ],
            'preferred_funding_provider' => $settings->default_funding_provider,
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

    private function finishLogin(Request $request, User $user, bool $remember = true): RedirectResponse
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $redirectTo = $user->requiresSocialProfileCompletion()
            ? route('social.profile.complete', absolute: false)
            : route('dashboard', absolute: false);

        if ($user->hasTwoFactorEnabled()) {
            Auth::guard('web')->logout();
            $request->session()->put([
                'two_factor.login.id' => $user->id,
                'two_factor.remember' => $remember,
                'url.intended' => $redirectTo,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        if ($user->requiresSocialProfileCompletion()) {
            return redirect()->route('social.profile.complete');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function resolveIntent(Request $request): string
    {
        if ($request->user()) {
            return 'link';
        }

        $intent = strtolower((string) $request->query('intent', 'login'));

        return match ($intent) {
            'register' => 'signup',
            'login', 'signup', 'link' => $intent,
            default => 'login',
        };
    }

    private function pullFlow(Request $request, string $provider): array
    {
        $flow = (array) $request->session()->pull(self::FLOW_SESSION_KEY, []);

        if (($flow['provider'] ?? null) !== $provider) {
            return [];
        }

        return $flow;
    }

    private function syncSocialAccount(User $user, string $provider, mixed $socialUser, string $email): void
    {
        $existingByProviderId = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingByProviderId && ! $existingByProviderId->user?->is($user)) {
            throw new RuntimeException('This social account is already linked to another user.');
        }

        SocialAccount::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $socialUser->getId(),
                'provider_email' => $email ?: null,
                'avatar_url' => $socialUser->getAvatar(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'expires_at' => $socialUser->expiresIn ? now()->addSeconds((int) $socialUser->expiresIn) : null,
            ],
        );
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
