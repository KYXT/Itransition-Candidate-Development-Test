<?php

namespace Tests\Unit;

use App\DTO\ProductImportRow;
use App\Rules\DiscontinuedRule;
use App\Rules\MaximumPriceRule;
use App\Rules\MinimumStockPriceRule;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ProductImportRulesTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_product_with_price_less_than_5_and_stock_less_than_10_should_be_skipped(): void
    {
        $reason = (new MinimumStockPriceRule)->skipReason($this->row(price: '4.99', stock: 9));

        $this->assertSame('Product costs less than 5 and has less than 10 stock.', $reason);
    }

    public function test_product_with_price_less_than_5_and_stock_at_least_10_should_be_imported(): void
    {
        $reason = (new MinimumStockPriceRule)->skipReason($this->row(price: '4.99', stock: 10));

        $this->assertNull($reason);
    }

    public function test_product_with_price_at_least_5_and_stock_less_than_10_should_be_imported(): void
    {
        $reason = (new MinimumStockPriceRule)->skipReason($this->row(price: '5.00', stock: 1));

        $this->assertNull($reason);
    }

    public function test_product_with_price_greater_than_1000_should_be_skipped(): void
    {
        $reason = (new MaximumPriceRule)->skipReason($this->row(price: '1000.01', stock: 20));

        $this->assertSame('Product costs more than 1000.', $reason);
    }

    public function test_product_marked_as_discontinued_should_be_imported(): void
    {
        $row = $this->row(price: '10.00', stock: 20, discontinued: true);

        $this->assertNull((new MinimumStockPriceRule)->skipReason($row));
        $this->assertNull((new MaximumPriceRule)->skipReason($row));
    }

    public function test_discontinued_product_should_have_discontinued_at_set_to_current_date(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-09 12:30:00'));

        $row = (new DiscontinuedRule)->apply($this->row(discontinued: true));

        $this->assertTrue($row->discontinuedAt?->isSameDay(CarbonImmutable::now()));
    }

    private function row(string $price = '10.00', int $stock = 10, bool $discontinued = false): ProductImportRow
    {
        return new ProductImportRow(
            lineNumber: 2,
            productCode: 'P0001',
            productName: 'Test Product',
            productDescription: 'Test description',
            stock: $stock,
            price: $price,
            discontinued: $discontinued,
        );
    }
}
