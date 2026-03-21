<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\ProviderConfig;
use App\Models\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_user_settings_and_wallet_state(): void
    {
        Role::findOrCreate('super-admin');
        Role::findOrCreate('admin');
        Role::findOrCreate('customer');

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $user = User::factory()->create([
            'status' => 'active',
            'theme_preference' => 'system',
        ]);
        $user->assignRole('customer');
        $user->wallet()->create([
            'currency' => 'NGN',
            'balance' => 2500,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put('/admin/users/'.$user->id, [
            'name' => 'Updated User',
            'username' => 'updateduser',
            'email' => 'updated@example.com',
            'phone' => '+2348000000000',
            'company_name' => 'Updated Co',
            'timezone' => 'UTC',
            'status' => 'suspended',
            'theme_preference' => 'dark',
            'roles' => ['customer', 'admin'],
            'wallet_status' => 'frozen',
            'wallet_adjustment' => 500,
            'wallet_adjustment_note' => 'Top up from admin',
            'settings' => [
                'security_alerts' => '1',
                'monthly_reports' => '0',
                'marketing_emails' => '1',
            ],
            'deactivate_api_keys' => '0',
        ]);

        $response->assertRedirect('/admin/users/'.$user->id.'/edit');

        $user->refresh();
        $this->assertSame('Updated User', $user->name);
        $this->assertSame('updateduser', $user->username);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('suspended', $user->status);
        $this->assertSame('dark', $user->theme_preference);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertSame('frozen', $user->wallet->fresh()->status);
        $this->assertSame('3000.00', $user->wallet->fresh()->balance);
        $this->assertTrue((bool) data_get($user->settings, 'security_alerts'));
        $this->assertFalse((bool) data_get($user->settings, 'monthly_reports'));
    }

    public function test_admin_can_update_site_settings(): void
    {
        Role::findOrCreate('admin');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        SiteSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'site_name' => 'Kycappx',
                'site_tagline' => 'Default tagline',
                'support_email' => 'old@example.com',
                'support_phone' => null,
                'default_currency' => 'NGN',
                'default_theme' => 'system',
                'registration_enabled' => true,
                'wallet_funding_enabled' => true,
                'verification_enabled' => true,
                'dark_mode_enabled' => true,
            ]
        );

        $response = $this->actingAs($admin)->put('/admin/settings/site', [
            'site_name' => 'Kycappx Pro',
            'site_tagline' => 'Better onboarding and controls',
            'support_email' => 'support@example.com',
            'support_phone' => '+2348000000000',
            'default_currency' => 'NGN',
            'default_theme' => 'dark',
            'registration_enabled' => '1',
            'wallet_funding_enabled' => '0',
            'verification_enabled' => '1',
            'dark_mode_enabled' => '1',
            'maintenance_message' => 'Maintenance tonight',
            'footer_text' => 'Powered by Kycappx Pro',
        ]);

        $response->assertRedirect('/admin/settings/site');

        $settings = SiteSetting::query()->firstOrFail();
        $this->assertSame('Kycappx Pro', $settings->site_name);
        $this->assertSame('dark', $settings->default_theme);
        $this->assertFalse($settings->wallet_funding_enabled);
    }

    public function test_admin_with_permission_can_toggle_verification_services_and_support_cannot(): void
    {
        $permission = Permission::findOrCreate('admin.services.manage');
        $adminRole = Role::findOrCreate('admin');
        $supportRole = Role::findOrCreate('support');
        $adminRole->givePermissionTo($permission);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $support = User::factory()->create();
        $support->assignRole($supportRole);

        $service = VerificationService::create([
            'code' => 'BVN_BASIC',
            'name' => 'BVN Basic',
            'type' => 'kyc',
            'country' => 'NG',
            'is_active' => true,
            'default_price' => 150,
            'default_cost' => 95,
            'required_fields' => ['number'],
        ]);

        $this->actingAs($support)->put('/admin/services/'.$service->id, [
            'name' => 'BVN Basic',
            'type' => 'kyc',
            'country' => 'NG',
            'default_price' => 150,
            'default_cost' => 95,
            'required_fields' => 'number',
            'is_active' => '0',
        ])->assertForbidden();

        $this->actingAs($admin)->put('/admin/services/'.$service->id, [
            'name' => 'BVN Basic',
            'type' => 'kyc',
            'country' => 'NG',
            'default_price' => 150,
            'default_cost' => 95,
            'required_fields' => 'number',
            'is_active' => '0',
        ])->assertRedirect('/admin/services');

        $this->assertFalse($service->fresh()->is_active);
    }

    public function test_admin_with_permission_can_toggle_provider_products(): void
    {
        $permission = Permission::findOrCreate('admin.providers.manage');
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo($permission);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $provider = ProviderConfig::create([
            'provider' => 'prembly',
            'is_active' => true,
            'priority' => 1,
            'config' => [
                'channel' => 'identity',
                'mode' => 'live',
                'timeout_seconds' => 30,
                'default_product' => 'bvn_basic',
                'enabled_products' => [
                    'bvn_basic' => true,
                    'nin_basic' => true,
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->put('/admin/providers/'.$provider->id, [
            'priority' => 1,
            'channel' => 'identity',
            'mode' => 'live',
            'timeout_seconds' => 30,
            'default_product' => 'bvn_basic',
            'is_active' => '1',
            'enabled_products' => [
                'bvn_basic' => '1',
                'nin_basic' => '0',
            ],
        ]);

        $response->assertRedirect('/admin/providers');
        $this->assertFalse((bool) data_get($provider->fresh()->config, 'enabled_products.nin_basic'));
    }
}
