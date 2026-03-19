<?php

namespace App\Services\Billing\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KoraGateway
{
    private string $baseUrl;

    private ?string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.kora.base_url');
        $this->secretKey = config('services.kora.secret_key');
    }

    public function isConfigured(): bool
    {
        return filled($this->secretKey);
    }

    public function initializeCheckout(array $payload): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Kora is not configured.',
                'http_status' => 0,
                'body' => [],
            ];
        }

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

    public function createVirtualAccount(array $payload): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Kora is not configured.',
                'http_status' => 0,
                'body' => [],
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->baseUrl, '/').'/merchant/api/v1/virtual-bank-account', $payload);

        return [
            'ok' => $response->ok() && (bool) data_get($response->json(), 'status', false),
            'message' => data_get($response->json(), 'message', 'Kora virtual account request failed.'),
            'http_status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }

    public function queryCharge(string $reference): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Kora is not configured.',
                'http_status' => 0,
                'body' => [],
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get(rtrim($this->baseUrl, '/').'/merchant/api/v1/charges/'.urlencode($reference));

        return [
            'ok' => $response->ok() && (bool) data_get($response->json(), 'status', false),
            'message' => data_get($response->json(), 'message', 'Could not query the Kora charge.'),
            'http_status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }

    public function verifyWebhookSignature(array $fullBody, ?string $signatureHeader): bool
    {
        if (! $signatureHeader || ! array_key_exists('data', $fullBody) || ! $this->isConfigured()) {
            return false;
        }

        $hash = hash_hmac('sha256', json_encode($fullBody['data']), $this->secretKey);

        return hash_equals($hash, $signatureHeader);
    }
}
