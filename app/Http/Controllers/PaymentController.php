<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\PaytrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaytrService $paytr;

    public function __construct(PaytrService $paytr)
    {
        $this->paytr = $paytr;
    }

    /**
     * Initialize payment for a plan via PayTR
     */
    public function initialize(Request $request, SubscriptionPlan $plan)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Ödeme için giriş yapmalısınız.');
            }

            // ✅ E-posta doğrulaması kontrolü
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('error', 'Ödeme yapabilmek için önce e-posta adresinizi doğrulamalısınız.');
            }

            // ✅ Plan aktif mi kontrolü
            if (!$plan->is_active) {
                return redirect()->route('subscriptions.index')
                    ->with('error', 'Bu plan şu anda aktif değil.');
            }

            // ✅ Zaten aktif abonelik var mı?
            $existingSubscription = $user->subscriptions()
                ->where('subscription_plan_id', $plan->id)
                ->where('status', SubscriptionStatus::STATUS_ACTIVE)
                ->where('expires_at', '>', now())
                ->first();

            if ($existingSubscription) {
                return redirect()->route('subscriptions.index')
                    ->with('info', 'Bu plana zaten aktif bir aboneliğiniz var.');
            }

            // Create pending payment log
            $payment = PaymentLog::create([
                'user_id' => $user->id,
                'subscription_id' => null,
                'transaction_id' => null,
                'amount' => $plan->price,
                'status' => PaymentLog::STATUS_PENDING,
                'payment_method' => 'paytr',
                'payment_data' => [],
            ]);

            // merchant_oid used by PayTR - keep unique per payment
            $merchant_oid = 'ORD' . $payment->id . '-' . time() . '-' . uniqid();

            // Prepare user basket
            $basket = [[
                $plan->name,
                (float)$plan->price,
                1
            ]];
            $user_basket = base64_encode(json_encode($basket));

            // PayTR amount expects kuruş (amount * 100)
            // Minimum tutar kontrolü (PayTR minimum 50 kuruş)
            $payment_amount = max(50, (string)round($plan->price * 100));

            $email = $user->email;

            $paytr_token = $this->paytr->makePaytrToken($merchant_oid, $email, $user_basket, $payment_amount);

            // Request token from PayTR
            $post = [
                'merchant_id' => config('services.paytr.merchant_id'),
                'user_ip' => $request->ip(),
                'merchant_oid' => $merchant_oid,
                'email' => $email,
                'payment_amount' => $payment_amount,
                'paytr_token' => $paytr_token,
                'user_basket' => $user_basket,
                'debug_on' => config('services.paytr.debug', 0),
                'no_installment' => 0,
                'max_installment' => 0,
                'currency' => 'TL',
                'test_mode' => config('services.paytr.test_mode', 1),
                'success_url' => route('subscriptions.index', absolute: true),
                'fail_url' => route('subscriptions.index', absolute: true),
            ];

            $res = $this->paytr->getToken($post);

            if (!isset($res['status']) || $res['status'] !== 'success') {
                Log::error('PayTR get-token failed', ['res' => $res, 'user_id' => $user->id]);

                // ✅ Hatalı ödemeyi logla
                $payment->update([
                    'status' => PaymentLog::STATUS_FAILED,
                    'payment_data' => array_merge($payment->payment_data ?? [], [
                        'error' => 'Token alınamadı',
                        'paytr_response' => $res,
                    ]),
                ]);

                return back()->with('error', 'Ödeme başlatılamadı. Lütfen daha sonra tekrar deneyin.');
            }

            $token = $res['token'];

            // Save merchant_oid and token info in payment_data
            $payment->payment_data = array_merge($payment->payment_data ?? [], [
                'merchant_oid' => $merchant_oid,
                'paytr_token' => $token,
                'paytr_response' => $res,
                'plan_id' => $plan->id,
                'user_ip' => $request->ip(),
                'initiated_at' => now()->toDateTimeString(),
            ]);
            $payment->transaction_id = $merchant_oid;
            $payment->save();

            // Show form to submit to PayTR iframe
            return view('paytr.form', [
                'merchant_id' => config('services.paytr.merchant_id'),
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            Log::error('PaymentController::initialize error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id ?? null,
                'plan_id' => $plan->id ?? null,
            ]);
            return back()->with('error', 'İşlem sırasında beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
        }
    }

    /**
     * PayTR callback (webhook)
     */
    public function callback(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('PayTR callback received', ['payload_keys' => array_keys($payload)]);

            // verify signature
            $valid = $this->paytr->verifyCallback($payload);
            if (!$valid) {
                Log::warning('PayTR callback signature invalid', [
                    'merchant_oid' => $payload['merchant_oid'] ?? null,
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ]);
                // Güvenlik: Geçersiz imza ile işlem yapmıyoruz ama PayTR'ye OK dönüyoruz
                return response('OK', 200);
            }

            $merchant_oid = $payload['merchant_oid'] ?? null;
            $status = $payload['status'] ?? null;
            $total_amount = $payload['total_amount'] ?? null;

            // Find payment log by transaction_id (we stored merchant_oid there)
            $payment = PaymentLog::where('transaction_id', $merchant_oid)->first();

            if (!$payment) {
                Log::error('PayTR callback: payment not found for merchant_oid ' . $merchant_oid);
                return response('OK', 200);
            }

            // ✅ Zaten işlenmiş mi kontrolü (idempotency)
            if ($payment->status === PaymentLog::STATUS_COMPLETED) {
                Log::info('PayTR callback: payment already completed', ['payment_id' => $payment->id]);
                return response('OK', 200);
            }

            // Update payment data
            $payment->payment_data = array_merge($payment->payment_data ?? [], [
                'callback_received_at' => now()->toDateTimeString(),
                'callback_data' => $payload,
            ]);

            if ($status === 'success') {
                $payment->status = PaymentLog::STATUS_COMPLETED;

                // Create subscription if not exists
                $planId = $payment->payment_data['plan_id'] ?? null;
                if ($planId) {
                    try {
                        // ✅ Eski aktif abonelikleri iptal et
                        Subscription::where('user_id', $payment->user_id)
                            ->where('status', SubscriptionStatus::STATUS_ACTIVE)
                            ->update(['status' => SubscriptionStatus::STATUS_CANCELLED, 'cancelled_at' => now()]);

                        $plan = SubscriptionPlan::find($planId);

                        if (!$plan) {
                            Log::error('PayTR callback: plan not found', [
                                'plan_id' => $planId,
                                'payment_id' => $payment->id,
                            ]);
                            $payment->status = PaymentLog::STATUS_FAILED;
                            $payment->save();
                            return response('OK', 200);
                        }

                        $subscription = Subscription::create([
                            'user_id' => $payment->user_id,
                            'subscription_plan_id' => $planId,
                            'status' => SubscriptionStatus::STATUS_ACTIVE,
                            'started_at' => now(),
                            'expires_at' => now()->addDays($plan->duration_days ?? 30),
                        ]);

                        $payment->subscription_id = $subscription->id;

                        Log::info('Subscription created successfully', [
                            'subscription_id' => $subscription->id,
                            'payment_id' => $payment->id,
                            'user_id' => $payment->user_id,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Creating subscription on PayTR callback failed: ' . $e->getMessage(), [
                            'exception' => $e,
                            'payment_id' => $payment->id,
                        ]);
                    }
                }
            } else {
                $payment->status = PaymentLog::STATUS_FAILED;
                $payment->payment_data = array_merge($payment->payment_data ?? [], [
                    'failure_reason' => $payload['failed_reason_msg'] ?? 'Bilinmeyen hata',
                ]);
            }

            $payment->save();

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('PaymentController::callback error: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all(),
            ]);
            // Always return 200 to PayTR so they don't retry aggressively
            return response('OK', 200);
        }
    }

    /**
     * Simulate a successful payment for testing when PayTR keys are not available.
     * Only allow admins to call this.
     */
    public function simulate(Request $request, PaymentLog $payment)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return abort(403, 'Yetkiniz yok.');
        }

        try {
            // ✅ Zaten tamamlanmış mı?
            if ($payment->status === PaymentLog::STATUS_COMPLETED) {
                return back()->with('info', 'Bu ödeme zaten tamamlanmış.');
            }

            $payment->payment_data = array_merge($payment->payment_data ?? [], [
                'simulated_by' => $user->id,
                'simulated_at' => now()->toDateTimeString(),
                'simulation_note' => 'Admin tarafından test için simüle edildi.',
            ]);
            $payment->status = PaymentLog::STATUS_COMPLETED;

            $planId = $payment->payment_data['plan_id'] ?? null;
            if ($planId) {
                // ✅ Eski abonelikleri iptal et
                Subscription::where('user_id', $payment->user_id)
                    ->where('status', SubscriptionStatus::STATUS_ACTIVE)
                    ->update(['status' => SubscriptionStatus::STATUS_CANCELLED, 'cancelled_at' => now()]);

                $plan = SubscriptionPlan::find($planId);

                if (!$plan) {
                    Log::error('PaymentController::simulate: plan not found', [
                        'plan_id' => $planId,
                        'payment_id' => $payment->id,
                    ]);
                    return back()->with('error', 'Plan bulunamadı.');
                }

                $subscription = Subscription::create([
                    'user_id' => $payment->user_id,
                    'subscription_plan_id' => $planId,
                    'status' => SubscriptionStatus::STATUS_ACTIVE,
                    'started_at' => now(),
                    'expires_at' => now()->addDays($plan->duration_days ?? 30),
                ]);

                $payment->subscription_id = $subscription->id;
            }

            $payment->save();

            return back()->with('status', 'Ödeme simulasyonu başarılı, abonelik oluşturuldu.');
        } catch (\Throwable $e) {
            Log::error('PaymentController::simulate error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Simülasyon sırasında hata oluştu.');
        }
    }
}
