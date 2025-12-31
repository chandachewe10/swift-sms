<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_reference');
            $table->unsignedBigInteger('company_id');
            $table->string('reference');
            $table->string('currency');
            $table->string('customer_wallet');
            $table->decimal('amount', 10, 2);
            $table->decimal('fee_amount', 10, 2);
            $table->decimal('percentage', 10, 2);
            $table->decimal('transaction_amount', 10, 2);
            $table->foreign('company_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
