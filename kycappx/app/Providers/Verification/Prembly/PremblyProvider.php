<?php

namespace App\Providers\Verification\Prembly;

use App\Contracts\VerificationProviderInterface;
use App\DTOs\ProviderResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PremblyProvider implements VerificationProviderInterface
{
    private function request(string $method, string $endpoint, array $payload): array
    {
        $timeout = (int) data_get(config('services.prembly'), 'timeout_seconds', 30);
        $body = array_filter($payload, static fn ($value) => $value !== null && $value !== '');

        $request = Http::withHeaders([
                'app-id' => config('services.prembly.app_id'),
                'x-api-key' => config('services.prembly.secret_key'),
            ])
            ->acceptJson()
            ->asJson()
            ->timeout($timeout);
        $url = rtrim((string) config('services.prembly.base_url'), '/').$endpoint;
        $method = Str::upper($method);

        $response = match ($method) {
            'GET' => $request->get($url, $body),
            default => $request->send($method, $url, [
                'json' => $body,
            ]),
        };

        $json = $response->json() ?? [];

        return [
            'ok' => $response->successful() && $this->responseSuccessful($json),
            'body' => $json,
            'status' => $response->status(),
        ];
    }

    private function requestProduct(string $productKey, array $payload): array
    {
        $endpoints = $this->productEndpoints($productKey);
        $method = $this->productMethod($productKey);

        if ($endpoints === []) {
            return [
                'ok' => false,
                'body' => [
                    'detail' => sprintf('Prembly endpoint is not configured for %s.', str_replace('_', ' ', $productKey)),
                ],
                'status' => null,
                'endpoint' => null,
            ];
        }

        $lastResponse = [
            'ok' => false,
            'body' => [],
            'status' => null,
            'endpoint' => null,
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->request($method, $endpoint, $payload);
            $lastResponse = $response + ['endpoint' => $endpoint, 'method' => $method];

            if ($response['ok'] || ! in_array($response['status'], [404, 405], true)) {
                return $lastResponse;
            }
        }

        return $lastResponse;
    }

    public function providerName(): string
    {
        return 'prembly';
    }

    public function verifyCatalogProduct(string $productKey, array $payload, array $definition = []): ProviderResult
    {
        $response = $this->requestProduct($productKey, $payload);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', sprintf('Prembly %s request failed.', str_replace('_', ' ', $productKey)))
            );
        }

        return match (data_get($definition, 'normalizer')) {
            'bvn' => $this->bvnResultFromResponse($raw, $payload),
            'nin' => $this->ninResultFromResponse($raw, $payload),
            'phone' => $this->phoneResultFromResponse($raw, $payload),
            'cac' => $this->cacResultFromResponse($raw, $payload),
            'account_name_match' => $this->accountNameMatchResultFromResponse($raw, $payload),
            default => $this->genericResultFromResponse($raw, $productKey, $definition, $payload),
        };
    }

    public function verifyBvn(array $payload): ProviderResult
    {
        $response = $this->requestProduct('bvn_advance', [
            'number' => $payload['bvn'] ?? $payload['number'] ?? null,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                ok: false,
                provider: $this->providerName(),
                reference: $this->responseReference($raw),
                normalized: [],
                raw: $raw,
                error: data_get($raw, 'detail', 'Prembly BVN verification failed.')
            );
        }

        return $this->bvnResultFromResponse($raw, $payload);
    }

    public function verifyNin(array $payload): ProviderResult
    {
        $response = $this->requestProduct('nin_basic', [
            'number' => $payload['nin'] ?? null,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly NIN verification failed.')
            );
        }

        return $this->ninResultFromResponse($raw, $payload);
    }

    public function verifyPhone(array $payload): ProviderResult
    {
        $number = $payload['phone'] ?? $payload['identifier'] ?? $payload['number'] ?? null;
        $response = $this->requestProduct('basic_phone_number', [
            'number' => $number,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly phone verification failed.')
            );
        }

        return $this->phoneResultFromResponse($raw, $payload);
    }

    public function verifyCac(array $payload): ProviderResult
    {
        $response = $this->requestProduct('advance_cac', [
            'rc_number' => $payload['registration_number'] ?? null,
            'company_type' => $payload['company_type'] ?? 'RC',
            'company_name' => $payload['company_name'] ?? null,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly CAC verification failed.')
            );
        }

        return $this->cacResultFromResponse($raw, $payload);
    }

    public function verifyBankAccountComparison(array $payload): ProviderResult
    {
        $accountNumber = $payload['account_number'] ?? $payload['number'] ?? null;
        $customerName = $payload['account_name'] ?? $payload['customer_name'] ?? $payload['customer'] ?? null;
        $response = $this->requestProduct('account_with_name_comparism', [
            'number' => $accountNumber,
            'bank_code' => $payload['bank_code'] ?? null,
            'customer' => $customerName,
        ]);
        $raw = $response['body'];

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly account name comparison failed.')
            );
        }

        return $this->accountNameMatchResultFromResponse($raw, $payload);
    }

    public function verifyUsBiodata(array $payload): ProviderResult
    {
        $response = $this->requestProduct('us_biodata', [
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
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly USA biodata verification failed.')
            );
        }

        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'first_name' => $data['first_name'] ?? $data['firstName'] ?? ($payload['first_name'] ?? null),
            'middle_name' => $data['middle_name'] ?? $data['middleName'] ?? ($payload['middle_name'] ?? null),
            'last_name' => $data['last_name'] ?? $data['lastName'] ?? ($payload['last_name'] ?? null),
            'dob' => $data['date_of_birth'] ?? $data['dateOfBirth'] ?? ($payload['dob'] ?? null),
            'address' => $data['address'] ?? $this->buildAddress($payload),
            'city' => $data['city'] ?? ($payload['city'] ?? null),
            'state' => $data['state'] ?? ($payload['state'] ?? null),
            'zip' => $data['zip'] ?? ($payload['zip'] ?? null),
            'match_status' => $data['status'] ?? data_get($raw, 'detail'),
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    public function verifyUsAddress(array $payload): ProviderResult
    {
        $response = $this->requestProduct('us_address', [
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
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly USA address verification failed.')
            );
        }

        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'address' => $data['address'] ?? $this->buildAddress($payload),
            'city' => $data['city'] ?? ($payload['city'] ?? null),
            'state' => $data['state'] ?? ($payload['state'] ?? null),
            'zip' => $data['zip'] ?? ($payload['zip'] ?? null),
            'match_status' => $data['status'] ?? data_get($raw, 'detail'),
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    public function verifyUsSsn(array $payload): ProviderResult
    {
        $response = $this->requestProduct('us_ssn', [
            'tin' => $payload['ssn'] ?? $payload['tin'] ?? null,
            'country' => $payload['country'] ?? 'US',
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'date_of_birth' => $this->formatDateForPrembly($payload['dob'] ?? null),
        ]);
        $raw = $response['body'];
        $data = $this->extractData($raw);
        $details = $this->normalizeResponsePayload($data['details'] ?? null);

        if (! $response['ok']) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly USA SSN/TIN verification failed.')
            );
        }

        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'ssn' => $payload['ssn'] ?? $payload['tin'] ?? data_get($details, 'tin'),
            'tin' => data_get($details, 'tin', $payload['tin'] ?? null),
            'country' => $this->firstFilled(data_get($details, 'country'), data_get($data, 'country'), $payload['country'] ?? 'US'),
            'first_name' => $data['first_name'] ?? ($payload['first_name'] ?? null),
            'last_name' => $data['last_name'] ?? ($payload['last_name'] ?? null),
            'dob' => $data['date_of_birth'] ?? ($payload['dob'] ?? null),
            'match_status' => $data['result'] ?? $data['status'] ?? data_get($raw, 'verification.status') ?? data_get($raw, 'detail'),
            'tin_format' => data_get($details, 'format'),
            'tin_type' => data_get($details, 'type'),
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    private function bvnResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $data = $this->extractData($raw, ['data', 'bvn_data']);
        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'bvn' => $this->firstFilled($data['bvn'] ?? null, $data['number'] ?? null, $payload['bvn'] ?? $payload['number'] ?? null),
            'first_name' => $this->firstFilled($data['firstName'] ?? null, $data['first_name'] ?? null),
            'middle_name' => $this->firstFilled($data['middleName'] ?? null, $data['middle_name'] ?? null),
            'last_name' => $this->firstFilled($data['lastName'] ?? null, $data['last_name'] ?? null),
            'dob' => $this->firstFilled($data['dateOfBirth'] ?? null, $data['date_of_birth'] ?? null),
            'phone' => $this->firstFilled($data['phoneNumber1'] ?? null, $data['phoneNumber'] ?? null),
            'alternate_phone' => $data['phoneNumber2'] ?? null,
            'gender' => $data['gender'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['residentialAddress'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'watch_listed' => $data['watchListed'] ?? null,
            'number' => $data['number'] ?? null,
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    private function ninResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $data = $this->extractData($raw, ['data', 'nin_data']);
        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'nin' => $this->firstFilled($data['nin'] ?? null, $payload['nin'] ?? $payload['number_nin'] ?? $payload['number'] ?? null),
            'first_name' => $this->firstFilled($data['firstName'] ?? null, $data['firstname'] ?? null),
            'middle_name' => $this->firstFilled($data['middleName'] ?? null, $data['middlename'] ?? null),
            'last_name' => $this->firstFilled($data['lastName'] ?? null, $data['surname'] ?? null, $data['lastname'] ?? null),
            'dob' => $this->firstFilled($data['dateOfBirth'] ?? null, $data['birthdate'] ?? null),
            'phone' => $this->firstFilled($data['phoneNumber'] ?? null, $data['telephoneno'] ?? null),
            'gender' => $data['gender'] ?? null,
            'address' => $data['residence_address'] ?? null,
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    private function phoneResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $number = $payload['phone'] ?? $payload['identifier'] ?? $payload['number'] ?? null;
        $data = $this->extractData($raw);
        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'phone' => data_get($data, 'numbering.original.complete_phone_number', $number),
            'country' => data_get($data, 'location.country.iso2'),
            'carrier' => data_get($data, 'carrier.name'),
            'phone_type' => Str::lower((string) data_get($data, 'phone_type.description', '')),
            'blocked' => (bool) data_get($data, 'blocklisting.blocked', false),
            'first_name' => data_get($data, 'contact.first_name'),
            'last_name' => data_get($data, 'contact.last_name'),
            'dob' => data_get($data, 'contact.date_of_birth'),
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    private function cacResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $data = $this->extractData($raw, ['data', 'company_data']);
        $company = $this->extractFirstRecord($data);

        if (! is_array($company)) {
            return new ProviderResult(
                false,
                $this->providerName(),
                $this->responseReference($raw),
                [],
                $raw,
                data_get($raw, 'detail', 'Prembly CAC verification failed.')
            );
        }

        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'registration_number' => $company['rc_number'] ?? ($payload['registration_number'] ?? $payload['rc_number'] ?? null),
            'company_name' => $company['company_name'] ?? null,
            'company_status' => $company['company_status'] ?? null,
            'entity_type' => $company['entity_type'] ?? null,
            'registration_date' => $company['registrationDate'] ?? null,
            'email' => $company['email_address'] ?? null,
            'address' => $company['company_address'] ?? null,
            'city' => $company['city'] ?? null,
            'state' => $company['state'] ?? null,
            'directors' => $company['directors'] ?? [],
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    private function accountNameMatchResultFromResponse(array $raw, array $payload): ProviderResult
    {
        $accountNumber = $payload['account_number'] ?? $payload['number'] ?? null;
        $customerName = $payload['account_name'] ?? $payload['customer_name'] ?? $payload['customer'] ?? null;
        $comparisonMatched = (bool) data_get($raw, 'comparism_data.status', false);
        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'account_number' => data_get($raw, 'account_data.account_number', $accountNumber),
            'account_name' => data_get($raw, 'account_data.account_name'),
            'bank_id' => data_get($raw, 'account_data.bank_id'),
            'bank_code' => $payload['bank_code'] ?? null,
            'customer_name' => $customerName,
            'match_status' => $comparisonMatched,
            'confidence' => data_get($raw, 'comparism_data.confidence'),
            'provider_message' => data_get($raw, 'detail'),
        ]));

        return new ProviderResult(
            $comparisonMatched,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw,
            $comparisonMatched ? null : 'The supplied account name does not match the bank account details.'
        );
    }

    private function genericResultFromResponse(array $raw, string $productKey, array $definition, array $payload): ProviderResult
    {
        $data = $this->extractData($raw, ['data', 'result', 'account_data', 'comparism_data']);
        $normalized = $this->filterNormalized(array_merge($this->baseNormalized($raw), [
            'provider_product' => $productKey,
            'method' => Str::upper((string) data_get($definition, 'method', 'POST')),
            'path' => data_get($definition, 'path'),
            'submitted_fields' => collect($payload)
                ->keys()
                ->values()
                ->all(),
            'result' => $data !== [] ? $data : null,
        ]));

        return new ProviderResult(
            true,
            $this->providerName(),
            $this->responseReference($raw),
            $normalized,
            $raw
        );
    }

    private function responseSuccessful(array $body): bool
    {
        $verificationStatus = Str::upper((string) data_get($body, 'verification.status', ''));

        if ($verificationStatus !== '' && ! in_array($verificationStatus, ['VERIFIED', 'SUCCESS', 'COMPLETED'], true)) {
            return false;
        }

        if (data_get($body, 'status') === false || data_get($body, 'success') === false) {
            return false;
        }

        if (data_get($body, 'status') === true || data_get($body, 'success') === true) {
            return true;
        }

        if ($verificationStatus !== '') {
            return true;
        }

        if (in_array((string) data_get($body, 'response_code'), ['00', '200', '201'], true)) {
            return true;
        }

        return $body !== [];
    }

    private function extractData(array $body, array $keys = ['data']): array
    {
        foreach ($keys as $key) {
            $data = $this->normalizeResponsePayload(data_get($body, $key));

            if ($data !== []) {
                return $data;
            }
        }

        return [];
    }

    private function normalizeResponsePayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_object($payload)) {
            $encoded = json_encode($payload);

            if (! is_string($encoded) || $encoded === '') {
                return [];
            }

            $decoded = json_decode($encoded, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (! is_string($payload)) {
            return [];
        }

        $trimmed = trim($payload);

        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $normalized = preg_replace("/'([^']*)'/", '"$1"', $trimmed);
        $normalized = str_replace(['True', 'False', 'None'], ['true', 'false', 'null'], $normalized);
        $decoded = json_decode($normalized, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }

    private function productEndpoints(string $productKey): array
    {
        $product = config("services.prembly.products.$productKey", []);
        $primaryEndpoint = data_get($product, 'path', data_get($product, 'endpoint'));
        $fallbackEndpoints = data_get($product, 'paths', data_get($product, 'endpoints', []));

        return collect([$primaryEndpoint, ...Arr::wrap($fallbackEndpoints)])
            ->filter(fn ($endpoint) => is_string($endpoint) && $endpoint !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function productMethod(string $productKey): string
    {
        return Str::upper((string) data_get(config("services.prembly.products.$productKey", []), 'method', 'POST'));
    }

    private function responseReference(array $body): ?string
    {
        return data_get($body, 'verification.reference') ?: data_get($body, 'reference');
    }

    private function baseNormalized(array $raw): array
    {
        return $this->filterNormalized([
            'provider_status' => data_get($raw, 'verification.status'),
            'provider_message' => data_get($raw, 'detail'),
            'response_code' => data_get($raw, 'response_code'),
            'endpoint_name' => data_get($raw, 'endpoint_name'),
        ]);
    }

    private function filterNormalized(array $payload): array
    {
        return array_filter($payload, static function ($value) {
            if (is_array($value)) {
                return $value !== [];
            }

            return $value !== null && $value !== '';
        });
    }

    private function firstFilled(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                if ($value !== []) {
                    return $value;
                }

                continue;
            }

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractFirstRecord(array $payload): ?array
    {
        if ($payload === []) {
            return null;
        }

        if (! Arr::isList($payload)) {
            return $payload;
        }

        $record = collect($payload)->first(fn ($item) => is_array($item));

        return is_array($record) ? $record : null;
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
