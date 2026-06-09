<?php

namespace App\Rules;

use App\DTO\ProductImportRow;
use Carbon\CarbonImmutable;

class DiscontinuedRule
{
    public function apply(ProductImportRow $row): ProductImportRow
    {
        if (! $row->discontinued) {
            return $row;
        }

        return $row->withDiscontinuedAt(CarbonImmutable::now());
    }
}
