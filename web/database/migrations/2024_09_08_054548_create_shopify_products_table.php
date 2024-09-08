<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyProductsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shopify_products', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('product_id');
			$table->string('title');
			$table->text('description');
			$table->string('vendor');
			$table->string('product_type');
			$table->timestamp('created_at');
			$table->timestamp('updated_at')->useCurrent();
			$table->timestamp('published_at')->nullable();
			$table->string('published_scope');
			$table->string('tags')->nullable();
			$table->enum('status', ['active', 'archived', 'draft']);
			$table->json('options')->nullable();
			$table->json('images')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('shopify_products');
	}
}
