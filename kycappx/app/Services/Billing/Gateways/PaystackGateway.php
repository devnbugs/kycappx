<?php

namespace App\Services\Billing\Gateways;

use Illuminate\Support\Facades\Http;

class PaystackGateway
{
    private string $baseUrl;

    private ?string $secretKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.paystack.base_url'), '/');
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function isConfigured(): bool
    {
        return filled($this->secretKey);
    }

    public function createCustomer(array $payload): array
    {
        return $this->post('/customer', $payload, 'Could not create the Paystack customer profile.');
    }

    public function fetchCustomer(string $emailOrCode): array
    {
        return $this->get('/customer/'.urlencode($emailOrCode), [], 'Could not fetch the Paystack customer profile.');
    }

    public function createDedicatedAccount(string $customerCode, ?string $preferredBank = null): array
    {
        return $this->post('/dedicated_account', [
            'customer' => $customerCode,
            'preferred_bank' => $preferredBank ?: config('services.paystack.preferred_bank'),
        ], 'Could not create the Paystack dedicated account.');
    }

    public function requeryDedicatedAccount(string $accountNumber, string $providerSlug, string $date): array
    {
        return $this->get('/dedicated_account/requery', [
            'account_number' => $accountNumber,
            'provider_slug' => $providerSlug,
            'date' => $date,
        ], 'Could not requery the Paystack dedicated account.');
    }

    public function verifyWebhookSignature(string $rawPayload, ?string $signatureHeader): bool
    {
        if (! $signatureHeader || ! $this->isConfigured()) {
            return false;
        }

        $hash = hash_hmac('sha512', $rawPayload, (string) $this->secretKey);

        return hash_equals($hash, $signatureHeader);
    }

    private function post(string $path, array $payload, string $fallbackMessage): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Paystack is not configured.',
                'http_status' => 0,
                'body' => [],
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl.$path, $payload);

        return [
            'ok' => $response->ok() && (bool) data_get($response->json(), 'status', false),
            'message' => data_get($response->json(), 'message', $fallbackMessage),
            'http_status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }

    private function get(string $path, array $query, string $fallbackMessage): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Paystack is not configured.',
                'http_status' => 0,
                'body' => [],
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get($this->baseUrl.$path, $query);

        return [
            'ok' => $response->ok() && (bool) data_get($response->json(), 'status', false),
            'message' => data_get($response->json(), 'message', $fallbackMessage),
            'http_status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }
}
