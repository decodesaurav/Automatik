<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyVariationInventoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shopify_variation_inventories', function (Blueprint $table) {
			$table->id();
			$table->foreignId('variation_id');
			$table->foreignId('session_id');
			$table->bigInteger('shopify_location_id')->nullable();
			$table->bigInteger('location_id')->nullable();
			$table->integer('available')->nullable();
			$table->bigInteger('inventory_item_id')->nullable()->index();
			$table->timestamps();

			$table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
			$table->foreign('variation_id')->references('id')->on('shopify_product_variations')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('shopify_variation_inventories');
	}
}
