@props([
    'action' => 'form',
])

@if (filled(config('services.cloudflare.turnstile.site_key')) && filled(config('services.cloudflare.turnstile.secret_key')))
    <div class="space-y-2">
        <div
            class="cf-turnstile"
            data-sitekey="{{ config('services.cloudflare.turnstile.site_key') }}"
            data-theme="auto"
            data-action="{{ $action }}"
        ></div>

        <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
    </div>

    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
