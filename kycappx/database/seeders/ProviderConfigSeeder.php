<?php

namespace Database\Seeders;

use App\Models\ProviderConfig;
use Illuminate\Database\Seeder;

class ProviderConfigSeeder extends Seeder
{
    public function run(): void
    {
        ProviderConfig::updateOrCreate(
            ['provider' => 'prembly'],
            ['is_active' => false, 'priority' => 1, 'config' => ['channel' => 'identity']]
        );

        ProviderConfig::updateOrCreate(
            ['provider' => 'youverify'],
            ['is_active' => false, 'priority' => 2, 'config' => ['channel' => 'identity']]
        );

        ProviderConfig::updateOrCreate(
            ['provider' => 'paystack'],
            ['is_active' => false, 'priority' => 1, 'config' => ['channel' => 'payments', 'product' => 'dva']]
        );

        ProviderConfig::updateOrCreate(
            ['provider' => 'kora'],
            ['is_active' => false, 'priority' => 2, 'config' => ['channel' => 'payments', 'product' => 'dva']]
        );
    }
}
