<x-guest-layout>
    <div class="space-y-8">
        <div>
            <p class="section-kicker">Complete Signup</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">Finish your Google account setup.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Your Google authentication is active already. Add the remaining details below so your workspace, verification forms, and wallet preferences are ready to use.
            </p>
        </div>

        <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/90 p-4 text-sm text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
            Signed in as <span class="font-semibold text-slate-950 dark:text-white">{{ $user->email }}</span>
        </div>

        <form method="POST" action="{{ route('social.profile.complete.store') }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <flux:field class="md:col-span-2">
                    <flux:label>Full Name</flux:label>
                    <flux:input name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" placeholder="Rabiu Salisu" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Username</flux:label>
                    <flux:input name="username" type="text" :value="old('username', $user->username)" required autocomplete="username" placeholder="rhsalisu" />
                    <flux:error name="username" />
                </flux:field>

                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input name="phone" type="text" :value="old('phone', $user->phone)" required autocomplete="tel" placeholder="08012345678" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>Company</flux:label>
                    <flux:input name="company_name" type="text" :value="old('company_name', $user->company_name)" placeholder="Optional" />
                    <flux:error name="company_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Timezone</flux:label>
                    <flux:input name="timezone" type="text" :value="old('timezone', $user->timezone ?: 'UTC')" required placeholder="Africa/Lagos" />
                    <flux:error name="timezone" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Preferred Funding Provider</flux:label>
                    <select name="preferred_funding_provider" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Use platform default</option>
                        @foreach (['paystack' => 'Paystack DVA', 'kora' => 'Kora Virtual Account', 'squad' => 'Squad Virtual Account'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('preferred_funding_provider', $user->preferred_funding_provider) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <flux:error name="preferred_funding_provider" />
                </flux:field>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Google login will stay linked for future sign-ins once this profile is saved.</p>

                <flux:button type="submit" variant="primary" color="teal">
                    Continue to Workspace
                </flux:button>
            </div>
        </form>
    </div>
</x-guest-layout>
