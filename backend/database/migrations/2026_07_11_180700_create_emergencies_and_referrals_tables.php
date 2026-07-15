<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Emergency Cases Table
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null'); // Attending doctor
            $table->foreignId('triaged_by')->nullable()->constrained('staff')->onDelete('set null'); // Nurse who triaged
            $table->string('triage_level')->default('yellow'); // red, yellow, green, black
            $table->string('chief_complaint');
            $table->text('presenting_symptoms')->nullable();
            $table->string('vitals_bp')->nullable();
            $table->decimal('vitals_temp', 4, 1)->nullable();
            $table->integer('vitals_pulse')->nullable();
            $table->integer('vitals_spo2')->nullable(); // Oxygen saturation
            $table->integer('vitals_gcs')->nullable(); // Glasgow Coma Scale
            $table->string('mode_of_arrival')->default('walk-in'); // walk-in, ambulance, police, referred
            $table->string('status')->default('waiting'); // waiting, in_treatment, admitted, discharged, deceased, transferred
            $table->text('treatment_notes')->nullable();
            $table->text('disposition_notes')->nullable();
            $table->timestamp('arrived_at')->useCurrent();
            $table->timestamp('triaged_at')->nullable();
            $table->timestamp('treatment_started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'triage_level']);
            $table->index('arrived_at');
        });

        // Referrals Table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('visit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('emergency_id')->nullable()->constrained('emergencies')->onDelete('set null');
            $table->foreignId('referring_doctor_id')->constrained('staff')->onDelete('cascade');
            $table->string('referring_facility')->default('NIS Medical Services Portal (NIS-MSP)');
            $table->string('receiving_facility');
            $table->string('receiving_doctor')->nullable();
            $table->string('referral_type')->default('outgoing'); // outgoing, incoming
            $table->string('priority')->default('routine'); // routine, urgent, emergency
            $table->string('reason');
            $table->text('clinical_summary');
            $table->text('special_instructions')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, in_transit, arrived, completed, cancelled
            $table->timestamp('referred_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('referred_at');
        });

        // Add extra columns to admissions for enhanced IPD
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('admission_type')->default('elective')->after('status'); // elective, emergency, transfer
            $table->text('diagnosis_on_admission')->nullable()->after('admission_type');
            $table->text('ward_notes')->nullable()->after('diagnosis_on_admission');
            $table->foreignId('emergency_id')->nullable()->after('ward_notes')->constrained('emergencies')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['emergency_id']);
            $table->dropColumn(['admission_type', 'diagnosis_on_admission', 'ward_notes', 'emergency_id']);
        });
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('emergencies');
    }
};
