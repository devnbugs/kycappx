<x-layouts.dashboard-admin title="Services" header="Verification Services">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Service Catalog</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Pricing, activation, and request inputs</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            Update service pricing, toggle availability, and control the input fields customers must provide for each verification product.
        </p>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($services as $service)
            <form method="POST" action="{{ route('admin.services.update', $service) }}" class="surface-card p-6 sm:p-8">
                @csrf
                @method('PUT')

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">{{ $service->code }}</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ number_format($service->verification_requests_count) }} requests submitted</div>
                    </div>
                    <x-ui.status-badge :value="$service->is_active ? 'Active' : 'Inactive'" :tone="$service->is_active ? 'success' : 'warning'" />
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label :for="'name-'.$service->id" value="Service Name" />
                        <x-text-input :id="'name-'.$service->id" name="name" type="text" class="mt-2" :value="old('name', $service->name)" />
                    </div>
                    <div>
                        <x-input-label :for="'type-'.$service->id" value="Type" />
                        <x-text-input :id="'type-'.$service->id" name="type" type="text" class="mt-2" :value="old('type', $service->type)" />
                    </div>
                    <div>
                        <x-input-label :for="'country-'.$service->id" value="Country" />
                        <x-text-input :id="'country-'.$service->id" name="country" type="text" maxlength="2" class="mt-2 uppercase" :value="old('country', $service->country)" />
                    </div>
                    <div>
                        <x-input-label :for="'default_price-'.$service->id" value="Sell Price" />
                        <x-text-input :id="'default_price-'.$service->id" name="default_price" type="number" step="0.01" class="mt-2" :value="old('default_price', $service->default_price)" />
                    </div>
                    <div>
                        <x-input-label :for="'default_cost-'.$service->id" value="Provider Cost" />
                        <x-text-input :id="'default_cost-'.$service->id" name="default_cost" type="number" step="0.01" class="mt-2" :value="old('default_cost', $service->default_cost)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label :for="'required_fields-'.$service->id" value="Required Fields" />
                        <x-text-input :id="'required_fields-'.$service->id" name="required_fields" type="text" class="mt-2" :value="old('required_fields', collect($service->required_fields ?? [])->implode(', '))" placeholder="bvn, first_name, last_name, dob" />
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Separate fields with commas. These power the customer request form and validation hints.</p>
                    </div>
                </div>

                <label class="mt-5 inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-slate-950 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" @checked(old('is_active', $service->is_active))>
                    <span>Service is active for customers</span>
                </label>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="submit">Save Service</x-ui.button>
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
