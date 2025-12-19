<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PaymentLog;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Manuel veya Test aboneliği başlatır.
     */
    public function subscribe(User $user, SubscriptionPlan $plan): void
    {
        if (!$plan->is_active) {
            throw new Exception('Bu plan şu anda aktif değil.');
        }

        // Production ortamında test aboneliğini engelle (Güvenlik)
        if (app()->environment('production')) {
            Log::warning('Production ortamında test aboneliği denendi', ['user_id' => $user->id]);
            throw new Exception('Lütfen ödeme sayfasından devam edin.');
        }

        $current = $user->activeSubscription;

        // Zaten aynı paketteyse
        if ($current && $current->subscription_plan_id === $plan->id && $current->isActive()) {
            throw new Exception('Zaten bu pakete aktif bir aboneliğiniz bulunuyor.');
        }

        DB::transaction(function () use ($user, $plan, $current) {
            // Varsa eskiyi iptal et
            if ($current && $current->isActive()) {
                $current->markAsCancelled();
            }

            // Yeni oluştur
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => Subscription::STATUS_ACTIVE, // Enum yerine Sabit
                'started_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days ?? 30),
            ]);

            // Fake ödeme logu
            PaymentLog::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => 'TEST-' . uniqid(),
                'amount' => $plan->price,
                'status' => PaymentLog::STATUS_COMPLETED,
                'payment_method' => 'test',
                'payment_data' => ['note' => 'Test ödeme - Manuel Aktivasyon'],
            ]);
        });
    }

    /**
     * Ödeme sonrası gerçek abonelik aktivasyonu
     */
    public function activateNewSubscription(User $user, SubscriptionPlan $plan): Subscription
    {
        // Kullanıcının önceki 'active' olan tüm aboneliklerini iptal et
        $user->subscriptions()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->update([
                'status' => Subscription::STATUS_CANCELLED,
                'cancelled_at' => now()
            ]);

        // Yeni abonelik oluştur
        return Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'started_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days ?? 30),
        ]);
    }

    public function cancel(User $user): void
    {
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            throw new Exception('Aktif bir aboneliğiniz bulunmuyor.');
        }

        DB::transaction(function () use ($user, $subscription) {
            $subscription->markAsCancelled();

            // İade/İptal logu
            PaymentLog::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => 'CANCEL-' . uniqid(),
                'amount' => 0,
                'status' => PaymentLog::STATUS_REFUNDED,
                'payment_method' => 'manual',
                'payment_data' => ['note' => 'Kullanıcı isteğiyle iptal edildi.'],
            ]);
        });
    }
}
