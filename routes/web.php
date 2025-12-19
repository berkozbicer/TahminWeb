<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
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
Route::post('/iletisim', [HomeController::class, 'contactSubmit'])
    ->name('contact.submit')
    ->middleware('throttle:5,1');
Route::get('/gizlilik-politikasi', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/kullanim-sartlari', [HomeController::class, 'terms'])->name('terms');

// Tahminler (Genel Erişim)
Route::prefix('tahminler')->name('predictions.')->group(function () {
    Route::get('/bugun', [PredictionController::class, 'today'])->name('today');
    Route::get('/', [PredictionController::class, 'index'])->name('index');
    Route::get('/{prediction}', [PredictionController::class, 'show'])->name('show');
});

// Abonelik Paketleri
Route::prefix('abonelik')->name('subscriptions.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');

    Route::middleware('auth')->group(function () {
        Route::get('/yukselt', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/{plan}/abone-ol', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/iptal', [SubscriptionController::class, 'cancel'])->name('cancel');
    });
});

// Kullanıcı Paneli (Giriş Zorunlu)
Route::middleware('auth')->group(function () {
    Route::get('/panel', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// PayTR Payment Routes
Route::prefix('paytr')->name('paytr.')->group(function () {
    Route::post('/odeme', [PaymentController::class, 'ode'])
        ->name('odeme')
        ->middleware('auth');

    // CSRF koruması devre dışı (PayTR webhook için)
    Route::post('/odeme/callback', [PaymentController::class, 'callback'])
        ->name('callback')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

    //Simulation (admin only)
    Route::post('/simulate/{payment}', [PaymentController::class, 'simulate'])
        ->name('simulate')
        ->middleware(['auth', 'admin']);
});

// Auth Routes
require __DIR__ . '/auth.php';
