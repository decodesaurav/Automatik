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
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('shopify_product_id');

            $table->string('title');
            $table->string('handle');
            $table->text('body_html')->nullable();
            $table->string('vendor');
            $table->string('product_type');
            $table->timestamp('shopify_created_at')->nullable();
            $table->timestamp('shopify_updated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('published_scope')->nullable();
            $table->string('status');
            $table->text('tags')->nullable();
            $table->text('variants')->nullable();
            $table->text('options')->nullable();
            $table->text('images')->nullable();
            $table->text('image')->nullable();
            $table->text('metafields')->nullable();

            $table->unsignedBigInteger('feed_id')->nullable();
            $table->unsignedBigInteger('collection_id')->nullable();

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
		Schema::dropIfExists('shopify_products');
	}
}
