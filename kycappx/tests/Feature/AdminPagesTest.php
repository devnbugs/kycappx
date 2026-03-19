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

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('Operations Overview');

        $this->actingAs($user)->get('/admin/customers')->assertOk();
        $this->actingAs($user)->get('/admin/services')->assertOk();
    }
}
