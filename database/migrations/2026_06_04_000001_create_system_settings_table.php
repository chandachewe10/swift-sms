<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // Seed defaults
        DB::table('system_settings')->insert([
            ['key' => 'sms_provider',     'value' => 'zamtel', 'label' => 'Active SMS Provider', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'mocean_api_token', 'value' => null,     'label' => 'Mocean API Token',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'mocean_sender_id', 'value' => null,     'label' => 'Mocean Default Sender ID', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
