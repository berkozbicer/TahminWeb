<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param string $requiredLevel 'standard' | 'premium'
     */
    public function handle(Request $request, Closure $next, string $requiredLevel = 'standard'): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        // 1. Giriş Kontrolü
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Bu içeriği görüntülemek için giriş yapmalısınız.');
        }

        // 2. Admin Her Şeyi Görür (Bypass)
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 3. Aktif Abonelik Varlığı Kontrolü
        // (User modeline eklediğimiz helper metodu kullanıyoruz)
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Bu içeriği görüntülemek için aktif bir aboneliğiniz olmalıdır.');
        }

        // 4. Seviye Kontrolü (Standard vs Premium)
        $userLevel = $user->getSubscriptionLevel(); // 'free', 'standard', 'premium'

        // Eğer içerik Premium ise ve kullanıcı Premium değilse engelle
        if ($requiredLevel === 'premium' && $userLevel !== 'premium') {
            return redirect()->route('subscriptions.upgrade') // Varsa upgrade rotası, yoksa index
            ->with('error', 'Bu içerik Premium üyelere özeldir. Hesabınızı yükseltin!');
        }

        return $next($request);
    }
}
