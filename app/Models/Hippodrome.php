<?php

namespace App\Models;

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
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->city) {
            return "{$this->name} ({$this->city})";
        }

        return $this->name;
    }
}
