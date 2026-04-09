<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingZoneArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id',
        'area_type',
        'area_name',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }
}
