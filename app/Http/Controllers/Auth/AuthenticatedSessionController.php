<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Son giriş zamanını güncelle (Hata olsa bile girişi engelleme)
        $this->updateLastLogin($user);

        // 🔥 YÖNLENDİRME MANTIĞI:
        // Eğer giren kişiyse Admin ise direkt Filament Paneline gitsin.
        if ($user->isAdmin()) {
            return redirect()->intended('/admin');
        }

        // Değilse Kullanıcı Paneline gitsin
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Son giriş tarihini güvenli bir şekilde günceller.
     */
    private function updateLastLogin($user): void
    {
        try {
            // 'updated_at' sütununu değiştirmeden sadece login tarihini güncelle
            // 'saveQuietly' kullanarak Observer'ları tetiklemeyi önleriz (Performans)
            $user->forceFill([
                'last_login_at' => now(),
            ])->saveQuietly();
        } catch (\Throwable $e) {
            // Logla ama kullanıcı akışını bozma
            \Log::warning('Login timestamp update failed', ['id' => $user->id]);
        }
    }
}
