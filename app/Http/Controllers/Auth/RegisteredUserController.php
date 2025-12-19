<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasyon (Otomatik hata fırlatır, catch bloğuna gerek yok)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // Transaction: Veritabanı bütünlüğü için (User oluşur ama Event fırlamazsa geri almayız ama iyi alışkanlıktır)
            // Özellikle ileride kayıt sırasında "Cüzdan oluştur", "Profil oluştur" gibi ek işler gelirse hayat kurtarır.

            DB::transaction(function () use ($request) {

                // 2. Kullanıcı Oluşturma
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'user', // Güvenlik: Varsayılan her zaman 'user'
                ]);

                // 3. KRİTİK DEĞİŞİKLİK: Standart Laravel Event'ini kullanıyoruz.
                // Bu event, User modelindeki 'MustVerifyEmail' arayüzünü görür ve
                // otomatik olarak doğru formatta (imzalı) doğrulama mailini atar.
                event(new Registered($user));

                // 4. Otomatik Giriş Yap
                Auth::login($user);
            });

            // Yönlendirme
            return redirect(route('dashboard', absolute: false));

        } catch (Throwable $e) {
            // Hata Loglama
            report($e);

            return redirect()->back()
                ->withInput($request->except('password')) // Şifre hariç inputları geri doldur
                ->with('error', 'Kayıt işlemi sırasında beklenmeyen bir hata oluştu.');
        }
    }
}
