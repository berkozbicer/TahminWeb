<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PaymentLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected PaytrService        $paytrService,
        protected SubscriptionService $subscriptionService
    )
    {
    }

    public function initializePaytr(User $user, SubscriptionPlan $plan, string $ip): array
    {
        if (!$plan->is_active) {
            throw new Exception('Bu plan şu anda aktif değil.');
        }

        if ($user->hasActiveSubscriptionTo($plan->id)) {
            throw new Exception('Bu plana zaten aktif bir aboneliğiniz var.');
        }

        // Ödeme kaydını oluştur
        $payment = PaymentLog::create([
            'user_id' => $user->id,
            'amount' => $plan->price,
            'status' => PaymentLog::STATUS_PENDING,
            'payment_method' => 'paytr',
            'payment_data' => ['plan_id' => $plan->id],
        ]);

        // PayTR Hazırlığı
        $merchantOid = 'ORD' . $payment->id . '-' . time() . '-' . uniqid();
        $paymentAmount = (int)($plan->price * 100);

        $userBasket = base64_encode(json_encode([
            [$plan->name, (string)$plan->price, 1]
        ]));

        $paytrResult = $this->paytrService->requestToken([
            'merchant_oid' => $merchantOid,
            'email' => $user->email,
            'payment_amount' => $paymentAmount,
            'user_basket' => $userBasket,
            'user_ip' => $ip,
            'user_name' => $user->name,
            'user_address' => 'Dijital Hizmet',
            'user_phone' => $user->phone ?? '05555555555',
        ]);

        // Kaydı güncelle
        $payment->update([
            'transaction_id' => $merchantOid,
            'payment_data' => array_merge($payment->payment_data ?? [], [
                'merchant_oid' => $merchantOid,
                'paytr_token' => $paytrResult['token']
            ])
        ]);

        return [
            'token' => $paytrResult['token'],
            'merchant_id' => config('services.paytr.merchant_id')
        ];
    }

    public function handleCallback(array $payload): void
    {
        // 1. İmza Doğrulama
        if (!$this->paytrService->verifyCallback($payload)) {
            Log::warning('PayTR Callback: Geçersiz imza', ['payload' => $payload]);
            return;
        }

        $merchantOid = $payload['merchant_oid'] ?? '';
        $status = $payload['status'] ?? '';

        $payment = PaymentLog::where('transaction_id', $merchantOid)->first();

        // Ödeme yoksa veya zaten tamamlanmışsa işlem yapma (Idempotency)
        if (!$payment || $payment->status === PaymentLog::STATUS_COMPLETED) {
            return;
        }

        DB::transaction(function () use ($payment, $status, $payload) {
            $payment->payment_data = array_merge($payment->payment_data ?? [], [
                'callback_at' => now()->toDateTimeString(),
                'callback_payload' => $payload
            ]);

            if ($status === 'success') {
                $this->completePaymentSuccess($payment);
            } else {
                $payment->status = PaymentLog::STATUS_FAILED;
                $payment->save();
            }
        });
    }

    public function completePaymentSuccess(PaymentLog $payment, bool $isSimulation = false): void
    {
        $payment->status = PaymentLog::STATUS_COMPLETED;

        if ($isSimulation) {
            $payment->payment_data = array_merge($payment->payment_data ?? [], ['simulated' => true]);
        }

        $planId = $payment->payment_data['plan_id'] ?? null;

        if ($planId) {
            $plan = SubscriptionPlan::find($planId);
            $user = User::find($payment->user_id);

            if ($plan && $user) {
                // SubscriptionService üzerinden aboneliği başlat
                $subscription = $this->subscriptionService->activateNewSubscription($user, $plan);
                $payment->subscription_id = $subscription->id;
            }
        }

        $payment->save();
    }
}
