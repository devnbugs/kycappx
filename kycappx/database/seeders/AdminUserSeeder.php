<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('super-admin');

        $admin = User::query()->updateOrCreate(
            ['username' => 'rhsalisu'],
            [
                'name' => 'Rabiu Salisu',
                'email' => 'rhsalisu@kycappx.local',
                'phone' => null,
                'company_name' => 'Kycappx Operations',
                'timezone' => 'UTC',
                'theme_preference' => 'system',
                'status' => 'active',
                'settings' => [
                    'security_alerts' => true,
                    'monthly_reports' => true,
                    'marketing_emails' => false,
                ],
                'email_verified_at' => now(),
                'password' => Hash::make('Rabiu2004@'),
            ]
        );

        $admin->syncRoles(['super-admin']);

        Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['currency' => 'NGN', 'balance' => 100000, 'status' => 'active']
        );
    }
}
