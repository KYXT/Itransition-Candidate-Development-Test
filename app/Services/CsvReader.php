<?php

namespace App\Services;

use App\DTO\ProductImportRow;
use Generator;
use RuntimeException;

class CsvReader
{
    /**
     * @return Generator<int, ProductImportRow>
     */
    public function read(string $path): Generator
    {
        if (! is_readable($path)) {
            throw new RuntimeException("CSV file [{$path}] cannot be read.");
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("CSV file [{$path}] could not be opened.");
        }

        try {
            fgetcsv($handle);

            $lineNumber = 1;

            while (($record = fgetcsv($handle)) !== false) {
                $lineNumber++;

                yield ProductImportRow::fromCsvRecord($record, $lineNumber);
            }
        } finally {
            fclose($handle);
        }
    }
}
