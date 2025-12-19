<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hippodrome extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Bu hipodroma ait tahminler
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * Scope: aktif hipodromlar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Görüntüleme için isim (Veliefendi - İstanbul gibi)
     * Laravel Modern Accessor
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->city ? "{$this->name} ({$this->city})" : $this->name
        );
    }
}
