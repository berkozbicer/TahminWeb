<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            // Eğer zaten doğrulanmışsa, mantıklı yere yönlendir
            if ($user->hasVerifiedEmail()) {
                $redirectUrl = $user->isAdmin()
                    ? '/admin'
                    : route('dashboard', absolute: false);

                return redirect()->intended($redirectUrl);
            }

            $user->sendEmailVerificationNotification();

            return back()->with('status', 'verification-link-sent');
        } catch (\Throwable $e) {
            // Hataları loglayalım (Mail sunucusu hatası vb.)
            report($e);

            return back()->with('error', 'Doğrulama e-postası gönderilirken bir hata oluştu. Lütfen biraz bekleyip tekrar deneyin.');
        }
    }
}
