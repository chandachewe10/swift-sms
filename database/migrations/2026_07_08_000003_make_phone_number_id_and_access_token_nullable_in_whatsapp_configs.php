<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_configs', function (Blueprint $table) {
            $table->string('phone_number_id')->nullable()->change();
            $table->text('access_token')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_configs', function (Blueprint $table) {
            $table->string('phone_number_id')->nullable(false)->change();
            $table->text('access_token')->nullable(false)->change();
        });
    }
};
