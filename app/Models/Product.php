<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const IMAGE_COLLECTION = 'images';

    public const IMAGE_CONVERSION_THUMB = 'thumb';

    public const IMAGE_CONVERSION_CARD = 'card';

    public const IMAGE_CONVERSION_GALLERY = 'gallery';

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'short_description',
        'description',
        'sku',
        'status',
        'product_type',
        'base_price',
        'compare_at_price',
        'cost_price',
        'track_inventory',
        'allow_backorders',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'compare_at_price' => 'integer',
            'cost_price' => 'integer',
            'track_inventory' => 'boolean',
            'allow_backorders' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function optionTypes(): HasMany
    {
        return $this->hasMany(ProductOptionType::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('tags.name');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('collections.name');
    }

    public function saleDiscountAmount(): int
    {
        if (!$this->isOnSale()) {
            return 0;
        }

        return $this->compare_at_price - $this->base_price;
    }

    public function saleDiscountPercentage(): ?int
    {
        if (!$this->isOnSale() || $this->compare_at_price <= 0) {
            return null;
        }

        return (int) round(
            (($this->compare_at_price - $this->base_price) / $this->compare_at_price) * 100,
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeVisibleOnStorefront(Builder $query): Builder
    {
        return $query
            ->active()
            ->where(fn($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['successful', 'paid'])
            ->whereNotNull('paid_at');
    }

    public function scopeOnSale(Builder $query): Builder
    {
        return $query
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'base_price');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->base_price;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE_COLLECTION);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(self::IMAGE_CONVERSION_THUMB)
            ->performOnCollections(self::IMAGE_COLLECTION)
            ->fit(Fit::Crop, 160, 160)
            ->optimize()
            ->queued();

        $this->addMediaConversion(self::IMAGE_CONVERSION_CARD)
            ->performOnCollections(self::IMAGE_COLLECTION)
            ->fit(Fit::Crop, 640, 640)
            ->optimize()
            ->queued();

        $this->addMediaConversion(self::IMAGE_CONVERSION_GALLERY)
            ->performOnCollections(self::IMAGE_COLLECTION)
            ->fit(Fit::Max, 1400, 1400)
            ->optimize()
            ->queued();
    }
}
