<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_subscribed')->default(false)->after('whatsapp_subscribed');
            $table->unsignedSmallInteger('whatsapp_credits')->default(10)->after('email_subscribed');
            $table->unsignedSmallInteger('email_credits')->default(10)->after('whatsapp_credits');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_subscribed', 'whatsapp_credits', 'email_credits']);
        });
    }
};
