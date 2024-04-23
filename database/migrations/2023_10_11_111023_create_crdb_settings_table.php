<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nbc_api_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('access_name', 11)->nullable();
            $table->string('account_name')->unique();
            $table->string('msisdn')->nullable();
            $table->string('biller_code')->nullable();
            $table->string('base_url')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('grant_type')->nullable();
            $table->string('environment')->nullable();
            $table->text('access_token')->nullable();
            $table->string('brand_id')->nullable();
            $table->string('brand_pin')->nullable();
            $table->string('language')->nullable();
            $table->string('account_to_wallet_type')->nullable();
            $table->string('wallet_to_account_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nbc_api_settings');
    }
};
