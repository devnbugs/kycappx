<?php

namespace App\Providers\Verification\Prembly;

use App\Contracts\VerificationProviderInterface;
use App\DTOs\ProviderResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PremblyProvider implements VerificationProviderInterface
{
    private function request(string $endpoint, array $payload): array
    {
        $timeout = (int) data_get(config('services.prembly'), 'timeout_seconds', 30);
        $body = array_filter($payload, static fn ($value) => $value !== null && $value !== '');

        $response = Http::withHeaders([
                'app-id' => config('services.prembly.app_id'),
                'x-api-key' => config('services.prembly.secret_key'),
            ])
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->post(rtrim((string) config('services.prembly.base_url'), '/').$endpoint, $body);

        $json = $response->json() ?? [];

        return [
            'ok' => $response->successful() && $this->responseSuccessful($json),
            'body' => $json,
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
        $data = $this->extractData($raw);

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

        $normalized = [
            'first_name' => $data['firstName'] ?? null,
            'middle_name' => $data['middleName'] ?? null,
            'last_name' => $data['lastName'] ?? null,
            'dob' => $data['dateOfBirth'] ?? null,
            'phone' => $data['phoneNumber1'] ?? null,
            'bvn' => $data['bvn'] ?? null,
            'gender' => $data['gender'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['residentialAddress'] ?? null,
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
        $data = $this->extractData($raw);

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

        $normalized = [
            'first_name' => $data['firstName'] ?? null,
            'middle_name' => $data['middleName'] ?? null,
            'last_name' => $data['lastName'] ?? null,
            'dob' => $data['dateOfBirth'] ?? null,
            'nin' => $data['nin'] ?? ($payload['nin'] ?? null),
            'phone' => $data['phoneNumber'] ?? null,
            'gender' => $data['gender'] ?? null,
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
        $data = $this->extractData($raw);

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
        $response = $this->request('/verification/cac/advance', [
            'rc_number' => $payload['registration_number'] ?? null,
            'company_type' => $payload['company_type'] ?? 'RC',
            'company_name' => $payload['company_name'] ?? null,
        ]);
        $raw = $response['body'];
        $data = $this->extractData($raw);
        $company = collect(is_array($data) ? $data : [])->first();

        if (! $response['ok'] || ! is_array($company)) {
            return new ProviderResult(
                false,
                $this->providerName(),
                data_get($raw, 'reference'),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly CAC verification failed.')
            );
        }

        $normalized = [
            'registration_number' => $company['rc_number'] ?? ($payload['registration_number'] ?? null),
            'company_name' => $company['company_name'] ?? null,
            'company_status' => $company['company_status'] ?? null,
            'entity_type' => $company['entity_type'] ?? null,
            'registration_date' => $company['registrationDate'] ?? null,
            'email' => $company['email_address'] ?? null,
            'address' => $company['company_address'] ?? null,
            'city' => $company['city'] ?? null,
            'state' => $company['state'] ?? null,
            'directors' => $company['directors'] ?? [],
        ];

        return new ProviderResult(
            true,
            $this->providerName(),
            data_get($raw, 'reference'),
            $normalized,
            $raw
        );
    }

    public function verifyUsBiodata(array $payload): ProviderResult
    {
        $response = $this->request('/background-check/api/v1/usa/bio-data', [
            'first_name' => $payload['first_name'] ?? null,
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'date_of_birth' => $this->formatDateForPrembly($payload['dob'] ?? null),
            'address' => $this->buildAddress($payload),
            'city' => $payload['city'] ?? null,
            'state' => $payload['state'] ?? null,
            'zip' => $payload['zip'] ?? null,
        ]);
        $raw = $response['body'];
        $data = $this->extractData($raw);

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                data_get($raw, 'reference'),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly USA biodata verification failed.')
            );
        }

        $normalized = [
            'first_name' => $data['first_name'] ?? $data['firstName'] ?? ($payload['first_name'] ?? null),
            'middle_name' => $data['middle_name'] ?? $data['middleName'] ?? ($payload['middle_name'] ?? null),
            'last_name' => $data['last_name'] ?? $data['lastName'] ?? ($payload['last_name'] ?? null),
            'dob' => $data['date_of_birth'] ?? $data['dateOfBirth'] ?? ($payload['dob'] ?? null),
            'address' => $data['address'] ?? $this->buildAddress($payload),
            'city' => $data['city'] ?? ($payload['city'] ?? null),
            'state' => $data['state'] ?? ($payload['state'] ?? null),
            'zip' => $data['zip'] ?? ($payload['zip'] ?? null),
            'match_status' => $data['status'] ?? data_get($raw, 'detail'),
        ];

        return new ProviderResult(
            true,
            $this->providerName(),
            data_get($raw, 'reference'),
            $normalized,
            $raw
        );
    }

    public function verifyUsAddress(array $payload): ProviderResult
    {
        $response = $this->request('/background-check/api/v1/usa/address', [
            'address' => $this->buildAddress($payload),
            'city' => $payload['city'] ?? null,
            'state' => $payload['state'] ?? null,
            'zip' => $payload['zip'] ?? null,
        ]);
        $raw = $response['body'];
        $data = $this->extractData($raw);

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                data_get($raw, 'reference'),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly USA address verification failed.')
            );
        }

        $normalized = [
            'address' => $data['address'] ?? $this->buildAddress($payload),
            'city' => $data['city'] ?? ($payload['city'] ?? null),
            'state' => $data['state'] ?? ($payload['state'] ?? null),
            'zip' => $data['zip'] ?? ($payload['zip'] ?? null),
            'match_status' => $data['status'] ?? data_get($raw, 'detail'),
        ];

        return new ProviderResult(
            true,
            $this->providerName(),
            data_get($raw, 'reference'),
            $normalized,
            $raw
        );
    }

    public function verifyUsSsn(array $payload): ProviderResult
    {
        $response = $this->request('/identitypass/verification/global/tin-check', [
            'number' => $payload['ssn'] ?? $payload['tin'] ?? null,
            'country' => $payload['country'] ?? 'US',
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'date_of_birth' => $this->formatDateForPrembly($payload['dob'] ?? null),
        ]);
        $raw = $response['body'];
        $data = $this->extractData($raw);

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                data_get($raw, 'reference'),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly USA SSN/TIN verification failed.')
            );
        }

        $normalized = [
            'ssn' => $payload['ssn'] ?? $payload['tin'] ?? null,
            'country' => $data['country'] ?? 'US',
            'first_name' => $data['first_name'] ?? ($payload['first_name'] ?? null),
            'last_name' => $data['last_name'] ?? ($payload['last_name'] ?? null),
            'dob' => $data['date_of_birth'] ?? ($payload['dob'] ?? null),
            'match_status' => $data['status'] ?? data_get($raw, 'detail'),
        ];

        return new ProviderResult(
            true,
            $this->providerName(),
            data_get($raw, 'reference'),
            $normalized,
            $raw
        );
    }

    private function responseSuccessful(array $body): bool
    {
        if (data_get($body, 'status') === true || data_get($body, 'success') === true) {
            return true;
        }

        return in_array((string) data_get($body, 'response_code'), ['00', '200'], true);
    }

    private function extractData(array $body): array
    {
        $data = data_get($body, 'data', []);

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    private function formatDateForPrembly(?string $date): ?string
    {
        if (! filled($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('m/d/Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    private function buildAddress(array $payload): ?string
    {
        $address = collect([
            $payload['address'] ?? null,
            $payload['address_line1'] ?? null,
            $payload['address_line2'] ?? null,
        ])->filter()->unique()->implode(', ');

        return $address !== '' ? $address : null;
    }
}
