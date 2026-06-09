<?php

namespace App\Services;

use App\DTO\ProductImportRow;

class ProductImportReport
{
    private int $processed = 0;

    private int $successful = 0;

    private int $skipped = 0;

    private int $failed = 0;

    /**
     * @var list<array{line: int, code: string|null, reason: string}>
     */
    private array $skippedRows = [];

    /**
     * @var list<array{line: int, code: string|null, reason: string}>
     */
    private array $failedRows = [];

    public function processed(): void
    {
        $this->processed++;
    }

    public function successful(): void
    {
        $this->successful++;
    }

    public function skipped(ProductImportRow $row, string $reason): void
    {
        $this->skipped++;

        $this->skippedRows[] = [
            'line' => $row->lineNumber,
            'code' => $row->productCode,
            'reason' => $reason,
        ];
    }

    public function failed(ProductImportRow $row, string $reason): void
    {
        $this->failed++;

        $this->failedRows[] = [
            'line' => $row->lineNumber,
            'code' => $row->productCode,
            'reason' => $reason,
        ];
    }

    /**
     * @return array{processed: int, successful: int, skipped: int, failed: int}
     */
    public function summary(): array
    {
        return [
            'processed' => $this->processed,
            'successful' => $this->successful,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
    }

    /**
     * @return list<array{line: int, code: string|null, reason: string}>
     */
    public function skippedRows(): array
    {
        return $this->skippedRows;
    }

    /**
     * @return list<array{line: int, code: string|null, reason: string}>
     */
    public function failedRows(): array
    {
        return $this->failedRows;
    }
}
