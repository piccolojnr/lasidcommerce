<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class, 'option_type_id');
    }
}
