<?php

namespace App\Services\Billing\Gateways;

use Illuminate\Support\Facades\Http;

class SquadGateway
{
    private string $baseUrl;

    private ?string $secretKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.squad.base_url'), '/');
        $this->secretKey = config('services.squad.secret_key');
    }

    public function isConfigured(): bool
    {
        return filled($this->secretKey);
    }

    public function createVirtualAccount(array $payload): array
    {
        return $this->post('/virtual-account', $payload, 'Could not create the Squad virtual account.');
    }

    public function sendInstantSms(array $payload): array
    {
        return $this->post('/sms/send/instant', $payload, 'Could not send the SMS batch through Squad.');
    }

    public function createSmsTemplate(array $payload): array
    {
        return $this->post('/sms/template', $payload, 'Could not create the Squad SMS template.');
    }

    public function listSmsTemplates(array $query = []): array
    {
        return $this->get('/sms/template', $query, 'Could not load the Squad SMS templates.');
    }

    public function verifyWebhookSignature(
        string $rawPayload,
        ?string $encryptedBodyHeader = null,
        ?string $signatureHeader = null,
    ): bool {
        if (! $this->isConfigured()) {
            return false;
        }

        $hash = hash_hmac('sha512', $rawPayload, (string) $this->secretKey);

        foreach ([$encryptedBodyHeader, $signatureHeader] as $header) {
            if (! $header) {
                continue;
            }

            if (hash_equals(strtoupper($hash), strtoupper($header)) || hash_equals(strtolower($hash), strtolower($header))) {
                return true;
            }
        }

        return false;
    }

    private function post(string $path, array $payload, string $fallbackMessage): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Squad is not configured.',
                'http_status' => 0,
                'body' => [],
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl.$path, $payload);

        return [
            'ok' => $response->successful() && (bool) data_get($response->json(), 'success', false),
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
                'message' => 'Squad is not configured.',
                'http_status' => 0,
                'body' => [],
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get($this->baseUrl.$path, $query);

        return [
            'ok' => $response->successful() && (bool) data_get($response->json(), 'success', false),
            'message' => data_get($response->json(), 'message', $fallbackMessage),
            'http_status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }
}
