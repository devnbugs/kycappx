<x-layouts.dashboard-admin title="Customers" header="Customer Accounts">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Customer Directory</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950">Accounts, balances, and usage in one table</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            Review customer roles, wallet balances, verification volume, and active API key counts without leaving the operations cockpit.
        </p>
    </section>

    <section class="table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Customer</th>
                        <th class="px-6 py-4 text-left font-semibold">Roles</th>
                        <th class="px-6 py-4 text-left font-semibold">Wallet</th>
                        <th class="px-6 py-4 text-left font-semibold">Verifications</th>
                        <th class="px-6 py-4 text-left font-semibold">API Keys</th>
                        <th class="px-6 py-4 text-left font-semibold">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-950">{{ $customer->name }}</div>
                                <div class="text-xs text-slate-500">{{ $customer->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $customer->getRoleNames()->implode(', ') ?: 'Unassigned' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-950">NGN {{ number_format((float) ($customer->wallet?->balance ?? 0), 2) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ number_format($customer->verification_requests_count) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ number_format($customer->api_keys_count) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $customer->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">No customer accounts exist yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5">
            {{ $customers->links() }}
        </div>
    </section>
</x-layouts.dashboard-admin>
