<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_google_redirect_without_credentials_redirects_with_error(): void
    {
        config(['services.google.client_id' => '']);
        config(['services.google.client_secret' => '']);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_google_redirect_with_credentials_redirects_to_google(): void
    {
        config(['services.google.client_id' => 'test-client-id']);
        config(['services.google.client_secret' => 'test-client-secret']);

        $response = $this->get(route('auth.google'));

        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_google_callback_rejects_unregistered_user_because_closed_registration(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-unique-id-12345');
        $socialiteUser->shouldReceive('getEmail')->andReturn('unregistered@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Budi Santoso');
        $socialiteUser->shouldReceive('getNickname')->andReturn('budi');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');

        $user = User::where('email', 'unregistered@example.com')->first();
        $this->assertNull($user);
    }

    public function test_google_callback_authenticates_and_auto_verifies_pre_created_user(): void
    {
        // Akun sudah dibuatkan oleh admin di sistem, namun belum diverifikasi (email_verified_at = null)
        $preCreatedUser = User::factory()->create([
            'email' => 'stafbaru@inspektorat.go.id',
            'email_verified_at' => null,
            'google_id' => null,
            'avatar' => null,
            'is_active' => true,
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-staf-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('stafbaru@inspektorat.go.id');
        $socialiteUser->shouldReceive('getName')->andReturn('Staf Baru');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/staf.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($preCreatedUser);
        $response->assertRedirect(route('dashboard', absolute: false));

        $preCreatedUser->refresh();
        $this->assertNotNull($preCreatedUser->email_verified_at);
        $this->assertEquals('google-id-staf-123', $preCreatedUser->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/staf.jpg', $preCreatedUser->avatar);
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'auditor@inspektorat.go.id',
            'google_id' => null,
            'avatar' => null,
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-existing-999');
        $socialiteUser->shouldReceive('getEmail')->andReturn('auditor@inspektorat.go.id');
        $socialiteUser->shouldReceive('getName')->andReturn('Auditor Resmi');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://google.com/avatar.png');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existingUser);
        $response->assertRedirect(route('dashboard', absolute: false));

        $existingUser->refresh();
        $this->assertEquals('google-id-existing-999', $existingUser->google_id);
        $this->assertEquals('https://google.com/avatar.png', $existingUser->avatar);
    }

    public function test_google_callback_rejects_inactive_user(): void
    {
        $inactiveUser = User::factory()->create([
            'email' => 'nonaktif@inspektorat.go.id',
            'google_id' => 'google-inactive-001',
            'is_active' => false,
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-inactive-001');
        $socialiteUser->shouldReceive('getEmail')->andReturn('nonaktif@inspektorat.go.id');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_google_callback_handles_denied_or_error_gracefully(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }
}
