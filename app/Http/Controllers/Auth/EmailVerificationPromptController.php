<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            // Eğer e-posta zaten doğrulanmışsa, kullanıcıyı uygun yere gönder.
            if ($user->hasVerifiedEmail()) {
                $redirectUrl = $user->isAdmin()
                    ? '/admin'
                    : route('dashboard', absolute: false);

                return redirect()->intended($redirectUrl);
            }

            // Doğrulanmamışsa uyarı ekranını göster
            return view('auth.verify-email');

        } catch (\Throwable $e) {
            report($e);
            // Hata durumunda güvenli limana (dashboard) çekil
            return redirect()->route('dashboard')
                ->with('error', 'Doğrulama ekranı yüklenirken bir hata oluştu.');
        }
    }
}
