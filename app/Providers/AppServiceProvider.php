<?php

namespace App\Providers;

use App\Models\Hippodrome;
use App\Models\Prediction;
use App\Models\SubscriptionPlan;
use App\Observers\HippodromeObserver;
use App\Observers\PredictionObserver;
use App\Observers\SubscriptionPlanObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model Observer'ları kaydet (Cache invalidation için)
        SubscriptionPlan::observe(SubscriptionPlanObserver::class);
        Hippodrome::observe(HippodromeObserver::class);
        Prediction::observe(PredictionObserver::class);
    }
}
