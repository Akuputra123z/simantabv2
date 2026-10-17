<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman otentikasi Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Fitur masuk dengan Google belum dikonfigurasi pada server (GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET belum diatur di .env).',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback otentikasi dari Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error') || $request->has('denied')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Proses autentikasi dengan akun Google dibatalkan atau ditolak.',
            ]);
        }

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal menghubungkan akun Google. Silakan coba lagi atau gunakan login manual.',
            ]);
        }

        $email = $googleUser->getEmail();
        $googleId = (string) $googleUser->getId();

        if (empty($email)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Google Anda tidak menyediakan alamat email yang valid.',
            ]);
        }

        // 1. Cari pengguna berdasarkan google_id, atau email yang sudah terdaftar
        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            // Karena sistem pendaftaran mandiri ditutup (akun dibuatkan admin),
            // tolak login Google jika email belum terdaftar di database.
            return redirect()->route('login')->withErrors([
                'email' => "Akun dengan alamat email Google ({$email}) belum terdaftar di sistem SIMANTAB. Akun harus dibuatkan terlebih dahulu oleh Administrator. Silakan hubungi admin.",
            ]);
        }

        // Verifikasi status keaktifan akun
        if (! $user->is_active) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        // Tautkan google_id, avatar, dan lakukan auto-verifikasi email jika belum diverifikasi
        $updates = [];
        $wasUnverified = is_null($user->email_verified_at);

        if (empty($user->google_id)) {
            $updates['google_id'] = $googleId;
        }
        if (empty($user->avatar) && $googleUser->getAvatar()) {
            $updates['avatar'] = $googleUser->getAvatar();
        }

        if (! empty($updates)) {
            $user->update($updates);
        }

        if ($wasUnverified) {
            // Login Google membuktikan kepemilikan email secara sah
            $user->markEmailAsVerified();
        }

        // Inisiasi sesi terautentikasi aman via SessionSecurityManager
        app(\App\Services\SessionSecurityManager::class)->startAuthenticatedSession($request, $user, true);

        $message = $wasUnverified
            ? 'Akun Anda berhasil diverifikasi secara otomatis dan terhubung dengan Google.'
            : 'Berhasil masuk menggunakan akun Google.';

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', $message);
    }
}
