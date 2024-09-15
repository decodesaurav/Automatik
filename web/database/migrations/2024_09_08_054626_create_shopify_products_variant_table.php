<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyProductsVariantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_products_variant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('shopify_product_id');
            $table->unsignedBigInteger('shopify_variant_id');
            $table->string('title');
            $table->float('price');
            $table->string('sku');
            $table->integer('inventory_quantity');
            $table->integer('old_inventory_quantity');
            $table->integer('position');
            $table->string('inventory_policy')->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->float('weight');
            $table->string('weight_unit');
            $table->boolean('requires_shipping')->default(0);
            $table->string('option1')->nullable();
            $table->string('option2')->nullable();
            $table->string('option3')->nullable();
            $table->integer('grams')->default(0);
            $table->boolean('taxable')->default(false);
            $table->string('fulfillment_service')->nullable();
            $table->string('inventory_management')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_products_variant');
    }
}
