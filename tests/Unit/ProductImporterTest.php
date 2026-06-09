<?php

namespace Tests\Unit;

use App\DTO\ProductImportRow;
use App\Repositories\ProductRepository;
use App\Rules\DiscontinuedRule;
use App\Rules\MaximumPriceRule;
use App\Rules\MinimumStockPriceRule;
use App\Services\CsvReader;
use App\Services\ProductImporter;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ProductImporterTest extends TestCase
{
    public function test_it_imports_valid_products_successfully(): void
    {
        $repository = new FakeProductRepository;
        $importer = $this->importer([row('P0001')], $repository);

        $report = $importer->import('unused.csv');

        $this->assertSame(['processed' => 1, 'successful' => 1, 'skipped' => 0, 'failed' => 0], $report->summary());
        $this->assertCount(1, $repository->insertedRows);
    }

    public function test_it_skips_products_rejected_by_business_rules(): void
    {
        $repository = new FakeProductRepository;
        $importer = $this->importer([row('P0001', price: '4.99', stock: 9)], $repository);

        $report = $importer->import('unused.csv');

        $this->assertSame(['processed' => 1, 'successful' => 0, 'skipped' => 1, 'failed' => 0], $report->summary());
        $this->assertSame('Product costs less than 5 and has less than 10 stock.', $report->skippedRows()[0]['reason']);
        $this->assertSame([], $repository->insertedRows);
    }

    public function test_it_continues_processing_when_one_row_fails(): void
    {
        $repository = new FakeProductRepository(failingCodes: ['P0001']);
        $importer = $this->importer([row('P0001'), row('P0002')], $repository);

        $report = $importer->import('unused.csv');

        $this->assertSame(['processed' => 2, 'successful' => 1, 'skipped' => 0, 'failed' => 1], $report->summary());
        $this->assertSame(['P0002'], array_map(
            static fn (ProductImportRow $row): ?string => $row->productCode,
            $repository->insertedRows,
        ));
    }

    public function test_it_collects_failed_rows_in_the_final_report(): void
    {
        $repository = new FakeProductRepository(failingCodes: ['P0001']);
        $importer = $this->importer([row('P0001')], $repository);

        $report = $importer->import('unused.csv');

        $this->assertSame(1, $report->summary()['failed']);
        $this->assertSame('P0001', $report->failedRows()[0]['code']);
        $this->assertSame('Insert failed for P0001.', $report->failedRows()[0]['reason']);
    }

    public function test_it_returns_correct_counters(): void
    {
        $repository = new FakeProductRepository(failingCodes: ['P0003']);
        $importer = $this->importer([
            row('P0001'),
            row('P0002', price: '1000.01'),
            row('P0003'),
            invalidRow('P0004'),
        ], $repository);

        $report = $importer->import('unused.csv');

        $this->assertSame([
            'processed' => 4,
            'successful' => 1,
            'skipped' => 1,
            'failed' => 2,
        ], $report->summary());
    }

    public function test_in_test_mode_it_processes_csv_rows_but_does_not_insert_records(): void
    {
        $repository = new FakeProductRepository;
        $importer = $this->importer([row('P0001')], $repository);

        $report = $importer->import('unused.csv', dryRun: true);

        $this->assertSame(['processed' => 1, 'successful' => 1, 'skipped' => 0, 'failed' => 0], $report->summary());
        $this->assertSame([], $repository->insertedRows);
    }

    /**
     * @param  list<ProductImportRow>  $rows
     */
    private function importer(array $rows, FakeProductRepository $repository): ProductImporter
    {
        return new ProductImporter(
            new FakeCsvReader($rows),
            $repository,
            new MinimumStockPriceRule,
            new MaximumPriceRule,
            new DiscontinuedRule,
        );
    }
}

/**
 * @param  list<string>  $errors
 */
function row(string $code, string $price = '10.00', int $stock = 10, bool $discontinued = false, array $errors = []): ProductImportRow
{
    return new ProductImportRow(
        lineNumber: 2,
        productCode: $code,
        productName: 'Product '.$code,
        productDescription: 'Description '.$code,
        stock: $stock,
        price: $price,
        discontinued: $discontinued,
        errors: $errors,
    );
}

function invalidRow(string $code): ProductImportRow
{
    return row($code, errors: ['Product name is required.']);
}

class FakeCsvReader extends CsvReader
{
    /**
     * @param  list<ProductImportRow>  $rows
     */
    public function __construct(private readonly array $rows) {}

    public function read(string $path): Generator
    {
        foreach ($this->rows as $row) {
            yield $row;
        }
    }
}

class FakeProductRepository extends ProductRepository
{
    /**
     * @var list<ProductImportRow>
     */
    public array $insertedRows = [];

    /**
     * @param  list<string>  $existingCodes
     * @param  list<string>  $failingCodes
     */
    public function __construct(
        private readonly array $existingCodes = [],
        private readonly array $failingCodes = [],
    ) {}

    public function existingCodes(array $productCodes): array
    {
        return array_values(array_intersect($productCodes, $this->existingCodes));
    }

    public function insertMany(array $rows): void
    {
        foreach ($rows as $row) {
            if (in_array($row->productCode, $this->failingCodes, true)) {
                throw new RuntimeException("Insert failed for {$row->productCode}.");
            }
        }

        array_push($this->insertedRows, ...$rows);
    }

    public function insert(ProductImportRow $row): void
    {
        if (in_array($row->productCode, $this->failingCodes, true)) {
            throw new RuntimeException("Insert failed for {$row->productCode}.");
        }

        $this->insertedRows[] = $row;
    }
}
