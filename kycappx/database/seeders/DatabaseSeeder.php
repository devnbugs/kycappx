<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SiteSettingsSeeder::class,
            ProviderConfigSeeder::class,
            VerificationServiceSeeder::class,
            AdminUserSeeder::class,
        ]);

        $customer = User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        Role::findOrCreate('customer');
        Role::findOrCreate('super-admin');

        $customer->assignRole('customer');

        Wallet::firstOrCreate([
            'user_id' => $customer->id,
        ], [
            'currency' => 'NGN',
            'balance' => 25000,
            'status' => 'active',
        ]);
    }
}
