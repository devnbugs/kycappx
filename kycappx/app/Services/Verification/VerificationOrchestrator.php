<?php

namespace App\Services\Verification;

use App\Contracts\VerificationProviderInterface;
use App\Models\User;
use App\Models\VerificationAttempt;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use App\Providers\Verification\Interswitch\InterswitchIdentityProvider;
use App\Providers\Verification\Kora\KoraIdentityProvider;
use App\Providers\Verification\Prembly\PremblyProvider;
use App\Services\Billing\WalletService;
use App\Services\Messaging\SquadSmsService;
use App\Services\SiteSettings;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class VerificationOrchestrator
{
    public function __construct(
        private WalletService $walletService,
        private SiteSettings $siteSettings,
        private SquadSmsService $smsService,
        private VerificationCatalogService $verificationCatalog,
        private IdentityEngineRegistry $identityEngines,
    ) {
    }

    public function submit(User $user, VerificationService $service, array $payload): VerificationRequest
    {
        $reference = 'VER_'.Str::upper(Str::random(14));
        $maskedPayload = $this->maskSensitivePayload($payload);
        $discountRate = $user->currentDiscountRate((float) $this->siteSettings->current()->user_pro_discount_rate);
        $customerPrice = round(max(0, (float) $service->default_price * (1 - ($discountRate / 100))), 2);

        $verificationRequest = VerificationRequest::create([
            'user_id' => $user->id,
            'verification_service_id' => $service->id,
            'reference' => $reference,
            'status' => 'pending',
            'customer_price' => $customerPrice,
            'provider_cost' => $service->default_cost,
            'request_payload' => $payload,
        ]);

        $processedRequest = $this->process($verificationRequest);

        return $processedRequest->forceFill([
            'request_payload' => $maskedPayload,
        ])->fresh(['service']);
    }

    public function process(VerificationRequest $verificationRequest): VerificationRequest
    {
        $verificationRequest->loadMissing(['service', 'user']);

        if ($verificationRequest->completed_at) {
            return $verificationRequest->fresh(['service']);
        }

        $service = $verificationRequest->service;
        $user = $verificationRequest->user;

        if (! $service || ! $user) {
            throw new RuntimeException('Verification request is missing its service or user.');
        }

        $verificationRequest->update([
            'status' => 'processing',
        ]);

        $payload = $this->payloadFromRequest($verificationRequest);
        $maskedPayload = $this->maskSensitivePayload($payload);
        $providers = $this->resolveProviders($service);

        if ($providers === []) {
            $verificationRequest->update([
                'status' => 'failed',
                'request_payload' => $maskedPayload,
                'normalized_response' => [
                    'message' => 'No active identity engine is configured for this verification yet.',
                ],
                'completed_at' => now(),
            ]);

            return $verificationRequest->forceFill([
                'request_payload' => $maskedPayload,
            ])->fresh(['service']);
        }

        $attemptNumber = (int) $verificationRequest->attempts()->max('attempt_no');
        $successfulProvider = null;
        $result = null;
        $lastErrorMessage = null;

        foreach ($providers as $providerBundle) {
            /** @var VerificationProviderInterface $provider */
            $provider = $providerBundle['instance'];
            $route = $providerBundle['route'];

            $attempt = VerificationAttempt::create([
                'verification_request_id' => $verificationRequest->id,
                'provider' => $provider->providerName(),
                'attempt_no' => ++$attemptNumber,
                'status' => 'processing',
                'request_payload' => $maskedPayload,
                'started_at' => now(),
            ]);

            try {
                $result = $this->dispatchVerification($provider, $service, $payload, $route);
            } catch (Throwable $exception) {
                report($exception);

                $lastErrorMessage = $exception->getMessage();
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $lastErrorMessage,
                    'finished_at' => now(),
                ]);

                continue;
            }

            $attempt->update([
                'status' => $result->ok ? 'success' : 'failed',
                'response_payload' => $result->raw,
                'error_message' => $result->error,
                'finished_at' => now(),
            ]);

            if ($result->ok) {
                $successfulProvider = $provider;
                break;
            }

            $lastErrorMessage = $result->error;
        }

        if (! $successfulProvider || ! $result) {
            $verificationRequest->update([
                'status' => 'failed',
                'request_payload' => $maskedPayload,
                'provider_used' => $result?->provider,
                'normalized_response' => [
                    'message' => $result?->error ?: $lastErrorMessage ?: 'Verification provider request failed.',
                ],
                'raw_response' => $result?->raw ?? ['error' => $lastErrorMessage ?: 'Verification provider request failed.'],
                'completed_at' => now(),
            ]);

            return $verificationRequest->forceFill([
                'request_payload' => $maskedPayload,
            ])->fresh(['service']);
        }

        $status = 'success';
        $errorMessage = $result->error;
        $discountRate = $user->currentDiscountRate((float) $this->siteSettings->current()->user_pro_discount_rate);
        $customerPrice = (float) $verificationRequest->customer_price;

        if ($customerPrice > 0) {
            try {
                $this->walletService->debit(
                    userId: $user->id,
                    amount: $customerPrice,
                    reference: 'BILL_'.$verificationRequest->reference,
                    source: 'verification',
                    description: sprintf('%s verification', $service->name),
                    meta: [
                        'provider' => $successfulProvider->providerName(),
                        'discount_rate' => $discountRate,
                    ]
                );
            } catch (Throwable $exception) {
                report($exception);

                $errorMessage = 'Provider completed, but wallet billing could not be captured automatically.';
            }
        }

        $normalizedResponse = $result->normalized;

        if ($result->reference) {
            $normalizedResponse['provider_reference'] = $result->reference;
        }

        if ($errorMessage) {
            $normalizedResponse['message'] = $normalizedResponse['message'] ?? $errorMessage;
        }

        $verificationRequest->update([
            'status' => $status,
            'request_payload' => $maskedPayload,
            'provider_used' => $successfulProvider->providerName(),
            'normalized_response' => $normalizedResponse,
            'raw_response' => $result->raw,
            'completed_at' => now(),
        ]);

        $verificationRequest->refresh();
        $this->smsService->notifyVerificationResult($user, $verificationRequest->loadMissing('service'));

        return $verificationRequest->forceFill([
            'request_payload' => $maskedPayload,
        ])->fresh(['service']);
    }

    private function dispatchVerification(
        VerificationProviderInterface $provider,
        VerificationService $service,
        array $payload,
        array $route
    ) {
        $definition = array_merge($this->verificationCatalog->definitionFor($service) ?? [], $route);
        $productKey = (string) ($route['productKey'] ?? data_get($definition, 'product_key', ''));

        if (! $definition || ! $productKey) {
            throw new RuntimeException('This verification service is not supported yet.');
        }

        return $provider->verifyCatalogProduct($productKey, $payload, $definition);
    }

    private function resolveProviders(VerificationService $service): array
    {
        return collect($this->identityEngines->availableProvidersForService($service))
            ->map(function (string $providerName) use ($service) {
                $provider = match ($providerName) {
                    'prembly' => new PremblyProvider(),
                    'kora' => new KoraIdentityProvider(),
                    'interswitch' => new InterswitchIdentityProvider(),
                    default => null,
                };

                $route = $this->identityEngines->routeForService($providerName, $service);

                if (! $provider || ! $route) {
                    return null;
                }

                return [
                    'instance' => $provider,
                    'route' => $route,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function maskSensitivePayload(array $payload): array
    {
        return collect($payload)->map(function ($value, $key) {
            if (is_array($value)) {
                return $this->maskSensitivePayload($value);
            }

            if (! is_string($value)) {
                return $value;
            }

            return match ($key) {
                'bvn', 'nin', 'ssn', 'tin', 'registration_number', 'account_number' => Str::mask($value, '*', 3, max(strlen($value) - 5, 0)),
                'phone', 'identifier' => Str::mask($value, '*', 3, max(strlen($value) - 5, 0)),
                default => $value,
            };
        })->all();
    }

    private function payloadFromRequest(VerificationRequest $verificationRequest): array
    {
        $payload = $verificationRequest->getRawOriginal('request_payload');

        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
