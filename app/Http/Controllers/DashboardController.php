<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\SubscriptionPlanRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected SubscriptionPlanRepository $planRepository
    )
    {
    }

    /**
     * /panel -> Kullanıcı paneli
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $activeSubscription = $user->activeSubscription;

        $plans = $this->planRepository->getActivePlans();

        return view('dashboard', compact(
            'user',
            'activeSubscription',
            'plans'
        ));
    }
}
