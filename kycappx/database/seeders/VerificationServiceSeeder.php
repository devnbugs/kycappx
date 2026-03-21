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
                'code' => 'PHONE',
                'name' => 'Phone Intelligence Lookup',
                'type' => 'kyc',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 120,
                'default_cost' => 80,
                'required_fields' => ['phone'],
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
                'code' => 'ACCOUNT_NAME_MATCH',
                'name' => 'Bank Account Name Match',
                'type' => 'kyc',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 100,
                'default_cost' => 70,
                'required_fields' => ['account_number', 'bank_code', 'account_name'],
            ],
            [
                'code' => 'US_PHONE',
                'name' => 'US Phone Intelligence Lookup',
                'type' => 'kyc',
                'country' => 'US',
                'is_active' => true,
                'default_price' => 250,
                'default_cost' => 180,
                'required_fields' => ['phone'],
            ],
            [
                'code' => 'US_BIODATA',
                'name' => 'US Biodata Check',
                'type' => 'kyc',
                'country' => 'US',
                'is_active' => true,
                'default_price' => 450,
                'default_cost' => 320,
                'required_fields' => ['first_name', 'last_name', 'dob', 'address_line1', 'city', 'state'],
            ],
            [
                'code' => 'US_ADDRESS',
                'name' => 'US Address Verification',
                'type' => 'kyc',
                'country' => 'US',
                'is_active' => true,
                'default_price' => 320,
                'default_cost' => 220,
                'required_fields' => ['address_line1', 'city', 'state', 'zip'],
            ],
            [
                'code' => 'US_SSN',
                'name' => 'US SSN / TIN Check',
                'type' => 'kyc',
                'country' => 'US',
                'is_active' => true,
                'default_price' => 500,
                'default_cost' => 360,
                'required_fields' => ['ssn', 'first_name', 'last_name', 'dob'],
            ],
        ];

        VerificationService::query()
            ->whereIn('code', ['TIN', 'ACCOUNT', 'SOON'])
            ->delete();

        foreach ($services as $service) {
            VerificationService::updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
