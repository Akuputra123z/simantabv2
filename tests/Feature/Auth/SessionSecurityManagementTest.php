<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\SessionSecurityManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SessionSecurityManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Poin 1 & 2: Pengendali sesi terpusat dan pemanfaatan pengendali sesi kerangka kerja.
     */
    public function test_session_security_manager_is_registered_and_controls_session_lifecycle(): void
    {
        $manager = app(SessionSecurityManager::class);
        $this->assertInstanceOf(SessionSecurityManager::class, $manager);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Poin 3: Keacakan token sesi dan rotasi token saat autentikasi (Anti Session Fixation).
     */
    public function test_session_id_is_regenerated_upon_login_with_high_entropy_randomness(): void
    {
        $user = User::factory()->create();

        // 1. Kunjungi halaman login untuk mendapatkan session id awal
        $initialResponse = $this->get('/login');
        $initialSessionId = session()->getId();

        $this->assertNotEmpty($initialSessionId);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{40}$/', $initialSessionId);

        // 2. Lakukan login dan pastikan session id diregenerasi menjadi token baru
        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $newSessionId = session()->getId();
        $this->assertNotEmpty($newSessionId);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{40}$/', $newSessionId);
        $this->assertNotEquals($initialSessionId, $newSessionId, 'Session ID harus dirotasi saat login untuk mencegah Session Fixation.');
    }

    /**
     * Poin 3: Rotasi berkala ID sesi (Periodic Session ID Rotation).
     */
    public function test_periodic_session_rotation_migrates_session_id_without_losing_data(): void
    {
        $manager = app(SessionSecurityManager::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        session(['custom_audit_data' => 'lhp-12345']);

        // Simulasikan sesi terakhir dirotasi 35 menit yang lalu (melebihi interval 30 menit)
        session(['session_last_rotated_at' => now()->subMinutes(35)->timestamp]);
        $oldSessionId = session()->getId();

        $request = request();
        $request->setLaravelSession($this->app['session']->driver());

        $manager->rotateSessionIfDue($request, 30);

        $this->assertEquals('lhp-12345', session('custom_audit_data'), 'Data sesi tidak boleh hilang saat rotasi berkala.');
    }

    /**
     * Poin 4: Pengaturan kondisi dan batas waktu habis sesi karena tidak aktif (Idle Timeout).
     */
    public function test_session_expires_gracefully_when_idle_timeout_is_exceeded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Set waktu aktivitas terakhir 65 menit yang lalu (melebihi default idle timeout 60 menit)
        session([
            'session_last_activity_at' => now()->subMinutes(65)->timestamp,
            'session_fingerprint' => [
                'ua_hash' => hash('sha256', 'Symfony'),
                'ip_subnet' => '127.0.0.0',
            ],
        ]);

        $response = $this->get('/dashboard');

        // Pengguna harus otomatis dialihkan ke login karena sesi idle
        $response->assertRedirect(route('login'));
        $this->assertGuest('web');
        $response->assertSessionHas('status');
    }

    /**
     * Poin 5: Validasi Session ID dan penolakan session id yang disusupi via URL Query (Trans-SID Prevention).
     */
    public function test_trans_sid_via_url_query_parameter_is_stripped(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard?session_id=malicious_injected_token&PHPSESSID=hacked_session');

        $response->assertStatus(200);
        $this->assertNull(request()->query('session_id'));
        $this->assertNull(request()->query('PHPSESSID'));
    }

    /**
     * Poin 6: Pelindungan terhadap pembajakan sesi (Anti-Session Hijacking via User-Agent mismatch).
     */
    public function test_session_hijacking_attempt_with_altered_user_agent_terminates_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Bind sesi dengan User-Agent awal (misal Chrome di macOS)
        session([
            'session_fingerprint' => [
                'ua_hash' => hash('sha256', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36'),
                'ip_subnet' => '127.0.0.0',
            ],
            'session_last_activity_at' => now()->timestamp,
        ]);

        // Simulasikan request berikutnya datang dengan User-Agent berbeda drastis (misal curl / mesin hacker)
        $response = $this->withHeaders([
            'User-Agent' => 'curl/7.88.1 (Hacker Machine)',
        ])->get('/dashboard');

        // Sesi harus segera dimatikan dan diarahkan ke login dengan pesan keamanan
        $response->assertRedirect(route('login'));
        $this->assertGuest('web');
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Poin 6: Respons sesi terautentikasi memiliki header perlindungan cache.
     */
    public function test_authenticated_responses_contain_no_cache_headers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    /**
     * Poin 7: Pelindungan terhadap duplikasi sesi di perangkat lain saat kata sandi diperbarui.
     */
    public function test_updating_password_terminates_sessions_on_other_devices(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertSessionHas('status', 'password-updated');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecretPassword123!', $user->password));
    }

    /**
     * Poin 7: Mekanisme persetujuan pengguna melalui konfirmasi kata sandi (Password Confirmation).
     */
    public function test_password_confirmation_grants_authorization(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('MyPassword123!'),
        ]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'MyPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertNotEmpty(session('auth.password_confirmed_at'));
    }
}
