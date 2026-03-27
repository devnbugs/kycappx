<span {{ $attributes->merge(['class' => 'logo-mark']) }}>
    <img
        src="{{ asset('brand/app-logo-placeholder.svg') }}"
        alt="{{ $siteSettings->site_name ?? config('app.name', 'Kycappx') }} logo"
        class="logo-mark__image"
    >
    <span class="sr-only">{{ $siteSettings->site_name ?? config('app.name', 'Kycappx') }}</span>
</span>
