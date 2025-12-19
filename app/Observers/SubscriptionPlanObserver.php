<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Cache;

class SubscriptionPlanObserver
{
    /**
     * Handle the SubscriptionPlan "created" event.
     */
    public function created(SubscriptionPlan $subscriptionPlan): void
    {
        $this->clearCache();
    }

    /**
     * Handle the SubscriptionPlan "updated" event.
     */
    public function updated(SubscriptionPlan $subscriptionPlan): void
    {
        $this->clearCache();
    }

    /**
     * Handle the SubscriptionPlan "deleted" event.
     */
    public function deleted(SubscriptionPlan $subscriptionPlan): void
    {
        $this->clearCache();
    }

    /**
     * Handle the SubscriptionPlan "restored" event.
     */
    public function restored(SubscriptionPlan $subscriptionPlan): void
    {
        $this->clearCache();
    }

    /**
     * Clear related cache
     */
    private function clearCache(): void
    {
        Cache::forget('subscription_plans.active');
    }
}
