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
            $table->unsignedBigInteger('session_id');
            $table->string('shopify_session_id');
            $table->string('shopify_product_id')->nullable();
            $table->string('shopify_product_url')->nullable();
            $table->longText('descriptionHtml')->nullable();
            $table->longText('handle')->nullable();
            $table->string('title');
            $table->string('status')->nullable();
            $table->string('tags')->nullable();
            $table->string('product_type')->nullable();
            $table->string('vendor')->nullable();
            $table->string('image_src')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('upload_status')->default(0)->comment('0 = Not Uploaded, 1 = Success, 2 = Error');
            $table->boolean('isVariable')->nullable();
            $table->timestamps();
			// $table->foreign(columns: 'profile_id')->references('id')->on('profiles')->onDelete('set null');
            $table->foreign('shopify_session_id')->references('session_id')->on('sessions')->onDelete('cascade');
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
