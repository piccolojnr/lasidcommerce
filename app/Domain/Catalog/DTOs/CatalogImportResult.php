<?php

namespace App\Domain\Catalog\DTOs;

class CatalogImportResult
{
    /**
     * @param  list<array{row:int,message:string}>  $errors
     */
    public function __construct(
        public int $processed = 0,
        public int $created = 0,
        public int $updated = 0,
        public int $skipped = 0,
        public int $failed = 0,
        public array $errors = [],
    ) {}

    public function addError(int $row, string $message): void
    {
        $this->failed++;

        if (count($this->errors) < 25) {
            $this->errors[] = [
                'row' => $row,
                'message' => $message,
            ];
        }
    }

    public function toArray(): array
    {
        return [
            'processed' => $this->processed,
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
            'errors' => $this->errors,
        ];
    }
}
