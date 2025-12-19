<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Hippodrome;
use Illuminate\Support\Facades\Cache;

class HippodromeObserver
{
    /**
     * Handle the Hippodrome "created" event.
     */
    public function created(Hippodrome $hippodrome): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Hippodrome "updated" event.
     */
    public function updated(Hippodrome $hippodrome): void
    {
        // Sadece isim veya şehir değişirse cache temizle
        if ($hippodrome->wasChanged(['name', 'city', 'is_active'])) {
            $this->clearCache();
        }
    }

    /**
     * Handle the Hippodrome "deleted" event.
     */
    public function deleted(Hippodrome $hippodrome): void
    {
        $this->clearCache();
    }

    /**
     * Clear related cache
     */
    private function clearCache(): void
    {
        Cache::forget('hippodromes.active');
    }
}
