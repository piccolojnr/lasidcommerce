<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantOptionValue extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $fillable = [
        'product_variant_id',
        'option_value_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(ProductOptionValue::class, 'option_value_id');
    }
}
