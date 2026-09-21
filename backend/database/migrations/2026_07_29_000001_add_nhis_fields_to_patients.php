<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->boolean('is_nhis')->default(false)->after('sponsor_service_number');
            $table->string('nhis_number', 60)->nullable()->after('is_nhis');
        });

        // Preserve current coverage: officers (service number not a plain /PAT/
        // patient code) and dependants of a sponsor were previously treated as
        // NHIS by the derived logic, so mark them accordingly.
        DB::table('patients')
            ->whereNotNull('sponsor_service_number')
            ->update(['is_nhis' => true]);

        DB::table('patients')
            ->whereNotNull('immigration_service_number')
            ->where('immigration_service_number', 'not like', '%/PAT/%')
            ->update(['is_nhis' => true]);
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['is_nhis', 'nhis_number']);
        });
    }
};
