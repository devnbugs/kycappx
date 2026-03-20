<x-layouts.dashboard-user title="KYC Strength" header="KYC Verification & Strength">
    <section class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
        <div class="hero-tile surface-card relative overflow-hidden p-8 text-white">
            <div class="absolute -right-10 top-0 h-48 w-48 rounded-full bg-amber-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-52 w-52 rounded-full bg-teal-400/20 blur-3xl"></div>

            <div class="relative">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge-soft border-white/10 bg-white/10 text-white/85">{{ $snapshot['role_label'] }}</span>
                    <span class="badge-soft border-white/10 bg-white/10 text-white/85">{{ $snapshot['level_label'] }}</span>
                    <span class="badge-soft border-white/10 bg-white/10 text-white/85">{{ $snapshot['score'] }}% strength</span>
                </div>

                <h2 class="mt-5 max-w-3xl text-3xl font-semibold text-balance">Build a stronger KYC posture with one profile that feeds the rest of the workspace.</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200/80">
                    Add your NIN, BVN, phone number, gender, and full bio details once. We use this profile to measure KYC strength levels, prefill verification flows, and satisfy Squad virtual-account requirements.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                        <div class="text-sm text-white/60">Target level</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $snapshot['target_level'] }}</div>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                        <div class="text-sm text-white/60">Phone</div>
                        <div class="mt-2 text-lg font-semibold">{{ $snapshot['masked_profile']['phone'] ?: 'Not saved yet' }}</div>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                        <div class="text-sm text-white/60">Next move</div>
                        <div class="mt-2 text-sm font-medium leading-6 text-white/85">{{ $snapshot['next_step'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4">
            <div class="metric-card">
                <p class="section-kicker">Level Ladder</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-[1.25rem] border p-4 {{ $snapshot['bio_complete'] && $snapshot['phone_complete'] ? 'border-teal-200 bg-teal-50 dark:border-teal-500/20 dark:bg-teal-500/10' : 'border-slate-200/80 bg-white/70 dark:border-slate-700 dark:bg-slate-900/60' }}">
                        <div class="font-semibold text-slate-950 dark:text-slate-50">Level 1</div>
                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Phone plus full bio details.</div>
                    </div>
                    <div class="rounded-[1.25rem] border p-4 {{ $snapshot['nin_complete'] && $snapshot['bio_complete'] && $snapshot['phone_complete'] ? 'border-teal-200 bg-teal-50 dark:border-teal-500/20 dark:bg-teal-500/10' : 'border-slate-200/80 bg-white/70 dark:border-slate-700 dark:bg-slate-900/60' }}">
                        <div class="font-semibold text-slate-950 dark:text-slate-50">Level 2</div>
                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">NIN, phone, and full bio details.</div>
                    </div>
                    <div class="rounded-[1.25rem] border p-4 {{ $snapshot['bvn_complete'] && $snapshot['nin_complete'] && $snapshot['bio_complete'] && $snapshot['phone_complete'] ? 'border-teal-200 bg-teal-50 dark:border-teal-500/20 dark:bg-teal-500/10' : 'border-slate-200/80 bg-white/70 dark:border-slate-700 dark:bg-slate-900/60' }}">
                        <div class="font-semibold text-slate-950 dark:text-slate-50">Level 3</div>
                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">BVN, NIN, phone, and full bio details.</div>
                    </div>
                </div>
            </div>

            <div class="metric-card">
                <p class="section-kicker">Quick Actions</p>
                <div class="mt-4 grid gap-3">
                    @forelse ($recommendedServices as $service)
                        <a href="{{ route('verifications.create', ['service' => $service->id]) }}" class="rounded-[1.25rem] border border-slate-200/80 bg-white/70 px-4 py-4 transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/60 dark:hover:border-slate-600">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-slate-950 dark:text-slate-50">{{ $service->name }}</span>
                                <span class="badge-soft">{{ $service->country }}</span>
                            </div>
                            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Open this service with your KYC profile as the default input source.</div>
                        </a>
                    @empty
                        <div class="rounded-[1.25rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                            No active KYC services are available right now.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('kyc.update') }}" class="surface-card mt-4 p-6 sm:p-8">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">KYC Profile</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Save your identity profile once</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                    This page stores your KYC bio and strength inputs securely so verification forms, wallet account setup, and future reviews can pick them up automatically.
                </p>
            </div>

            <span class="badge-soft">{{ $snapshot['level_label'] }} now</span>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-input-label for="first_name" value="First Name" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-2" :value="old('first_name', $profile['first_name'])" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="middle_name" value="Middle Name" />
                <x-text-input id="middle_name" name="middle_name" type="text" class="mt-2" :value="old('middle_name', $profile['middle_name'])" />
                <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="last_name" value="Last Name" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-2" :value="old('last_name', $profile['last_name'])" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="dob" value="Date of Birth" />
                <x-text-input id="dob" name="dob" type="date" class="mt-2" :value="old('dob', $profile['dob'])" />
                <x-input-error :messages="$errors->get('dob')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="gender" value="Gender" />
                <select id="gender" name="gender" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Select gender</option>
                    @foreach (['male' => 'Male', 'female' => 'Female'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $profile['gender']) === $value || old('gender', $profile['gender']) === ($value === 'male' ? '1' : '2'))>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" value="Phone Number" />
                <x-text-input id="phone" name="phone" type="text" class="mt-2" :value="old('phone', $profile['phone'])" placeholder="+2348030000000" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="country" value="Country" />
                <select id="country" name="country" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    @foreach (['NG' => 'Nigeria', 'US' => 'United States'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('country', $profile['country']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="nin" value="NIN" />
                <x-text-input id="nin" name="nin" type="text" class="mt-2" :value="old('nin', $profile['nin'])" placeholder="12345678901" />
                <x-input-error :messages="$errors->get('nin')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="bvn" value="BVN" />
                <x-text-input id="bvn" name="bvn" type="text" class="mt-2" :value="old('bvn', $profile['bvn'])" placeholder="22123456789" />
                <x-input-error :messages="$errors->get('bvn')" class="mt-2" />
            </div>

            <div class="md:col-span-2 xl:col-span-1">
                <x-input-label for="address_line1" value="Address Line 1" />
                <x-text-input id="address_line1" name="address_line1" type="text" class="mt-2" :value="old('address_line1', $profile['address_line1'])" />
                <x-input-error :messages="$errors->get('address_line1')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="address_line2" value="Address Line 2" />
                <x-text-input id="address_line2" name="address_line2" type="text" class="mt-2" :value="old('address_line2', $profile['address_line2'])" />
                <x-input-error :messages="$errors->get('address_line2')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="city" value="City" />
                <x-text-input id="city" name="city" type="text" class="mt-2" :value="old('city', $profile['city'])" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="state" value="State / Region" />
                <x-text-input id="state" name="state" type="text" class="mt-2" :value="old('state', $profile['state'])" />
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="zip" value="ZIP / Postal Code" />
                <x-text-input id="zip" name="zip" type="text" class="mt-2" :value="old('zip', $profile['zip'])" />
                <x-input-error :messages="$errors->get('zip')" class="mt-2" />
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button type="submit">Save KYC Profile</x-ui.button>
            <a href="{{ route('verifications.create') }}">
                <x-ui.button variant="secondary">Open Verification Catalog</x-ui.button>
            </a>
        </div>
    </form>
</x-layouts.dashboard-user>
