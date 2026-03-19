<x-layouts.dashboard-admin :title="'Manage '.$customer->name" header="User Settings & Access">
    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="section-kicker">User Profile</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $customer->name }}</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Update this user’s identity, access level, password, wallet state, theme preference, and communication settings from one page.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="badge-soft">{{ '@'.$customer->username }}</span>
                <x-ui.status-badge :value="$customer->status" :tone="$customer->status === 'active' ? 'success' : 'warning'" />
                <x-ui.status-badge :value="$wallet->status" :tone="$wallet->status === 'active' ? 'success' : 'warning'" />
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.users.update', $customer) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="surface-card p-6 sm:p-8">
                <p class="section-kicker">Identity</p>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" value="Full Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-2" :value="old('name', $customer->name)" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="username" value="Username" />
                        <x-text-input id="username" name="username" type="text" class="mt-2" :value="old('username', $customer->username)" />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-2" :value="old('email', $customer->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-2" :value="old('phone', $customer->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="company_name" value="Company" />
                        <x-text-input id="company_name" name="company_name" type="text" class="mt-2" :value="old('company_name', $customer->company_name)" />
                        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="timezone" value="Timezone" />
                        <x-text-input id="timezone" name="timezone" type="text" class="mt-2" :value="old('timezone', $customer->timezone)" />
                        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="surface-card p-6 sm:p-8">
                <p class="section-kicker">Access & State</p>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="status" value="Account Status" />
                        <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            @foreach (['active' => 'Active', 'suspended' => 'Suspended', 'pending' => 'Pending'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $customer->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="wallet_status" value="Wallet Status" />
                        <select id="wallet_status" name="wallet_status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            @foreach (['active' => 'Active', 'frozen' => 'Frozen', 'closed' => 'Closed'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('wallet_status', $wallet->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('wallet_status')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="theme_preference" value="Theme Preference" />
                        <select id="theme_preference" name="theme_preference" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            @foreach (['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('theme_preference', $customer->theme_preference) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('theme_preference')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="preferred_funding_provider" value="Preferred Funding Provider" />
                        <select id="preferred_funding_provider" name="preferred_funding_provider" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            @foreach (['paystack' => 'Paystack', 'kora' => 'Kora'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('preferred_funding_provider', $customer->preferred_funding_provider) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('preferred_funding_provider')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label value="Role Access" />
                        <div class="mt-2 grid gap-2 rounded-[1.5rem] border border-slate-200/80 p-4 dark:border-slate-700">
                            @foreach ($roles as $role)
                                <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked(collect(old('roles', $customer->getRoleNames()->all()))->contains($role->name))>
                                    <span>{{ str($role->name)->headline() }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="surface-card p-6 sm:p-8">
                <p class="section-kicker">Security & Notifications</p>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="password" value="Reset Password" />
                        <x-text-input id="password" name="password" type="password" class="mt-2" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" value="Confirm Password" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="service_discount_rate" value="Service Discount Rate (%)" />
                        <x-text-input id="service_discount_rate" name="service_discount_rate" type="number" min="0" max="100" step="0.01" class="mt-2" :value="old('service_discount_rate', $customer->service_discount_rate)" />
                        <x-input-error :messages="$errors->get('service_discount_rate')" class="mt-2" />
                    </div>
                </div>

                @php($settings = old('settings', $customer->settingsPayload()))
                <div class="mt-6 grid gap-3 rounded-[1.5rem] border border-slate-200/80 p-4 dark:border-slate-700">
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="settings[security_alerts]" value="0">
                        <input type="checkbox" name="settings[security_alerts]" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked((bool) data_get($settings, 'security_alerts', false))>
                        <span>Security alert emails</span>
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="settings[monthly_reports]" value="0">
                        <input type="checkbox" name="settings[monthly_reports]" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked((bool) data_get($settings, 'monthly_reports', false))>
                        <span>Monthly usage reports</span>
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="settings[marketing_emails]" value="0">
                        <input type="checkbox" name="settings[marketing_emails]" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked((bool) data_get($settings, 'marketing_emails', false))>
                        <span>Marketing and feature updates</span>
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="deactivate_api_keys" value="0">
                        <input type="checkbox" name="deactivate_api_keys" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500">
                        <span>Deactivate all current API keys on save</span>
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                        <input type="hidden" name="reset_two_factor" value="0">
                        <input type="checkbox" name="reset_two_factor" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500">
                        <span>Reset two-factor authentication</span>
                    </label>
                </div>
            </div>

            <div class="surface-card p-6 sm:p-8">
                <p class="section-kicker">Wallet Adjustment</p>
                <div class="mt-4 rounded-[1.5rem] bg-slate-50 px-4 py-4 dark:bg-slate-900/70">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Current balance</div>
                    <div class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format((float) $wallet->balance, 2) }}</div>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <x-input-label for="wallet_adjustment" value="Adjustment Amount" />
                        <x-text-input id="wallet_adjustment" name="wallet_adjustment" type="number" step="0.01" class="mt-2" :value="old('wallet_adjustment', 0)" />
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Use a positive number to credit and a negative number to debit.</p>
                        <x-input-error :messages="$errors->get('wallet_adjustment')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="wallet_adjustment_note" value="Adjustment Note" />
                        <x-text-input id="wallet_adjustment_note" name="wallet_adjustment_note" type="text" class="mt-2" :value="old('wallet_adjustment_note')" placeholder="Explain why this wallet was adjusted" />
                        <x-input-error :messages="$errors->get('wallet_adjustment_note')" class="mt-2" />
                    </div>
                </div>
            </div>
        </section>

        <section class="flex flex-wrap items-center gap-3">
            <x-ui.button type="submit">Save User Changes</x-ui.button>
            <a href="{{ route('admin.users.index') }}">
                <x-ui.button variant="secondary">Back to Directory</x-ui.button>
            </a>
        </section>
    </form>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="table-shell">
            <div class="px-6 py-5">
                <p class="section-kicker">Recent API Keys</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Credential activity</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Name</th>
                            <th class="px-6 py-4 text-left font-semibold">Prefix</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Last Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentApiKeys as $apiKey)
                            <tr class="table-row">
                                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">{{ $apiKey->name }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $apiKey->prefix }}…</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge :value="$apiKey->is_active ? 'Active' : 'Revoked'" :tone="$apiKey->is_active ? 'success' : 'danger'" />
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $apiKey->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No API keys recorded for this user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-shell">
            <div class="px-6 py-5">
                <p class="section-kicker">Recent Verifications</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Latest request outcomes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Service</th>
                            <th class="px-6 py-4 text-left font-semibold">Reference</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentVerifications as $verification)
                            <tr class="table-row">
                                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">{{ $verification->service?->name ?? 'Unknown service' }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $verification->reference }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge :value="$verification->status" :tone="match ($verification->status) {
                                        'success' => 'success',
                                        'failed' => 'danger',
                                        'manual_review' => 'warning',
                                        default => 'info',
                                    }" />
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $verification->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No verification history for this user yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Dedicated Accounts</p>
            <div class="mt-5 space-y-3">
                @forelse ($customer->dedicatedVirtualAccounts as $account)
                    <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-950 dark:text-white">{{ strtoupper($account->provider) }}</div>
                                <div class="mt-1 font-mono text-sm text-slate-600 dark:text-slate-300">{{ $account->account_number ?: 'Pending assignment' }}</div>
                            </div>
                            <x-ui.status-badge :value="$account->status" :tone="$account->status === 'active' ? 'success' : 'warning'" />
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                        No dedicated accounts assigned to this user yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Linked Social Accounts</p>
            <div class="mt-5 space-y-3">
                @forelse ($customer->socialAccounts as $socialAccount)
                    <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="font-semibold text-slate-950 dark:text-white">{{ ucfirst($socialAccount->provider) }}</div>
                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $socialAccount->provider_email ?: 'No provider email stored' }}</div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                        No social sign-ins linked to this user yet.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.dashboard-admin>
