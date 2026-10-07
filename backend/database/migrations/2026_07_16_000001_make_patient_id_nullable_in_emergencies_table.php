<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Emergency walk-ins frequently arrive unidentified (no registered patient
     * record yet), and the EmergencyController already treats patient_id as
     * nullable. Align the schema so such cases can be recorded.
     */
    public function up(): void
    {
        Schema::table('emergencies', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('emergencies', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable(false)->change();
        });
    }
};
