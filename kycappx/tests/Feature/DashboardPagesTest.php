<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_open_the_customer_workspace_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/wallet')->assertOk();
        $this->actingAs($user)->get('/transactions')->assertOk();
        $this->actingAs($user)->get('/verifications')->assertOk();
        $this->actingAs($user)->get('/verifications/new')->assertOk();
        $this->actingAs($user)->get('/sms')->assertOk();
        $this->actingAs($user)->get('/api-keys')->assertOk();
        $this->actingAs($user)->get('/profile')->assertOk();
    }
}
