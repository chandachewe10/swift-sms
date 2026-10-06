<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_delivery_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('sender_id');
            $table->text('message');
            $table->json('contacts');
            $table->unsignedInteger('failed_count');
            $table->text('provider_response')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('company_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_delivery_queue');
    }
};
