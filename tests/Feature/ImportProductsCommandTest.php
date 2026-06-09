<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportProductsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createLegacyProductsTable();
    }

    public function test_command_accepts_a_csv_file_path_argument(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
        ]);

        $this->artisan('products:import', ['path' => $path])
            ->assertSuccessful();

        $this->assertSame(1, DB::table('tblProductData')->count());
    }

    public function test_command_runs_the_import_process_successfully(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
        ]);

        $this->artisan('products:import', ['path' => $path])
            ->expectsOutputToContain('Import complete.')
            ->assertSuccessful();
    }

    public function test_command_supports_the_test_option(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
        ]);

        $this->artisan('products:import', ['path' => $path, '--test' => true])
            ->expectsOutputToContain('Test mode enabled. No rows were inserted.')
            ->assertSuccessful();
    }

    public function test_in_normal_mode_valid_products_are_inserted_into_the_database(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
        ]);

        $this->artisan('products:import', ['path' => $path])
            ->assertSuccessful();

        $this->assertDatabaseHas('tblProductData', [
            'strProductCode' => 'P0001',
            'strProductName' => 'TV',
            'stock' => 10,
            'price' => 399.99,
        ]);
    }

    public function test_in_test_mode_no_products_are_inserted_into_the_database(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
        ]);

        $this->artisan('products:import', ['path' => $path, '--test' => true])
            ->assertSuccessful();

        $this->assertSame(0, DB::table('tblProductData')->count());
    }

    public function test_command_output_contains_summary_counts(): void
    {
        $path = $this->csv([
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32 inch TV,10,399.99,',
            'P0002,Cable,Cheap cable,1,4.99,',
            'P0003,Premium TV,Expensive TV,4,1200.00,',
            'P0004,Broken row',
        ]);

        $this->artisan('products:import', ['path' => $path, '--test' => true])
            ->expectsTable(
                ['Processed', 'Successful', 'Skipped', 'Failed'],
                [[4, 1, 2, 1]],
            )
            ->assertSuccessful();
    }

    public function test_missing_csv_file_returns_failure_status_and_clear_error_message(): void
    {
        $path = sys_get_temp_dir().'/missing-products-'.uniqid().'.csv';

        $exitCode = Artisan::call('products:import', ['path' => $path]);
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('CSV file', $output);
        $this->assertStringContainsString('cannot be read', $output);
    }

    private function createLegacyProductsTable(): void
    {
        Schema::dropIfExists('tblProductData');

        Schema::create('tblProductData', function (Blueprint $table): void {
            $table->increments('intProductDataId');
            $table->string('strProductName', 50);
            $table->string('strProductDesc', 255);
            $table->string('strProductCode', 10)->unique();
            $table->dateTime('dtmAdded')->nullable();
            $table->dateTime('dtmDiscontinued')->nullable();
            $table->timestamp('stmTimestamp')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->decimal('price', 8, 2)->nullable();
        });
    }

    /**
     * @param  list<string>  $lines
     */
    private function csv(array $lines): string
    {
        $path = tempnam(sys_get_temp_dir(), 'import-products-command-');

        file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL);

        return $path;
    }
}
