<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @property int $id
 */
class Subscription extends Model
{

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'started_at',
        'expires_at',
        'cancelled_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'status' => SubscriptionStatus::class, // başka bilgileri engeller.
    ];

    /**
     * Kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Abonelik planı (standard / premium)
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Bu aboneliğe bağlı ödeme logları
     */
    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /**
     * Scope: aktif abonelikler
     */
    public function scopeActive($query)
    {
        return $query
            ->where('status', SubscriptionStatus::STATUS_ACTIVE)
            ->where('expires_at', '>', now());
    }

    /**
     * Abonelik şu anda aktif mi?
     */
    public function isActive(): bool
    {
        if ($this->status != SubscriptionStatus::STATUS_ACTIVE) {
            return false;
        }

        if (!$this->expires_at) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Kalan gün sayısı (negatif dönebilir, süresi geçmişse)
     */
    public function remainingDays(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at);
    }

    /**
     * İptal et ve tarih ata
     */
    public function markAsCancelled(): void
    {
        $this->status = SubscriptionStatus::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->save();
    }
}
