<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ongoing ward observations / nursing vitals during an admission.
        Schema::create('admission_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->onDelete('cascade');
            $table->foreignId('recorded_by')->nullable()->constrained('staff')->onDelete('set null');
            $table->string('blood_pressure')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('pulse_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->integer('spo2')->nullable();
            $table->decimal('fluid_intake_ml', 8, 1)->nullable();
            $table->decimal('fluid_output_ml', 8, 1)->nullable();
            $table->integer('news_score')->nullable(); // early warning score
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
            $table->index('admission_id');
        });

        // Medication Administration Record (MAR).
        Schema::create('medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->onDelete('cascade');
            $table->foreignId('prescription_item_id')->nullable()->constrained('prescription_items')->onDelete('set null');
            $table->foreignId('administered_by')->nullable()->constrained('staff')->onDelete('set null');
            $table->string('drug_name');
            $table->string('dose')->nullable();
            $table->string('route')->nullable(); // Oral, IV, IM, etc.
            $table->string('status')->default('given'); // given, missed, held, refused
            $table->text('notes')->nullable();
            $table->timestamp('administered_at')->useCurrent();
            $table->timestamps();
            $table->index('admission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_administrations');
        Schema::dropIfExists('admission_observations');
    }
};
