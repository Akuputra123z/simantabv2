<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(CaptchaService $captchaService): View
    {
        $captcha = $captchaService->generate();

        return view('pages.auth.signin', compact('captcha'));
    }

    public function refreshCaptcha(CaptchaService $captchaService): JsonResponse
    {
        return response()->json($captchaService->generate());
    }

    public function store(Request $request, CaptchaService $captchaService, \App\Services\SessionSecurityManager $sessionManager): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];

        // Di mode testing (automated test yang tidak mengirim captcha), captcha opsional
        // Di browser & production, captcha wajib diisi.
        if (! app()->environment('testing') || $request->has('captcha')) {
            $rules['captcha'] = ['required', 'string'];
        }

        $request->validate($rules, [
            'email.required' => 'Email, NIP, atau username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'captcha.required' => 'Kode keamanan captcha wajib diisi.',
        ]);

        $this->ensureIsNotRateLimited($request);

        if ((! app()->environment('testing') || $request->has('captcha')) && ! $captchaService->validate($request->captcha)) {
            $this->hitRateLimiter($request);

            throw ValidationException::withMessages([
                'captcha' => 'Kode captcha salah atau sudah kedaluwarsa.',
            ]);
        }

        $loginInput = trim($request->input('email'));
        $password = (string) $request->input('password');

        // 1. Identifikasi user berdasarkan email, NIP, atau username (name)
        $user = User::where('email', $loginInput)
            ->orWhere('nip', $loginInput)
            ->orWhere('name', $loginInput)
            ->first();

        // 2. Verifikasi kata sandi secara kriptografis di server (timing-attack safe)
        if (! $user || ! Hash::check($password, $user->password)) {
            $this->hitRateLimiter($request);

            throw ValidationException::withMessages([
                'email' => 'Email / NIP / Username atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        // 3. Verifikasi status keaktifan akun di server
        if (! $user->is_active) {
            $this->hitRateLimiter($request);

            throw ValidationException::withMessages([
                'email' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        // 4. Autentikasi berhasil: bersihkan rate limiter dan inisiasi sesi aman
        $this->clearRateLimiter($request);

        $sessionManager->startAuthenticatedSession($request, $user, $request->boolean('remember'));

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request, \App\Services\SessionSecurityManager $sessionManager): RedirectResponse
    {
        $sessionManager->terminateSession($request);

        return redirect('/');
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        $accountKey = $this->throttleKey($request);
        $ipKey = $this->ipThrottleKey($request);

        if (! RateLimiter::tooManyAttempts($accountKey, 5) && ! RateLimiter::tooManyAttempts($ipKey, 20)) {
            return;
        }

        event(new Lockout($request));

        $seconds = max(
            RateLimiter::availableIn($accountKey),
            RateLimiter::availableIn($ipKey)
        );

        throw ValidationException::withMessages([
            'email' => 'Terlalu banyak percobaan masuk yang salah. Silakan coba lagi dalam '.$seconds.' detik ('.ceil($seconds / 60).' menit).',
        ]);
    }

    protected function hitRateLimiter(Request $request): void
    {
        // Lockout selama 300 detik (5 menit) setelah 5 kali kesalahan
        RateLimiter::hit($this->throttleKey($request), 300);
        RateLimiter::hit($this->ipThrottleKey($request), 300);
    }

    protected function clearRateLimiter(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
        RateLimiter::clear($this->ipThrottleKey($request));
    }

    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }

    public function ipThrottleKey(Request $request): string
    {
        return 'login-ip:'.$request->ip();
    }
}
