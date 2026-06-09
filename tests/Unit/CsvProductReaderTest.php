<?php

namespace Tests\Unit;

use App\Services\CsvReader;
use Generator;
use PHPUnit\Framework\TestCase;

class CsvProductReaderTest extends TestCase
{
    public function test_it_reads_a_valid_csv_file_and_returns_product_import_rows(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
            'P0002,Cd Player,Nice CD player,11,50.12,yes',
        ]);

        $rows = iterator_to_array((new CsvReader)->read($path));

        $this->assertCount(2, $rows);
        $this->assertTrue($rows[0]->isValid());
        $this->assertTrue($rows[1]->isValid());
    }

    public function test_it_correctly_maps_csv_columns_to_dto_fields(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0002,Cd Player,Nice CD player,11,50.12,yes',
        ]);

        $row = iterator_to_array((new CsvReader)->read($path))[0];

        $this->assertSame(2, $row->lineNumber);
        $this->assertSame('P0002', $row->productCode);
        $this->assertSame('Cd Player', $row->productName);
        $this->assertSame('Nice CD player', $row->productDescription);
        $this->assertSame(11, $row->stock);
        $this->assertSame('50.12', $row->price);
        $this->assertTrue($row->discontinued);
    }

    public function test_it_handles_missing_required_columns(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV',
        ]);

        $row = iterator_to_array((new CsvReader)->read($path))[0];

        $this->assertFalse($row->isValid());
        $this->assertContains('CSV row must contain exactly 6 columns.', $row->errors);
    }

    public function test_it_handles_invalid_price_values(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,$399.99,',
        ]);

        $row = iterator_to_array((new CsvReader)->read($path))[0];

        $this->assertFalse($row->isValid());
        $this->assertContains('Price must be a positive decimal value with up to two decimal places.', $row->errors);
    }

    public function test_it_handles_invalid_stock_values(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,,399.99,',
        ]);

        $row = iterator_to_array((new CsvReader)->read($path))[0];

        $this->assertFalse($row->isValid());
        $this->assertContains('Stock must be a non-negative integer.', $row->errors);
    }

    public function test_it_handles_empty_rows(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            '',
        ]);

        $row = iterator_to_array((new CsvReader)->read($path))[0];

        $this->assertFalse($row->isValid());
        $this->assertContains('CSV row must contain exactly 6 columns.', $row->errors);
    }

    public function test_it_handles_malformed_csv_rows_gracefully(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0017,CPU,Processing power, ideal for multimedia,4,4.22,',
        ]);

        $row = iterator_to_array((new CsvReader)->read($path))[0];

        $this->assertFalse($row->isValid());
        $this->assertContains('CSV row must contain exactly 6 columns.', $row->errors);
    }

    public function test_it_returns_a_generator_and_does_not_load_the_entire_file_into_memory(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
        ]);

        $rows = (new CsvReader)->read($path);

        $this->assertInstanceOf(Generator::class, $rows);
    }

    /**
     * @param  list<string>  $lines
     */
    private function csv(array $lines): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv-product-reader-');

        file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL);

        return $path;
    }
}
