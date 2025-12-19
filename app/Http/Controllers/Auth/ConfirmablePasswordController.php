<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        // Şifre doğrulama işlemi
        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        // Oturuma "şifre doğrulandı" damgasını vur
        $request->session()->put('auth.password_confirmed_at', time());

        // 🔥 AKILLI YÖNLENDİRME (FALLBACK):
        // Kullanıcı bir sayfaya gitmek isterken şifre sorulduysa oraya (intended) döner.
        // Amaçsızca bu sayfaya geldiyse; Admin ise '/admin', değilse 'dashboard'a gider.

        $fallbackUrl = $request->user()->isAdmin()
            ? '/admin'
            : route('dashboard', absolute: false);

        return redirect()->intended($fallbackUrl);
    }
}
