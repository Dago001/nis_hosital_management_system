<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Returning/walk-in patients booking via the assistant may not have an email
     * on file, so email should be optional on an appointment request.
     */
    public function up(): void
    {
        Schema::table('appointment_requests', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('appointment_requests', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
