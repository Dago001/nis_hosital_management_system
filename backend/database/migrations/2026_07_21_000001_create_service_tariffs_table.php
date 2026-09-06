<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('other'); // consultation, procedure, lab, radiology, bed, registration, other
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('category');
        });

        // Seed default tariffs (idempotent-ish for fresh installs).
        $now = now();
        DB::table('service_tariffs')->insert([
            ['code' => 'CONSULT_GOPD', 'name' => 'General Outpatient Consultation', 'category' => 'consultation', 'price' => 2000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'REG_NEW', 'name' => 'New Patient Registration Fee', 'category' => 'registration', 'price' => 5000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'BED_DAY_GENERAL', 'name' => 'General Ward Bed (per day)', 'category' => 'bed', 'price' => 3000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'BED_DAY_ICU', 'name' => 'ICU Bed (per day)', 'category' => 'bed', 'price' => 15000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'LAB_FBC', 'name' => 'Full Blood Count', 'category' => 'lab', 'price' => 3500, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'LAB_MP', 'name' => 'Malaria Parasite', 'category' => 'lab', 'price' => 1500, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'RAD_XRAY', 'name' => 'X-Ray (per region)', 'category' => 'radiology', 'price' => 8000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'RAD_USS', 'name' => 'Ultrasound Scan', 'category' => 'radiology', 'price' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tariffs');
    }
};
