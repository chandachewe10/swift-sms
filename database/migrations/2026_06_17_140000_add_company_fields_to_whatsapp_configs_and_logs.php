<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_configs', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('phone_number_id');
            $table->string('business_id')->nullable()->after('business_account_id');
        });

        Schema::create('whatsapp_embedded_signup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('step');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status')->default('info');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_embedded_signup_logs');

        Schema::table('whatsapp_configs', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'business_id']);
        });
    }
};
