<x-layouts.app :title="($siteSettings->site_name ?? config('app.name')).' | Verification Operations'">
    <div class="px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="surface-card px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-lg font-bold text-white shadow-lg">KX</div>
                        <div>
                            <div class="text-lg font-semibold text-slate-950 dark:text-slate-50">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">{{ $siteSettings->site_tagline ?? 'Verification and wallet operations platform' }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}">
                                <x-ui.button>Open Workspace</x-ui.button>
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
            </header>

            <section class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
                <div class="surface-card relative overflow-hidden bg-slate-950 p-8 text-white sm:p-10">
                    <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-amber-300/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-64 w-64 rounded-full bg-teal-400/20 blur-3xl"></div>

                    <div class="relative">
                        <p class="section-kicker !text-teal-200">Production-ready workflow</p>
                        <h1 class="mt-4 max-w-2xl text-4xl font-semibold leading-tight text-balance sm:text-5xl">
                            {{ $siteSettings->site_tagline ?? 'One place for KYC runs, wallet funding, API access, and operator visibility.' }}
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-slate-200/80">
                            {{ $siteSettings->footer_text ?? 'Kycappx gives customers a clean self-serve workspace while giving your operations team the controls needed to monitor providers, webhooks, verifications, and account activity.' }}
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}">
                                    <x-ui.button>Go to Dashboard</x-ui.button>
                                </a>
                            @else
                                @if (Route::has('register') && $siteSettings->registration_enabled)
                                    <a href="{{ route('register') }}">
                                        <x-ui.button>Create Workspace</x-ui.button>
                                    </a>
                                @endif

                                <a href="{{ route('login') }}">
                                    <x-ui.button variant="secondary">Use Existing Account</x-ui.button>
                                </a>
                            @endauth
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">Wallet rails</div>
                                <div class="mt-3 text-2xl font-semibold">Webhook-driven credits</div>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">Verification layer</div>
                                <div class="mt-3 text-2xl font-semibold">BVN, NIN, CAC-ready services</div>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">Operator visibility</div>
                                <div class="mt-3 text-2xl font-semibold">Admin metrics, logs, and customer views</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="metric-card">
                        <p class="section-kicker">Customer experience</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Self-serve dashboard with stronger defaults</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Manage balances, fund wallets through Kora, run verifications, inspect transaction history, and rotate API credentials without leaving the workspace.
                        </p>
                    </div>

                    <div class="metric-card">
                        <p class="section-kicker">Operational control</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">A calmer admin cockpit</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Review customer usage, monitor provider readiness, inspect webhook deliveries, and stay ahead of manual review queues.
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-3">
                <div class="metric-card">
                    <div class="badge-soft">1. Fund with confidence</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">Pending requests stay clean until the webhook confirms.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Funding requests are tracked separately from wallet credits so duplicate callbacks do not inflate balances.
                    </p>
                </div>

                <div class="metric-card">
                    <div class="badge-soft">2. Run verifications</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">Structured forms for the services that matter.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Users can choose a service, supply the right fields, and track the request status from the same dashboard.
                    </p>
                </div>

                <div class="metric-card">
                    <div class="badge-soft">3. Operate at a glance</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">See what the system is doing without hunting.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Admin views surface customers, services, provider readiness, and verification/webhook logs in a single flow.
                    </p>
                </div>
            </section>

            <section class="surface-card p-8 sm:p-10">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="section-kicker">Ready to move</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-950 text-balance dark:text-slate-50">
                            Start with the customer workspace, then step into the operations cockpit when you need more control.
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
                                    <x-ui.button>Create Your Account</x-ui.button>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
