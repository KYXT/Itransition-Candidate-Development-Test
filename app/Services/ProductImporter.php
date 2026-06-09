<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Rules\DiscontinuedRule;
use App\Rules\MaximumPriceRule;
use App\Rules\MinimumStockPriceRule;
use Throwable;

class ProductImporter
{
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

        foreach ($this->csvReader->read($path) as $row) {
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

            if ($this->products->existsByCode($row->productCode)) {
                $report->skipped($row, 'Product code already exists.');

                continue;
            }

            $row = $this->discontinuedRule->apply($row);

            if (! $dryRun) {
                try {
                    $this->products->insert($row);
                } catch (Throwable $exception) {
                    $report->failed($row, $exception->getMessage());

                    continue;
                }
            }

            $report->successful();
        }

        return $report;
    }
}
