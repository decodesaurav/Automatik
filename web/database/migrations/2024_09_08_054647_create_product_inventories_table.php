<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductInventoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_inventories', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('product_id');
			$table->unsignedBigInteger('inventory_item_id');
			$table->unsignedBigInteger('location_id');
			$table->integer('inventory_quantity');
			$table->integer('old_inventory_quantity');
			$table->string('sku')->nullable();
			$table->boolean('is_tracked');
			$table->timestamp('updated_at')->useCurrent();

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
		Schema::dropIfExists('product_inventories');
	}
}
