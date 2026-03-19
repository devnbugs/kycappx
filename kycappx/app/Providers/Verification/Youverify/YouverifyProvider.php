<?php

namespace App\Providers\Verification\Youverify;

use App\Contracts\VerificationProviderInterface;
use App\DTOs\ProviderResult;
use Illuminate\Support\Facades\Http;

class YouverifyProvider implements VerificationProviderInterface
{
    public function providerName(): string
    {
        return 'youverify';
    }

    public function verifyBvn(array $payload): ProviderResult
    {
        // Youverify legacy identity endpoint shown in docs. :contentReference[oaicite:11]{index=11}
        $endpoint = rtrim(config('services.youverify.base_url'), '/') . '/v1/identities/candidates/check';

        $body = [
            'report_type' => 'identity',
            'type' => 'ibvn',
            'reference' => $payload['bvn'] ?? null,
            'first_name' => $payload['first_name'] ?? null,
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'dob' => $payload['dob'] ?? null,
            'subject_consent' => true,
        ];

        $res = Http::withHeaders([
                'token' => config('services.youverify.token'),
            ])
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $body);

        $raw = $res->json() ?? [];

        if (!$res->ok()) {
            return new ProviderResult(false, $this->providerName(), null, [], $raw, 'Youverify BVN HTTP error');
        }

        // Normalize based on your observed response body
        $normalized = [
            'bvn' => $payload['bvn'] ?? null,
            'first_name' => data_get($raw, 'data.first_name') ?? data_get($raw, 'data.firstName'),
            'last_name' => data_get($raw, 'data.last_name') ?? data_get($raw, 'data.lastName'),
            'dob' => data_get($raw, 'data.dob') ?? data_get($raw, 'data.dateOfBirth'),
        ];

        $ok = (bool) (data_get($raw, 'success') ?? data_get($raw, 'status') ?? true);

        return new ProviderResult($ok, $this->providerName(), null, $normalized, $raw, $ok ? null : 'Youverify BVN failed');
    }

    public function verifyNin(array $payload): ProviderResult
    {
        $endpoint = rtrim(config('services.youverify.base_url'), '/') . '/v1/identities/candidates/check'; // :contentReference[oaicite:12]{index=12}

        $body = [
            'report_type' => 'identity',
            'type' => 'nin',
            'reference' => $payload['nin'] ?? null,
            'first_name' => $payload['first_name'] ?? null,
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'dob' => $payload['dob'] ?? null,
            'subject_consent' => true,
        ];

        $res = Http::withHeaders([
                'token' => config('services.youverify.token'),
            ])
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $body);

        $raw = $res->json() ?? [];
        if (!$res->ok()) {
            return new ProviderResult(false, $this->providerName(), null, [], $raw, 'Youverify NIN HTTP error');
        }

        $normalized = [
            'nin' => $payload['nin'] ?? null,
            'first_name' => data_get($raw, 'data.first_name') ?? data_get($raw, 'data.firstName'),
            'last_name' => data_get($raw, 'data.last_name') ?? data_get($raw, 'data.lastName'),
            'dob' => data_get($raw, 'data.dob') ?? data_get($raw, 'data.dateOfBirth'),
        ];

        $ok = (bool) (data_get($raw, 'success') ?? data_get($raw, 'status') ?? true);

        return new ProviderResult($ok, $this->providerName(), null, $normalized, $raw, $ok ? null : 'Youverify NIN failed');
    }

    public function verifyCac(array $payload): ProviderResult
    {
        return new ProviderResult(false, $this->providerName(), null, [], [], 'Not implemented yet');
    }
}