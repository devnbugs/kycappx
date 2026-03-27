<x-layouts.dashboard-admin title="Services" header="Verification Services">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Service Catalog</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Pricing, activation, engine routing, and response layout</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            Update service pricing, toggle availability, assign the primary and fallback engine version for each verification, and control the response layout customers receive.
        </p>
        @unless ($canManageServices ?? false)
            <div class="mt-4 rounded-[1.25rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                This page is in read-only mode for your account. Only admins with the verification-service permission can save changes.
            </div>
        @endunless
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($services as $service)
            @php($editable = $canManageServices ?? false)
            @php($serviceProviders = $supportedProviders[$service->id] ?? [])
            <form method="POST" action="{{ route('admin.services.update', $service) }}" class="surface-card p-6 sm:p-8">
                @csrf
                @method('PUT')

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">{{ $service->code }}</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $service->country }} · {{ strtoupper($service->type) }} · {{ number_format($service->verification_requests_count) }} requests submitted</div>
                        @if (count($serviceProviders))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($serviceProviders as $provider)
                                    <span class="badge-soft">{{ $provider['label'] }} · {{ $provider['public'] }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <x-ui.status-badge :value="$service->is_active ? 'Active' : 'Inactive'" :tone="$service->is_active ? 'success' : 'warning'" />
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label :for="'name-'.$service->id" value="Service Name" />
                        <x-text-input :id="'name-'.$service->id" name="name" type="text" class="mt-2" :value="old('name', $service->name)" @disabled(! $editable) />
                    </div>
                    <div>
                        <x-input-label :for="'type-'.$service->id" value="Type" />
                        <x-text-input :id="'type-'.$service->id" name="type" type="text" class="mt-2" :value="old('type', $service->type)" @disabled(! $editable) />
                    </div>
                    <div>
                        <x-input-label :for="'country-'.$service->id" value="Country" />
                        <x-text-input :id="'country-'.$service->id" name="country" type="text" maxlength="2" class="mt-2 uppercase" :value="old('country', $service->country)" @disabled(! $editable) />
                    </div>
                    <div>
                        <x-input-label :for="'default_price-'.$service->id" value="Sell Price" />
                        <x-text-input :id="'default_price-'.$service->id" name="default_price" type="number" step="0.01" class="mt-2" :value="old('default_price', $service->default_price)" @disabled(! $editable) />
                    </div>
                    <div>
                        <x-input-label :for="'default_cost-'.$service->id" value="Provider Cost" />
                        <x-text-input :id="'default_cost-'.$service->id" name="default_cost" type="number" step="0.01" class="mt-2" :value="old('default_cost', $service->default_cost)" @disabled(! $editable) />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label :for="'required_fields-'.$service->id" value="Required Fields" />
                        <x-text-input :id="'required_fields-'.$service->id" name="required_fields" type="text" class="mt-2" :value="old('required_fields', collect($service->required_fields ?? [])->implode(', '))" placeholder="number, first_name, last_name" @disabled(! $editable) />
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Separate fields with commas. These power the customer request form and validation hints.</p>
                    </div>
                    <div>
                        <x-input-label :for="'primary-engine-'.$service->id" value="Primary Engine" />
                        <select id="{{ 'primary-engine-'.$service->id }}" name="primaryEngine" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" @disabled(! $editable || count($serviceProviders) === 0)>
                            <option value="">No primary engine</option>
                            @foreach ($serviceProviders as $provider)
                                <option value="{{ $provider['code'] }}" @selected(old('primaryEngine', data_get($service->engine_preferences, '0')) === $provider['code'])>{{ $provider['label'] }} · {{ $provider['public'] }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('primaryEngine')" />
                    </div>
                    <div>
                        <x-input-label :for="'secondary-engine-'.$service->id" value="Fallback Engine" />
                        <select id="{{ 'secondary-engine-'.$service->id }}" name="secondaryEngine" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" @disabled(! $editable || count($serviceProviders) <= 1)>
                            <option value="">No fallback engine</option>
                            @foreach ($serviceProviders as $provider)
                                <option value="{{ $provider['code'] }}" @selected(old('secondaryEngine', data_get($service->engine_preferences, '1')) === $provider['code'])>{{ $provider['label'] }} · {{ $provider['public'] }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('secondaryEngine')" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label :for="'response-template-'.$service->id" value="Response Template" />
                        <select id="{{ 'response-template-'.$service->id }}" name="responseTemplate" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" @disabled(! $editable)>
                            @foreach ($responseTemplates as $templateCode => $templateLabel)
                                <option value="{{ $templateCode }}" @selected(old('responseTemplate', $service->response_template ?: 'auto') === $templateCode)>{{ $templateLabel }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Choose how saved responses should render in the user response page, download, and print sheet.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('responseTemplate')" />
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.toggle
                        name="is_active"
                        :checked="(bool) old('is_active', $service->is_active)"
                        label="Service is active for customers"
                        description="Turn this off to hide the service from the workspace without deleting its history."
                        :disabled="! $editable"
                    />
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="submit" :disabled="! $editable">Save Service</x-ui.button>
                    <span class="badge-soft">Code: {{ $service->code }}</span>
                </div>
            </form>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300 xl:col-span-2">
                No verification services are configured yet.
            </div>
        @endforelse
    </section>
</x-layouts.dashboard-admin>
