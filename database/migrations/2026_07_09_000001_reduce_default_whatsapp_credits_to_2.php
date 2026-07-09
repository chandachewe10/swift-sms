<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change the default for new users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('whatsapp_credits')->default(2)->change();
        });

        // Cap existing users who still have the old default of 10 and have not
        // used any credits yet (i.e. still exactly 10) down to 2.
        DB::table('users')->where('whatsapp_credits', 10)->update(['whatsapp_credits' => 2]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('whatsapp_credits')->default(10)->change();
        });
    }
};
