<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Security\TurnstileService;
use App\Services\SiteSettings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function __construct(
        private SiteSettings $siteSettings,
        private TurnstileService $turnstile,
    ) {
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $settings = $this->siteSettings->current();

        abort_unless($settings->registration_enabled, 403, 'New account registration is currently disabled.');
        $this->turnstile->ensureValidRequest($request, 'register');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => Str::lower($request->string('username')->value()),
            'email' => Str::lower($request->string('email')->value()),
            'timezone' => 'UTC',
            'theme_preference' => $settings->default_theme,
            'status' => 'active',
            'settings' => [
                'security_alerts' => true,
                'monthly_reports' => true,
                'marketing_emails' => false,
                'login_with_google' => true,
            ],
            'preferred_funding_provider' => $settings->default_funding_provider,
            'password' => Hash::make($request->password),
        ]);

        Role::findOrCreate('customer');
        $user->assignRole('customer');
        $user->wallet()->create([
            'currency' => $settings->default_currency,
            'balance' => 0,
            'status' => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
