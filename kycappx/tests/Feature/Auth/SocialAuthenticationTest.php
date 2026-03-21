<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_sign_in_redirect_uses_the_callback_route_when_redirect_env_is_missing(): void
    {
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-secret',
            'services.google.redirect' => null,
        ]);

        $provider = Mockery::mock(AbstractProvider::class);
        $provider->shouldReceive('redirectUrl')
            ->once()
            ->with(route('social.callback', ['provider' => 'google'], absolute: true))
            ->andReturnSelf();
        $provider->shouldReceive('scopes')
            ->once()
            ->with(['openid', 'profile', 'email'])
            ->andReturnSelf();
        $provider->shouldReceive('stateless')
            ->once()
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/v2/auth'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('social.redirect', ['provider' => 'google']))
            ->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
    }

    public function test_google_callback_can_log_in_an_existing_user_and_link_the_account(): void
    {
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-secret',
            'services.google.redirect' => null,
        ]);

        $user = User::factory()->create([
            'email' => 'member@example.com',
            'email_verified_at' => null,
            'last_login_at' => null,
        ]);

        $provider = Mockery::mock(AbstractProvider::class);
        $provider->shouldReceive('redirectUrl')
            ->once()
            ->with(route('social.callback', ['provider' => 'google'], absolute: true))
            ->andReturnSelf();
        $provider->shouldReceive('scopes')
            ->once()
            ->with(['openid', 'profile', 'email'])
            ->andReturnSelf();
        $provider->shouldReceive('stateless')
            ->once()
            ->andReturnSelf();
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->socialiteUser(
                id: 'google-123',
                name: 'Member Example',
                email: 'Member@Example.com',
                avatar: 'https://example.com/avatar.png',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNotNull($user->fresh()->last_login_at);

        $this->assertDatabaseHas(SocialAccount::class, [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
            'provider_email' => 'member@example.com',
        ]);
    }

    public function test_google_sign_in_fails_gracefully_when_credentials_are_missing(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.google.redirect' => null,
        ]);

        $this->from(route('login'))
            ->get(route('social.redirect', ['provider' => 'google']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'login' => 'Google sign-in is not configured yet. Please contact support.',
            ]);
    }

    private function socialiteUser(string $id, string $name, string $email, string $avatar): SocialiteUser
    {
        $user = new SocialiteUser();
        $user->map([
            'id' => $id,
            'nickname' => null,
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
        ]);
        $user->token = 'google-access-token';
        $user->refreshToken = 'google-refresh-token';
        $user->expiresIn = 3600;

        return $user;
    }
}
