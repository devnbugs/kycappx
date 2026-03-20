<?php

namespace App\Services\Billing;

use App\Models\DedicatedVirtualAccount;
use App\Models\User;
use App\Services\Billing\Gateways\KoraGateway;
use App\Services\Billing\Gateways\PaystackGateway;
use App\Services\Billing\Gateways\SquadGateway;
use App\Services\Kyc\KycStrengthService;
use App\Services\Providers\ProviderFeatureService;
use App\Services\SiteSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class VirtualAccountService
{
    public function __construct(
        private SiteSettings $siteSettings,
        private PaystackGateway $paystackGateway,
        private KoraGateway $koraGateway,
        private SquadGateway $squadGateway,
        private ProviderFeatureService $providerFeatures,
        private KycStrengthService $kycStrength,
    ) {
    }

    public function providers(): Collection
    {
        $settings = $this->siteSettings->current();

        return collect([
            [
                'code' => 'paystack',
                'name' => 'Paystack DVA',
                'description' => 'Instant account assignment with webhook-based wallet credits.',
                'enabled' => $settings->dva_enabled
                    && $settings->paystack_dva_enabled
                    && $this->providerFeatures->isProductEnabled('paystack', 'dedicated_accounts', true),
                'configured' => $this->paystackGateway->isConfigured(),
            ],
            [
                'code' => 'kora',
                'name' => 'Kora Virtual Account',
                'description' => 'Permanent NGN virtual accounts powered by Korapay.',
                'enabled' => $settings->dva_enabled
                    && $settings->kora_dva_enabled
                    && $this->providerFeatures->isProductEnabled('kora', 'virtual_accounts', true),
                'configured' => $this->koraGateway->isConfigured(),
            ],
            [
                'code' => 'squad',
                'name' => 'Squad Virtual Account',
                'description' => 'GTBank-backed virtual accounts with webhook-driven wallet credits.',
                'enabled' => $settings->dva_enabled
                    && $settings->squad_dva_enabled
                    && $this->providerFeatures->isProductEnabled('squad', 'virtual_accounts', true),
                'configured' => $this->squadGateway->isConfigured(),
            ],
        ]);
    }

    public function assign(User $user, string $provider, array $attributes = []): DedicatedVirtualAccount
    {
        $provider = Str::lower($provider);

        $record = DedicatedVirtualAccount::query()->firstOrNew([
            'user_id' => $user->id,
            'provider' => $provider,
        ]);

        if ($record->exists && $record->status === 'active' && filled($record->account_number)) {
            return $record;
        }

        $providerConfig = $this->providers()->firstWhere('code', $provider);

        if (! $providerConfig || ! $providerConfig['enabled']) {
            throw new RuntimeException('This dedicated account provider is currently disabled.');
        }

        if (! $providerConfig['configured']) {
            throw new RuntimeException('This dedicated account provider has not been configured yet.');
        }

        return match ($provider) {
            'paystack' => $this->assignPaystackAccount($user, $record, $attributes),
            'kora' => $this->assignKoraAccount($user, $record, $attributes),
            'squad' => $this->assignSquadAccount($user, $record, $attributes),
            default => throw new RuntimeException('Unsupported virtual account provider.'),
        };
    }

    public function requery(DedicatedVirtualAccount $account): array
    {
        if ($account->provider !== 'paystack') {
            throw new RuntimeException('Only Paystack accounts support on-demand requery right now.');
        }

        if (! filled($account->account_number) || ! filled(Arr::get($account->meta, 'provider_slug'))) {
            throw new RuntimeException('This Paystack account does not have enough metadata to run a requery.');
        }

        $response = $this->paystackGateway->requeryDedicatedAccount(
            $account->account_number,
            Arr::get($account->meta, 'provider_slug'),
            now()->toDateString(),
        );

        if (! $response['ok']) {
            throw new RuntimeException($response['message']);
        }

        $account->forceFill([
            'requery_after' => now()->addMinutes(10),
            'meta' => array_merge($account->meta ?? [], ['last_requery_message' => $response['message']]),
        ])->save();

        return $response;
    }

    public function syncPaystackAssignment(array $payload): ?DedicatedVirtualAccount
    {
        $user = $this->resolvePaystackUser($payload);

        if (! $user) {
            return null;
        }

        $account = data_get($payload, 'dedicated_account') ?? $payload;
        $bank = data_get($account, 'bank', []);

        return DedicatedVirtualAccount::query()->updateOrCreate(
            ['user_id' => $user->id, 'provider' => 'paystack'],
            [
                'provider_reference' => (string) (data_get($account, 'id') ?? data_get($payload, 'id') ?? ''),
                'customer_reference' => data_get($account, 'customer.customer_code') ?? data_get($payload, 'customer.customer_code'),
                'account_name' => data_get($account, 'account_name') ?? data_get($payload, 'account_name'),
                'account_number' => data_get($account, 'account_number') ?? data_get($payload, 'account_number'),
                'bank_name' => data_get($bank, 'name') ?? data_get($payload, 'bank.name'),
                'bank_code' => data_get($bank, 'slug') ?? data_get($payload, 'bank.slug'),
                'currency' => data_get($account, 'currency', 'NGN'),
                'status' => 'active',
                'is_primary' => true,
                'assigned_at' => data_get($account, 'assignment.assigned_at') ?? now(),
                'meta' => [
                    'provider_slug' => data_get($bank, 'slug') ?? data_get($payload, 'bank.slug'),
                    'payload' => $payload,
                ],
            ],
        );
    }

    public function markPaystackAssignmentFailed(array $payload): void
    {
        $user = $this->resolvePaystackUser($payload);

        if (! $user) {
            return;
        }

        DedicatedVirtualAccount::query()->updateOrCreate(
            ['user_id' => $user->id, 'provider' => 'paystack'],
            [
                'status' => 'failed',
                'meta' => ['payload' => $payload],
            ],
        );
    }

    public function locateAccountFromPayment(string $provider, array $payload): ?DedicatedVirtualAccount
    {
        return match ($provider) {
            'paystack' => DedicatedVirtualAccount::query()
                ->where('provider', 'paystack')
                ->where(function ($query) use ($payload) {
                    $query
                        ->where('account_number', data_get($payload, 'authorization.receiver_bank_account_number'))
                        ->orWhere('customer_reference', data_get($payload, 'customer.customer_code'));
                })
                ->first(),
            'kora' => DedicatedVirtualAccount::query()
                ->where('provider', 'kora')
                ->where(function ($query) use ($payload) {
                    $query
                        ->where('account_number', data_get($payload, 'virtual_bank_account_details.virtual_bank_account.account_number'))
                        ->orWhere('provider_reference', data_get($payload, 'virtual_bank_account_details.virtual_bank_account.account_reference'));
                })
                ->first(),
            'squad' => DedicatedVirtualAccount::query()
                ->where('provider', 'squad')
                ->where(function ($query) use ($payload) {
                    $query
                        ->where('account_number', data_get($payload, 'virtual_account_number'))
                        ->orWhere('customer_reference', data_get($payload, 'customer_identifier'));
                })
                ->first(),
            default => null,
        };
    }

    public function syncKoraAccount(User $user, array $accountData): DedicatedVirtualAccount
    {
        return DedicatedVirtualAccount::query()->updateOrCreate(
            ['user_id' => $user->id, 'provider' => 'kora'],
            [
                'provider_reference' => data_get($accountData, 'account_reference') ?: data_get($accountData, 'unique_id'),
                'customer_reference' => data_get($accountData, 'unique_id'),
                'account_name' => data_get($accountData, 'account_name'),
                'account_number' => data_get($accountData, 'account_number'),
                'bank_name' => data_get($accountData, 'bank_name'),
                'bank_code' => data_get($accountData, 'bank_code'),
                'currency' => data_get($accountData, 'currency', 'NGN'),
                'status' => data_get($accountData, 'account_status', 'active'),
                'is_primary' => true,
                'assigned_at' => now(),
                'meta' => ['payload' => $accountData],
            ],
        );
    }

    private function assignPaystackAccount(User $user, DedicatedVirtualAccount $record, array $attributes): DedicatedVirtualAccount
    {
        [$firstName, $lastName] = $this->splitName($user->name);

        $customer = $this->paystackGateway->createCustomer([
            'email' => $user->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $user->phone,
        ]);

        if (! $customer['ok']) {
            $customer = $this->paystackGateway->fetchCustomer($user->email);
        }

        if (! $customer['ok']) {
            throw new RuntimeException($customer['message']);
        }

        $customerCode = (string) data_get($customer, 'body.data.customer_code');
        $account = $this->paystackGateway->createDedicatedAccount(
            $customerCode,
            $attributes['preferred_bank'] ?? null,
        );

        if (! $account['ok']) {
            throw new RuntimeException($account['message']);
        }

        $data = data_get($account, 'body.data', []);

        $record->fill([
            'provider_reference' => data_get($data, 'id'),
            'customer_reference' => data_get($data, 'customer.customer_code', $customerCode),
            'account_name' => data_get($data, 'account_name'),
            'account_number' => data_get($data, 'account_number'),
            'bank_name' => data_get($data, 'bank.name'),
            'bank_code' => data_get($data, 'bank.slug'),
            'currency' => data_get($data, 'currency', 'NGN'),
            'status' => data_get($data, 'active') ? 'active' : 'pending',
            'is_primary' => true,
            'assigned_at' => data_get($data, 'assignment.assigned_at') ?: now(),
            'meta' => [
                'provider_slug' => data_get($data, 'bank.slug'),
                'customer' => data_get($data, 'customer'),
                'payload' => $data,
            ],
        ]);

        $record->save();

        return $record;
    }

    private function assignKoraAccount(User $user, DedicatedVirtualAccount $record, array $attributes): DedicatedVirtualAccount
    {
        if (! filled($attributes['bvn'] ?? null)) {
            throw new RuntimeException('BVN is required before a Kora virtual account can be assigned.');
        }

        $response = $this->koraGateway->createVirtualAccount([
            'account_name' => $user->name,
            'account_reference' => 'KYCAPPX-'.$user->id.'-'.Str::upper(Str::random(10)),
            'permanent' => true,
            'bank_code' => $attributes['bank_code'] ?? config('services.kora.bank_code'),
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'kyc' => [
                'bvn' => $attributes['bvn'],
                'nin' => $attributes['nin'] ?? null,
            ],
        ]);

        if (! $response['ok']) {
            throw new RuntimeException($response['message']);
        }

        $data = data_get($response, 'body.data', []);

        $record->fill([
            'provider_reference' => data_get($data, 'account_reference') ?: data_get($data, 'unique_id'),
            'customer_reference' => data_get($data, 'unique_id'),
            'account_name' => data_get($data, 'account_name'),
            'account_number' => data_get($data, 'account_number'),
            'bank_name' => data_get($data, 'bank_name'),
            'bank_code' => data_get($data, 'bank_code'),
            'currency' => data_get($data, 'currency', 'NGN'),
            'status' => data_get($data, 'account_status', 'active'),
            'is_primary' => true,
            'assigned_at' => now(),
            'meta' => ['payload' => $data],
        ]);

        $record->save();

        return $record;
    }

    public function squadRequirements(User $user, array $attributes = []): array
    {
        $profile = array_merge($this->kycStrength->profile($user), $attributes);
        $missing = [];

        foreach ([
            'first_name' => 'first name',
            'last_name' => 'last name',
            'dob' => 'date of birth',
            'phone' => 'phone number',
            'gender' => 'gender',
            'bvn' => 'BVN',
            'address_line1' => 'address line 1',
            'city' => 'city',
            'state' => 'state',
        ] as $key => $label) {
            if (! filled($profile[$key] ?? null)) {
                $missing[] = $label;
            }
        }

        return [
            'ready' => $missing === [],
            'missing' => $missing,
            'profile' => $profile,
        ];
    }

    private function assignSquadAccount(User $user, DedicatedVirtualAccount $record, array $attributes): DedicatedVirtualAccount
    {
        $requirements = $this->squadRequirements($user, [
            'bvn' => $attributes['bvn'] ?? data_get($user->kyc_profile, 'bvn'),
        ]);

        if (! $requirements['ready']) {
            throw new RuntimeException('Update your KYC profile with '.collect($requirements['missing'])->implode(', ').' before creating a Squad virtual account.');
        }

        $profile = $requirements['profile'];
        $payload = [
            'customer_identifier' => 'SQUAD_'.$user->id.'_'.Str::upper(Str::random(8)),
            'first_name' => $profile['first_name'],
            'last_name' => $profile['last_name'],
            'mobile_num' => $this->normalizeNigerianPhone($profile['phone']),
            'email' => $user->email,
            'bvn' => $profile['bvn'],
            'dob' => Carbon::parse($profile['dob'])->format('m/d/Y'),
            'address' => $this->buildAddressLine($profile),
            'gender' => $this->normalizeGender($profile['gender']),
            'beneficiary_account' => $attributes['beneficiary_account'] ?? config('services.squad.beneficiary_account'),
        ];

        if (filled($profile['middle_name'] ?? null)) {
            $payload['middle_name'] = $profile['middle_name'];
        }

        $response = $this->squadGateway->createVirtualAccount($payload);

        if (! $response['ok']) {
            throw new RuntimeException($response['message']);
        }

        $data = data_get($response, 'body.data', []);

        $record->fill([
            'provider_reference' => data_get($data, 'customer_identifier'),
            'customer_reference' => data_get($data, 'customer_identifier'),
            'account_name' => trim(collect([
                data_get($data, 'first_name'),
                data_get($data, 'last_name'),
            ])->filter()->implode(' ')) ?: $user->name,
            'account_number' => data_get($data, 'virtual_account_number'),
            'bank_name' => config('services.squad.bank_name', 'GTBank'),
            'bank_code' => data_get($data, 'bank_code', '058'),
            'currency' => 'NGN',
            'status' => 'active',
            'is_primary' => true,
            'assigned_at' => now(),
            'meta' => [
                'payload' => $data,
                'request_payload' => Arr::except($payload, ['bvn']),
            ],
        ]);

        $record->save();

        return $record;
    }

    private function resolvePaystackUser(array $payload): ?User
    {
        $email = Str::lower((string) (data_get($payload, 'customer.email') ?? data_get($payload, 'email')));

        if ($email !== '') {
            return User::query()->where('email', $email)->first();
        }

        $customerCode = (string) (data_get($payload, 'customer.customer_code') ?? data_get($payload, 'customer_code'));

        if ($customerCode === '') {
            return null;
        }

        return DedicatedVirtualAccount::query()
            ->where('provider', 'paystack')
            ->where('customer_reference', $customerCode)
            ->with('user')
            ->first()
            ?->user;
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $firstName = $parts[0] ?? 'Customer';
        $lastName = count($parts) > 1 ? Arr::last($parts) : 'Account';

        return [$firstName, $lastName];
    }

    private function normalizeNigerianPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if (str_starts_with($digits, '234') && strlen($digits) === 13) {
            return '0'.substr($digits, 3);
        }

        return $digits;
    }

    private function normalizeGender(?string $gender): string
    {
        return match (Str::lower((string) $gender)) {
            '2', 'female' => '2',
            default => '1',
        };
    }

    private function buildAddressLine(array $profile): string
    {
        return collect([
            $profile['address_line1'] ?? null,
            $profile['address_line2'] ?? null,
            $profile['city'] ?? null,
            $profile['state'] ?? null,
            $profile['country'] ?? null,
        ])->filter()->implode(', ');
    }
}
