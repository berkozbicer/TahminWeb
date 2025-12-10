<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $requiredLevel = 'standard'): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Bu içeriği görüntülemek için giriş yapmalısınız.');
        }

        // Admin her şeyi görebilir
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Aktif abonelik kontrolü
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Bu içeriği görüntülemek için aktif bir aboneliğiniz olmalıdır.');
        }

        // Erişim seviyesi kontrolü
        $userLevel = $user->getSubscriptionLevel();

        if ($requiredLevel === 'premium' && $userLevel !== 'premium') {
            return redirect()->route('subscriptions.upgrade')
                ->with('error', 'Bu içerik Premium üyelere özeldir. Yükseltme yapın!');
        }

        return $next($request);
    }
}
