<x-layouts.dashboard-admin title="Site Settings" header="Site Settings">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Platform Controls</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Manage the site-wide experience from the admin workspace</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            Configure branding, support contacts, feature availability, default theme behavior, and the messaging users see across the app.
        </p>
    </section>

    <form method="POST" action="{{ route('admin.settings.site.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="surface-card p-6 sm:p-8">
                <p class="section-kicker">Brand & Support</p>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label for="site_name" value="Site Name" />
                        <x-text-input id="site_name" name="site_name" type="text" class="mt-2" :value="old('site_name', $settings->site_name)" />
                        <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="site_tagline" value="Site Tagline" />
                        <x-text-input id="site_tagline" name="site_tagline" type="text" class="mt-2" :value="old('site_tagline', $settings->site_tagline)" />
                        <x-input-error :messages="$errors->get('site_tagline')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="support_email" value="Support Email" />
                        <x-text-input id="support_email" name="support_email" type="email" class="mt-2" :value="old('support_email', $settings->support_email)" />
                        <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="support_phone" value="Support Phone" />
                        <x-text-input id="support_phone" name="support_phone" type="text" class="mt-2" :value="old('support_phone', $settings->support_phone)" />
                        <x-input-error :messages="$errors->get('support_phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="default_currency" value="Default Currency" />
                        <x-text-input id="default_currency" name="default_currency" type="text" maxlength="3" class="mt-2 uppercase" :value="old('default_currency', $settings->default_currency)" />
                        <x-input-error :messages="$errors->get('default_currency')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="default_theme" value="Default Theme" />
                        <select id="default_theme" name="default_theme" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            @foreach (['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('default_theme', $settings->default_theme) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('default_theme')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="surface-card p-6 sm:p-8">
                <p class="section-kicker">Feature Switches</p>
                <div class="mt-6 grid gap-3 rounded-[1.5rem] border border-slate-200/80 p-4 dark:border-slate-700">
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="registration_enabled" value="0">
                        <input type="checkbox" name="registration_enabled" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked(old('registration_enabled', $settings->registration_enabled))>
                        <span>Allow new user registration</span>
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="wallet_funding_enabled" value="0">
                        <input type="checkbox" name="wallet_funding_enabled" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked(old('wallet_funding_enabled', $settings->wallet_funding_enabled))>
                        <span>Enable wallet funding flows</span>
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="verification_enabled" value="0">
                        <input type="checkbox" name="verification_enabled" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked(old('verification_enabled', $settings->verification_enabled))>
                        <span>Enable verification submissions</span>
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="dark_mode_enabled" value="0">
                        <input type="checkbox" name="dark_mode_enabled" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked(old('dark_mode_enabled', $settings->dark_mode_enabled))>
                        <span>Expose the dark mode switch to users</span>
                    </label>
                </div>

                <div class="mt-6">
                    <x-input-label for="footer_text" value="Footer Text" />
                    <x-text-input id="footer_text" name="footer_text" type="text" class="mt-2" :value="old('footer_text', $settings->footer_text)" />
                    <x-input-error :messages="$errors->get('footer_text')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Maintenance Messaging</p>
            <div class="mt-6">
                <x-input-label for="maintenance_message" value="Maintenance Message" />
                <textarea id="maintenance_message" name="maintenance_message" rows="4" class="mt-2 w-full rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-slate-500 dark:focus:ring-slate-700">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea>
                <x-input-error :messages="$errors->get('maintenance_message')" class="mt-2" />
            </div>
        </section>

        <section class="flex flex-wrap gap-3">
            <x-ui.button type="submit">Save Site Settings</x-ui.button>
            <a href="{{ route('admin.dashboard') }}">
                <x-ui.button variant="secondary">Back to Overview</x-ui.button>
            </a>
        </section>
    </form>
</x-layouts.dashboard-admin>
