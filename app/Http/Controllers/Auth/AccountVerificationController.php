<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountVerificationController extends Controller
{
    /**
     * Tampilkan formulir permintaan kirim ulang tautan verifikasi/aktivasi akun.
     */
    public function create(): View
    {
        return view('pages.auth.verify-account-request');
    }

    /**
     * Kirimkan tautan verifikasi ke email pengguna berdasarkan Email atau NIP terdaftar.
     */
    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:100'],
        ], [
            'identifier.required' => 'Email atau NIP wajib diisi.',
        ]);

        $throttleKey = 'account-verify:' . Str::lower($request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput()->withErrors([
                'identifier' => "Terlalu banyak permintaan. Silakan tunggu {$seconds} detik lagi.",
            ]);
        }
        RateLimiter::hit($throttleKey, 300);

        $identifier = trim($request->input('identifier'));

        $user = User::where('email', $identifier)
            ->orWhere('nip', $identifier)
            ->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'identifier' => 'Akun dengan Email atau NIP tersebut tidak ditemukan. Akun harus dibuatkan terlebih dahulu oleh Administrator. Silakan hubungi admin.',
            ]);
        }

        if (! $user->is_active) {
            return back()->withInput()->withErrors([
                'identifier' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi Administrator.',
            ]);
        }

        if ($user->isVerified()) {
            return back()->with('info', 'Akun Anda sudah terverifikasi sebelumnya. Anda dapat langsung masuk dengan kata sandi atau akun Google Anda. Gunakan fitur "Lupa kata sandi" jika lupa password.');
        }

        // Kirimkan email verifikasi
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email aktivasi: ' . $e->getMessage());

            $errorMessage = str_contains($e->getMessage(), 'BadCredentials') || str_contains($e->getMessage(), '535')
                ? 'Gagal mengirim email: Kredensial SMTP Gmail ditolak oleh Google (BadCredentials). Pastikan menggunakan 16 digit Sandi Aplikasi (App Password) di file .env, bukan password login biasa.'
                : 'Gagal mengirim email: ' . $e->getMessage();

            return back()->withInput()->withErrors(['identifier' => $errorMessage]);
        }

        // Sensor sebagian email untuk privasi
        $maskedEmail = $this->maskEmail($user->email);

        return back()->with('status', "Tautan verifikasi dan aktivasi akun telah berhasil dikirim ke alamat email Anda ({$maskedEmail}). Silakan periksa kotak masuk atau spam email Anda.");
    }

    /**
     * Verifikasi akun pengguna melalui signed URL yang dikirim ke email.
     */
    public function verify(Request $request, $id, $hash): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('account.verify.request')->withErrors([
                'identifier' => 'Tautan verifikasi tidak valid atau telah kedaluwarsa. Silakan minta tautan verifikasi baru.',
            ]);
        }

        $user = is_numeric($id) ? User::find($id) : User::where('uuid', $id)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Pengguna tidak ditemukan dalam sistem.',
            ]);
        }

        // Jika ada pengguna lain yang sedang aktif di sesi browser, logout sesi lama
        if (Auth::check() && Auth::id() !== $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('account.verify.request')->withErrors([
                'identifier' => 'Tautan verifikasi tidak cocok.',
            ]);
        }

        $justVerified = false;
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->is_active = true;
            $user->save();

            event(new Verified($user));
            $justVerified = true;
        }

        // Simpan token sesi bertanda tangan aman untuk mengizinkan set initial password
        $activationToken = hash_hmac('sha256', $user->id . '|' . $user->email, config('app.key'));
        session(['activation_user_id' => $user->id, 'activation_token' => $activationToken]);

        return view('pages.auth.account-verified', [
            'user' => $user,
            'justVerified' => $justVerified,
            'activationToken' => $activationToken,
        ]);
    }

    /**
     * Mengatur kata sandi pertama kali setelah verifikasi berhasil.
     */
    public function setPassword(Request $request): RedirectResponse
    {
        $sessionUserId = session('activation_user_id');
        $sessionToken = session('activation_token');

        $validationMessages = [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus :min karakter.',
            'password.letters' => 'Kata sandi wajib mengandung huruf.',
            'password.mixed' => 'Kata sandi wajib mengandung kombinasi huruf besar (A-Z) dan huruf kecil (a-z).',
            'password.numbers' => 'Kata sandi harus mengandung setidaknya satu angka (0-9).',
            'password.symbols' => 'Kata sandi harus mengandung setidaknya satu simbol atau karakter khusus (!@#$%...).',
            'password.uncompromised' => 'Kata sandi yang dipilih terlalu umum atau pernah terdeteksi bocor. Harap gunakan kata sandi yang lebih unik.',
        ];

        $request->validate([
            'user_id' => ['required', 'integer'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], $validationMessages);

        if (! $sessionUserId || $sessionUserId != $request->input('user_id') || ! hash_equals((string) $sessionToken, (string) $request->input('token'))) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi aktivasi telah berakhir. Silakan masuk atau minta tautan baru.',
            ]);
        }

        $user = User::findOrFail($sessionUserId);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'is_active' => true,
            'remember_token' => Str::random(60),
        ])->save();

        session()->forget(['activation_user_id', 'activation_token']);

        // Login otomatis aman ke aplikasi
        app(\App\Services\SessionSecurityManager::class)->startAuthenticatedSession($request, $user, true);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', 'Selamat! Akun Anda telah aktif dan kata sandi baru Anda berhasil disimpan.');
    }

    /**
     * Sensor alamat email untuk perlindungan data pribadi (contoh: a***@domain.com)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $maskedName = substr($name, 0, 1) . '***';
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(3, strlen($name) - 2));
        }

        return $maskedName . '@' . $domain;
    }
}
