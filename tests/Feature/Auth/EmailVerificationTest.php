<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_verification_screen_renders_user_information(): void
    {
        $user = User::factory()->unverified()->create([
            'name' => 'Auditor Muda',
            'email' => 'auditor.muda@rembangkab.test',
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee('Auditor Muda');
        $response->assertSee('auditor.muda@rembangkab.test');
        $response->assertSee('Kirim Ulang Email Verifikasi');
        $response->assertSee('Menunggu Verifikasi');
    }

    public function test_resend_verification_email_can_be_dispatched(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'auditor.resend@rembangkab.test',
        ]);

        \Illuminate\Support\Facades\Notification::fake();

        $response = $this->actingAs($user)->post(route('verification.store'));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \App\Notifications\AccountVerificationNotification::class
        );
    }
}
