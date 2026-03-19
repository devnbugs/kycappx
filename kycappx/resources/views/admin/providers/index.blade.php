<x-layouts.dashboard-admin title="Providers" header="Provider Health & Controls">
    <section class="grid gap-4 lg:grid-cols-3">
        @foreach ($providerHealth as $provider)
            <div class="metric-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">{{ $provider['name'] }}</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $provider['base_url'] }}</div>
                    </div>
                    <x-ui.status-badge :value="$provider['configured'] ? 'Configured' : 'Missing Secrets'" :tone="$provider['configured'] ? 'success' : 'warning'" />
                </div>

                <div class="mt-6 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    {{ $provider['configured'] ? 'Environment credentials are present and ready for live traffic once the provider is active.' : 'Credentials are missing from the environment, so the platform will pause or fall back instead of dispatching live requests.' }}
                </div>
            </div>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($providerConfigs as $config)
            <form method="POST" action="{{ route('admin.providers.update', $config) }}" class="surface-card p-6 sm:p-8">
                @csrf
                @method('PUT')

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="section-kicker">Provider</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ strtoupper($config->provider) }}</h2>
                    </div>
                    <x-ui.status-badge :value="$config->is_active ? 'Active' : 'Standby'" :tone="$config->is_active ? 'success' : 'warning'" />
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label :for="'priority-'.$config->id" value="Priority" />
                        <x-text-input :id="'priority-'.$config->id" name="priority" type="number" min="1" max="99" class="mt-2" :value="old('priority', $config->priority)" />
                    </div>
                    <div>
                        <x-input-label :for="'channel-'.$config->id" value="Channel" />
                        <x-text-input :id="'channel-'.$config->id" name="channel" type="text" class="mt-2" :value="old('channel', data_get($config->config, 'channel'))" placeholder="identity" />
                    </div>
                    <div>
                        <x-input-label :for="'mode-'.$config->id" value="Mode" />
                        <x-text-input :id="'mode-'.$config->id" name="mode" type="text" class="mt-2" :value="old('mode', data_get($config->config, 'mode', 'live'))" placeholder="live or sandbox" />
                    </div>
                    <div>
                        <x-input-label :for="'timeout-'.$config->id" value="Timeout (seconds)" />
                        <x-text-input :id="'timeout-'.$config->id" name="timeout_seconds" type="number" min="5" max="120" class="mt-2" :value="old('timeout_seconds', data_get($config->config, 'timeout_seconds', 30))" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label :for="'notes-'.$config->id" value="Notes" />
                        <x-text-input :id="'notes-'.$config->id" name="notes" type="text" class="mt-2" :value="old('notes', data_get($config->config, 'notes'))" placeholder="Fallback after Prembly or use for manual escalation" />
                    </div>
                </div>

                <label class="mt-5 inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked(old('is_active', $config->is_active))>
                    <span>Enable this provider for orchestration</span>
                </label>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="submit">Save Provider</x-ui.button>
                    <span class="badge-soft">Priority {{ $config->priority }}</span>
                </div>
            </form>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300 xl:col-span-2">
                No provider activation records have been saved yet.
            </div>
        @endforelse
    </section>
</x-layouts.dashboard-admin>
