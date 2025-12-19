<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Kullanıcının en son oluşturulmuş AKTİF aboneliği
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', Subscription::STATUS_ACTIVE) // Sabit kullanımı
            ->where('expires_at', '>', now())
            ->latestOfMany();
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function hasActiveSubscriptionTo(int $planId): bool
    {
        return $this->subscriptions()
            ->where('subscription_plan_id', $planId)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function getSubscriptionLevel(): ?string
    {
        $subscription = $this->activeSubscription;
        return $subscription ? $subscription->plan->slug : null;
    }

    public function canAccessPrediction(string $accessLevel): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $userLevel = $this->getSubscriptionLevel();

        if (!$userLevel) {
            return false;
        }

        if ($accessLevel === 'standard') {
            return in_array($userLevel, ['standard', 'premium']);
        }

        if ($accessLevel === 'premium') {
            return $userLevel === 'premium';
        }

        return false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
