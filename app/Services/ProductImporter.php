<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Rules\DiscontinuedRule;
use App\Rules\MaximumPriceRule;
use App\Rules\MinimumStockPriceRule;
use Illuminate\Support\Arr;
use Throwable;

class ProductImporter
{
    private const ChunkSize = 1000;

    public function __construct(
        private readonly CsvReader $csvReader,
        private readonly ProductRepository $products,
        private readonly MinimumStockPriceRule $minimumStockPriceRule,
        private readonly MaximumPriceRule $maximumPriceRule,
        private readonly DiscontinuedRule $discontinuedRule,
    ) {}

    public function import(string $path, bool $dryRun = false): ProductImportReport
    {
        $report = new ProductImportReport;
        $seenImportCodes = [];

        foreach ($this->csvReader->readChunks($path, self::ChunkSize) as $chunk) {
            $rowsToInsert = [];

            foreach ($chunk as $row) {
                $report->processed();

                if (! $row->isValid()) {
                    $report->failed($row, implode(' ', $row->errors));

                    continue;
                }

                if ($reason = $this->minimumStockPriceRule->skipReason($row)) {
                    $report->skipped($row, $reason);

                    continue;
                }

                if ($reason = $this->maximumPriceRule->skipReason($row)) {
                    $report->skipped($row, $reason);

                    continue;
                }

                if (isset($seenImportCodes[$row->productCode])) {
                    $report->skipped($row, 'Product code is duplicated in the import file.');

                    continue;
                }

                $rowsToInsert[] = $this->discontinuedRule->apply($row);
                $seenImportCodes[$row->productCode] = true;
            }

            $existingCodes = array_flip($this->products->existingCodes(
                Arr::pluck($rowsToInsert, 'productCode'),
            ));

            $insertableRows = [];

            foreach ($rowsToInsert as $row) {
                if (isset($existingCodes[$row->productCode])) {
                    $report->skipped($row, 'Product code already exists.');

                    unset($seenImportCodes[$row->productCode]);

                    continue;
                }

                $insertableRows[] = $row;
            }

            if ($dryRun) {
                for ($count = 0; $count < count($insertableRows); $count++) {
                    $report->successful();
                }

                continue;
            }

            try {
                $this->products->insertMany($insertableRows);

                for ($count = 0; $count < count($insertableRows); $count++) {
                    $report->successful();
                }
            } catch (Throwable $exception) {
                foreach ($insertableRows as $row) {
                    try {
                        $this->products->insert($row);
                        $report->successful();
                    } catch (Throwable $rowException) {
                        $report->failed($row, $rowException->getMessage());
                        unset($seenImportCodes[$row->productCode]);
                    }
                }
            }
        }

        return $report;
    }
}
