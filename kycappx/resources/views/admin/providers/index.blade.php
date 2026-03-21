<x-layouts.dashboard-admin title="Providers" header="Provider Health & Controls">
    @unless ($canManageProviders ?? false)
        <section class="surface-card p-6 sm:p-8">
            <div class="rounded-[1.25rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                This page is in read-only mode for your account. Only admins with the provider-management permission can save changes.
            </div>
        </section>
    @endunless

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

                <div class="mt-5 flex flex-wrap gap-2 text-xs">
                    <span class="badge-soft">{{ $provider['enabled_products'] }} enabled</span>
                    <span class="badge-soft">{{ $provider['products'] }} catalog products</span>
                </div>
            </div>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($providerConfigs as $config)
            @php($catalog = config("services.{$config->provider}.products", []))
            @php($editable = $canManageProviders ?? false)
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
                        <x-text-input :id="'priority-'.$config->id" name="priority" type="number" min="1" max="99" class="mt-2" :value="old('priority', $config->priority)" @disabled(! $editable) />
                    </div>
                    <div>
                        <x-input-label :for="'channel-'.$config->id" value="Channel" />
                        <x-text-input :id="'channel-'.$config->id" name="channel" type="text" class="mt-2" :value="old('channel', data_get($config->config, 'channel'))" placeholder="identity" @disabled(! $editable) />
                    </div>
                    <div>
                        <x-input-label :for="'mode-'.$config->id" value="Mode" />
                        <x-text-input :id="'mode-'.$config->id" name="mode" type="text" class="mt-2" :value="old('mode', data_get($config->config, 'mode', 'live'))" placeholder="live or sandbox" @disabled(! $editable) />
                    </div>
                    <div>
                        <x-input-label :for="'timeout-'.$config->id" value="Timeout (seconds)" />
                        <x-text-input :id="'timeout-'.$config->id" name="timeout_seconds" type="number" min="5" max="120" class="mt-2" :value="old('timeout_seconds', data_get($config->config, 'timeout_seconds', 30))" @disabled(! $editable) />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label :for="'default-product-'.$config->id" value="Default Product" />
                        <select id="{{ 'default-product-'.$config->id }}" name="default_product" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" @disabled(! $editable)>
                            <option value="">Select a default product</option>
                            @foreach ($catalog as $productKey => $product)
                                <option value="{{ $productKey }}" @selected(old('default_product', data_get($config->config, 'default_product')) === $productKey)>{{ $product['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label :for="'notes-'.$config->id" value="Notes" />
                        <x-text-input :id="'notes-'.$config->id" name="notes" type="text" class="mt-2" :value="old('notes', data_get($config->config, 'notes'))" placeholder="Fallback or operational notes" @disabled(! $editable) />
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.toggle
                        name="is_active"
                        :checked="(bool) old('is_active', $config->is_active)"
                        label="Enable this provider"
                        description="Only active providers are eligible for orchestration or wallet flows."
                        :disabled="! $editable"
                    />
                </div>

                @if (count(data_get($config->config, 'country_scope', [])) || $config->provider === 'prembly')
                    <div class="mt-5">
                        <div class="text-sm font-semibold text-slate-950 dark:text-slate-50">Country Scope</div>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach (['NG' => 'Nigeria', 'US' => 'United States'] as $countryCode => $countryLabel)
                                <label class="inline-flex items-center gap-3 rounded-full border px-4 py-2 text-sm text-slate-700 dark:text-slate-200" style="border-color: var(--ui-border); background: var(--ui-panel-strong);">
                                    <input
                                        type="checkbox"
                                        name="country_scope[]"
                                        value="{{ $countryCode }}"
                                        class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500"
                                        @checked(in_array($countryCode, old('country_scope', data_get($config->config, 'country_scope', [])), true))
                                        @disabled(! $editable)
                                    >
                                    <span>{{ $countryLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6">
                    <div class="text-sm font-semibold text-slate-950 dark:text-slate-50">Product Toggles</div>
                    <div class="mt-3 grid gap-3">
                        @foreach ($catalog as $productKey => $product)
                            @php($descriptor = collect([
                                data_get($product, 'method') && data_get($product, 'path') ? strtoupper((string) data_get($product, 'method')).' '.data_get($product, 'path') : null,
                                data_get($product, 'notes'),
                            ])->filter()->implode(' · '))
                            <x-ui.toggle
                                :name="'enabled_products['.$productKey.']'"
                                :checked="(bool) old('enabled_products.'.$productKey, data_get($config->config, 'enabled_products.'.$productKey, data_get($product, 'required', false)))"
                                :label="$product['label']"
                                :description="$descriptor"
                                :disabled="! $editable"
                            />
                            @if (data_get($product, 'docs_url'))
                                <div class="-mt-1 ps-4 text-xs text-slate-500 dark:text-slate-400">
                                    <a href="{{ data_get($product, 'docs_url') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-slate-700 underline underline-offset-4 dark:text-slate-200">
                                        Open docs
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="submit" :disabled="! $editable">Save Provider</x-ui.button>
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
