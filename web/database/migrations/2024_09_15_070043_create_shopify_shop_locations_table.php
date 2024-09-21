<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyShopLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_shop_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id');
            $table->bigInteger('shopify_location_id')->index();
            $table->string('name')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(0);
            $table->boolean('is_active')->default(0);
            $table->boolean('is_primary')->default(0);
            $table->timestamps();

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
        Schema::dropIfExists('shopify_shop_locations');
    }
}
