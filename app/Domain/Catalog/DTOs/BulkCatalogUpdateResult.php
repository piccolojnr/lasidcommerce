<?php

namespace App\Domain\Catalog\DTOs;

class BulkCatalogUpdateResult
{
    public int $processed = 0;

    public int $updated = 0;

    public int $failed = 0;

    /**
     * @var list<array{row: int, message: string}>
     */
    public array $errors = [];

    public function addError(int $row, string $message): void
    {
        $this->failed++;

        if (count($this->errors) >= 25) {
            return;
        }

        $this->errors[] = [
            'row' => $row,
            'message' => $message,
        ];
    }

    public function toArray(): array
    {
        return [
            'processed' => $this->processed,
            'updated' => $this->updated,
            'failed' => $this->failed,
            'errors' => $this->errors,
        ];
    }
}
