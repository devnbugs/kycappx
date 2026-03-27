<x-layouts.dashboard-user title="New Verification" header="Create Verification">
    <section x-data="{ search: '' }" class="grid gap-4 xl:grid-cols-[0.95fr,1.05fr]">
        <div class="surface-card p-6 sm:p-8">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="section-kicker">Service Picker</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Choose the verification you want to run</h2>
                    </div>
                    <div class="badge-soft">Wallet: NGN {{ number_format((float) $wallet->balance, 2) }}</div>
                </div>

            <div class="mt-5">
                <x-input-label for="service-search" value="Search Services" />
                <x-text-input id="service-search" type="text" class="mt-2" x-model="search" placeholder="Search by name or code" />
            </div>

            <div class="mt-6 space-y-3">
                @forelse ($services as $service)
                    <a
                        href="{{ route('verifications.create', ['service' => $service->id]) }}"
                        x-show="@js(strtolower($service->name.' '.$service->code)).includes(search.toLowerCase())"
                        @class([
                            'block rounded-[1.5rem] border p-5 transition',
                            'border-slate-950 bg-slate-950 text-white shadow-lg' => optional($selectedService)->id === $service->id,
                            'border-slate-200 bg-white/80 text-slate-900 hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-100 dark:hover:border-slate-600 dark:hover:bg-slate-900' => optional($selectedService)->id !== $service->id,
                        ])
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-lg font-semibold">{{ $service->name }}</div>
                                <div class="mt-1 text-sm {{ optional($selectedService)->id === $service->id ? 'text-slate-200/80' : 'text-slate-500 dark:text-slate-400' }}">
                                    {{ $service->code }} · {{ strtoupper($service->country) }}
                                </div>
                            </div>
                            <x-ui.status-badge :value="$service->is_active ? 'Active' : 'Inactive'" :tone="$service->is_active ? 'success' : 'warning'" />
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2 text-xs">
                            <span class="badge-soft {{ optional($selectedService)->id === $service->id ? 'border-white/10 bg-white/10 text-white' : '' }}">
                                NGN {{ number_format((float) $service->default_price, 2) }}
                            </span>
                            <span class="badge-soft {{ optional($selectedService)->id === $service->id ? 'border-white/10 bg-white/10 text-white' : '' }}">
                                {{ count($service->required_fields ?? []) }} fields
                            </span>
                            @foreach ($serviceEngineVersions[$service->id] ?? [] as $engineVersion)
                                <span class="badge-soft {{ optional($selectedService)->id === $service->id ? 'border-white/10 bg-white/10 text-white' : '' }}">
                                    {{ $engineVersion }}
                                </span>
                            @endforeach
                        </div>
                    </a>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
                        There are no active verification services yet. Seed the service catalog or enable a provider from the admin workspace.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            @if ($selectedService)
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="section-kicker">Request Details</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $selectedService->name }}</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Fill in the required details below. Automated requests bill <span class="font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format((float) $selectedPrice, 2) }}</span> on success.
                            @if ($discountRate > 0)
                                <span class="mt-1 inline-flex items-center rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800 dark:bg-teal-500/10 dark:text-teal-200">{{ rtrim(rtrim(number_format($discountRate, 2), '0'), '.') }}% user discount applied</span>
                            @endif
                        </p>
                        @if (count($serviceEngineVersions[$selectedService->id] ?? []))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($serviceEngineVersions[$selectedService->id] as $engineVersion)
                                    <span class="badge-soft">{{ $engineVersion }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <x-ui.status-badge :value="$selectedService->code" tone="info" />
                </div>

                <div class="mt-5 rounded-[1.25rem] border border-slate-200/80 bg-slate-50/80 px-4 py-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                    Saved your KYC bio already?
                    <a href="{{ route('kyc.edit') }}" class="font-semibold text-slate-950 underline underline-offset-4 dark:text-slate-50">Open the KYC page</a>
                    to keep NIN, BVN, phone, and address details ready for auto-prefill. Verification submits directly to the active identity engine for this service and returns the saved outcome immediately.
                </div>

                <form method="POST" action="{{ route('verifications.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $selectedService->id }}">

                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ($fieldBlueprints as $field)
                            <flux:field @class(['md:col-span-2' => in_array($field['name'], ['company_name', 'payload_json'], true)])>
                                <flux:label>{{ $field['label'] }}</flux:label>
                                @if ($field['type'] === 'textarea')
                                    <flux:textarea :id="$field['name']" :name="$field['name']" rows="7">{{ old($field['name'], data_get($fieldDefaults, $field['name'])) }}</flux:textarea>
                                @else
                                    <flux:input
                                        :id="$field['name']"
                                        :name="$field['name']"
                                        :type="$field['type']"
                                        :value="old($field['name'], data_get($fieldDefaults, $field['name']))"
                                        :placeholder="$field['placeholder'] ?: null"
                                    />
                                @endif
                                <flux:error :name="$field['name']" />
                                <flux:description>
                                    {{ $field['helper'] }}
                                    @if ($field['required'])
                                        <span class="font-semibold text-slate-950 dark:text-slate-50">Required</span>
                                    @endif
                                </flux:description>
                            </flux:field>
                        @endforeach
                    </div>

                    <flux:error name="service_id" />

                    <div class="flex flex-wrap gap-3 pt-2">
                        <x-ui.button type="submit">Submit Verification</x-ui.button>
                        <a href="{{ route('verifications.index') }}">
                            <x-ui.button variant="secondary">Back to Requests</x-ui.button>
                        </a>
                    </div>
                </form>
            @else
                <div class="flex h-full flex-col items-start justify-center rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-10">
                    <p class="section-kicker">Next Step</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Pick a service to reveal the correct form.</h2>
                    <p class="mt-3 max-w-lg text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Each service has its own payload requirements and response layout. Choose one from the left and we will show the right input fields, pricing, and available engine version(s).
                    </p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.dashboard-user>
