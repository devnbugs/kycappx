<?php

namespace App\Services\Messaging;

use App\Models\SmsDispatch;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\Billing\Gateways\SquadGateway;
use App\Services\Providers\ProviderFeatureService;
use App\Services\SiteSettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SquadSmsService
{
    public function __construct(
        private SquadGateway $squadGateway,
        private ProviderFeatureService $providerFeatures,
        private SiteSettings $siteSettings,
    ) {
    }

    public function enabled(): bool
    {
        return $this->siteSettings->current()->sms_enabled
            && $this->squadGateway->isConfigured()
            && $this->providerFeatures->isProductEnabled('squad', 'sms_messages', true);
    }

    public function templateEnabled(): bool
    {
        return $this->enabled()
            && $this->providerFeatures->isProductEnabled('squad', 'sms_templates', false);
    }

    public function paginateForUser(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        return SmsDispatch::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function sendInstant(User $user, string $senderId, array $messages, ?string $title = null): SmsDispatch
    {
        $dispatch = SmsDispatch::create([
            'user_id' => $user->id,
            'provider' => 'squad',
            'action' => 'instant_send',
            'sender_id' => $senderId,
            'reference' => 'SMS_'.Str::upper(Str::random(14)),
            'status' => 'processing',
            'title' => $title,
            'message' => collect($messages)->pluck('message')->implode(' | '),
            'recipients' => collect($messages)->pluck('phone_number')->values()->all(),
            'request_payload' => [
                'sender_id' => $senderId,
                'messages' => $messages,
            ],
        ]);

        if (! $this->enabled()) {
            $dispatch->update([
                'status' => 'failed',
                'response_payload' => ['message' => 'SMS is not configured for this workspace.'],
                'completed_at' => now(),
            ]);

            return $dispatch->fresh();
        }

        $response = $this->squadGateway->sendInstantSms([
            'sender_id' => $senderId,
            'messages' => $messages,
        ]);

        $dispatch->update([
            'remote_reference' => data_get($response, 'body.data.data.batch_id'),
            'status' => $response['ok'] ? 'success' : 'failed',
            'response_payload' => $response['body'],
            'completed_at' => now(),
        ]);

        return $dispatch->fresh();
    }

    public function createTemplate(User $user, string $name, string $description, string $message): SmsDispatch
    {
        $dispatch = SmsDispatch::create([
            'user_id' => $user->id,
            'provider' => 'squad',
            'action' => 'template_create',
            'reference' => 'SMSTPL_'.Str::upper(Str::random(14)),
            'status' => 'processing',
            'title' => $name,
            'message' => $message,
            'request_payload' => compact('name', 'description', 'message'),
        ]);

        if (! $this->templateEnabled()) {
            $dispatch->update([
                'status' => 'failed',
                'response_payload' => ['message' => 'SMS templates are not configured for this workspace.'],
                'completed_at' => now(),
            ]);

            return $dispatch->fresh();
        }

        $response = $this->squadGateway->createSmsTemplate([
            'name' => $name,
            'description' => $description,
            'message' => $message,
        ]);

        $dispatch->update([
            'remote_reference' => data_get($response, 'body.data.uuid'),
            'status' => $response['ok'] ? 'success' : 'failed',
            'response_payload' => $response['body'],
            'completed_at' => now(),
        ]);

        return $dispatch->fresh();
    }

    public function notifyVerificationResult(User $user, VerificationRequest $verificationRequest): ?SmsDispatch
    {
        if (! $this->enabled() || ! (bool) data_get($user->settingsPayload(), 'security_alerts', true)) {
            return null;
        }

        $phone = data_get($user->kyc_profile, 'phone') ?: $user->phone;
        $normalizedPhone = $this->normalizePhone($phone);

        if (! $normalizedPhone) {
            return null;
        }

        $serviceName = $verificationRequest->service?->name ?? 'Verification';
        $message = match ($verificationRequest->status) {
            'success' => sprintf('%s %s completed successfully. Ref: %s', config('app.name', 'Kycappx'), $serviceName, $verificationRequest->reference),
            'failed' => sprintf('%s %s failed. Ref: %s', config('app.name', 'Kycappx'), $serviceName, $verificationRequest->reference),
            default => sprintf('%s %s is processing. Ref: %s', config('app.name', 'Kycappx'), $serviceName, $verificationRequest->reference),
        };

        return $this->sendInstant(
            user: $user,
            senderId: (string) config('services.squad.sms_sender_id', 'S-Alert'),
            messages: [[
                'phone_number' => $normalizedPhone,
                'message' => Str::limit($message, 160, ''),
            ]],
            title: 'Verification status update',
        );
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '234') && strlen($digits) === 13) {
            return '0'.substr($digits, 3);
        }

        return $digits;
    }
}
