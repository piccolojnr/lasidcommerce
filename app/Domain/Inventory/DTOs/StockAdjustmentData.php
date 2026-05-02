<?php

namespace App\Domain\Inventory\DTOs;

use App\Models\StockMovement;

class StockAdjustmentData
{
    public function __construct(
        public readonly string $type,
        public readonly int $quantity,
        public readonly ?string $referenceType,
        public readonly ?int $referenceId,
        public readonly ?string $note,
        public readonly ?int $createdBy,
    ) {}

    public static function fromArray(array $attributes, ?int $createdBy = null): self
    {
        return new self(
            type: (string) $attributes['type'],
            quantity: (int) $attributes['quantity'],
            referenceType: $attributes['reference_type'] ?? null,
            referenceId: isset($attributes['reference_id']) ? (int) $attributes['reference_id'] : null,
            note: $attributes['note'] ?? null,
            createdBy: $createdBy,
        );
    }

    public function stockDelta(): int
    {
        return StockMovement::stockDeltaForType($this->type, $this->quantity);
    }

    public function toMovementAttributes(): array
    {
        return [
            'type' => $this->type,
            'quantity' => $this->quantity,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'note' => $this->note,
            'created_by' => $this->createdBy,
        ];
    }
}
