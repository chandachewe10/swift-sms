<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_pending_onboardings', function (Blueprint $table) {
            $table->id();
            // Nullable so that webhook-first scenario can create a stub without a known user
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Meta Business Portfolio ID (owner_business_id in webhook, business_id in frontend callback)
            $table->string('meta_business_id')->nullable()->index();
            $table->string('waba_id')->nullable();
            $table->string('app_id');
            // pending: initiated, webhook_received: webhook arrived before frontend, completed: fully onboarded
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_pending_onboardings');
    }
};
