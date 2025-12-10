<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    // Relations
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class);
    }

    // Helper Methods
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

    /**
     * Filament panel access check. Only users with `role === 'admin'` can access.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
