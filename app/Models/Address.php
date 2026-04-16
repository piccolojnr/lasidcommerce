<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'phone',
        'country',
        'region',
        'city',
        'district',
        'address_line_1',
        'address_line_2',
        'landmark',
        'postal_code',
        'is_default',
        'shipping_zone_id',
        'shipping_zone_area_id',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function shippingZoneArea(): BelongsTo
    {
        return $this->belongsTo(ShippingZoneArea::class);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim(implode(' ', array_filter([$this->name]))),
        );
    }
}
