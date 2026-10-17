<?php

use App\Http\Controllers\Auth\ConfirmationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Settings;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('captcha/refresh', [LoginController::class, 'refreshCaptcha'])->name('captcha.refresh');

    // ── Google OAuth via Socialite ─────────────────────────────────────
    Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('password.store');

    // ── Fitur Permintaan Tautan Verifikasi Akun ─────────────────────────
    Route::get('verify-account', [\App\Http\Controllers\Auth\AccountVerificationController::class, 'create'])->name('account.verify.request');
    Route::post('verify-account', [\App\Http\Controllers\Auth\AccountVerificationController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('account.verify.send');
});

// ── Eksekusi Verifikasi & Aktivasi Akun (Bisa diakses baik saat login maupun guest) ──
Route::get('verify-account/{id}/{hash}', [\App\Http\Controllers\Auth\AccountVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('account.verify.execute');
Route::post('verify-account/set-password', [\App\Http\Controllers\Auth\AccountVerificationController::class, 'setPassword'])
    ->middleware('throttle:10,1')
    ->name('account.verify.setPassword');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::post('verify-email', [VerificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.store');
    Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::get('confirm-password', [ConfirmationController::class, 'create'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmationController::class, 'store'])->name('confirmation.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
