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
        Schema::create('property_payments', function (Blueprint $table) {
            $table->id();
            $table->string('agent_id');
            $table->string('property_id');
            $table->string('buyer_name');
            $table->string('buyer_phone');
            $table->string('buyer_email');
            $table->string('amount');
            $table->string('reference');
            $table->string('payment_method');
            $table->string('currency');
            $table->string('property_status');
            $table->boolean('payment_status')->default(false);
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
        Schema::dropIfExists('property_payments');
    }
};
