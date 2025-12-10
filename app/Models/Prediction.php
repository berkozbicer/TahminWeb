<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    use HasFactory;

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

    /**
     * Hipodrom ilişkisi
     */
    public function hippodrome(): BelongsTo
    {
        return $this->belongsTo(Hippodrome::class);
    }

    /**
     * Tahmini oluşturan kullanıcı (admin)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: yayında olan tahminler
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope: belirli tarih için
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('race_date', $date);
    }

    /**
     * Scope: belirli hipodrom için
     */
    public function scopeForHippodrome($query, int $hippodromeId)
    {
        return $query->where('hippodrome_id', $hippodromeId);
    }

    /**
     * Scope: kullanıcının abonelik seviyesine göre erişilebilir tahminler
     *
     * $userLevel: null | 'standard' | 'premium'
     * - premium: her şeye erişir
     * - standard: sadece access_level = 'standard'
     * - null/diğer: hiçbir şey
     */
    public function scopeAccessibleForLevel($query, ?string $userLevel)
    {
        if ($userLevel === self::ACCESS_PREMIUM) {
            return $query;
        }

        if ($userLevel === self::ACCESS_STANDARD) {
            return $query->where('access_level', self::ACCESS_STANDARD);
        }

        // Abonelik yoksa boş döndür
        return $query->whereRaw('1 = 0');
    }

    /**
     * Sadece bugünün tahminleri
     */
    public function scopeToday($query)
    {
        return $query->whereDate('race_date', today());
    }

    /**
     * Bu tahmin sadece premium mu?
     */
    public function isPremiumOnly(): bool
    {
        return $this->access_level === self::ACCESS_PREMIUM;
    }

    /**
     * Sonuç belli mi?
     */
    public function isSettled(): bool
    {
        return $this->prediction_result !== self::RESULT_PENDING;
    }
}
