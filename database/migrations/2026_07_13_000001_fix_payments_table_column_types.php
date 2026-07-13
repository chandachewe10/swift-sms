<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // merchant_reference and reference were created NOT NULL with no default —
            // they are not always available from every payment channel, so make them nullable.
            $table->string('merchant_reference')->nullable()->default(null)->change();
            $table->string('reference')->nullable()->default(null)->change();

            // customer_wallet is not present for card payments; make it nullable.
            $table->string('customer_wallet')->nullable()->default(null)->change();

            // fee_amount and percentage are not always returned by the gateway.
            $table->decimal('fee_amount', 10, 2)->nullable()->default(null)->change();
            $table->decimal('percentage', 10, 2)->nullable()->default(null)->change();

            // messages was added as decimal(10,2) but every controller stores a human-readable
            // description string (e.g. "WhatsApp Business subscription — K500/month").
            // Storing a string in a decimal column causes a SQL error in strict-mode MySQL/MariaDB
            // and is the primary cause of the 500 response on WhatsApp payment completion.
            $table->string('messages', 500)->nullable()->default(null)->change();

            // depositId is UNIQUE but can be empty when the gateway does not return an id.
            // Make it nullable so that an absent id does not collide with a stored empty string.
            $table->string('depositId')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('merchant_reference')->nullable(false)->change();
            $table->string('reference')->nullable(false)->change();
            $table->string('customer_wallet')->nullable(false)->change();
            $table->decimal('fee_amount', 10, 2)->nullable(false)->change();
            $table->decimal('percentage', 10, 2)->nullable(false)->change();
            $table->decimal('messages', 10, 2)->nullable(false)->change();
            $table->string('depositId')->nullable(false)->change();
        });
    }
};
