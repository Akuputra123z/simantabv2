<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class AuditLoggerService
{
    /**
     * Daftar kata kunci sensitif yang tidak boleh dicatat dalam payload log.
     */
    protected array $blacklistedKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'old_password',
        'token',
        'remember_token',
        'activation_token',
        'access_token',
        'secret',
        'api_key',
        'authorization',
        'cookie',
        'signature',
        'captcha',
        'pin',
        'cvv',
    ];

    /**
     * Mencatat peristiwa login berhasil.
     */
    public function logAuthSuccess(User $user, ?Request $request = null): void
    {
        $request = $request ?? request();
        $ip = $request ? $request->ip() : 'CLI';
        $ua = $request ? (string) $request->userAgent() : 'CLI';

        $properties = [
            'event' => 'LOGIN_SUCCESS',
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->first() ?? 'user',
            'ip_address' => $ip,
            'user_agent' => $ua,
            'timestamp' => now()->toIso8601String(),
        ];

        // 1. Simpan ke Spatie Activity Log (Database)
        try {
            activity('authentication')
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties($properties)
                ->log("Pengguna {$user->name} ({$user->email}) berhasil masuk ke dalam sistem dari IP {$ip}");
        } catch (\Throwable $e) {
            // Ignore DB log failure to keep app running
        }

        // 2. Simpan ke Secure Audit Log (Terenkripsi)
        $this->writeSecureAudit('LOGIN_SUCCESS', "Pengguna {$user->email} berhasil login", $properties);
    }

    /**
     * Mencatat peristiwa percobaan login yang gagal.
     */
    public function logAuthFailed(string $identifier, ?Request $request = null, string $reason = 'Kredensial tidak valid'): void
    {
        $request = $request ?? request();
        $ip = $request ? $request->ip() : 'CLI';
        $ua = $request ? (string) $request->userAgent() : 'CLI';

        $properties = [
            'event' => 'LOGIN_FAILED',
            'attempted_identifier' => $this->sanitizeString($identifier),
            'reason' => $reason,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            activity('authentication')
                ->withProperties($properties)
                ->log("Percobaan login gagal untuk identifier: {$identifier} dari IP {$ip}. Alasan: {$reason}");
        } catch (\Throwable $e) {
            // Fallback
        }

        $this->writeSecureAudit('LOGIN_FAILED', "Percobaan login gagal untuk {$identifier} dari IP {$ip}", $properties);
    }

    /**
     * Mencatat peristiwa logout.
     */
    public function logAuthLogout(?User $user = null, ?Request $request = null): void
    {
        $request = $request ?? request();
        $user = $user ?? Auth::user();
        $ip = $request ? $request->ip() : 'CLI';
        $ua = $request ? (string) $request->userAgent() : 'CLI';

        $properties = [
            'event' => 'LOGOUT',
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($user) {
            try {
                activity('authentication')
                    ->causedBy($user)
                    ->withProperties($properties)
                    ->log("Pengguna {$user->name} ({$user->email}) keluar dari sistem (Logout)");
            } catch (\Throwable $e) {
                // Fallback
            }
        }

        $this->writeSecureAudit('LOGOUT', "Pengguna " . ($user ? $user->email : 'Anonymous') . " logout dari sistem", $properties);
    }

    /**
     * Mencatat insiden keamanan (Security Incident) seperti pembajakan sesi, percobaan akses ilegal, CSRF mismatch.
     */
    public function logSecurityIncident(string $incidentType, ?Request $request = null, array $details = []): void
    {
        $request = $request ?? request();
        $user = Auth::user();
        $ip = $request ? $request->ip() : 'CLI';
        $url = $request ? $request->fullUrl() : 'N/A';

        $cleanedDetails = $this->sanitizeData($details);

        $properties = array_merge([
            'event' => 'SECURITY_INCIDENT',
            'incident_type' => $incidentType,
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'url' => $url,
            'method' => $request ? $request->method() : 'CLI',
            'timestamp' => now()->toIso8601String(),
        ], $cleanedDetails);

        try {
            activity('security_incident')
                ->causedBy($user)
                ->withProperties($properties)
                ->log("Insiden Keamanan [{$incidentType}] terdeteksi pada IP {$ip} pada URL {$url}");
        } catch (\Throwable $e) {
            // Fallback
        }

        $this->writeSecureAudit('SECURITY_INCIDENT', "Insiden Keamanan [{$incidentType}] dari IP {$ip}", $properties, 'warning');
    }

    /**
     * Mencatat error sistem tidak terduga atau kesalahan database dengan Incident ID unik.
     */
    public function logSystemIncident(\Throwable $exception, string $incidentId, ?Request $request = null): void
    {
        $request = $request ?? request();
        $user = Auth::user();
        $ip = $request ? $request->ip() : 'CLI';
        $url = $request ? $request->fullUrl() : 'N/A';

        $properties = [
            'event' => 'SYSTEM_INCIDENT',
            'incident_id' => $incidentId,
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'url' => $url,
            'method' => $request ? $request->method() : 'CLI',
            'timestamp' => now()->toIso8601String(),
        ];

        // Tulis ke secure audit channel
        $this->writeSecureAudit(
            'SYSTEM_INCIDENT',
            "Kesalahan Sistem [{$incidentId}] - " . get_class($exception) . ": " . $exception->getMessage(),
            $properties,
            'critical'
        );
    }

    /**
     * Menulis ke channel secure_audit terenkripsi dengan HMAC tamper-proofing.
     */
    protected function writeSecureAudit(string $type, string $message, array $context, string $level = 'info'): void
    {
        $sanitizedContext = $this->sanitizeData($context);

        // Tambahkan HMAC tamper-proofing
        $payloadString = json_encode($sanitizedContext, JSON_UNESCAPED_SLASHES);
        $hmac = hash_hmac('sha256', $payloadString, (string) config('app.key'));

        $sanitizedContext['_tamper_proof_hmac'] = $hmac;

        try {
            Log::channel('secure_audit')->log($level, $message, $sanitizedContext);
        } catch (\Throwable $e) {
            // Fallback ke default log jika channel secure_audit mengalami kendala
            Log::log($level, "[SECURE-AUDIT-FALLBACK] {$message}", $sanitizedContext);
        }
    }

    /**
     * Membersihkan array secara rekursif dari informasi sensitif (Poin 3).
     */
    public function sanitizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeData($value);
            } elseif (is_string($key) && $this->isSensitiveKey($key)) {
                $data[$key] = '******** [REDACTED]';
            } elseif (is_string($value) && $this->isSensitiveValue($value)) {
                $data[$key] = '******** [REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Memeriksa apakah key termasuk data sensitif.
     */
    protected function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(str_replace(['-', '_'], '', $key));

        foreach ($this->blacklistedKeys as $sensitive) {
            $normalizedSensitive = strtolower(str_replace(['-', '_'], '', $sensitive));
            if ($normalized === $normalizedSensitive || str_contains($normalized, $normalizedSensitive)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Memeriksa apakah value berisi token bearer atau authorization header.
     */
    protected function isSensitiveValue(string $value): bool
    {
        return str_starts_with(strtolower($value), 'bearer ');
    }

    /**
     * Membersihkan string dari karakter kendali atau spasi berlebih.
     */
    protected function sanitizeString(string $input): string
    {
        return trim(preg_replace('/[\x00-\x1F\x7F]/', '', $input));
    }
}
