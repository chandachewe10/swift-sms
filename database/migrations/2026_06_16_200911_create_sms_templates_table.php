<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');          // user_id of the owning company
            $table->string('name');                // e.g. "Loan Reminder"
            $table->text('body');                  // e.g. "Dear {name}, your balance is K{amount}."
            $table->string('category')->nullable();// e.g. "Finance", "Marketing", "OTP"
            $table->timestamps();

            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
