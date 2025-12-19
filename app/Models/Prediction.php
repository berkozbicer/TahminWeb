<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    use HasFactory;

    // Constants
    public const ACCESS_STANDARD = 'standard';
    public const ACCESS_PREMIUM = 'premium';

    public const RESULT_WON = 'won';
    public const RESULT_LOST = 'lost';
    public const RESULT_PENDING = 'pending';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'hippodrome_id',
        'race_date',
        'race_time',
        'race_number',
        'access_level',
        'race_title',
        'basic_prediction',
        'detailed_analysis',
        'banker_tips',
        'statistics',
        'winning_horse',
        'winning_odds',
        'prediction_result',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'race_date' => 'date',
        'statistics' => 'array',
        'winning_odds' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function hippodrome(): BelongsTo
    {
        return $this->belongsTo(Hippodrome::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('race_date', $date);
    }

    public function scopeForHippodrome($query, int $hippodromeId)
    {
        return $query->where('hippodrome_id', $hippodromeId);
    }

    public function scopeAccessibleForLevel($query, ?string $userLevel)
    {
        if ($userLevel === self::ACCESS_PREMIUM) {
            return $query;
        }

        if ($userLevel === self::ACCESS_STANDARD) {
            return $query->where('access_level', self::ACCESS_STANDARD);
        }

        // Abonelik yoksa hiçbir şey döndürme
        return $query->whereRaw('1 = 0');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('race_date', today());
    }

    public function isPremiumOnly(): bool
    {
        return $this->access_level === self::ACCESS_PREMIUM;
    }

    public function isSettled(): bool
    {
        return $this->prediction_result !== self::RESULT_PENDING;
    }
}
