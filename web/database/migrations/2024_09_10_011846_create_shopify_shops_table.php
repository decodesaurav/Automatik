<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('name');
            $table->string('domain');
            $table->string('token');
            $table->integer('current_package_id');
            $table->boolean('is_trial')->default(1);
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('api_version')->default('2024-07');
            $table->boolean('billing_activated')->default(0);
            $table->boolean('billing_activated_on')->nullable();
            $table->string('country')->nullable();
            $table->string('currency');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('shop_owner')->nullable();
            $table->string('timezone')->nullable();
            $table->string('shopify_shop_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
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
        Schema::dropIfExists('shopify_shops');
    }
}
