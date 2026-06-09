<?php

namespace App\Repositories;

use App\DTO\ProductImportRow;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function existsByCode(string $productCode): bool
    {
        return DB::table('tblProductData')
            ->where('strProductCode', $productCode)
            ->exists();
    }

    public function insert(ProductImportRow $row): void
    {
        DB::table('tblProductData')->insert([
            'strProductName' => $row->productName,
            'strProductDesc' => $row->productDescription,
            'strProductCode' => $row->productCode,
            'dtmAdded' => now(),
            'dtmDiscontinued' => $row->discontinuedAt,
            'stock' => $row->stock,
            'price' => $row->price,
        ]);
    }
}
