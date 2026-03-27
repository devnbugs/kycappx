<?php

namespace App\Providers\Verification\Interswitch;

use App\Contracts\VerificationProviderInterface;
use App\DTOs\ProviderResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InterswitchIdentityProvider implements VerificationProviderInterface
{
    public function providerName(): string
    {
        return 'interswitch';
    }

    public function verifyCatalogProduct(string $productKey, array $payload, array $definition = []): ProviderResult
    {
        $route = array_merge(
            (array) data_get(config('verification_engines.routeCatalog'), 'interswitch.'.$productKey, []),
            $definition
        );

        $endpointPath = (string) ($route['endpointPath'] ?? '');

        if ($endpointPath === '') {
            return new ProviderResult(
                false,
                $this->providerName(),
                null,
                [],
                [],
                'Interswitch route is not configured for this verification.'
            );
        }

        $token = $this->accessToken();

        if (! $token) {
            return new ProviderResult(
                false,
                $this->providerName(),
                null,
                [],
                [],
                'Interswitch authentication could not be completed.'
            );
        }

        $endpoint = Str::startsWith($endpointPath, ['http://', 'https://'])
            ? $endpointPath
            : rtrim((string) config('services.interswitch.base_url'), '/').'/'.ltrim($endpointPath, '/');

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->send(Str::upper((string) ($route['requestMethod'] ?? 'POST')), $endpoint, [
                'json' => $this->payloadForRoute($route, $payload),
            ]);

        $raw = $response->json() ?? [];
        $reference = data_get($raw, (string) ($route['referencePath'] ?? 'transactionReference'));
        $successField = (string) ($route['successField'] ?? '');
        $successValue = $route['successValue'] ?? null;
        $responseIsSuccessful = $response->successful();

        if ($successField !== '') {
            $responseIsSuccessful = $responseIsSuccessful
                && (string) data_get($raw, $successField) === (string) $successValue;
        }

        if (! $responseIsSuccessful) {
            return new ProviderResult(
                false,
                $this->providerName(),
                is_string($reference) ? $reference : null,
                [],
                is_array($raw) ? $raw : [],
                (string) data_get($raw, 'responseMessage', data_get($raw, 'message', 'Interswitch identity verification failed.'))
            );
        }

        return match ((string) ($route['normalizer'] ?? 'generic')) {
            'vehicle' => $this->vehicleResultFromResponse(
                raw: is_array($raw) ? $raw : [],
                payload: $payload,
                route: $route,
                reference: is_string($reference) ? $reference : null,
            ),
            default => new ProviderResult(
                true,
                $this->providerName(),
                is_string($reference) ? $reference : null,
                ['message' => (string) data_get($raw, 'responseMessage', 'Verification completed successfully.')],
                is_array($raw) ? $raw : [],
            ),
        };
    }

    public function verifyBvn(array $payload): ProviderResult
    {
        return $this->unsupported('BVN verification is not mapped for Interswitch identity.');
    }

    public function verifyNin(array $payload): ProviderResult
    {
        return $this->unsupported('NIN verification is not mapped for Interswitch identity.');
    }

    public function verifyPhone(array $payload): ProviderResult
    {
        return $this->unsupported('Phone verification is not mapped for Interswitch identity.');
    }

    public function verifyCac(array $payload): ProviderResult
    {
        return $this->unsupported('CAC verification is not mapped for Interswitch identity.');
    }

    public function verifyBankAccountComparison(array $payload): ProviderResult
    {
        return $this->unsupported('Bank account comparison is not mapped for Interswitch identity.');
    }

    public function verifyUsBiodata(array $payload): ProviderResult
    {
        return $this->unsupported('US biodata verification is not mapped for Interswitch identity.');
    }

    public function verifyUsAddress(array $payload): ProviderResult
    {
        return $this->unsupported('US address verification is not mapped for Interswitch identity.');
    }

    public function verifyUsSsn(array $payload): ProviderResult
    {
        return $this->unsupported('US SSN verification is not mapped for Interswitch identity.');
    }

    private function payloadForRoute(array $route, array $payload): array
    {
        $payloadKey = (string) ($route['payloadKey'] ?? '');

        if ($payloadKey === '') {
            return $payload;
        }

        return [
            $payloadKey => $payload['vin'] ?? $payload['vehicle_number'] ?? $payload['number'] ?? null,
        ];
    }

    private function accessToken(): ?string
    {
        return Cache::remember('interswitch.identity.access-token', now()->addMinutes(45), function () {
            $clientId = (string) config('services.interswitch.client_id');
            $clientSecret = (string) config('services.interswitch.client_secret');

            if ($clientId === '' || $clientSecret === '') {
                return null;
            }

            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
                ])
                ->post((string) config('services.interswitch.token_url'), [
                    'grant_type' => 'client_credentials',
                ]);

            if (! $response->successful()) {
                return null;
            }

            return data_get($response->json(), 'access_token');
        });
    }

    private function vehicleResultFromResponse(array $raw, array $payload, array $route, ?string $reference): ProviderResult
    {
        $data = data_get($raw, (string) ($route['dataPath'] ?? 'data'), []);
        $vehicle = is_array($data) && array_is_list($data) ? ($data[0] ?? []) : $data;
        $vehicle = is_array($vehicle) ? $vehicle : [];

        return new ProviderResult(
            true,
            $this->providerName(),
            $reference,
            array_filter([
                'vin' => $vehicle['vin'] ?? $vehicle['vehicleIdentificationNumber'] ?? $payload['vin'] ?? null,
                'plate_number' => $vehicle['plateNumber'] ?? $vehicle['plate_number'] ?? null,
                'vehicle_make' => $vehicle['make'] ?? $vehicle['vehicleMake'] ?? null,
                'vehicle_model' => $vehicle['model'] ?? $vehicle['vehicleModel'] ?? null,
                'vehicle_color' => $vehicle['color'] ?? $vehicle['vehicleColor'] ?? null,
                'registration_status' => $vehicle['status'] ?? $vehicle['registrationStatus'] ?? data_get($raw, 'responseMessage'),
                'owner_name' => $vehicle['ownerName'] ?? $vehicle['owner_name'] ?? null,
                'message' => data_get($raw, 'responseMessage', data_get($raw, 'message')),
            ], fn ($value) => $value !== null && $value !== ''),
            $raw
        );
    }

    private function unsupported(string $message): ProviderResult
    {
        return new ProviderResult(false, $this->providerName(), null, [], [], $message);
    }
}
