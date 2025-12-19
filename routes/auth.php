<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// --- ZİYARETÇİ ROTALARI (GUEST) ---
Route::middleware('guest')->group(function () {
    // Kayıt Ol
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Giriş Yap
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Şifremi Unuttum
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Şifre Sıfırlama
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// --- ÜYE ROTALARI (AUTH) ---
Route::middleware('auth')->group(function () {
    // E-posta Doğrulama Uyarı Sayfası
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // Linke Tıklayınca Doğrulama İşlemi
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Doğrulama Linkini Tekrar Gönder
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Hassas İşlem Öncesi Şifre Onayı
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Şifre Güncelleme (Profil içinden)
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Çıkış Yap
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
