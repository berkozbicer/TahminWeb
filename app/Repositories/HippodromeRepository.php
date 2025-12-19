<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Hippodrome;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class HippodromeRepository
{
    public function getActiveList(): Collection
    {
        // 86400 saniye = 1 gün
        return Cache::remember('hippodromes.active', 86400, function () {
            return Hippodrome::active()->orderBy('name')->get();
        });
    }

    public function findByIdOrSlug(string|int $input): ?Hippodrome
    {
        $query = Hippodrome::active();

        if (is_numeric($input)) {
            return $query->find($input);
        }

        return $query->where('slug', $input)->first();
    }
}
