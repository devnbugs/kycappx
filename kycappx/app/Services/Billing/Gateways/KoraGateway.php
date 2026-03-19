<?php

namespace App\Services\Billing\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KoraGateway
{
    private string $baseUrl;
    private string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.kora.base_url');
        $this->secretKey = config('services.kora.secret_key');
    }

    public function initializeCheckout(array $payload): array
    {
        $payload['reference'] = $payload['reference'] ?? ('KORA_' . Str::upper(Str::random(12)));

        $endpoint = rtrim($this->baseUrl, '/') . '/merchant/api/v1/charges/initialize';

        $res = Http::withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $payload);

        if (! $res->ok()) {
            return [
                'ok' => false,
                'message' => 'Kora initialize failed',
                'http_status' => $res->status(),
                'body' => $res->json(),
            ];
        }

        return [
            'ok' => true,
            'body' => $res->json(),
        ];
    }

    public function verifyWebhookSignature(array $fullBody, ?string $signatureHeader): bool
    {
        if (! $signatureHeader || ! array_key_exists('data', $fullBody)) {
            return false;
        }

        $hash = hash_hmac('sha256', json_encode($fullBody['data']), $this->secretKey);

        return hash_equals($hash, $signatureHeader);
    }
}