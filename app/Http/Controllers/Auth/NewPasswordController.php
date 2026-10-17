<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('pages.auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus :min karakter.',
            'password.letters' => 'Kata sandi wajib mengandung huruf.',
            'password.mixed' => 'Kata sandi wajib mengandung kombinasi huruf besar (A-Z) dan huruf kecil (a-z).',
            'password.numbers' => 'Kata sandi harus mengandung setidaknya satu angka (0-9).',
            'password.symbols' => 'Kata sandi harus mengandung setidaknya satu simbol atau karakter khusus (!@#$%...).',
            'password.uncompromised' => 'Kata sandi yang dipilih terlalu umum atau pernah terdeteksi bocor. Harap gunakan kata sandi yang lebih unik.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'password_changed_at' => now(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'is_active' => true,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? to_route('login')->with('status', 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.')
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
