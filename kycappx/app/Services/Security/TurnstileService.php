<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;

class TurnstileService
{
    public function isConfigured(): bool
    {
        return filled(config('services.cloudflare.turnstile.site_key'))
            && filled(config('services.cloudflare.turnstile.secret_key'));
    }

    public function verify(?string $token, ?string $remoteIp = null, ?string $expectedAction = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => true, 'skipped' => true];
        }

        if (! filled($token)) {
            return [
                'success' => false,
                'message' => 'Please complete the Cloudflare security check.',
                'data' => ['error-codes' => ['missing-input-response']],
            ];
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post(config('services.cloudflare.turnstile.siteverify_url'), array_filter([
                'secret' => config('services.cloudflare.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $remoteIp,
            ]));

        $data = $response->json() ?? [];

        if (! $response->ok() || ! data_get($data, 'success')) {
            return [
                'success' => false,
                'message' => 'Cloudflare Turnstile could not validate this request.',
                'data' => $data,
            ];
        }

        $expectedHost = config('services.cloudflare.turnstile.expected_host');

        if ($expectedAction && filled(data_get($data, 'action')) && data_get($data, 'action') !== $expectedAction) {
            return [
                'success' => false,
                'message' => 'Cloudflare Turnstile action verification failed.',
                'data' => $data,
            ];
        }

        if (filled($expectedHost) && filled(data_get($data, 'hostname')) && data_get($data, 'hostname') !== $expectedHost) {
            return [
                'success' => false,
                'message' => 'Cloudflare Turnstile hostname verification failed.',
                'data' => $data,
            ];
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }
}
