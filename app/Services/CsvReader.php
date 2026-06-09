<?php

namespace App\Services;

use App\DTO\ProductImportRow;
use Generator;
use ReflectionClass;
use RuntimeException;
use Spatie\SimpleExcel\SimpleExcelReader;

class CsvReader
{
    /**
     * @return Generator<int, list<ProductImportRow>>
     */
    public function readChunks(string $path, int $chunkSize): Generator
    {
        $chunk = [];

        foreach ($this->read($path) as $row) {
            $chunk[] = $row;

            if (count($chunk) === $chunkSize) {
                yield $chunk;

                $chunk = [];
            }
        }

        if ($chunk !== []) {
            yield $chunk;
        }
    }

    /**
     * @return Generator<int, ProductImportRow>
     */
    public function read(string $path): Generator
    {
        $lineNumber = 0;

        foreach ($this->reader($path)->noHeaderRow()->getRows() as $row) {
            $lineNumber++;

            if ($lineNumber === 1) {
                continue;
            }

            yield ProductImportRow::fromCsvRecord($row, $lineNumber);
        }
    }

    private function reader(string $path): SimpleExcelReader
    {
        if (! is_readable($path)) {
            throw new RuntimeException("CSV file [{$path}] cannot be read.");
        }

        $reader = SimpleExcelReader::create($path, 'csv')->useDelimiter(',');

        $csvOptionsProperty = (new ReflectionClass($reader))->getProperty('csvOptions');
        $csvOptionsProperty->setAccessible(true);

        $csvOptions = $csvOptionsProperty->getValue($reader);
        $csvOptions->SHOULD_PRESERVE_EMPTY_ROWS = true;

        return $reader;
    }
}
