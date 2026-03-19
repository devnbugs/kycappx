<?php

namespace Database\Seeders;

use App\Models\VerificationService;
use Illuminate\Database\Seeder;

class VerificationServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'BVN',
                'name' => 'Bank Verification Number',
                'type' => 'kyc',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 150,
                'default_cost' => 95,
                'required_fields' => ['bvn', 'first_name', 'last_name', 'dob'],
            ],
            [
                'code' => 'NIN',
                'name' => 'National Identity Number',
                'type' => 'kyc',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 200,
                'default_cost' => 130,
                'required_fields' => ['nin', 'first_name', 'last_name', 'dob'],
            ],
            [
                'code' => 'CAC',
                'name' => 'CAC Business Lookup',
                'type' => 'kyb',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 350,
                'default_cost' => 240,
                'required_fields' => ['registration_number', 'company_name'],
            ],
            [
                'code' => 'TIN',
                'name' => 'Tax Identification Number',
                'type' => 'tax',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 180,
                'default_cost' => 120,
                'required_fields' => ['tin', 'name'],
            ],
            [
                'code' => 'PHONE',
                'name' => 'Phone Intelligence Lookup',
                'type' => 'intel',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 120,
                'default_cost' => 80,
                'required_fields' => ['phone'],
            ],
            [
                'code' => 'ACCOUNT',
                'name' => 'Bank Account Lookup',
                'type' => 'bank',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 100,
                'default_cost' => 60,
                'required_fields' => ['account_number', 'bank_code'],
            ],
            [
                'code' => 'SOON',
                'name' => 'More Services Coming Soon',
                'type' => 'soon',
                'country' => 'NG',
                'is_active' => false,
                'default_price' => 0,
                'default_cost' => 0,
                'required_fields' => [],
            ],
        ];

        foreach ($services as $service) {
            VerificationService::updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
