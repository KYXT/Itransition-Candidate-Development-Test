<?php

namespace App\Console\Commands;

use App\Services\ProductImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('products:import {path=storage/app/private/stock.csv : CSV file path} {--test : Run import without inserting rows}')]
#[Description('Import supplier products from a CSV file')]
class ImportProductsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ProductImporter $importer): int
    {
        $path = $this->resolvePath((string) $this->argument('path'));
        $dryRun = (bool) $this->option('test');

        $report = $importer->import($path, $dryRun);
        $summary = $report->summary();

        if ($dryRun) {
            $this->warn('Test mode enabled. No rows were inserted.');
        }

        $this->info('Import complete.');
        $this->table(
            ['Processed', 'Successful', 'Skipped', 'Failed'],
            [[
                $summary['processed'],
                $summary['successful'],
                $summary['skipped'],
                $summary['failed'],
            ]],
        );

        if ($report->skippedRows() !== []) {
            $this->warn('Skipped rows:');
            $this->table(['Line', 'Product Code', 'Reason'], $report->skippedRows());
        }

        if ($report->failedRows() !== []) {
            $this->error('Failed rows:');
            $this->table(['Line', 'Product Code', 'Reason'], $report->failedRows());
        }

        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }
}
