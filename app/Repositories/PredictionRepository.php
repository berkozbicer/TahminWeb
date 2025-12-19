<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Prediction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PredictionRepository
{
    public function getFilteredPredictions(string $date, ?int $hippodromeId, ?string $userLevel): LengthAwarePaginator
    {
        $query = Prediction::with(['hippodrome', 'creator'])
            ->published() // Scope: status = published
            ->forDate($date);

        if ($hippodromeId) {
            $query->forHippodrome($hippodromeId);
        }

        $query->accessibleForLevel($userLevel);

        return $query
            ->orderBy('race_time')
            ->orderBy('race_number')
            ->paginate(12)
            ->withQueryString();
    }

    public function getTodayPredictions(?string $userLevel): Collection
    {
        return Prediction::with(['hippodrome', 'creator'])
            ->published()
            ->today()
            ->accessibleForLevel($userLevel)
            ->orderBy('race_time')
            ->orderBy('race_number')
            ->get();
    }

    public function getRelatedPredictions(Prediction $prediction, ?string $userLevel): Collection
    {
        return Prediction::with('hippodrome')
            ->published()
            ->forDate($prediction->race_date)
            ->forHippodrome($prediction->hippodrome_id)
            ->where('id', '<>', $prediction->id)
            ->accessibleForLevel($userLevel)
            ->orderBy('race_time')
            ->limit(4)
            ->get();
    }

    /**
     * Return: [total, won, rate]
     */
    public function getStats(): array
    {
        return Cache::remember('home.stats', 3600, function () {
            // Model sabitlerini kullanıyoruz
            $total = Prediction::published()->count();

            $won = Prediction::published()
                ->where('prediction_result', Prediction::RESULT_WON)
                ->count();

            $rate = $total > 0 ? round(($won / $total) * 100, 1) : 0;

            return [
                'total' => $total,
                'won' => $won,
                'rate' => $rate
            ];
        });
    }
}
