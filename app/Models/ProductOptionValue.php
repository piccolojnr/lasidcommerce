<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_type_id',
        'value',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function optionType(): BelongsTo
    {
        return $this->belongsTo(ProductOptionType::class, 'option_type_id');
    }

    public function variantLinks(): HasMany
    {
        return $this->hasMany(ProductVariantOptionValue::class, 'option_value_id');
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_option_values',
            'option_value_id',
            'product_variant_id'
        );
    }
}
