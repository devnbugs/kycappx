<?php

namespace Database\Seeders;

use App\Models\ProviderConfig;
use Illuminate\Database\Seeder;

class ProviderConfigSeeder extends Seeder
{
    public function run(): void
    {
        $premblyProducts = collect(config('services.prembly.products', []));
        $premblyEnabledProducts = $premblyProducts
            ->mapWithKeys(fn (array $product, string $productKey) => [$productKey => (bool) data_get($product, 'required', false)])
            ->all();

        ProviderConfig::query()
            ->where('provider', 'youverify')
            ->delete();

        ProviderConfig::updateOrCreate(
            ['provider' => 'prembly'],
            [
                'is_active' => false,
                'priority' => 1,
                'config' => [
                    'channel' => 'identity',
                    'mode' => 'live',
                    'timeout_seconds' => 30,
                    'country_scope' => ['NG'],
                    'default_product' => $premblyProducts->has('bvn_basic') ? 'bvn_basic' : $premblyProducts->keys()->first(),
                    'enabled_products' => $premblyEnabledProducts,
                ],
            ]
        );

        ProviderConfig::updateOrCreate(
            ['provider' => 'paystack'],
            [
                'is_active' => false,
                'priority' => 1,
                'config' => [
                    'channel' => 'payments',
                    'mode' => 'live',
                    'timeout_seconds' => 30,
                    'default_product' => 'dedicated_accounts',
                    'enabled_products' => [
                        'customers' => true,
                        'dedicated_accounts' => true,
                        'requery' => true,
                        'transactions' => true,
                        'transfer_recipients' => false,
                        'transfers' => false,
                        'refunds' => false,
                        'identity' => false,
                    ],
                ],
            ]
        );

        ProviderConfig::updateOrCreate(
            ['provider' => 'kora'],
            [
                'is_active' => false,
                'priority' => 3,
                'config' => [
                    'channel' => 'payments',
                    'mode' => 'live',
                    'timeout_seconds' => 30,
                    'default_product' => 'virtual_accounts',
                    'enabled_products' => [
                        'checkout' => true,
                        'bank_transfer' => false,
                        'virtual_accounts' => true,
                        'account_holders' => false,
                        'verification' => true,
                        'virtual_cards' => false,
                        'refunds' => false,
                        'payouts' => false,
                        'balances' => false,
                        'conversions' => false,
                    ],
                ],
            ]
        );

        ProviderConfig::updateOrCreate(
            ['provider' => 'squad'],
            [
                'is_active' => false,
                'priority' => 2,
                'config' => [
                    'channel' => 'payments',
                    'mode' => 'live',
                    'timeout_seconds' => 30,
                    'country_scope' => ['NG'],
                    'default_product' => 'virtual_accounts',
                    'enabled_products' => [
                        'virtual_accounts' => true,
                        'webhooks' => true,
                        'sms_messages' => true,
                        'sms_templates' => true,
                    ],
                ],
            ]
        );
    }
}
