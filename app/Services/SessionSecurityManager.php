<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SessionSecurityManager
{
    /**
     * Durasi idle default sebelum sesi dianggap kedaluwarsa karena tidak aktif (dalam menit).
     */
    public const DEFAULT_IDLE_TIMEOUT_MINUTES = 60;

    /**
     * Interval rotasi berkala ID sesi saat pengguna aktif (dalam menit).
     */
    public const PERIODIC_ROTATION_MINUTES = 30;

    /**
     * Memulai sesi terautentikasi baru secara aman:
     * - Login pengguna
     * - Regenerasi ID sesi untuk mencegah Session Fixation
     * - Mengikat fingerprint User-Agent dan IP
     * - Menetapkan timestamp awal dan aktivitas terakhir
     */
    public function startAuthenticatedSession(Request $request, User $user, bool $remember = false): void
    {
        Auth::login($user, $remember);

        // Regenerasi ID sesi (CSPRNG 40 karakter dari framework)
        $request->session()->regenerate();

        // Ikat fingerprint lingkungan klien
        $this->bindFingerprint($request);

        // Catat timestamp inisiasi dan aktivitas pertama
        $now = now()->timestamp;
        $request->session()->put('session_created_at', $now);
        $request->session()->put('session_last_rotated_at', $now);
        $request->session()->put('session_last_activity_at', $now);
    }

    /**
     * Mengikat informasi fingerprint lingkungan klien (User-Agent & Subnet IP) ke sesi.
     */
    public function bindFingerprint(Request $request): void
    {
        $userAgent = (string) $request->userAgent();
        $ip = (string) $request->ip();

        $request->session()->put('session_fingerprint', [
            'ua_hash' => hash('sha256', $userAgent),
            'ip_subnet' => $this->getIpSubnet($ip),
        ]);
    }

    /**
     * Memvalidasi apakah fingerprint request saat ini cocok dengan yang terikat pada sesi.
     * Mencegah Session Hijacking jika token sesi dicuri dan digunakan di browser/lingkungan berbeda.
     */
    public function isValidFingerprint(Request $request): bool
    {
        if (! $request->session()->has('session_fingerprint')) {
            // Jika sesi belum memiliki fingerprint (misal migrasi), bind sekarang
            $this->bindFingerprint($request);
            return true;
        }

        $stored = $request->session()->get('session_fingerprint', []);
        $currentUaHash = hash('sha256', (string) $request->userAgent());

        // Verifikasi User-Agent (wajib sama persis)
        if (! isset($stored['ua_hash']) || ! hash_equals($stored['ua_hash'], $currentUaHash)) {
            Log::warning('Potensi pembajakan sesi terdeteksi: User-Agent mismatch.', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'stored_ua_hash' => $stored['ua_hash'] ?? null,
                'current_ua' => $request->userAgent(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Memeriksa apakah sesi telah melampaui batas waktu idle (tidak aktif).
     */
    public function isSessionIdle(Request $request, ?int $idleMinutes = null): bool
    {
        $idleLimit = ($idleMinutes ?? (int) config('session.idle_timeout', self::DEFAULT_IDLE_TIMEOUT_MINUTES)) * 60;
        $lastActivity = $request->session()->get('session_last_activity_at');

        if ($lastActivity && (now()->timestamp - (int) $lastActivity) > $idleLimit) {
            return true;
        }

        return false;
    }

    /**
     * Memperbarui timestamp aktivitas terakhir sesi pengguna.
     */
    public function touchActivity(Request $request): void
    {
        $request->session()->put('session_last_activity_at', now()->timestamp);
    }

    /**
     * Melakukan rotasi berkala ID sesi (Session ID Rotation) tanpa menghapus data payload sesi.
     */
    public function rotateSessionIfDue(Request $request, int $intervalMinutes = self::PERIODIC_ROTATION_MINUTES): void
    {
        $lastRotated = $request->session()->get('session_last_rotated_at', 0);
        $intervalSeconds = $intervalMinutes * 60;

        if ((now()->timestamp - (int) $lastRotated) >= $intervalSeconds) {
            // Migrasikan session ID lama ke session ID baru yang baru diacak
            $request->session()->migrate(true);
            $request->session()->put('session_last_rotated_at', now()->timestamp);
        }
    }

    /**
     * Memvalidasi format string Session ID (menolak karakter ilegal atau path traversal).
     */
    public function isValidSessionIdString(?string $sessionId): bool
    {
        if (empty($sessionId) || ! is_string($sessionId)) {
            return false;
        }

        // Session ID Laravel adalah alfanumerik sepanjang 40 karakter
        return (bool) preg_match('/^[a-zA-Z0-9]{40}$/', $sessionId);
    }

    /**
     * Mengakhiri sesi secara total (Logout):
     * - Logout dari guard web
     * - Invalidate seluruh payload data sesi lama di storage
     * - Regenerasi token CSRF baru
     */
    public function terminateSession(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Mengeluarkan pengguna dari seluruh sesi di perangkat lain (Logout Other Devices).
     * Mencegah duplikasi sesi yang tidak sah ketika kata sandi diperbarui.
     */
    public function logoutOtherDevices(string $currentPassword): bool
    {
        try {
            return (bool) Auth::logoutOtherDevices($currentPassword);
        } catch (\Throwable $e) {
            Log::warning('Gagal menghentikan sesi perangkat lain:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return false;
        }
    }

    /**
     * Mengambil daftar sesi aktif milik pengguna saat ini dari tabel database.
     */
    public function getActiveUserSessions(User $user, ?string $currentSessionId = null): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        try {
            $sessions = DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->orderBy('last_activity', 'desc')
                ->get();

            return $sessions->map(function ($s) use ($currentSessionId) {
                return [
                    'id' => $s->id,
                    'is_current' => $currentSessionId !== null && $s->id === $currentSessionId,
                    'ip_address' => $s->ip_address,
                    'user_agent' => $s->user_agent,
                    'last_activity' => Carbon::createFromTimestamp($s->last_activity),
                    'last_activity_human' => Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                ];
            })->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Mengambil subnet IP (misal 192.168.1.0/24) untuk perbandingan toleran pada jaringan dinamis.
     */
    private function getIpSubnet(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 4)) . '::';
        }

        return $ip;
    }
}
