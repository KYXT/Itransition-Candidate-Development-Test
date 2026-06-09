<?php

namespace App\Rules;

use App\DTO\ProductImportRow;

class MaximumPriceRule
{
    public function skipReason(ProductImportRow $row): ?string
    {
        if ($row->price !== null && (float) $row->price > 1000) {
            return 'Product costs more than 1000.';
        }

        return null;
    }
}
