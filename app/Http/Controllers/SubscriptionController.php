<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Models\PaymentLog;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * /abonelik  -> paketlerin genel listesi (misafir + üye)
     */
    public function index()
    {
        // Planlar cache ile optimize edildi
        $plans = cache()->remember('subscription_plans.active', 3600, function () {
            return SubscriptionPlan::active()
                ->orderBy('price')
                ->get();
        });

        $user = Auth::user();

        // DÜZELTME 1: () kaldırıldı. Artık model verisi gelir.
        $activeSubscription = $user?->activeSubscription;

        return view('subscriptions.index', compact(
            'plans',
            'activeSubscription',
            'user'
        ));
    }

    /**
     * /abonelik/yukselt  -> sadece giriş yapmış kullanıcı
     */
    public function upgrade()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Abonelik yükseltmek için önce giriş yapmalısınız.');
        }

        // Planlar cache ile optimize edildi
        $plans = cache()->remember('subscription_plans.active', 3600, function () {
            return SubscriptionPlan::active()
                ->orderBy('price')
                ->get();
        });

        $activeSubscription = $user->activeSubscription;
        $currentLevel = $user->getSubscriptionLevel();

        return view('subscriptions.upgrade', compact(
            'plans',
            'activeSubscription',
            'currentLevel',
            'user'
        ));
    }

    /**
     * /abonelik/{plan}/abone-ol  -> "test" ödeme ile abonelik oluştur
     */
    public function subscribe(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Abonelik satın almak için giriş yapmalısınız.');
            }

            // E-posta doğrulaması kontrolü
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('error', 'Abonelik satın almak için önce e-posta adresinizi doğrulamalısınız.');
            }

            // Plan aktif mi kontrolü
            if (!$plan->is_active) {
                return back()->with('error', 'Bu plan şu anda aktif değil.');
            }

            $current = $user->activeSubscription;

            if ($current && $current->subscription_plan_id === $plan->id && $current->isActive()) {
                return back()->with('info', 'Zaten bu pakete aktif bir aboneliğiniz bulunuyor.');
            }

            if ($current && $current->isActive()) {
                $current->markAsCancelled();
            }

            if (config('app.env') === 'production') {
                \Log::warning('SubscriptionController::subscribe called in production - should use PaymentController', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ]);
                return redirect()->route('subscriptions.index')
                    ->with('error', 'Lütfen ödeme sayfasından devam edin.');
            }

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => SubscriptionStatus::STATUS_ACTIVE,
                'started_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days ?? 30),
            ]);
            (new Subscription())->id;

            PaymentLog::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => 'TEST-' . uniqid(),
                'amount' => $plan->price,
                'status' => PaymentLog::STATUS_COMPLETED,
                'payment_method' => 'test',
                'payment_data' => [
                    'note' => 'Test ödeme - gerçek PayTR entegrasyonu henüz aktif değil.',
                ],
            ]);

            return redirect()->route('dashboard')
                ->with('status', 'Aboneliğiniz başarıyla oluşturuldu. İyi şanslar!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Abonelik işlenirken hata oluştu.');
        }
    }

    /**
     * /abonelik/iptal  -> aktif aboneliği iptal et
     */
    public function cancel(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Önce giriş yapmalısınız.');
            }

            $subscription = $user->activeSubscription;

            if (!$subscription) {
                return back()->with('error', 'Aktif bir aboneliğiniz bulunmuyor.');
            }

            $subscription->markAsCancelled();

            PaymentLog::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => 'CANCEL-' . uniqid(),
                'amount' => 0,
                'status' => PaymentLog::STATUS_REFUNDED,
                'payment_method' => 'manual',
                'payment_data' => [
                    'note' => 'Kullanıcı tarafından abonelik iptal edildi.',
                ],
            ]);

            return back()->with('status', 'Aboneliğiniz iptal edildi.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Abonelik iptali sırasında bir hata oluştu.');
        }
    }
}
