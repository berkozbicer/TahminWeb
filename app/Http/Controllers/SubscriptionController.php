<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Models\SubscriptionPlan;
use App\Repositories\SubscriptionPlanRepository;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionPlanRepository $planRepository,
        protected SubscriptionService        $subscriptionService
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

    public function upgrade(): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Giriş yapmalısınız.');
        }

        $plans = $this->planRepository->getActivePlans();
        $activeSubscription = $user->activeSubscription;
        $currentLevel = $user->getSubscriptionLevel();

        return view('subscriptions.upgrade', compact(
            'plans',
            'activeSubscription',
            'currentLevel',
            'user'
        ));
    }

    public function subscribe(StoreSubscriptionRequest $request, SubscriptionPlan $plan): RedirectResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $this->subscriptionService->subscribe($user, $plan);

            return redirect()->route('dashboard')
                ->with('status', 'Aboneliğiniz başarıyla oluşturuldu.');

        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(): RedirectResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login');
            }

            $this->subscriptionService->cancel($user);

            return back()->with('status', 'Aboneliğiniz iptal edildi.');

        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }
}
