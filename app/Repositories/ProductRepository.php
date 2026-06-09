<?php

namespace App\Repositories;

use App\DTO\ProductImportRow;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    /**
     * @param  list<string>  $productCodes
     * @return list<string>
     */
    public function existingCodes(array $productCodes): array
    {
        if ($productCodes === []) {
            return [];
        }

        return DB::table('tblProductData')
            ->whereIn('strProductCode', array_unique($productCodes))
            ->pluck('strProductCode')
            ->all();
    }

    /**
     * @param  list<ProductImportRow>  $rows
     */
    public function insertMany(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $now = now();

        DB::table('tblProductData')->insert(array_map(
            static fn (ProductImportRow $row): array => [
                'strProductName' => $row->productName,
                'strProductDesc' => $row->productDescription,
                'strProductCode' => $row->productCode,
                'dtmAdded' => $now,
                'dtmDiscontinued' => $row->discontinuedAt,
                'stock' => $row->stock,
                'price' => $row->price,
            ],
            $rows,
        ));
    }

    public function insert(ProductImportRow $row): void
    {
        $this->insertMany([$row]);
    }
}
