<?php

declare(strict_types=1);

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
        // updated olayı tetiklendiğinde veritabanına yazılmıştır.
        // isDirty yerine wasChanged kullanmak daha sağlıklıdır.
        if ($prediction->wasChanged(['status', 'prediction_result'])) {
            $this->clearCache();
        }
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
        // İstatistikler değişeceği için bu cacheleri siliyoruz
        Cache::forget('stats.total_predictions');
        Cache::forget('stats.won_predictions');

        // Eğer günlük tahminleri cacheliyorsan onu da burada silmelisin
        // Örn: Cache::forget('predictions.today');
    }
}
