<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * /panel  -> Kullanıcı paneli
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // User modelinde bu ilişki tanımlı olmalı!
        $activeSubscription = $user->activeSubscription;

        // Kullanıcıya yükseltme teklifi sunmak için planları çekiyoruz (cache ile optimize edildi)
        $plans = cache()->remember('subscription_plans.active', 3600, function () {
            return SubscriptionPlan::active()->orderBy('price')->get();
        });

        return view('dashboard', compact(
            'user',
            'activeSubscription',
            'plans'
        ));
    }
}
