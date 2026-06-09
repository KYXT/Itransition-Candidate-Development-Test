<?php

namespace App\DTO;

use Carbon\CarbonImmutable;

class ProductImportRow
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        public readonly int $lineNumber,
        public readonly ?string $productCode,
        public readonly ?string $productName,
        public readonly ?string $productDescription,
        public readonly ?int $stock,
        public readonly ?string $price,
        public readonly bool $discontinued,
        public readonly ?CarbonImmutable $discontinuedAt = null,
        public readonly array $errors = [],
    ) {}

    /**
     * @param  list<string|null>  $record
     */
    public static function fromCsvRecord(array $record, int $lineNumber): self
    {
        if (count($record) !== 6) {
            return new self(
                lineNumber: $lineNumber,
                productCode: $record[0] ?? null,
                productName: $record[1] ?? null,
                productDescription: $record[2] ?? null,
                stock: null,
                price: null,
                discontinued: false,
                errors: ['CSV row must contain exactly 6 columns.'],
            );
        }

        [$productCode, $productName, $productDescription, $stock, $price, $discontinued] = array_map(
            static fn (?string $value): string => trim((string) $value),
            $record,
        );

        $errors = [];

        if ($productCode === '') {
            $errors[] = 'Product code is required.';
        }

        if ($productName === '') {
            $errors[] = 'Product name is required.';
        }

        if ($productDescription === '') {
            $errors[] = 'Product description is required.';
        }

        if ($stock === '' || filter_var($stock, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
            $errors[] = 'Stock must be a non-negative integer.';
            $parsedStock = null;
        } else {
            $parsedStock = (int) $stock;
        }

        if ($price === '' || preg_match('/^\d+(\.\d{1,2})?$/', $price) !== 1) {
            $errors[] = 'Price must be a positive decimal value with up to two decimal places.';
            $parsedPrice = null;
        } else {
            $parsedPrice = number_format((float) $price, 2, '.', '');
        }

        return new self(
            lineNumber: $lineNumber,
            productCode: $productCode !== '' ? $productCode : null,
            productName: $productName !== '' ? $productName : null,
            productDescription: $productDescription !== '' ? $productDescription : null,
            stock: $parsedStock,
            price: $parsedPrice,
            discontinued: strtolower($discontinued) === 'yes',
            errors: $errors,
        );
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function withDiscontinuedAt(CarbonImmutable $discontinuedAt): self
    {
        return new self(
            lineNumber: $this->lineNumber,
            productCode: $this->productCode,
            productName: $this->productName,
            productDescription: $this->productDescription,
            stock: $this->stock,
            price: $this->price,
            discontinued: $this->discontinued,
            discontinuedAt: $discontinuedAt,
            errors: $this->errors,
        );
    }
}
