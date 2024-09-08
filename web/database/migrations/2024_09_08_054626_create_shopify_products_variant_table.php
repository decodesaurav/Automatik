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
            $table->unsignedBigInteger('product_id');
			$table->unsignedBigInteger('variant_id');
			$table->string('title');
			$table->decimal('price',8,2);
			$table->integer('position');
			$table->string('inventory_policy');
			$table->decimal('compare_at_price',8,2)->nullable();
			$table->timestamp('created_at');
			$table->timestamp('updated_at')->useCurrent();
			$table->boolean('taxable');
			$table->string('inventory_management');
			$table->boolean('requires_shipping');
			$table->string('sku')->nullable();
			$table->unsignedBigInteger('inventory_item_id');
			$table->integer('inventory_quantity');
			$table->integer('old_inventory_quantity');
			$table->unsignedBigInteger('image_id')->nullable();

			$table->foreign('product_id')->references('id')->on('shopify_products')->onDelete('cascade');
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
