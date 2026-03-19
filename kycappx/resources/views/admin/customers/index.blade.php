<x-layouts.dashboard-admin title="Users" header="User Management">
    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="section-kicker">Account Control</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Manage every registered user from one directory</h2>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Search, filter, and open any account to update profile settings, roles, passwords, wallet status, API key access, and notification preferences.
                </p>
            </div>
            <a href="{{ route('admin.settings.site') }}">
                <x-ui.button variant="secondary">Platform Settings</x-ui.button>
            </a>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="mt-6 grid gap-4 lg:grid-cols-[1.4fr,0.7fr,0.7fr,auto]">
            <div>
                <x-input-label for="search" value="Search by name, username, or email" />
                <x-text-input id="search" name="search" type="text" class="mt-2" :value="$filters['search']" placeholder="Search users" />
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    @foreach (['all' => 'All statuses', 'active' => 'Active', 'suspended' => 'Suspended', 'pending' => 'Pending'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    <option value="all">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected($filters['role'] === $role->name)>{{ str($role->name)->headline() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <x-ui.button type="submit">Apply</x-ui.button>
                <a href="{{ route('admin.users.index') }}">
                    <x-ui.button variant="secondary">Reset</x-ui.button>
                </a>
            </div>
        </form>
    </section>

    <section class="table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">User</th>
                        <th class="px-6 py-4 text-left font-semibold">Roles</th>
                        <th class="px-6 py-4 text-left font-semibold">Wallet</th>
                        <th class="px-6 py-4 text-left font-semibold">Verifications</th>
                        <th class="px-6 py-4 text-left font-semibold">API Keys</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Last Login</th>
                        <th class="px-6 py-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-950 dark:text-slate-50">{{ $customer->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ '@'.$customer->username }} · {{ $customer->email }}</div>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    <span class="badge-soft">{{ $customer->dedicatedVirtualAccounts->count() }} DVA</span>
                                    <span class="badge-soft">{{ $customer->socialAccounts->count() }} social</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $customer->getRoleNames()->implode(', ') ?: 'Unassigned' }}</td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format((float) ($customer->wallet?->balance ?? 0), 2) }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ ucfirst($customer->wallet?->status ?? 'No wallet') }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ number_format($customer->verification_requests_count) }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ number_format($customer->api_keys_count) }}</td>
                            <td class="px-6 py-4">
                                <x-ui.status-badge :value="$customer->status" :tone="$customer->status === 'active' ? 'success' : 'warning'" />
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $customer->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.users.edit', $customer) }}">
                                    <x-ui.button variant="secondary">Manage</x-ui.button>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="8" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No user accounts match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5 dark:border-slate-800">
            {{ $customers->links() }}
        </div>
    </section>
</x-layouts.dashboard-admin>
