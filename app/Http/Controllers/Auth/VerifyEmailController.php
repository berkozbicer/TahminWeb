<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            // Rota Belirleme: Admin ise '/admin', değilse 'dashboard'
            $redirectUrl = $user->isAdmin()
                ? '/admin'
                : route('dashboard', absolute: false);

            // Zaten doğrulanmışsa direkt yönlendir
            if ($user->hasVerifiedEmail()) {
                return redirect()->intended($redirectUrl . '?verified=1');
            }

            // Doğrulanmamışsa doğrula ve Event fırlat
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return redirect()->intended($redirectUrl . '?verified=1');

        } catch (\Throwable $e) {
            // Hata logla ama kullanıcıyı da ortada bırakma
            report($e);
            return redirect()->route('dashboard')
                ->with('error', 'E-posta doğrulaması sırasında teknik bir sorun oluştu.');
        }
    }
}
