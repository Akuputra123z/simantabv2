<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Services\AuditLoggerService;
use App\Services\TimeSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Monolog\Level;
use Monolog\LogRecord;
use Tests\TestCase;

class ErrorHandlingAndLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Daftarkan route testing khusus simulasi error
        Route::get('/_test-sql-error', function () {
            // Simulasi query SQL yang sengaja salah/error
            return DB::select("SELECT * FROM non_existent_table_for_testing WHERE secret_column = 'super_secret_query_string'");
        });

        Route::get('/_test-forbidden-error', function () {
            abort(403, 'Akses ke berkas rahasia ditolak.');
        });
    }

    /**
     * Poin 1 & 2: Error SQL tidak boleh bocor ke pengguna, dan harus mengembalikan incident ID.
     */
    public function test_sql_error_does_not_leak_query_or_table_and_provides_incident_id(): void
    {
        // 1. Uji pada request JSON / API
        $jsonResponse = $this->getJson('/_test-sql-error');

        $jsonResponse->assertStatus(500);
        $jsonResponse->assertJsonStructure(['success', 'message', 'incident_id']);
        $jsonResponse->assertJsonFragment(['success' => false]);

        $content = $jsonResponse->getContent();
        $this->assertStringNotContainsString('non_existent_table_for_testing', $content, 'Nama tabel SQL tidak boleh bocor ke response!');
        $this->assertStringNotContainsString('super_secret_query_string', $content, 'Query SQL tidak boleh bocor ke response!');
        $this->assertStringNotContainsString('SQLSTATE', $content, 'Kode internal SQLSTATE tidak boleh bocor ke response!');

        // 2. Uji pada request Web HTML biasa
        $webResponse = $this->get('/_test-sql-error');

        $webResponse->assertStatus(500);
        $webContent = $webResponse->getContent();
        $this->assertStringNotContainsString('non_existent_table_for_testing', $webContent);
        $this->assertStringNotContainsString('super_secret_query_string', $webContent);
        $this->assertStringContainsString('ID Referensi Insiden', $webContent);
    }

    /**
     * Poin 2: Penanganan kesalahan terprediksi (403 Forbidden).
     */
    public function test_expected_errors_are_handled_cleanly(): void
    {
        // Uji 403 JSON
        $json403 = $this->getJson('/_test-forbidden-error');
        $json403->assertStatus(403);
        $json403->assertJsonFragment(['success' => false]);

        // Uji 403 HTML
        $html403 = $this->get('/_test-forbidden-error');
        $html403->assertStatus(403);
        $html403->assertSee('Akses Tidak Diizinkan');
    }

    /**
     * Poin 3: Masking data sensitif dalam log (password, token, kredensial).
     */
    public function test_sensitive_data_is_masked_in_log_context(): void
    {
        $processor = new \App\Logging\Processors\MaskSensitiveDataProcessor();

        $dirtyData = [
            'username' => 'john_doe',
            'password' => 'Secret123!@#',
            'password_confirmation' => 'Secret123!@#',
            'token' => 'plain_text_bearer_token',
            'api_key' => 'secret_api_key_value',
            'authorization' => 'Bearer eyJhbGciOi...',
            'profile' => [
                'current_password' => 'OldSecret123!',
                'email' => 'john@example.com',
            ],
        ];

        $cleaned = $processor->maskArray($dirtyData);

        $this->assertEquals('john_doe', $cleaned['username']);
        $this->assertEquals('john@example.com', $cleaned['profile']['email']);

        $this->assertStringContainsString('REDACTED', $cleaned['password']);
        $this->assertStringContainsString('REDACTED', $cleaned['password_confirmation']);
        $this->assertStringContainsString('REDACTED', $cleaned['token']);
        $this->assertStringContainsString('REDACTED', $cleaned['api_key']);
        $this->assertStringContainsString('REDACTED', $cleaned['authorization']);
        $this->assertStringContainsString('REDACTED', $cleaned['profile']['current_password']);

        $this->assertStringNotContainsString('Secret123!@#', json_encode($cleaned));
    }

    /**
     * Poin 4: Pencatatan cakupan log audit untuk aktivitas login, logout, dan insiden.
     */
    public function test_audit_logger_service_records_auth_activities(): void
    {
        $user = User::factory()->create([
            'email' => 'audit_test@example.com',
            'is_active' => true,
        ]);

        $auditLogger = app(AuditLoggerService::class);

        // 1. Log Login Success
        $auditLogger->logAuthSuccess($user);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'authentication',
            'causer_id' => $user->id,
        ]);

        // 2. Log Login Failed
        $auditLogger->logAuthFailed('invalid_user@example.com', null, 'Kata sandi salah');

        $this->assertTrue(
            \Spatie\Activitylog\Models\Activity::where('log_name', 'authentication')
                ->where('description', 'like', '%Percobaan login gagal untuk identifier: invalid_user@example.com%')
                ->exists(),
            'Gagal login harus tercatat di database activity_log'
        );

        // 3. Log Security Incident
        $auditLogger->logSecurityIncident('TAMPER_ATTEMPT', null, ['target' => '/admin']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'security_incident',
        ]);
    }

    /**
     * Poin 5 & 6: Log dienkripsi sebelum ditulis dan terlindungi dari manipulasi (HMAC verified).
     */
    public function test_encrypted_log_handler_encrypts_and_verifies_hmac(): void
    {
        $testLogPath = storage_path('logs/test-audit-encrypted.log');
        if (file_exists($testLogPath)) {
            @unlink($testLogPath);
        }

        $handler = new \App\Logging\Handlers\EncryptedLogHandler($testLogPath);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'secure_audit',
            level: Level::Info,
            message: 'User berhasil mengakses data audit rahasia',
            context: ['action' => 'VIEW_AUDIT_REPORT', 'actor' => 'superadmin'],
            extra: []
        );

        $handler->handle($record);

        $this->assertFileExists($testLogPath);
        $rawContent = file_get_contents($testLogPath);

        // Pastikan tidak ada plain text message yang tersimpan mentah di berkas
        $this->assertStringNotContainsString('User berhasil mengakses data audit rahasia', $rawContent);
        $this->assertStringContainsString('[ENC:', $rawContent);
        $this->assertStringContainsString('[HMAC:', $rawContent);

        // Dekripsi menggunakan Crypt untuk memastikan enkripsi AES-256 valid
        preg_match('/\[ENC:(.*?)\] \[HMAC:(.*?)\]/', $rawContent, $matches);
        $this->assertNotEmpty($matches);

        $encryptedPayload = $matches[1];
        $hmac = $matches[2];

        // Validasi HMAC signature
        $expectedHmac = hash_hmac('sha256', $encryptedPayload, (string) config('app.key'));
        $this->assertTrue(hash_equals($expectedHmac, $hmac), 'HMAC signature harus valid dan cocok dengan APP_KEY');

        // Validasi dekripsi payload
        $decryptedJson = Crypt::decryptString($encryptedPayload);
        $payloadData = json_decode($decryptedJson, true);

        $this->assertEquals('User berhasil mengakses data audit rahasia', $payloadData['message']);
        $this->assertEquals('VIEW_AUDIT_REPORT', $payloadData['context']['action']);

        // Uji command log:verify
        $this->artisan('log:verify', ['file' => $testLogPath])
            ->assertSuccessful();

        @unlink($testLogPath);
    }

    /**
     * Poin 6: Pencegahan injeksi log CRLF (SafeLogFormatter).
     */
    public function test_safe_log_formatter_prevents_crlf_log_injection(): void
    {
        $formatter = new \App\Logging\Formatters\SafeLogFormatter();

        $maliciousPayload = "Normal message\r\n[2026-10-17 12:00:00] secure_audit.CRITICAL: FAKE INJECTED LOG LINE\r\n";
        $sanitized = $formatter->sanitizeForLog($maliciousPayload);

        $this->assertStringNotContainsString("\r", $sanitized);
        $this->assertStringNotContainsString("\n", $sanitized);
        $this->assertStringContainsString('\n', $sanitized);
    }

    /**
     * Poin 7: Sinkronisasi waktu dan NTP Service.
     */
    public function test_time_sync_service_and_ntp_status(): void
    {
        $timeService = app(TimeSyncService::class);
        $status = $timeService->checkSyncStatus();

        $this->assertEquals('Asia/Jakarta', $status['app_timezone']);
        $this->assertNotNull($status['local_time']);
        $this->assertNotNull($status['db_time']);
        $this->assertIsFloat($status['drift_seconds']);

        // Uji artisan command system:ntp-status
        $this->artisan('system:ntp-status')
            ->assertSuccessful();
    }
}
