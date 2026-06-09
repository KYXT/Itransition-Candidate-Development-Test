<?php

namespace App\Rules;

use App\DTO\ProductImportRow;

class MinimumStockPriceRule
{
    public function skipReason(ProductImportRow $row): ?string
    {
        if ($row->price === null || $row->stock === null) {
            return null;
        }

        if ((float) $row->price < 5 && $row->stock < 10) {
            return 'Product costs less than 5 and has less than 10 stock.';
        }

        return null;
    }
}
