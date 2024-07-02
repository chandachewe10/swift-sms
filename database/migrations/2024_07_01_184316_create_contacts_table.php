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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone1')->unique();
            $table->string('phone2')->nullable()->unique()->default(null);
            $table->string('phone3')->nullable()->unique()->default(null);
            $table->string('email')->nullable()->unique()->default(null);
            $table->string('address')->nullable();
            $table->string('company')->nullable();
            $table->string('nationality')->nullable();
            $table->foreign('company_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
