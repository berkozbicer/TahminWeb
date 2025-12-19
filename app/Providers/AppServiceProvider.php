<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

// Modeller
use App\Models\ContactMessage;
use App\Models\Hippodrome;
use App\Models\Prediction;
use App\Models\SubscriptionPlan;

// Observerlar
use App\Observers\ContactMessageObserver;
use App\Observers\HippodromeObserver;
use App\Observers\PredictionObserver;
use App\Observers\SubscriptionPlanObserver;

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
        // MySQL eski sürümleri için index uzunluğu fix'i
        Schema::defaultStringLength(191);

        // Observer Tanımlamaları
        // (Model üzerinde bir işlem yapıldığında otomatik tetiklenirler)
        ContactMessage::observe(ContactMessageObserver::class);
        Hippodrome::observe(HippodromeObserver::class);
        Prediction::observe(PredictionObserver::class);
        SubscriptionPlan::observe(SubscriptionPlanObserver::class);
    }
}
