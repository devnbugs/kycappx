@php
    $themePreference = auth()->user()?->theme_preference ?? ($siteSettings->default_theme ?? 'system');
@endphp

<x-layouts.app :title="$siteSettings->site_name ?? config('app.name', 'Kycappx')">
    <div class="relative min-h-screen">
        <div class="mx-auto flex min-h-screen max-w-[1680px] flex-col lg:flex-row">
            <section class="relative hidden w-full max-w-[46rem] p-4 sm:p-6 lg:flex">
                <div class="glass-panel hero-tile relative flex w-full flex-col justify-between overflow-hidden px-8 py-8 lg:px-10 lg:py-10">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.14),transparent_28%)]"></div>
                    <div class="absolute -left-16 bottom-0 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute right-0 top-0 h-72 w-72 rounded-full bg-amber-300/18 blur-3xl"></div>

                    <div class="relative">
                        <div class="flex items-center justify-between gap-4">
                            <a href="/" class="logo-placeholder text-white/90">
                                <x-application-logo />
                                <span>{{ $siteSettings->site_name ?? config('app.name') }}</span>
                            </a>

                            <x-ui.theme-toggle />
                        </div>

                        <div class="mt-14 max-w-xl">
                            <p class="section-kicker !text-teal-100">OneTera ID</p>
                            <h1 class="mt-4 text-4xl font-semibold leading-tight text-balance xl:text-5xl">
                                {{ $siteSettings->site_tagline ?? "We're Best at it." }}
                            </h1>
                            <p class="mt-5 max-w-lg text-sm leading-7 text-white/80 sm:text-base">
                                Watch behind it. Verify it.
                            </p>
                        </div>
                    </div>

                    <div class="relative grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm text-white/65">User Tools</div>
                            <div class="mt-3 text-2xl font-semibold text-white">Get to Know Your Clients</div>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm text-white/65">Staffs Survey and  Maintainance</div>
                            <div class="mt-3 text-2xl font-semibold text-white">Our Services are Stables</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex min-w-0 flex-1 items-center justify-center px-4 py-6 sm:px-6 lg:px-10">
                <div class="w-full max-w-2xl">
                    <div class="mb-5 flex items-center justify-between gap-4 lg:hidden">
                        <a href="/" class="logo-placeholder text-sm">
                            <x-application-logo />
                            <span>{{ $siteSettings->site_name ?? config('app.name') }}</span>
                        </a>

                        <x-ui.theme-toggle />
                    </div>

                    <div class="surface-card overflow-hidden p-6 sm:p-8 lg:p-10">
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
