<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('customer');

        $admin = User::query()->updateOrCreate(
            ['username' => 'qaadmin'],
            [
                'name' => 'QA Admin',
                'email' => 'qaadmin@kycappx.local',
                'phone' => '08000000001',
                'company_name' => 'Kycappx QA',
                'timezone' => 'UTC',
                'theme_preference' => 'system',
                'status' => 'active',
                'settings' => [
                    'security_alerts' => true,
                    'monthly_reports' => true,
                    'marketing_emails' => false,
                    'login_with_google' => true,
                    'social_signup_completed' => true,
                ],
                'preferred_funding_provider' => 'paystack',
                'kyc_level' => 'level_0',
                'email_verified_at' => now(),
                'password' => Hash::make('Admin123!'),
            ]
        );

        $admin->syncRoles(['admin']);

        Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['currency' => 'NGN', 'balance' => 50000, 'status' => 'active']
        );

        $customer = User::query()->updateOrCreate(
            ['username' => 'qauser'],
            [
                'name' => 'QA User',
                'email' => 'qauser@kycappx.local',
                'phone' => '08000000002',
                'company_name' => 'Kycappx QA',
                'timezone' => 'UTC',
                'theme_preference' => 'system',
                'status' => 'active',
                'settings' => [
                    'security_alerts' => true,
                    'monthly_reports' => true,
                    'marketing_emails' => false,
                    'login_with_google' => true,
                    'social_signup_completed' => true,
                ],
                'preferred_funding_provider' => 'paystack',
                'kyc_level' => 'level_0',
                'email_verified_at' => now(),
                'password' => Hash::make('User123!'),
            ]
        );

        $customer->syncRoles(['customer']);

        Wallet::firstOrCreate(
            ['user_id' => $customer->id],
            ['currency' => 'NGN', 'balance' => 10000, 'status' => 'active']
        );
    }
}
