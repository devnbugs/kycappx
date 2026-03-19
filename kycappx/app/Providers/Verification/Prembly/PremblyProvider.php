<?php

namespace App\Providers\Verification\Prembly;

use App\Contracts\VerificationProviderInterface;
use App\DTOs\ProviderResult;
use Illuminate\Support\Facades\Http;

class PremblyProvider implements VerificationProviderInterface
{
    public function providerName(): string
    {
        return 'prembly';
    }

    public function verifyBvn(array $payload): ProviderResult
    {
        // Prembly BVN endpoint: POST https://api.prembly.com/verification/bvn with headers app-id and x-api-key. :contentReference[oaicite:8]{index=8}
        $res = Http::withHeaders([
                'app-id' => config('services.prembly.app_id'),
                'x-api-key' => config('services.prembly.secret_key'),
            ])
            ->acceptJson()
            ->asJson()
            ->post(config('services.prembly.base_url') . '/verification/bvn', [
                'number' => $payload['bvn'] ?? $payload['number'] ?? null,
            ]);

        $raw = $res->json() ?? [];

        if (!$res->ok() || data_get($raw, 'status') !== true) {
            return new ProviderResult(
                ok: false,
                provider: $this->providerName(),
                reference: null,
                normalized: [],
                raw: $raw,
                error: $raw['detail'] ?? 'Prembly BVN failed'
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

        return new ProviderResult(true, $this->providerName(), null, $normalized, $raw);
    }

    public function verifyNin(array $payload): ProviderResult
    {
        // Prembly NIN is documented, but the exact endpoint path depends on the specific product page. :contentReference[oaicite:9]{index=9}
        // You will set the correct endpoint once you confirm it on your Prembly dashboard docs.
        $endpoint = config('services.prembly.base_url') . '/verification/nin';

        $res = Http::withHeaders([
                'app-id' => config('services.prembly.app_id'),
                'x-api-key' => config('services.prembly.secret_key'),
            ])
            ->acceptJson()
            ->asJson()
            ->post($endpoint, [
                'number' => $payload['nin'] ?? null,
            ]);

        $raw = $res->json() ?? [];

        if (!$res->ok() || data_get($raw, 'status') !== true) {
            return new ProviderResult(false, $this->providerName(), null, [], $raw, $raw['detail'] ?? 'Prembly NIN failed');
        }

        // Normalize based on actual response fields
        $data = data_get($raw, 'data', []);
        $normalized = [
            'first_name' => $data['firstName'] ?? null,
            'last_name' => $data['lastName'] ?? null,
            'dob' => $data['dateOfBirth'] ?? null,
            'nin' => $data['nin'] ?? ($payload['nin'] ?? null),
        ];

        return new ProviderResult(true, $this->providerName(), null, $normalized, $raw);
    }

    public function verifyCac(array $payload): ProviderResult
    {
        // Prembly has “Company Search With Registration Number” in its references. :contentReference[oaicite:10]{index=10}
        // Wire exact endpoint when you decide which CAC/company lookup you are using.
        return new ProviderResult(false, $this->providerName(), null, [], [], 'Not implemented yet');
    }
}