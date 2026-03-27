<?php

namespace App\Providers\Verification\Kora;

use App\Contracts\VerificationProviderInterface;
use App\DTOs\ProviderResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KoraIdentityProvider implements VerificationProviderInterface
{
    public function providerName(): string
    {
        return 'kora';
    }

    public function verifyCatalogProduct(string $productKey, array $payload, array $definition = []): ProviderResult
    {
        $route = (array) data_get(config('verification_engines.routeCatalog'), 'kora.'.$productKey, []);

        if ($route === []) {
            return new ProviderResult(
                false,
                $this->providerName(),
                null,
                [],
                [],
                'Kora identity route is not configured for this service.'
            );
        }

        $response = $this->request(
            method: (string) ($route['requestMethod'] ?? 'POST'),
            endpointPath: (string) ($route['endpointPath'] ?? ''),
            payload: $this->payloadFor($productKey, $payload),
        );

        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                (string) data_get($raw, 'message', 'Kora identity verification failed.')
            );
        }

        return match ((string) ($route['normalizer'] ?? 'generic')) {
            'bvn' => $this->bvnResultFromResponse($raw, $payload),
            'nin' => $this->ninResultFromResponse($raw, $payload),
            'phone' => $this->phoneResultFromResponse($raw, $payload),
            'cac' => $this->cacResultFromResponse($raw, $payload),
            default => $this->genericResultFromResponse($raw, $productKey),
        };
    }

    public function verifyBvn(array $payload): ProviderResult
    {
        return $this->verifyCatalogProduct('bvnLookup', $payload);
    }

    public function verifyNin(array $payload): ProviderResult
    {
        return $this->verifyCatalogProduct('ninLookup', $payload);
    }

    public function verifyPhone(array $payload): ProviderResult
    {
        return $this->verifyCatalogProduct('phoneLookup', $payload);
    }

    public function verifyCac(array $payload): ProviderResult
    {
        return $this->verifyCatalogProduct('cacLookup', $payload);
    }

    public function verifyBankAccountComparison(array $payload): ProviderResult
    {
        return $this->genericUnsupportedResult('Bank account comparison is not mapped for Kora identity.');
    }

    public function verifyUsBiodata(array $payload): ProviderResult
    {
        return $this->genericUnsupportedResult('US biodata verification is not mapped for Kora identity.');
    }

    public function verifyUsAddress(array $payload): ProviderResult
    {
        return $this->genericUnsupportedResult('US address verification is not mapped for Kora identity.');
    }

    public function verifyUsSsn(array $payload): ProviderResult
    {
        return $this->genericUnsupportedResult('US SSN verification is not mapped for Kora identity.');
    }

    private function request(string $method, string $endpointPath, array $payload): array
    {
        $timeout = (int) (config('services.kora.timeout_seconds') ?: 30);
        $endpoint = rtrim((string) config('services.kora.base_url'), '/').'/'.ltrim($endpointPath, '/');

        $response = Http::withToken((string) config('services.kora.secret_key'))
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->send(Str::upper($method), $endpoint, [
                'json' => array_filter($payload, fn ($value) => $value !== null && $value !== '' && $value !== []),
            ]);

        $json = $response->json() ?? [];

        return [
            'ok' => $response->successful() && (bool) data_get($json, 'status', false),
            'body' => is_array($json) ? $json : [],
        ];
    }

    private function payloadFor(string $productKey, array $payload): array
    {
        return match ($productKey) {
            'bvnLookup' => [
                'id' => $payload['bvn'] ?? $payload['number'] ?? null,
                'verification_consent' => true,
                'validation' => $this->validationPayload($payload),
            ],
            'ninLookup' => [
                'id' => $payload['nin'] ?? $payload['number'] ?? $payload['number_nin'] ?? null,
                'verification_consent' => true,
                'validation' => $this->validationPayload($payload),
            ],
            'phoneLookup' => [
                'id' => $payload['phone'] ?? $payload['number'] ?? null,
                'verification_consent' => true,
                'validation' => $this->validationPayload($payload),
            ],
            'advancedPhoneSearch' => [
                'id' => $payload['phone'] ?? $payload['number'] ?? null,
                'verification_consent' => true,
            ],
            'cacLookup' => [
                'id' => $payload['registration_number'] ?? $payload['number'] ?? null,
                'verification_consent' => true,
                'validation' => array_filter([
                    'registration_name' => $payload['company_name'] ?? null,
                ]),
            ],
            default => $payload,
        };
    }

    private function validationPayload(array $payload): ?array
    {
        $validation = array_filter([
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'date_of_birth' => $payload['dob'] ?? $payload['date_of_birth'] ?? null,
            'selfie' => $payload['image'] ?? $payload['selfie'] ?? null,
        ]);

        return $validation === [] ? null : $validation;
    }

    private function bvnResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $data = $this->extractData($raw);

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $this->filterNormalized(array_merge($this->baseNormalized($raw), [
                'bvn' => $data['id'] ?? $payload['bvn'] ?? $payload['number'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'dob' => $data['date_of_birth'] ?? null,
                'phone' => $data['phone_number'] ?? null,
                'gender' => $data['gender'] ?? null,
                'match_status' => data_get($data, 'validation.first_name.match'),
            ])),
            $raw
        );
    }

    private function ninResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $data = $this->extractData($raw);
        $address = data_get($data, 'address');

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $this->filterNormalized(array_merge($this->baseNormalized($raw), [
                'nin' => $data['id'] ?? $payload['nin'] ?? $payload['number'] ?? $payload['number_nin'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'dob' => $data['date_of_birth'] ?? null,
                'phone' => $data['phone_number'] ?? $payload['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => is_array($address)
                    ? collect([$address['street'] ?? null, $address['town'] ?? null, $address['state'] ?? null])->filter()->implode(', ')
                    : $address,
                'photo' => $data['image'] ?? null,
                'signature' => $data['signature'] ?? null,
            ])),
            $raw
        );
    }

    private function phoneResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $data = $this->extractData($raw);

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $this->filterNormalized(array_merge($this->baseNormalized($raw), [
                'phone' => $data['id'] ?? $payload['phone'] ?? $payload['number'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'dob' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'match_status' => data_get($data, 'validation.first_name.match'),
            ])),
            $raw
        );
    }

    private function cacResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $data = $this->extractData($raw);

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $this->filterNormalized(array_merge($this->baseNormalized($raw), [
                'registration_number' => $data['id'] ?? $payload['registration_number'] ?? null,
                'company_name' => $data['registration_name'] ?? $data['business_name'] ?? $payload['company_name'] ?? null,
                'status' => $data['status'] ?? null,
                'address' => Arr::first(array_filter([
                    $data['address'] ?? null,
                    data_get($data, 'office_address'),
                ])),
            ])),
            $raw
        );
    }

    private function genericResultFromResponse(array $raw, string $productKey): ProviderResult
    {
        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $this->filterNormalized(array_merge($this->baseNormalized($raw), [
                'service' => Str::headline($productKey),
            ])),
            $raw
        );
    }

    private function extractData(array $raw): array
    {
        $data = data_get($raw, 'data', []);

        return is_array($data) ? $data : [];
    }

    private function baseNormalized(array $raw): array
    {
        return array_filter([
            'message' => data_get($raw, 'message'),
            'provider_reference' => $this->responseReference($raw),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function responseReference(array $raw): ?string
    {
        return data_get($raw, 'data.reference');
    }

    private function filterNormalized(array $data): array
    {
        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

    private function genericUnsupportedResult(string $message): ProviderResult
    {
        return new ProviderResult(false, $this->providerName(), null, [], [], $message);
    }
}
