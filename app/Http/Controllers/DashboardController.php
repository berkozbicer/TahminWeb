<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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

        // Kullanıcıya yükseltme teklifi sunmak için planları çekiyoruz
        $plans = SubscriptionPlan::active()->orderBy('price')->get();

        return view('dashboard', compact(
            'user',
            'activeSubscription',
            'plans'
        ));
    }
}
