<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyProductVariantsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shopify_product_variants', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('shopify_product_id');
			$table->unsignedBigInteger('session_id');
			$table->string('variant_id')->nullable();
			$table->string('title')->nullable();
			$table->float('price')->nullable();
			$table->string('is_tracked')->nullable();
			$table->string('sku')->nullable();
			$table->string('inventory_policy')->nullable();
			$table->string('option1')->nullable();
			$table->string('option2')->nullable();
			$table->string('option3')->nullable();
			$table->string('image_id')->nullable();
			$table->bigInteger('inventory_item_id')->nullable();
			$table->bigInteger('inventory_quantity')->nullable();
			$table->timestamps();
			$table->foreign('shopify_product_id')->references('id')->on('shopify_products')->onDelete('cascade');
			$table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('shopify_product_variants');
	}
}
