<x-layouts.app :title="($siteSettings->site_name ?? config('app.name')).' | Verification Operations'">
    <div class="px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="surface-card px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-lg font-bold text-white shadow-lg">{{ $siteSettings->logo_text ?? 'O' }}</div>
                        <div>
                            <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Version 2026.02.1</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                      @auth
                      <a href="{{ route('dashboard') }}">
                        <span class="badge-soft text-lg px-4 py-2 cursor-pointer hover:opacity-80">
                          Dashboard
                        </span>
                      </a>
                      @else
                      @if (Route::has('register') && $siteSettings->registration_enabled)
                      <a href="{{ route('register') }}">
                        <span class="badge-soft text-lg px-5 py-2 rounded-xl shadow-sm hover:shadow-md transition cursor-pointer">
                          Register
                        </span>
                      </a>
                      @endif
                      
                      <a href="{{ route('login') }}">
                        <span class="badge-soft text-lg px-5 py-2 rounded-xl shadow-sm hover:shadow-md transition cursor-pointer">
                          Login
                        </span>
                      </a>
                      @endauth
                  </div>
                </div>
            </header>

            <section class="grid gap-6 lg:grid-cols-[1.15fr,0.85fr]">
                <div class="surface-card relative overflow-hidden bg-slate-950 p-8 text-white sm:p-10">
                    <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-amber-300/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-64 w-64 rounded-full bg-teal-400/20 blur-3xl"></div>

                    <div class="relative">
                        <p class="section-kicker !text-teal-200">Beyond Identity</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-balance sm:text-5xl">
                            Speedo Boost Ai Powered KYC/KYB Core
                        </h1>
                      
                      <br>
                      
                      <flux:separator />
                      
                      <br>
                      
                      <div class="flex flex-wrap items-center gap-6">
                        <span class="badge-soft">NIN/vNIN Services</span>
                        <span class="badge-soft">BVN Services</span>
                        <span class="badge-soft">TIN Services</span>
                        <span class="badge-soft">VIN Verification</span>
                        <span class="badge-soft">WAEC Results</span>
                        <span class="badge-soft">VIN Verification</span>
                      </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}">
                                    <x-ui.button>Dashboard</x-ui.button>
                                </a>
                            @else
                                @if (Route::has('register') && $siteSettings->registration_enabled)
                                    <a href="{{ route('register') }}">
                                        <x-ui.button>Create Account</x-ui.button>
                                    </a>
                                @endif
                                <a href="{{ route('login') }}">
                                    <x-ui.button variant="secondary">Sign In</x-ui.button>
                                </a>
                            @endauth
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">KYC coverage</div>
                                <div class="mt-3 text-2xl font-semibold">WorldWide KYC Services Applied</div>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">Payments</div>
                                <div class="mt-3 text-2xl font-semibold">99% On Scalling Transactions</div>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">Security</div>
                                <div class="mt-3 text-2xl font-semibold">True Privacy and Safety</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="metric-card">
                        <p class="section-kicker">Partners</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach (['Prembly', 'Kora', 'Paystack', 'Cloudflare'] as $partner)
                                <div class="rounded-[1.25rem] border border-slate-200/80 bg-white/70 px-4 py-4 text-sm font-semibold text-slate-950 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-50">{{ $partner }}</div>
                            @endforeach
                        </div>
                    </div>

                    <!--div class="metric-card">
                        <p class="section-kicker">What ships</p>
                        <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            <p>Responsive customer and admin workspaces across mobile, tablet, laptop, and wide desktop screens.</p>
                            <!--p>Admin-managed switches for providers and KYC services, including Nigeria and United States verification products.</p>
                            <p>Wallet operations with Kora hosted checkout, Kora virtual accounts, and Paystack dedicated virtual accounts.</p>
                        </div>
                    </div-->
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-3">
                <div class="metric-card">
                    <div class="badge-soft">Customer workspace</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">KYC strength, verifications, API keys, and wallet rails in one place.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Users can save KYC bio once, prefill core checks, fund wallets, review transactions, and manage their own access posture.
                    </p>
                </div>

                <div class="metric-card">
                    <div class="badge-soft">Admin control</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">Switch providers and services without redeploying the app.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Priority, product toggles, KYC catalog activation, and feature switches live in the admin workspace instead of hardcoded release steps.
                    </p>
                </div>

                <div class="metric-card">
                    <div class="badge-soft">Trust layer</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">Safer authentication and cleaner rendering on every device.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Cloudflare Turnstile protects sign-in and registration, while the refreshed UI avoids duplicate Alpine bootstraps and handles narrow screens more gracefully.
                    </p>
                </div>
            </section>

            <section class="surface-card p-8 sm:p-10">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="section-kicker">Ready to launch</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-950 text-balance dark:text-slate-50">
                            Start with a customer-facing KYC and wallet experience, then scale into the admin control plane as traffic grows.
                        </h2>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}">
                                <x-ui.button>Open Dashboard</x-ui.button>
                            </a>
                        @else
                            <a href="{{ route('login') }}">
                                <x-ui.button variant="secondary">Sign In</x-ui.button>
                            </a>

                            @if (Route::has('register') && $siteSettings->registration_enabled)
                                <a href="{{ route('register') }}">
                                    <x-ui.button>Create Account</x-ui.button>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </section>

            <footer class="surface-card px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-slate-950 dark:text-slate-50">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $siteSettings->footer_text ?? 'Secure identity and wallet operations from one workspace.' }}</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                        <span>{{ $siteSettings->support_email ?: 'hello@onetera.serv00.net' }}</span>
                        @if ($siteSettings->support_phone)
                            <span>{{ $siteSettings->support_phone }}</span>
                        @endif
                        <a href="{{ route('privacy-policy') }}" class="font-medium text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Privacy Policy</a>
                        <a href="{{ route('terms-of-service') }}" class="font-medium text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Terms</a>
                        <a href="{{ route('login') }}" class="font-medium text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Login</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</x-layouts.app>
