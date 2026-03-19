<x-layouts.dashboard-admin title="Services" header="Verification Services">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Service Catalog</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950">Configured verification products</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            Review service status, pricing, required fields, and request volume before enabling or repricing an offering.
        </p>
    </section>

    <section class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
        @forelse ($services as $service)
            <div class="metric-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold text-slate-950">{{ $service->name }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $service->code }} · {{ strtoupper($service->country) }}</div>
                    </div>
                    <x-ui.status-badge :value="$service->is_active ? 'Active' : 'Inactive'" :tone="$service->is_active ? 'success' : 'warning'" />
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Sell Price</div>
                        <div class="mt-2 text-lg font-semibold text-slate-950">NGN {{ number_format((float) $service->default_price, 2) }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Provider Cost</div>
                        <div class="mt-2 text-lg font-semibold text-slate-950">NGN {{ number_format((float) $service->default_cost, 2) }}</div>
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    Requests submitted: <span class="font-semibold text-slate-950">{{ number_format($service->verification_requests_count) }}</span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (($service->required_fields ?? []) as $field)
                        <span class="badge-soft">{{ \Illuminate\Support\Str::headline($field) }}</span>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-sm text-slate-500 xl:col-span-3">
                No verification services are configured yet.
            </div>
        @endforelse
    </section>
</x-layouts.dashboard-admin>
