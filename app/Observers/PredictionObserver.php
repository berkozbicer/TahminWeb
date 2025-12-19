<?php

namespace App\Observers;

use App\Models\Prediction;
use Illuminate\Support\Facades\Cache;

class PredictionObserver
{
    /**
     * Handle the Prediction "created" event.
     */
    public function created(Prediction $prediction): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Prediction "updated" event.
     */
    public function updated(Prediction $prediction): void
    {
        // Sadece status veya result değiştiğinde cache'i temizle
        if ($prediction->isDirty(['status', 'prediction_result'])) {
            $this->clearCache();
        }
        $prediction->wasChanged(['status', 'prediction_result']);
    }

    /**
     * Handle the Prediction "deleted" event.
     */
    public function deleted(Prediction $prediction): void
    {
        $this->clearCache();
    }

    /**
     * Clear related cache
     */
    private function clearCache(): void
    {
        Cache::forget('stats.total_predictions');
        Cache::forget('stats.won_predictions');
    }
}


