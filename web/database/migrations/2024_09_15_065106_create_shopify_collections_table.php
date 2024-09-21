<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('shopify_session_id');
            $table->bigInteger('shopify_collection_id');
            $table->string('handle');
            $table->string('title');
            $table->string('updated_at_shopify');
            $table->string('image_src');
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->timestamps();
            // $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_collections');
    }
}
