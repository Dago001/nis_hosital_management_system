<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->string('type', 40)->default('hospital'); // hospital, clinic, health_post
            $table->string('state')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the default headquarters facility (id = 1) so existing records
        // can be attributed to it.
        DB::table('facilities')->insert([
            'name' => 'NIS Medical Services HQ',
            'code' => 'NIS-HQ',
            'type' => 'hospital',
            'state' => 'FCT Abuja',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['patients', 'visits', 'admissions', 'appointments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('facility_id')->nullable()->after('id')->constrained('facilities')->nullOnDelete();
            });
            // Backfill existing rows to the default facility.
            DB::table($tableName)->whereNull('facility_id')->update(['facility_id' => 1]);
        }
    }

    public function down(): void
    {
        foreach (['patients', 'visits', 'admissions', 'appointments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('facility_id');
            });
        }
        Schema::dropIfExists('facilities');
    }
};
