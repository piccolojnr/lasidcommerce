<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'method_type',
        'price_type',
        'flat_rate_amount',
        'min_delivery_days',
        'max_delivery_days',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'flat_rate_amount' => 'integer',
            'min_delivery_days' => 'integer',
            'max_delivery_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function shippingZones(): BelongsToMany
    {
        return $this->belongsToMany(ShippingZone::class)
            ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
