<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeightToShopifyProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_product_variants', function (Blueprint $table) {
            $table->float('weight');
            $table->string('weight_unit');
			$table->string('barcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_product_variants', function (Blueprint $table) {
            $table->dropColumn(['weight','weight_unit','barcode']);
        });
    }
}
