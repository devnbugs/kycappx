<section>
    <header>
        <h2 class="text-lg font-medium text-slate-900 dark:text-slate-50">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
            {{ __("Update your account's profile information, workspace preferences, and notification settings.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="username" :value="__('Username')" />
                <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('username')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="email" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="company_name" :value="__('Company')" />
                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $user->company_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
            </div>

            <div>
                <x-input-label for="timezone" :value="__('Timezone')" />
                <x-text-input id="timezone" name="timezone" type="text" class="mt-1 block w-full" :value="old('timezone', $user->timezone)" />
                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
            </div>

            <div>
                <x-input-label for="theme_preference" :value="__('Theme Preference')" />
                <select id="theme_preference" name="theme_preference" class="mt-1 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    @foreach (['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('theme_preference', $user->theme_preference) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('theme_preference')" />
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="md:col-span-2">
                    <div>
                        <p class="text-sm mt-2 text-slate-800 dark:text-slate-200">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 dark:text-slate-300 dark:hover:text-slate-50">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-emerald-300">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @php($settings = old('settings', $user->settingsPayload()))
        <div class="rounded-[1.5rem] border border-slate-200/80 p-5 dark:border-slate-700">
            <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">Workspace Preferences</div>
            <div class="mt-4 grid gap-3">
                <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="settings[security_alerts]" value="0">
                    <input type="checkbox" name="settings[security_alerts]" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked((bool) data_get($settings, 'security_alerts', false))>
                    <span>Send security alert emails</span>
                </label>
                <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="settings[monthly_reports]" value="0">
                    <input type="checkbox" name="settings[monthly_reports]" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked((bool) data_get($settings, 'monthly_reports', false))>
                    <span>Receive monthly usage reports</span>
                </label>
                <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="settings[marketing_emails]" value="0">
                    <input type="checkbox" name="settings[marketing_emails]" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked((bool) data_get($settings, 'marketing_emails', false))>
                    <span>Receive product and feature updates</span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-600 dark:text-slate-300"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
