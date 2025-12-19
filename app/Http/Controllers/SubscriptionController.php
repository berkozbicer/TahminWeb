<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Repositories\SubscriptionPlanRepository;
use App\Services\PaymentService;

// EKLENDİ
use Illuminate\Http\Request;

// EKLENDİ
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionPlanRepository $planRepository,
        protected PaymentService             $paymentService // EKLENDİ: Ödeme servisini buraya enjekte ettik
    )
    {
    }

    public function index(): View
    {
        $plans = $this->planRepository->getActivePlans();
        $user = Auth::user();
        $activeSubscription = $user?->activeSubscription;

        return view('subscriptions.index', compact('plans', 'activeSubscription', 'user'));
    }

    /**
     * BU METOD ÖDEME SÜRECİNİ BAŞLATIR.
     * Direkt abone yapmaz, PayTR'dan token alır ve loading sayfasına atar.
     */
    public function initiatePayment(Request $request, SubscriptionPlan $plan)
    {
        try {
            $user = $request->user();

            // 1. Kullanıcının zaten bu paketi var mı?
            if ($user->hasActiveSubscriptionTo($plan->id)) {
                return back()->with('error', 'Bu pakete zaten aktif bir aboneliğiniz var.');
            }

            // 2. PayTR Token Alma İşlemi (PaymentService üzerinden)
            // Bu metod PaymentLog tablosuna 'pending' kaydı atar.
            $paytrData = $this->paymentService->initializePaytr($user, $plan, $request->ip());

            // 3. Token başarıyla alındıysa, iFrame formunu içeren view'a gönder
            return view('subscriptions.payment', [
                'token' => $paytrData['token'],
                'merchant_id' => $paytrData['merchant_id'],
                // View'da kullanmak istersen diye diğer dataları da geçebilirsin
            ]);

        } catch (Throwable $e) {
            // Hata olursa logla ve geri dön
            report($e);
            return back()->with('error', 'Ödeme başlatılamadı: ' . $e->getMessage());
        }
    }
}
