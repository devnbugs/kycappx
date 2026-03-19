<?php

namespace App\Services\Verification;

use App\Contracts\VerificationProviderInterface;
use App\Models\ProviderConfig;
use App\Models\User;
use App\Models\VerificationAttempt;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use App\Providers\Verification\Prembly\PremblyProvider;
use App\Providers\Verification\Youverify\YouverifyProvider;
use App\Services\Billing\WalletService;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class VerificationOrchestrator
{
    public function __construct(private WalletService $walletService)
    {
    }

    public function submit(User $user, VerificationService $service, array $payload): VerificationRequest
    {
        $reference = 'VER_'.Str::upper(Str::random(14));
        $maskedPayload = $this->maskSensitivePayload($payload);

        $verificationRequest = VerificationRequest::create([
            'user_id' => $user->id,
            'verification_service_id' => $service->id,
            'reference' => $reference,
            'status' => 'processing',
            'customer_price' => $service->default_price,
            'provider_cost' => $service->default_cost,
            'request_payload' => $maskedPayload,
        ]);

        $provider = $this->resolveProvider();

        if (! $provider) {
            $verificationRequest->update([
                'status' => 'manual_review',
                'normalized_response' => [
                    'message' => 'Automation is not configured for this verification yet.',
                ],
                'completed_at' => now(),
            ]);

            return $verificationRequest->fresh(['service']);
        }

        $attempt = VerificationAttempt::create([
            'verification_request_id' => $verificationRequest->id,
            'provider' => $provider->providerName(),
            'attempt_no' => 1,
            'status' => 'processing',
            'request_payload' => $maskedPayload,
            'started_at' => now(),
        ]);

        try {
            $result = $this->dispatchVerification($provider, $service, $payload);
        } catch (Throwable $exception) {
            report($exception);

            $attempt->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            $verificationRequest->update([
                'status' => 'failed',
                'provider_used' => $provider->providerName(),
                'normalized_response' => ['message' => 'Verification provider request failed.'],
                'raw_response' => ['error' => $exception->getMessage()],
                'completed_at' => now(),
            ]);

            return $verificationRequest->fresh(['service']);
        }

        $status = $result->ok ? 'success' : 'failed';
        $errorMessage = $result->error;

        if ($result->ok && (float) $service->default_price > 0) {
            try {
                $this->walletService->debit(
                    userId: $user->id,
                    amount: (float) $service->default_price,
                    reference: 'BILL_'.$verificationRequest->reference,
                    source: 'verification',
                    description: sprintf('%s verification', $service->name),
                    meta: ['provider' => $provider->providerName()]
                );
            } catch (Throwable $exception) {
                report($exception);

                $status = 'manual_review';
                $errorMessage = 'Provider completed, but billing needs manual review.';
            }
        }

        $attempt->update([
            'status' => $status === 'success' ? 'success' : 'failed',
            'response_payload' => $result->raw,
            'error_message' => $errorMessage,
            'finished_at' => now(),
        ]);

        $normalizedResponse = $result->normalized;

        if ($status === 'manual_review') {
            $normalizedResponse['billing_message'] = $errorMessage;
        }

        $verificationRequest->update([
            'status' => $status,
            'provider_used' => $provider->providerName(),
            'normalized_response' => $normalizedResponse,
            'raw_response' => $result->raw,
            'completed_at' => now(),
        ]);

        return $verificationRequest->fresh(['service']);
    }

    private function dispatchVerification(
        VerificationProviderInterface $provider,
        VerificationService $service,
        array $payload
    ) {
        return match (strtoupper($service->code)) {
            'BVN' => $provider->verifyBvn($payload),
            'NIN' => $provider->verifyNin($payload),
            'CAC' => $provider->verifyCac($payload),
            default => throw new RuntimeException('This verification service is not supported yet.'),
        };
    }

    private function resolveProvider(): ?VerificationProviderInterface
    {
        $configuredProviders = ProviderConfig::query()
            ->active()
            ->orderBy('priority')
            ->pluck('provider')
            ->all();

        if (empty($configuredProviders)) {
            $configuredProviders = array_values(array_filter([
                filled(config('services.prembly.app_id')) && filled(config('services.prembly.secret_key')) ? 'prembly' : null,
                filled(config('services.youverify.token')) ? 'youverify' : null,
            ]));
        }

        foreach ($configuredProviders as $providerName) {
            $provider = match ($providerName) {
                'prembly' => new PremblyProvider(),
                'youverify' => new YouverifyProvider(),
                default => null,
            };

            if ($provider) {
                return $provider;
            }
        }

        return null;
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
                'bvn', 'nin', 'registration_number' => Str::mask($value, '*', 3, max(strlen($value) - 5, 0)),
                default => $value,
            };
        })->all();
    }
}
