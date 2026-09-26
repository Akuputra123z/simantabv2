<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptchaSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders_with_captcha(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Kode Keamanan (Captcha)');
        $response->assertSee('data:image/png;base64,');
        $this->assertNotEmpty(session(CaptchaService::SESSION_KEY));
    }

    public function test_captcha_refresh_endpoint_returns_json_and_updates_session(): void
    {
        $this->get('/login');
        $oldCode = session(CaptchaService::SESSION_KEY);

        $response = $this->getJson(route('captcha.refresh'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['image']);
        $this->assertStringStartsWith('data:image/png;base64,', $response->json('image'));
        $this->assertNotEmpty(session(CaptchaService::SESSION_KEY));
    }

    public function test_login_fails_with_invalid_captcha(): void
    {
        $user = User::factory()->create();

        // Visit login to generate captcha in session
        $this->get('/login');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => 'WRONGCODE',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    public function test_login_succeeds_with_valid_captcha(): void
    {
        $user = User::factory()->create();

        // Visit login to generate captcha in session
        $this->get('/login');
        $validCode = session(CaptchaService::SESSION_KEY);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => strtolower($validCode), // Case-insensitive check
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_captcha_replay_attack_prevention(): void
    {
        $user = User::factory()->create();

        $this->get('/login');
        $validCode = session(CaptchaService::SESSION_KEY);

        // Attempt 1 with wrong password but valid captcha: captcha is consumed
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'captcha' => $validCode,
        ]);

        // Attempt 2: reusing the same captcha code must fail immediately
        $response2 = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => $validCode,
        ]);

        $response2->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    public function test_login_fails_with_expired_captcha(): void
    {
        $user = User::factory()->create();

        $this->get('/login');
        $code = session(CaptchaService::SESSION_KEY);

        // Simulate expired timestamp (6 minutes ago)
        session([CaptchaService::EXPIRES_KEY => now()->subMinutes(6)->timestamp]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => $code,
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }
}
