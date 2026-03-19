<x-layouts.dashboard-admin title="Admin Overview" header="Operations Overview">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        <div class="metric-card">
            <div class="text-sm text-slate-500 dark:text-slate-400">Users</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($metrics['users']) }}</div>
            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                {{ number_format($metrics['customers']) }} customer accounts and {{ number_format($metrics['admins']) }} operators.
            </div>
        </div>

        <div class="metric-card">
            <div class="text-sm text-slate-500 dark:text-slate-400">Verification Queue</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($metrics['verifications']) }}</div>
            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                {{ number_format($metrics['pending_verifications']) }} still need provider or manual attention.
            </div>
        </div>

        <div class="metric-card">
            <div class="text-sm text-slate-500 dark:text-slate-400">Wallet Float</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format($metrics['wallet_balance'], 2) }}</div>
            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Combined balance across every registered wallet.
            </div>
        </div>

        <div class="metric-card">
            <div class="text-sm text-slate-500 dark:text-slate-400">Platform Health</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($metrics['active_services']) }}/{{ number_format(max($metrics['active_providers'], 1)) }}</div>
            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                {{ number_format($metrics['failed_webhooks']) }} webhook deliveries are still pending review.
            </div>
        </div>

        <div class="metric-card">
            <div class="text-sm text-slate-500 dark:text-slate-400">User Pro</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($metrics['user_pro_accounts']) }}</div>
            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Discount-enabled customers with stronger security expectations.
            </div>
        </div>

        <div class="metric-card">
            <div class="text-sm text-slate-500 dark:text-slate-400">Dedicated Accounts</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($metrics['dedicated_accounts']) }}</div>
            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Paystack and Kora account cards currently assigned.
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
        <div class="surface-card p-6 sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="section-kicker">Admin Control</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Every core function is reachable from one cockpit</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Manage users, role access, pricing, provider activation, site-wide settings, and operational logs without leaving the admin workspace.
                    </p>
                </div>
                <a href="{{ route('admin.settings.site') }}">
                    <x-ui.button>Open Site Settings</x-ui.button>
                </a>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <a href="{{ route('admin.users.index') }}" class="surface-card block p-5 transition hover:-translate-y-0.5">
                    <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">User Management</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Edit profile data, roles, passwords, wallet state, and per-user preferences.</div>
                </a>
                <a href="{{ route('admin.services.index') }}" class="surface-card block p-5 transition hover:-translate-y-0.5">
                    <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">Service Controls</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Toggle services, reprice requests, and change required verification fields.</div>
                </a>
                <a href="{{ route('admin.providers.index') }}" class="surface-card block p-5 transition hover:-translate-y-0.5">
                    <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">Provider Controls</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Set activation order, fallback behavior, and integration operating modes.</div>
                </a>
                <a href="{{ route('admin.logs.webhooks') }}" class="surface-card block p-5 transition hover:-translate-y-0.5">
                    <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">Webhook Monitoring</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Track retries, payload references, and unprocessed delivery events.</div>
                </a>
                <a href="{{ route('admin.logs.verifications') }}" class="surface-card block p-5 transition hover:-translate-y-0.5">
                    <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">Verification Queue</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Review recent verification outcomes and isolate manual-review cases fast.</div>
                </a>
                <a href="{{ route('admin.logs.audit') }}" class="surface-card block p-5 transition hover:-translate-y-0.5">
                    <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">Audit Trail</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">See exactly which admin changed user access, settings, or platform controls.</div>
                </a>
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Site Snapshot</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $siteSettingsSnapshot->site_name }}</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $siteSettingsSnapshot->site_tagline }}
            </p>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4 dark:bg-slate-900/70">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Registration</div>
                    <div class="mt-2"><x-ui.status-badge :value="$siteSettingsSnapshot->registration_enabled ? 'Enabled' : 'Disabled'" :tone="$siteSettingsSnapshot->registration_enabled ? 'success' : 'warning'" /></div>
                </div>
                <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4 dark:bg-slate-900/70">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Wallet Funding</div>
                    <div class="mt-2"><x-ui.status-badge :value="$siteSettingsSnapshot->wallet_funding_enabled ? 'Enabled' : 'Disabled'" :tone="$siteSettingsSnapshot->wallet_funding_enabled ? 'success' : 'warning'" /></div>
                </div>
                <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4 dark:bg-slate-900/70">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Verification Runs</div>
                    <div class="mt-2"><x-ui.status-badge :value="$siteSettingsSnapshot->verification_enabled ? 'Enabled' : 'Disabled'" :tone="$siteSettingsSnapshot->verification_enabled ? 'success' : 'warning'" /></div>
                </div>
                <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4 dark:bg-slate-900/70">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Dark Mode</div>
                    <div class="mt-2"><x-ui.status-badge :value="$siteSettingsSnapshot->dark_mode_enabled ? 'Available' : 'Locked Light'" :tone="$siteSettingsSnapshot->dark_mode_enabled ? 'success' : 'info'" /></div>
                </div>
                <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4 dark:bg-slate-900/70">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Google Auth</div>
                    <div class="mt-2"><x-ui.status-badge :value="$siteSettingsSnapshot->google_auth_enabled ? 'Enabled' : 'Disabled'" :tone="$siteSettingsSnapshot->google_auth_enabled ? 'success' : 'warning'" /></div>
                </div>
                <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4 dark:bg-slate-900/70">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">DVA</div>
                    <div class="mt-2"><x-ui.status-badge :value="$siteSettingsSnapshot->dva_enabled ? 'Live' : 'Disabled'" :tone="$siteSettingsSnapshot->dva_enabled ? 'success' : 'warning'" /></div>
                </div>
            </div>

            <div class="mt-6 rounded-[1.5rem] border border-dashed border-slate-300 px-4 py-4 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-300">
                <div class="font-semibold text-slate-950 dark:text-slate-50">Support Contact</div>
                <div class="mt-2">{{ $siteSettingsSnapshot->support_email ?: 'No support email configured yet.' }}</div>
                <div class="mt-1">{{ $siteSettingsSnapshot->support_phone ?: 'No support phone configured yet.' }}</div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[0.95fr,1.05fr]">
        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                <div>
                    <p class="section-kicker">Recent Users</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Newest accounts</h2>
                </div>
                <a href="{{ route('admin.users.index') }}">
                    <x-ui.button variant="secondary">Manage Users</x-ui.button>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">User</th>
                            <th class="px-6 py-4 text-left font-semibold">Roles</th>
                            <th class="px-6 py-4 text-left font-semibold">Wallet</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentCustomers as $customer)
                            <tr class="table-row">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-950 dark:text-slate-50">{{ $customer->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ '@'.$customer->username }} · {{ $customer->email }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="badge-soft">{{ $customer->socialAccounts->count() }} social</span>
                                        <span class="badge-soft">{{ $customer->dedicatedVirtualAccounts->count() }} DVA</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $customer->getRoleNames()->implode(', ') ?: 'Unassigned' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format((float) ($customer->wallet?->balance ?? 0), 2) }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge :value="$customer->status" :tone="$customer->status === 'active' ? 'success' : 'warning'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                <div>
                    <p class="section-kicker">Recent Admin Actions</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Latest configuration changes</h2>
                </div>
                <a href="{{ route('admin.logs.audit') }}">
                    <x-ui.button variant="secondary">Open Audit Log</x-ui.button>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Action</th>
                            <th class="px-6 py-4 text-left font-semibold">Actor</th>
                            <th class="px-6 py-4 text-left font-semibold">Target</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentAdminActions as $log)
                            <tr class="table-row">
                                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">{{ str($log->action)->replace('.', ' ')->headline() }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ class_basename((string) $log->target_type) }}#{{ $log->target_id }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $log->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No admin mutations have been recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                <div>
                    <p class="section-kicker">Recent Webhooks</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Latest delivery events</h2>
                </div>
                <a href="{{ route('admin.logs.webhooks') }}">
                    <x-ui.button variant="secondary">Open Logs</x-ui.button>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Provider</th>
                            <th class="px-6 py-4 text-left font-semibold">Reference</th>
                            <th class="px-6 py-4 text-left font-semibold">Processed</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentWebhooks as $log)
                            <tr class="table-row">
                                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">{{ strtoupper($log->provider) }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $log->reference ?: 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge :value="$log->processed ? 'Processed' : 'Pending'" :tone="$log->processed ? 'success' : 'warning'" />
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $log->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                <div>
                    <p class="section-kicker">Verification Feed</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Latest verification requests</h2>
                </div>
                <a href="{{ route('admin.logs.verifications') }}">
                    <x-ui.button variant="secondary">Open Queue</x-ui.button>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">User</th>
                            <th class="px-6 py-4 text-left font-semibold">Service</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentVerifications as $verification)
                            <tr class="table-row">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-950 dark:text-slate-50">{{ $verification->user?->name ?? 'Unknown user' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $verification->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $verification->service?->name ?? 'Unknown service' }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge
                                        :value="$verification->status"
                                        :tone="match ($verification->status) {
                                            'success' => 'success',
                                            'failed' => 'danger',
                                            'manual_review' => 'warning',
                                            default => 'info',
                                        }"
                                    />
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $verification->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layouts.dashboard-admin>
