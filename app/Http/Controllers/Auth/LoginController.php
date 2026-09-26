<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function store(Request $request, CaptchaService $captchaService): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        // Di mode testing (automated test yang tidak mengirim captcha), captcha opsional
        // Di browser & production, captcha wajib diisi.
        if (! app()->environment('testing') || $request->has('captcha')) {
            $rules['captcha'] = ['required', 'string'];
        }

        $request->validate($rules, [
            'captcha.required' => 'Kode keamanan captcha wajib diisi.',
        ]);

        $this->ensureIsNotRateLimited($request);

        if ((! app()->environment('testing') || $request->has('captcha')) && ! $captchaService->validate($request->captcha)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'captcha' => 'Kode captcha salah atau sudah kedaluwarsa.',
            ]);
        }

        // ── Attempt Login ────────────────────────────────────────────────
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }
}
