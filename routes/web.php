<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// EKLENDİ
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ana Sayfa
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/hakkimizda', [HomeController::class, 'about'])->name('about');
Route::get('/iletisim', [HomeController::class, 'contact'])->name('contact');
Route::post('/iletisim', [HomeController::class, 'contactSubmit'])->name('contact.submit')->middleware('throttle:5,1');
Route::get('/gizlilik-politikasi', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/kullanim-sartlari', [HomeController::class, 'terms'])->name('terms');

// Tahminler (Genel Erişim - Bazı detaylar login gerektirebilir ama liste açıktır)
Route::prefix('tahminler')->name('predictions.')->group(function () {
    // 'bugun' rotası dinamik parametrelerden ({prediction}) ÖNCE gelmelidir.
    Route::get('/bugun', [PredictionController::class, 'today'])->name('today');
    Route::get('/', [PredictionController::class, 'index'])->name('index');
    Route::get('/{prediction}', [PredictionController::class, 'show'])->name('show');
});

// Abonelik Paketleri
Route::prefix('abonelik')->name('subscriptions.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');

    // Sadece giriş yapmış kullanıcılar abone olabilir veya iptal edebilir
    Route::middleware('auth')->group(function () {
        Route::get('/yukselt', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/{plan}/abone-ol', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/iptal', [SubscriptionController::class, 'cancel'])->name('cancel');
    });
});

// Kullanıcı Paneli ve Profil İşlemleri (Giriş Zorunlu)
Route::middleware('auth')->group(function () {
    // Dashboard (Panel Ana Sayfası)
    Route::get('/panel', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Düzenleme (Laravel Standart Yapısı)
    // Not: DashboardController içindeki updateProfile vb. yerine ProfileController kullanıyoruz.
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth Routes (Giriş, Kayıt, Şifre Sıfırlama vb.)
require __DIR__ . '/auth.php';

use App\Http\Controllers\PaymentController;

// PayTR payment routes
Route::prefix('paytr')->name('paytr.')->group(function () {
    Route::post('/{plan}/initialize', [PaymentController::class, 'initialize'])->name('initialize')->middleware('auth');
    Route::post('/callback', [PaymentController::class, 'callback'])->name('callback');
    // Simulation endpoint for local testing when PayTR keys are not available.
    Route::post('/simulate/{payment}', [PaymentController::class, 'simulate'])->name('simulate')->middleware('auth');
});
