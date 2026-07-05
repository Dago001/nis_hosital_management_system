<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique(); // e.g., GOPD, ER, PHARM, LAB
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('service_number')->unique()->nullable(); // NIS Service Number
            $table->string('rank')->nullable(); // e.g., Inspector, Assistant Superintendent
            $table->string('professional_license')->nullable();
            $table->string('phone');
            $table->string('specialization')->nullable();
            $table->string('status')->default('active'); // active, suspended, retired
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('service_number');
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender'); // Male, Female, Other
            $table->date('date_of_birth');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('immigration_service_number')->unique()->nullable(); // For NIS officers or dependants
            $table->string('nin', 11)->unique()->nullable(); // 11-digit NIN
            $table->string('passport_photograph_path')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->text('barcode_data')->nullable();
            $table->text('allergies')->nullable();
            $table->string('blood_group', 5)->nullable(); // A+, O-, etc.
            $table->string('genotype', 5)->nullable(); // AA, AS, SS, AC, SC
            $table->string('disability')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('immigration_service_number');
            $table->index('nin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('departments');
    }
};
