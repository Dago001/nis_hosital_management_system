<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('immigration_service_number')->nullable(); // Optional Hospital Code lookup
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_requests');
    }
};
