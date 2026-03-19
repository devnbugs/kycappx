<x-layouts.dashboard-user title="API Keys" header="API Credential Management">
    <section class="grid gap-4 xl:grid-cols-[0.8fr,1.2fr]">
        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Key Generator</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Issue a new API key</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Keys are displayed once after creation. Store the raw token in your secret manager and use the prefix below for later audits.
            </p>

            <form method="POST" action="{{ route('api.keys.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <x-input-label for="name" value="Key Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-2" :value="old('name', 'Production Key')" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <x-ui.button type="submit">Generate New Key</x-ui.button>
            </form>

            @if (session('generated_api_key'))
                <div class="mt-6 rounded-[1.5rem] border border-amber-200 bg-amber-50 px-4 py-4 dark:border-amber-900/60 dark:bg-amber-950/50">
                    <div class="text-sm font-semibold text-amber-900">Copy this key now. It will not be shown again.</div>
                    <div class="mt-3 break-all rounded-2xl bg-white px-4 py-3 font-mono text-sm text-slate-800 dark:bg-slate-900 dark:text-slate-100">
                        {{ session('generated_api_key') }}
                    </div>
                </div>
            @endif
        </div>

        <div class="table-shell">
            <div class="px-6 py-5">
                <p class="section-kicker">Active Credentials</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">API keys linked to this account</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Name</th>
                            <th class="px-6 py-4 text-left font-semibold">Prefix</th>
                            <th class="px-6 py-4 text-left font-semibold">Abilities</th>
                            <th class="px-6 py-4 text-left font-semibold">Last Used</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($apiKeys as $apiKey)
                            <tr class="table-row">
                                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">{{ $apiKey->name }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $apiKey->prefix }}…</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ implode(', ', $apiKey->abilities ?? []) ?: 'No explicit abilities' }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $apiKey->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge :value="$apiKey->is_active ? 'Active' : 'Revoked'" :tone="$apiKey->is_active ? 'success' : 'danger'" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($apiKey->is_active)
                                        <form method="POST" action="{{ route('api.keys.destroy', $apiKey) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger">Revoke</x-ui.button>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No API keys have been created for this account yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200/80 px-6 py-5 dark:border-slate-800">
                {{ $apiKeys->links() }}
            </div>
        </div>
    </section>
</x-layouts.dashboard-user>
