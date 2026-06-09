<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tblProductData', function (Blueprint $table) {
            /**
             * Existing rows cannot be safely backfilled with zero values here.
             * A zero price could allow users to buy products for free, while a zero
             * stock count could incorrectly hide available products.
             */
            $table->unsignedInteger('stock')->nullable();
            $table->decimal('price', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tblProductData', function (Blueprint $table) {
            $table->dropColumn(['stock', 'price']);
        });
    }
};
