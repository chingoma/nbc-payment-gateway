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
        Schema::create('nbc_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('date');
            $table->timestamp('transaction_date')->nullable();
            $table->timestamp('callback_date')->nullable();
            $table->string('access_name');
            $table->uuid('customer_id');
            $table->string('customer_msisdn');
            $table->string('biller_msisdn');
            $table->string('remarks', 400)->nullable();
            $table->string('amount')->nullable();
            $table->string('callback_amount')->nullable();
            $table->string('callback_status')->default('pending')->nullable();
            $table->string('callback_description', 400)->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('response_status')->nullable();
            $table->string('response_code')->nullable();
            $table->string('response_description')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('nbc_reference_id')->nullable();
            $table->string('status')->default('pending')->nullable();
            $table->string('type')->default('payment')->nullable();
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
        Schema::dropIfExists('nbc_requests');
    }
};
