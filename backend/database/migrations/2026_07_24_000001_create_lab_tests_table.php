<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category', 60)->default('General');
            $table->string('unit', 30)->nullable();
            $table->decimal('ref_low', 12, 3)->nullable();
            $table->decimal('ref_high', 12, 3)->nullable();
            $table->decimal('critical_low', 12, 3)->nullable();
            $table->decimal('critical_high', 12, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Flag computed against the reference range at result entry.
        Schema::table('lab_results', function (Blueprint $table) {
            // normal | low | high | critical_low | critical_high | abnormal
            $table->string('flag', 20)->nullable()->after('unit');
        });

        $now = now();
        $tests = [
            ['HB', 'Haemoglobin', 'Haematology', 'g/dL', 11, 16, 7, 20],
            ['WBC', 'White Cell Count', 'Haematology', 'x10^9/L', 4, 11, 1, 30],
            ['PLT', 'Platelet Count', 'Haematology', 'x10^9/L', 150, 450, 50, 1000],
            ['PCV', 'Packed Cell Volume', 'Haematology', '%', 36, 50, 20, 60],
            ['FBS', 'Fasting Blood Sugar', 'Chemistry', 'mmol/L', 3.9, 5.5, 2.2, 25],
            ['RBS', 'Random Blood Sugar', 'Chemistry', 'mmol/L', 3.9, 7.8, 2.2, 30],
            ['UREA', 'Blood Urea', 'Chemistry', 'mmol/L', 2.5, 6.7, null, 30],
            ['CREAT', 'Serum Creatinine', 'Chemistry', 'umol/L', 60, 110, null, 500],
            ['NA', 'Sodium', 'Electrolytes', 'mmol/L', 135, 145, 120, 160],
            ['K', 'Potassium', 'Electrolytes', 'mmol/L', 3.5, 5.1, 2.5, 6.5],
            ['ALT', 'Alanine Transaminase', 'Liver', 'U/L', 7, 56, null, 500],
            ['AST', 'Aspartate Transaminase', 'Liver', 'U/L', 10, 40, null, 500],
            ['TBIL', 'Total Bilirubin', 'Liver', 'umol/L', 3, 21, null, 300],
            ['MP', 'Malaria Parasite', 'Parasitology', null, null, null, null, null],
            ['HbA1c', 'Glycated Haemoglobin', 'Chemistry', '%', 4, 5.6, null, 15],
        ];

        foreach ($tests as [$code, $name, $cat, $unit, $lo, $hi, $clo, $chi]) {
            DB::table('lab_tests')->insert([
                'code' => $code, 'name' => $name, 'category' => $cat, 'unit' => $unit,
                'ref_low' => $lo, 'ref_high' => $hi, 'critical_low' => $clo, 'critical_high' => $chi,
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropColumn('flag');
        });
        Schema::dropIfExists('lab_tests');
    }
};
