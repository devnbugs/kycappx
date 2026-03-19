<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_admin_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_users_can_access_admin_pages(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('customer');

        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = User::factory()->create();
        $customer->assignRole('customer');
        $customer->wallet()->create([
            'currency' => 'NGN',
            'balance' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('Operations Overview');

        $this->actingAs($user)->get('/admin/users')->assertOk();
        $this->actingAs($user)->get('/admin/customers')->assertOk();
        $this->actingAs($user)->get('/admin/users/'.$customer->id.'/edit')->assertOk();
        $this->actingAs($user)->get('/admin/services')->assertOk();
        $this->actingAs($user)->get('/admin/providers')->assertOk();
        $this->actingAs($user)->get('/admin/settings/site')->assertOk();
        $this->actingAs($user)->get('/admin/logs/audit')->assertOk();
    }
}
