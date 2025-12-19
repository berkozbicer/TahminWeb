<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Prediction;
use App\Models\User;

class PredictionService
{
    public function checkAccess(Prediction $prediction, ?User $user): array
    {
        $canAccess = false;
        $needsUpgrade = false;

        if ($user && $user->hasActiveSubscription()) {
            // User modelindeki helper metodu kullan
            $canAccess = $user->canAccessPrediction($prediction->access_level);

            $userLevel = $user->getSubscriptionLevel();

            // Eğer erişemiyorsa ve içerik premium ise upgrade öner
            if (!$canAccess && $prediction->access_level === Prediction::ACCESS_PREMIUM && $userLevel !== 'premium') {
                $needsUpgrade = true;
            }
        }

        return [
            'canAccess' => $canAccess,
            'needsUpgrade' => $needsUpgrade
        ];
    }
}
