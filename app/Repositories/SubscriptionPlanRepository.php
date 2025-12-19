<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SubscriptionPlanRepository
{
    public function getActivePlans(): Collection
    {
        return Cache::remember('subscription_plans.active', 3600, function () {
            return SubscriptionPlan::active()
                ->orderBy('price')
                ->get();
        });
    }
}
