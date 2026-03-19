<?php

namespace App\Providers\Verification\Prembly;

use App\Contracts\VerificationProviderInterface;
use App\DTOs\ProviderResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PremblyProvider implements VerificationProviderInterface
{
    private function request(string $endpoint, array $payload): array
    {
        $timeout = (int) data_get(config('services.prembly'), 'timeout_seconds', 30);

        $response = Http::withHeaders([
                'app-id' => config('services.prembly.app_id'),
                'x-api-key' => config('services.prembly.secret_key'),
            ])
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->post(rtrim((string) config('services.prembly.base_url'), '/').$endpoint, $payload);

        return [
            'ok' => $response->ok() && (bool) data_get($response->json(), 'status', false),
            'body' => $response->json() ?? [],
        ];
    }

    public function providerName(): string
    {
        return 'prembly';
    }

    public function verifyBvn(array $payload): ProviderResult
    {
        $response = $this->request('/verification/bvn', [
            'number' => $payload['bvn'] ?? $payload['number'] ?? null,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                ok: false,
                provider: $this->providerName(),
                reference: data_get($raw, 'verification.reference'),
                normalized: [],
                raw: $raw,
                error: data_get($raw, 'detail', 'Prembly BVN verification failed.')
            );
        }

        $data = data_get($raw, 'data', []);

        $normalized = [
            'first_name' => $data['firstName'] ?? null,
            'middle_name' => $data['middleName'] ?? null,
            'last_name' => $data['lastName'] ?? null,
            'dob' => $data['dateOfBirth'] ?? null,
            'phone' => $data['phoneNumber1'] ?? null,
            'bvn' => $data['bvn'] ?? null,
        ];

        return new ProviderResult(
            true,
            $this->providerName(),
            data_get($raw, 'verification.reference'),
            $normalized,
            $raw
        );
    }

    public function verifyNin(array $payload): ProviderResult
    {
        $response = $this->request('/verification/vnin-basic', [
            'number' => $payload['nin'] ?? null,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                data_get($raw, 'verification.reference'),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly NIN verification failed.')
            );
        }

        $data = data_get($raw, 'data', []);
        $normalized = [
            'first_name' => $data['firstName'] ?? null,
            'last_name' => $data['lastName'] ?? null,
            'dob' => $data['dateOfBirth'] ?? null,
            'nin' => $data['nin'] ?? ($payload['nin'] ?? null),
        ];

        return new ProviderResult(
            true,
            $this->providerName(),
            data_get($raw, 'verification.reference'),
            $normalized,
            $raw
        );
    }

    public function verifyPhone(array $payload): ProviderResult
    {
        $number = $payload['phone'] ?? $payload['identifier'] ?? $payload['number'] ?? null;
        $response = $this->request('/identitypass/verification/global/phone-status-check', [
            'number' => $number,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                data_get($raw, 'verification.reference'),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly phone verification failed.')
            );
        }

        $data = data_get($raw, 'data', []);
        $normalized = [
            'phone' => data_get($data, 'numbering.original.complete_phone_number', $number),
            'country' => data_get($data, 'location.country.iso2'),
            'carrier' => data_get($data, 'carrier.name'),
            'phone_type' => Str::lower((string) data_get($data, 'phone_type.description', '')),
            'blocked' => (bool) data_get($data, 'blocklisting.blocked', false),
            'first_name' => data_get($data, 'contact.first_name'),
            'last_name' => data_get($data, 'contact.last_name'),
            'dob' => data_get($data, 'contact.date_of_birth'),
        ];

        return new ProviderResult(
            true,
            $this->providerName(),
            data_get($raw, 'verification.reference'),
            $normalized,
            $raw
        );
    }

    public function verifyCac(array $payload): ProviderResult
    {
        return new ProviderResult(
            false,
            $this->providerName(),
            null,
            [],
            ['payload' => $payload],
            'CAC automation is not wired yet for Prembly.'
        );
    }
}
