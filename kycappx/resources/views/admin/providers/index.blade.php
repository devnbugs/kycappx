<x-layouts.dashboard-admin title="Providers" header="Provider Health">
    <section class="grid gap-4 lg:grid-cols-3">
        @foreach ($providerHealth as $provider)
            <div class="metric-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold text-slate-950">{{ $provider['name'] }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $provider['base_url'] }}</div>
                    </div>
                    <x-ui.status-badge :value="$provider['configured'] ? 'Configured' : 'Missing Secrets'" :tone="$provider['configured'] ? 'success' : 'warning'" />
                </div>

                <div class="mt-6 text-sm leading-6 text-slate-600">
                    {{ $provider['configured'] ? 'Environment credentials are present and this provider can be used once activated.' : 'Credentials are missing from the environment, so automation will fall back or pause for manual review.' }}
                </div>
            </div>
        @endforeach
    </section>

    <section class="table-shell">
        <div class="px-6 py-5">
            <p class="section-kicker">Activation Order</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Persisted provider configuration</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Provider</th>
                        <th class="px-6 py-4 text-left font-semibold">Priority</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Config</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($providerConfigs as $config)
                        <tr class="table-row">
                            <td class="px-6 py-4 font-semibold text-slate-950">{{ strtoupper($config->provider) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $config->priority }}</td>
                            <td class="px-6 py-4">
                                <x-ui.status-badge :value="$config->is_active ? 'Active' : 'Standby'" :tone="$config->is_active ? 'success' : 'warning'" />
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ json_encode($config->config) }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500">No provider activation records have been saved yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.dashboard-admin>
