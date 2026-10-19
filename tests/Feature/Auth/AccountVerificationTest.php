<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\AccountVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_request_page_can_be_rendered(): void
    {
        $response = $this->get(route('account.verify.request'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi & Aktivasi Akun', false);
    }

    public function test_verification_request_rejects_unregistered_identifier(): void
    {
        $response = $this->post(route('account.verify.send'), [
            'identifier' => 'unknown@inspektorat.go.id',
        ]);

        $response->assertSessionHasErrors('identifier');
    }

    public function test_verification_request_sends_notification_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'pegawaibaru@inspektorat.go.id',
            'nip' => '199001012024011001',
            'email_verified_at' => null,
            'is_active' => true,
        ]);

        $response = $this->post(route('account.verify.send'), [
            'identifier' => '199001012024011001',
        ]);

        $response->assertSessionHas('status');

        Notification::assertSentTo($user, AccountVerificationNotification::class, function ($notification) use ($user) {
            return str_contains($notification->verificationUrl, (string) $user->id);
        });
    }

    public function test_verification_request_informs_already_verified_user(): void
    {
        $user = User::factory()->create([
            'email' => 'aktif@inspektorat.go.id',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->post(route('account.verify.send'), [
            'identifier' => 'aktif@inspektorat.go.id',
        ]);

        $response->assertSessionHas('info');
    }

    public function test_account_can_be_verified_with_valid_signed_url(): void
    {
        $user = User::factory()->create([
            'email' => 'aktivasi@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);

        $url = URL::temporarySignedRoute(
            'account.verify.execute',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee('Akun Berhasil Diverifikasi!');

        $user->refresh();
        $this->assertTrue($user->isVerified());
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_account_verification_with_invalid_signature_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'tamper@inspektorat.go.id',
            'email_verified_at' => null,
        ]);

        $url = route('account.verify.execute', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $response = $this->get($url);

        $response->assertRedirect(route('account.verify.request'));
        $response->assertSessionHasErrors('identifier');
    }

    public function test_user_can_set_initial_password_after_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'setpassword@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);

        $activationToken = hash_hmac('sha256', $user->id . '|' . $user->email, config('app.key'));

        $response = $this->withSession([
            'activation_user_id' => $user->id,
            'activation_token' => $activationToken,
        ])->post(route('account.verify.setPassword'), [
            'user_id' => $user->id,
            'token' => $activationToken,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));

        $user->refresh();
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertTrue($user->isVerified());
    }

    public function test_dashboard_displays_unverified_alert_when_user_is_not_verified(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => User::ROLE_STAFF_INSPEKTORAT]);

        $user = User::factory()->create([
            'email' => 'unverified@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);
        $user->assignRole(User::ROLE_STAFF_INSPEKTORAT);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Akun Anda Belum Terverifikasi');
        $response->assertSee('Kirim Email Verifikasi');
        $response->assertSee('Ubah Email di Profil');
    }

    public function test_dashboard_does_not_display_unverified_alert_when_user_is_verified(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => User::ROLE_STAFF_INSPEKTORAT]);

        $user = User::factory()->create([
            'email' => 'verified@inspektorat.go.id',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(User::ROLE_STAFF_INSPEKTORAT);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Akun Anda Belum Terverifikasi');
    }

    public function test_opd_dashboard_displays_unverified_alert_when_opd_user_is_not_verified(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => User::ROLE_OPD]);

        $opdUser = User::factory()->create([
            'email' => 'opd@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);
        $opdUser->assignRole(User::ROLE_OPD);

        $response = $this->actingAs($opdUser)->get(route('opd.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Akun Anda Belum Terverifikasi');
        $response->assertSee('Kirim Email Verifikasi');
    }

    public function test_account_can_be_verified_even_when_user_is_currently_authenticated(): void
    {
        $user = User::factory()->create([
            'email' => 'active.session@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);

        $url = URL::temporarySignedRoute(
            'account.verify.execute',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        // Akses saat sedang login (tidak boleh di-redirect ke dashboard oleh middleware guest)
        $response = $this->actingAs($user)->get($url);

        $response->assertStatus(200);
        $response->assertSee('Akun Berhasil Diverifikasi!');
        $this->assertTrue($user->fresh()->isVerified());
    }

    public function test_account_can_be_verified_using_uuid_in_route(): void
    {
        $user = User::factory()->create([
            'email' => 'uuid.verify@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);

        $url = URL::temporarySignedRoute(
            'account.verify.execute',
            now()->addMinutes(60),
            [
                'id' => $user->uuid,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee('Akun Berhasil Diverifikasi!');
        $this->assertTrue($user->fresh()->isVerified());
    }

    public function test_account_verified_page_displays_password_requirement_note(): void
    {
        $user = User::factory()->create([
            'email' => 'note.test@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);

        $url = URL::temporarySignedRoute(
            'account.verify.execute',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee('Ketentuan kata sandi:');
        $response->assertSee('huruf besar (A-Z)');
        $response->assertSee('huruf kecil (a-z)');
    }

    public function test_password_validation_error_is_in_indonesian_when_mixed_case_is_missing(): void
    {
        $user = User::factory()->create([
            'email' => 'weak.password@inspektorat.go.id',
            'email_verified_at' => null,
            'is_active' => true,
        ]);

        $activationToken = hash_hmac('sha256', $user->id . '|' . $user->email, config('app.key'));

        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols();
        });

        // Coba submit password tanpa huruf besar (hanya lowercase dan angka)
        $response = $this->withSession([
            'activation_user_id' => $user->id,
            'activation_token' => $activationToken,
        ])->post(route('account.verify.setPassword'), [
            'user_id' => $user->id,
            'token' => $activationToken,
            'password' => 'semuahurufkecil123!',
            'password_confirmation' => 'semuahurufkecil123!',
        ]);

        $response->assertSessionHasErrors(['password']);
        $errors = session('errors')->get('password');
        $this->assertStringContainsString('kombinasi huruf besar (A-Z) dan huruf kecil (a-z)', implode(' ', $errors));
        $this->assertStringNotContainsString('The password field must contain at least one uppercase', implode(' ', $errors));
    }
}
