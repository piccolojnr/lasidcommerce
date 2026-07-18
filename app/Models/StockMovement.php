<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_RESTOCK = 'restock';

    public const TYPE_RETURN = 'return';

    public const TYPE_CORRECTION_ADD = 'correction_add';

    public const TYPE_DAMAGE = 'damage';

    public const TYPE_SHRINKAGE = 'shrinkage';

    public const TYPE_CORRECTION_REMOVE = 'correction_remove';

    public const UPDATED_AT = null;

    protected $fillable = [
        'stock_item_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reference_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return list<string>
     */
    public static function adminAdjustmentTypes(): array
    {
        return [
            self::TYPE_RESTOCK,
            self::TYPE_RETURN,
            self::TYPE_CORRECTION_ADD,
            self::TYPE_DAMAGE,
            self::TYPE_SHRINKAGE,
            self::TYPE_CORRECTION_REMOVE,
        ];
    }

    public static function stockDeltaForType(string $type, int $quantity): int
    {
        return match ($type) {
            self::TYPE_RESTOCK,
            self::TYPE_RETURN,
            self::TYPE_CORRECTION_ADD => $quantity,
            self::TYPE_DAMAGE,
            self::TYPE_SHRINKAGE,
            self::TYPE_CORRECTION_REMOVE => -$quantity,
            default => 0,
        };
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
