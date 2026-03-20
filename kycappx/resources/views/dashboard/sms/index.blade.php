<x-layouts.dashboard-user title="SMS" header="Bulk SMS Sender">
    <section class="grid gap-4 xl:grid-cols-[0.95fr,1.05fr]">
        <div class="surface-card p-6 sm:p-8">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="section-kicker">Instant Send</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Send single or bulk SMS from the workspace</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Use one recipient per line or separate numbers with commas. Squad handles the delivery and this workspace stores the request history.
                    </p>
                </div>
                <x-ui.status-badge :value="$smsEnabled ? 'Live' : 'Setup Required'" :tone="$smsEnabled ? 'success' : 'warning'" />
            </div>

            <form method="POST" action="{{ route('sms.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <x-input-label for="sender_id" value="Sender ID" />
                    <x-text-input id="sender_id" name="sender_id" type="text" class="mt-2" :value="old('sender_id', $defaultSenderId)" />
                    <x-input-error :messages="$errors->get('sender_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="recipients" value="Recipients" />
                    <textarea id="recipients" name="recipients" rows="6" class="mt-2 w-full rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-slate-500 dark:focus:ring-slate-700">{{ old('recipients') }}</textarea>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Example: `08030000000` on each line, or `08030000000, 08031111111`.</p>
                    <x-input-error :messages="$errors->get('recipients')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="message" value="Message" />
                    <textarea id="message" name="message" rows="5" class="mt-2 w-full rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-slate-500 dark:focus:ring-slate-700">{{ old('message') }}</textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-ui.button type="submit" :disabled="! $smsEnabled">Send SMS Batch</x-ui.button>
                    <a href="{{ route('kyc.edit') }}">
                        <x-ui.button variant="secondary">Update KYC Phone</x-ui.button>
                    </a>
                </div>
            </form>

            @unless ($smsEnabled)
                <div class="mt-5 rounded-[1.5rem] border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/50 dark:text-amber-200">
                    {{ ! $gatewayConfigured ? 'Add `SQUAD_SECRET_KEY` to the environment before sending SMS.' : (! $siteSettings->sms_enabled ? 'SMS sending has been disabled from site settings.' : 'The Squad SMS product toggle is currently disabled.') }}
                </div>
            @endunless
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="section-kicker">Template Builder</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Save reusable Squad SMS templates</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Templates are helpful for onboarding alerts, verification updates, and wallet notifications that repeat often.
                    </p>
                </div>
                <x-ui.status-badge :value="$templatesEnabled ? 'Enabled' : 'Optional'" :tone="$templatesEnabled ? 'success' : 'info'" />
            </div>

            <form method="POST" action="{{ route('sms.templates.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <x-input-label for="name" value="Template Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-2" :value="old('name')" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <x-text-input id="description" name="description" type="text" class="mt-2" :value="old('description')" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="template_message" value="Template Message" />
                    <textarea id="template_message" name="template_message" rows="5" class="mt-2 w-full rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-slate-500 dark:focus:ring-slate-700">{{ old('template_message') }}</textarea>
                    <x-input-error :messages="$errors->get('template_message')" class="mt-2" />
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-ui.button type="submit" :disabled="! $templatesEnabled">Create Template</x-ui.button>
                </div>
            </form>
        </div>
    </section>

    <section class="table-shell">
        <div class="px-6 py-5">
            <p class="section-kicker">Recent Activity</p>
            <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">SMS logs and template actions</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Action</th>
                        <th class="px-6 py-4 text-left font-semibold">Reference</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Sender</th>
                        <th class="px-6 py-4 text-left font-semibold">Remote Ref</th>
                        <th class="px-6 py-4 text-left font-semibold">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dispatches as $dispatch)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-950 dark:text-slate-50">{{ str($dispatch->action)->headline() }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $dispatch->title ?: ($dispatch->message ? str($dispatch->message)->limit(70) : 'No preview') }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $dispatch->reference }}</td>
                            <td class="px-6 py-4">
                                <x-ui.status-badge
                                    :value="$dispatch->status"
                                    :tone="match ($dispatch->status) {
                                        'success' => 'success',
                                        'failed' => 'danger',
                                        default => 'warning',
                                    }"
                                />
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $dispatch->sender_id ?: 'N/A' }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $dispatch->remote_reference ?: 'Pending' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $dispatch->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No SMS activity has been recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5 dark:border-slate-800">
            {{ $dispatches->links() }}
        </div>
    </section>
</x-layouts.dashboard-user>
