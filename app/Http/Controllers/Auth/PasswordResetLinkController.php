<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // Şifre sıfırlama bağlantısını gönder
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Başarılı ise:
            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('status', __($status));
            }

            // Başarısız ise (Örn: Kullanıcı bulunamadı):
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

        } catch (Throwable $e) {
            // Loglama yap (Mail sunucusu hatası vb.)
            \Log::error('PasswordResetLinkController::store error: ' . $e->getMessage(), [
                'email' => $request->email, // Hangi mailde hata aldığını görmek için
                'exception' => $e
            ]);

            return back()->with('error', 'Şifre sıfırlama e-postası gönderilirken teknik bir hata oluştu. Lütfen biraz bekleyip tekrar deneyin.');
        }
    }
}
