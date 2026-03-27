<?php

namespace App\Services\Verification;

use App\Models\VerificationAttempt;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use Illuminate\Support\Str;

class VerificationResultPresenter
{
    public function __construct(private IdentityEngineRegistry $identityEngines)
    {
    }

    public function present(VerificationRequest $verificationRequest, bool $public = true): array
    {
        $verificationRequest->loadMissing(['service', 'attempts']);

        $service = $verificationRequest->service;
        $normalized = is_array($verificationRequest->normalized_response) ? $verificationRequest->normalized_response : [];
        $requestPayload = is_array($verificationRequest->request_payload) ? $verificationRequest->request_payload : [];
        $template = $this->templateFor($service, $normalized);
        $providerCode = strtolower((string) $verificationRequest->provider_used);
        $providerPublicLabel = $providerCode !== '' ? $this->identityEngines->publicLabel($providerCode) : null;
        $providerAdminLabel = $providerCode !== '' ? $this->identityEngines->adminLabel($providerCode) : null;
        $subjectName = $this->subjectName($normalized, $requestPayload);

        return [
            'reference' => $verificationRequest->reference,
            'status' => $verificationRequest->status,
            'statusLabel' => Str::headline(str_replace('_', ' ', $verificationRequest->status)),
            'statusTone' => $this->statusTone($verificationRequest->status),
            'serviceName' => $service?->name ?? 'Verification Response',
            'serviceCode' => $service?->code ?? 'UNKNOWN',
            'template' => $template,
            'subjectName' => $subjectName,
            'identityLabel' => $this->identityLabel($template),
            'identityValue' => $this->identityValue($template, $normalized, $requestPayload),
            'providerCode' => $providerCode ?: null,
            'providerPublicLabel' => $providerPublicLabel,
            'providerAdminLabel' => $providerAdminLabel,
            'providerDisplayLabel' => $public
                ? ($providerPublicLabel ?: 'Awaiting engine')
                : ($providerAdminLabel ?: 'Awaiting engine'),
            'providerVersion' => $providerPublicLabel,
            'message' => $this->message($verificationRequest->status, $normalized, is_array($verificationRequest->raw_response) ? $verificationRequest->raw_response : []),
            'summaryItems' => $this->summaryItems($template, $normalized, $requestPayload, $subjectName),
            'detailItems' => $this->detailItems($normalized, $template),
            'requestItems' => $this->flattenItems($requestPayload, ['image', 'selfie', 'photo', 'signature']),
            'metaItems' => $this->metaItems($verificationRequest, $public, $providerPublicLabel, $providerAdminLabel),
            'attempts' => $verificationRequest->attempts
                ->map(fn (VerificationAttempt $attempt) => $this->attemptItem($attempt, $public))
                ->values()
                ->all(),
            'rawJson' => json_encode(
                $verificationRequest->raw_response ?? [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) ?: '{}',
            'photo' => data_get($normalized, 'photo'),
            'signature' => data_get($normalized, 'signature'),
            'printModes' => $template === 'ninSlip' ? ['standard', 'premium'] : ['standard'],
            'downloadName' => Str::slug(($service?->code ?? 'verification').'-'.$verificationRequest->reference).'.html',
        ];
    }

    private function templateFor(?VerificationService $service, array $normalized): string
    {
        $configured = $service ? $this->identityEngines->responseTemplate($service) : 'auto';

        if ($configured !== 'auto') {
            return $configured;
        }

        return match (true) {
            filled(data_get($normalized, 'nin')) || str_contains(strtoupper((string) $service?->code), 'NIN') => 'ninSlip',
            filled(data_get($normalized, 'bvn')) || str_contains(strtoupper((string) $service?->code), 'BVN') => 'bvnSlip',
            filled(data_get($normalized, 'vin')) || filled(data_get($normalized, 'plate_number')) || str_contains(strtoupper((string) $service?->code), 'VIN') => 'vehicleSlip',
            default => 'report',
        };
    }

    private function identityLabel(string $template): string
    {
        return match ($template) {
            'ninSlip' => 'NIN',
            'bvnSlip' => 'BVN',
            'vehicleSlip' => 'VIN',
            default => 'Reference',
        };
    }

    private function identityValue(string $template, array $normalized, array $requestPayload): ?string
    {
        return match ($template) {
            'ninSlip' => $this->firstFilled(
                data_get($normalized, 'nin'),
                data_get($requestPayload, 'nin'),
                data_get($requestPayload, 'number'),
                data_get($requestPayload, 'number_nin')
            ),
            'bvnSlip' => $this->firstFilled(
                data_get($normalized, 'bvn'),
                data_get($requestPayload, 'bvn'),
                data_get($requestPayload, 'number')
            ),
            'vehicleSlip' => $this->firstFilled(
                data_get($normalized, 'vin'),
                data_get($requestPayload, 'vin'),
                data_get($requestPayload, 'vehicle_number'),
                data_get($requestPayload, 'number')
            ),
            default => null,
        };
    }

    private function subjectName(array $normalized, array $requestPayload): string
    {
        $fullName = trim(collect([
            data_get($normalized, 'first_name'),
            data_get($normalized, 'middle_name'),
            data_get($normalized, 'last_name'),
        ])->filter()->implode(' '));

        if ($fullName !== '') {
            return $fullName;
        }

        return $this->firstFilled(
            data_get($normalized, 'company_name'),
            data_get($normalized, 'owner_name'),
            trim((string) collect([
                data_get($requestPayload, 'first_name'),
                data_get($requestPayload, 'middle_name'),
                data_get($requestPayload, 'last_name'),
            ])->filter()->implode(' ')),
            data_get($requestPayload, 'company_name'),
            'Identity Subject'
        );
    }

    private function message(string $status, array $normalized, array $raw): string
    {
        return $this->firstFilled(
            data_get($normalized, 'message'),
            data_get($raw, 'message'),
            data_get($raw, 'detail'),
            data_get($raw, 'responseMessage'),
            match ($status) {
                'success' => 'Verification completed successfully.',
                'failed' => 'Verification could not be completed.',
                default => 'Verification is still processing.',
            }
        );
    }

    private function summaryItems(string $template, array $normalized, array $requestPayload, string $subjectName): array
    {
        return match ($template) {
            'ninSlip' => $this->buildItems([
                ['Full Name', $subjectName],
                ['NIN', $this->identityValue($template, $normalized, $requestPayload)],
                ['Phone', $this->firstFilled(data_get($normalized, 'phone'), data_get($requestPayload, 'phone'))],
                ['Date of Birth', data_get($normalized, 'dob')],
                ['Gender', data_get($normalized, 'gender')],
                ['Address', data_get($normalized, 'address')],
            ]),
            'bvnSlip' => $this->buildItems([
                ['Full Name', $subjectName],
                ['BVN', $this->identityValue($template, $normalized, $requestPayload)],
                ['Phone', $this->firstFilled(data_get($normalized, 'phone'), data_get($requestPayload, 'phone'))],
                ['Date of Birth', data_get($normalized, 'dob')],
                ['Match Status', data_get($normalized, 'match_status')],
                ['Address', data_get($normalized, 'address')],
            ]),
            'vehicleSlip' => $this->buildItems([
                ['Owner', $subjectName],
                ['VIN', $this->identityValue($template, $normalized, $requestPayload)],
                ['Plate Number', data_get($normalized, 'plate_number')],
                ['Make', data_get($normalized, 'vehicle_make')],
                ['Model', data_get($normalized, 'vehicle_model')],
                ['Status', data_get($normalized, 'registration_status')],
            ]),
            default => array_slice($this->detailItems($normalized, $template), 0, 6),
        };
    }

    private function detailItems(array $normalized, string $template): array
    {
        return $this->flattenItems($normalized, array_filter([
            'message',
            'provider_reference',
            'photo',
            'signature',
        ]));
    }

    private function metaItems(
        VerificationRequest $verificationRequest,
        bool $public,
        ?string $providerPublicLabel,
        ?string $providerAdminLabel
    ): array {
        return $this->buildItems([
            ['Reference', $verificationRequest->reference],
            ['Status', Str::headline(str_replace('_', ' ', $verificationRequest->status))],
            ['Engine', $public ? $providerPublicLabel : $providerAdminLabel],
            ['Version', $providerPublicLabel],
            ['Provider Reference', data_get($verificationRequest->normalized_response, 'provider_reference')],
            ['Submitted', $verificationRequest->created_at?->format('M d, Y H:i')],
            ['Completed', $verificationRequest->completed_at?->format('M d, Y H:i')],
            ['Customer Price', 'NGN '.number_format((float) $verificationRequest->customer_price, 2)],
        ]);
    }

    private function attemptItem(VerificationAttempt $attempt, bool $public): array
    {
        $provider = strtolower((string) $attempt->provider);

        return [
            'engine' => $provider !== ''
                ? ($public ? $this->identityEngines->publicLabel($provider) : $this->identityEngines->adminLabel($provider))
                : 'Unassigned',
            'version' => $provider !== '' ? $this->identityEngines->publicLabel($provider) : null,
            'providerName' => $provider !== '' ? $this->identityEngines->adminLabel($provider) : null,
            'status' => $attempt->status,
            'statusLabel' => Str::headline(str_replace('_', ' ', $attempt->status)),
            'statusTone' => $this->statusTone($attempt->status),
            'error' => $attempt->error_message,
            'startedAt' => $attempt->started_at?->format('M d, Y H:i:s'),
            'finishedAt' => $attempt->finished_at?->format('M d, Y H:i:s'),
        ];
    }

    private function flattenItems(array $data, array $exclude = [], string $prefix = ''): array
    {
        $items = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (in_array($key, $exclude, true) || in_array($path, $exclude, true)) {
                continue;
            }

            if (is_array($value)) {
                $items = array_merge($items, $this->flattenItems($value, $exclude, $path));

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $items[] = [
                'label' => Str::headline(str_replace(['.', '_'], ' ', $path)),
                'value' => $this->stringify($value),
            ];
        }

        return $items;
    }

    private function buildItems(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                return [
                    'label' => $item[0],
                    'value' => $this->stringify($item[1] ?? null),
                ];
            })
            ->filter(fn (array $item) => $item['value'] !== null && $item['value'] !== '')
            ->values()
            ->all();
    }

    private function stringify(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    private function statusTone(string $status): string
    {
        return match ($status) {
            'success' => 'success',
            'failed' => 'danger',
            'manual_review' => 'warning',
            default => 'info',
        };
    }

    private function firstFilled(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return is_scalar($value) ? (string) $value : null;
            }
        }

        return null;
    }
}
