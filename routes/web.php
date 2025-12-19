<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PredictionController;
use App\Services\ContactService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - FİNAL VE TEMİZLENMİŞ VERSİYON
|--------------------------------------------------------------------------
*/

// --- 1. GENEL SAYFALAR (Public) ---

// Ana Sayfa
Route::get('/', function () {
    // İstatistik önbelleği (Performans için)
    $stats = \Illuminate\Support\Facades\Cache::remember('home_stats', 3600, function () {
        return [
            'successRate' => 85,
            'totalPredictions' => \App\Models\Prediction::count(),
        ];
    });
    // DÜZELTME: Standart dosya ismi 'welcome' olduğu için burayı güncelledim.
    return view('home', $stats);
})->name('home');

// Statik Sayfalar
Route::view('/hakkimizda', 'about')->name('about');
Route::view('/gizlilik-politikasi', 'privacy')->name('privacy');
Route::view('/kullanim-sartlari', 'terms')->name('terms');

// İletişim
Route::view('/iletisim', 'contact')->name('contact');
Route::post('/iletisim', function (Request $request, ContactService $contactService) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string|max:1000',
    ]);

    $contactService->storeMessage($validated, $request->ip(), $request->userAgent());

    return back()->with('success', 'Mesajınız başarıyla gönderildi.');
})->name('contact.submit')->middleware('throttle:5,1');


// --- 2. KULLANICI PANELİ ---

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/panel', function () {
        return view('dashboard', [
            'user' => auth()->user(),
            'activeSubscription' => auth()->user()->activeSubscription
        ]);
    })->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// --- 3. TAHMİN SİSTEMİ ---

Route::group(['prefix' => 'tahminler', 'as' => 'predictions.'], function () {
    Route::get('/', [PredictionController::class, 'index'])->name('index');
    Route::get('/bugun', [PredictionController::class, 'today'])->middleware('auth')->name('today');
    Route::get('/{prediction}', [PredictionController::class, 'show'])->name('show');
});


// --- 4. ABONELİK VE ÖDEME (KRİTİK DÜZELTME YAPILDI) ---

Route::prefix('abonelik')->name('subscriptions.')->group(function () {

    // Paketleri Listele
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');

    // Sadece Üyeler İçin İşlemler
    Route::middleware('auth')->group(function () {

        // Ödeme Başlat (PayTR Token Alır ve View Döndürür)
        // SubscriptionController::upgrade metodu PayTR servisini çağırır.
        // DİKKAT: Direkt abone yapan kod buradan tamamen silindi.
        Route::match(['get', 'post'], '/odeme/{plan}', [SubscriptionController::class, 'upgrade'])
            ->name('upgrade');
    });
});


// --- 5. PAYTR CALLBACK (Webhook) ---

Route::post('/paytr/callback', function (Request $request, PaymentService $paymentService) {
    try {
        $paymentService->handleCallback($request->all());
        return response('OK');
    } catch (\Exception $e) {
        \Log::error('PayTR Callback Error: ' . $e->getMessage());
        return response('FAIL', 500);
    }
})->name('paytr.callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


// --- 6. AUTHENTICATION ---
require __DIR__ . '/auth.php';
