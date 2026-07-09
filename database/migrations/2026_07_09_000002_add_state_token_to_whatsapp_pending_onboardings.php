<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_pending_onboardings', function (Blueprint $table) {
            $table->string('state_token')->nullable()->unique()->after('app_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_pending_onboardings', function (Blueprint $table) {
            $table->dropColumn('state_token');
        });
    }
};
