<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Throwable;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // Laravel'in yerleşik şifre sıfırlama servisini kullanıyoruz.
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60), // Güvenlik: Eski oturumları geçersiz kıl
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            // İşlem Sonucu Kontrolü
            if ($status === Password::PASSWORD_RESET) {
                // Başarılı: Giriş sayfasına yönlendir
                return redirect()->route('login')->with('status', __($status));
            }

            // Başarısız: Hata mesajıyla geri dön (Örn: Geçersiz token)
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

        } catch (Throwable $e) {
            \Log::error('NewPasswordController::store error: ' . $e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Şifre sıfırlama işlemi sırasında beklenmeyen bir hata oluştu.');
        }
    }
}
