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
            ProviderConfigSeeder::class,
            VerificationServiceSeeder::class,
        ]);

        $customer = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $admin = User::factory()->create([
            'name' => 'Operations Admin',
            'email' => 'admin@kycappx.test',
        ]);

        Role::findOrCreate('customer');
        Role::findOrCreate('super-admin');

        $customer->assignRole('customer');
        $admin->assignRole('super-admin');

        Wallet::firstOrCreate([
            'user_id' => $customer->id,
        ], [
            'currency' => 'NGN',
            'balance' => 25000,
            'status' => 'active',
        ]);

        Wallet::firstOrCreate([
            'user_id' => $admin->id,
        ], [
            'currency' => 'NGN',
            'balance' => 100000,
            'status' => 'active',
        ]);
    }
}
