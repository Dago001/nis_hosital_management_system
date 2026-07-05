<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade'); // Requesting doctor
            $table->string('test_name');
            $table->text('clinical_indication')->nullable();
            $table->string('status')->default('requested'); // requested, sample_collected, completed, cancelled
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('scientist_id')->nullable()->constrained('staff')->onDelete('set null'); // Lab scientist
            $table->string('result_value');
            $table->string('normal_range_min')->nullable();
            $table->string('normal_range_max')->nullable();
            $table->string('unit')->nullable(); // e.g. g/dL, mmol/L
            $table->string('status')->default('draft'); // draft, approved
            $table->text('remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('radiology_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade'); // Requesting doctor
            $table->string('scan_type'); // X-Ray, CT Scan, MRI, Ultrasound, ECG
            $table->string('body_part');
            $table->text('clinical_indication')->nullable();
            $table->string('status')->default('requested'); // requested, completed, cancelled
            $table->timestamps();
        });

        Schema::create('radiology_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radiology_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('radiographer_id')->nullable()->constrained('staff')->onDelete('set null'); // Radiographer
            $table->string('image_path')->nullable(); // Path to scan file
            $table->text('report_text');
            $table->string('status')->default('draft'); // draft, approved
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_results');
        Schema::dropIfExists('radiology_requests');
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('lab_requests');
    }
};
