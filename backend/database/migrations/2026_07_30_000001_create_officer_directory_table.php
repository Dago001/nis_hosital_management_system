<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Local stand-in for the NIS ID Card Portal / personnel directory.
 *
 * When an external portal API is configured (NIS_IDCARD_PORTAL_URL), the
 * officer lookup calls it directly. When it is not — air-gapped / on-premise
 * deployments, or before the integration is provisioned — the lookup reads
 * from this table instead, so officer verification and auto-population keep
 * working. Records here can be imported from an official NIS personnel export.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_directory', function (Blueprint $table) {
            $table->id();
            $table->string('service_number')->unique();
            $table->string('rank')->nullable();
            $table->string('command')->nullable(); // formation / command / posting
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('nin')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('state')->nullable();
            $table->string('lga')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('status')->default('active'); // active, suspended, retired
            $table->timestamps();

            $table->index('service_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_directory');
    }
};
