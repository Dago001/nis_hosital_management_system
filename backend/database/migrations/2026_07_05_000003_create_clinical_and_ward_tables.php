<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null'); // The assigned doctor
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->integer('queue_number');
            $table->string('status')->default('pending'); // pending, checked_in, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['appointment_date', 'status']);
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade'); // The consulting doctor
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            
            // Vitals
            $table->string('vitals_blood_pressure')->nullable();
            $table->decimal('vitals_temperature', 4, 1)->nullable();
            $table->integer('vitals_pulse_rate')->nullable();
            $table->integer('vitals_respiratory_rate')->nullable();
            $table->decimal('vitals_weight', 5, 2)->nullable();
            $table->decimal('vitals_height', 5, 2)->nullable();
            
            // Consultation notes
            $table->text('chief_complaint')->nullable();
            $table->text('history')->nullable();
            
            // SOAP notes
            $table->text('soap_notes_subjective')->nullable();
            $table->text('soap_notes_objective')->nullable();
            $table->text('soap_notes_assessment')->nullable();
            $table->text('soap_notes_plan')->nullable();
            
            // Diagnosis
            $table->string('diagnosis_icd10')->nullable(); // ICD-10 Code
            $table->text('diagnosis_description')->nullable();
            
            $table->text('treatment_plan')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->onDelete('cascade');
            $table->string('bed_number');
            $table->string('status')->default('available'); // available, occupied, cleaning, maintenance
            $table->timestamps();
            
            $table->unique(['ward_id', 'bed_number']);
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade'); // Admitting doctor
            $table->timestamp('admitted_at');
            $table->timestamp('discharged_at')->nullable();
            $table->text('discharge_summary')->nullable();
            $table->string('status')->default('active'); // active, discharged
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('appointments');
    }
};
