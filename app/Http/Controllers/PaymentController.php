<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use App\Models\SubscriptionPlan;
use App\Services\PaymentService;

// <-- Yeni Servis
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    // Artık PaytrService DEĞİL, PaymentService inject ediyoruz.
    public function __construct(
        protected PaymentService $paymentService
    )
    {
    }

    /**
     * Ödeme sürecini başlatır (Iframe Formu gösterir).
     */
    public function initialize(Request $request, SubscriptionPlan $plan): View|RedirectResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            // Service: Log oluşturur, PayTR token alır
            $data = $this->paymentService->initializePaytr($user, $plan, $request->ip());

            return view('paytr.form', [
                'token' => $data['token'],
                'merchant_id' => $data['merchant_id']
            ]);

        } catch (Throwable $e) {
            return redirect()->route('subscriptions.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * PayTR Webhook (Callback)
     */
    public function callback(Request $request): Response
    {
        try {
            $this->paymentService->handleCallback($request->all());
            return response('OK');
        } catch (Throwable $e) {
            \Log::error('Callback hatası: ' . $e->getMessage());
            return response('OK');
        }
    }

    /**
     * Admin için manuel ödeme simülasyonu.
     */
    public function simulate(Request $request, PaymentLog $payment): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        try {
            if ($payment->status === 'completed') {
                return back()->with('info', 'Bu ödeme zaten tamamlanmış.');
            }

            $this->paymentService->completePaymentSuccess($payment, isSimulation: true);

            return back()->with('status', 'Simülasyon başarılı, abonelik tanımlandı.');

        } catch (Throwable $e) {
            return back()->with('error', 'Hata: ' . $e->getMessage());
        }
    }
}
