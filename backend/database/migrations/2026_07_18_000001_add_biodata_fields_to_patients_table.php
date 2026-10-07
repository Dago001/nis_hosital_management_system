<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extended patient bio-data captured on the registration form.
     * All nullable so existing records and lean registrations remain valid.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('religion')->nullable()->after('occupation');
            $table->string('place_of_birth')->nullable()->after('religion');
            $table->string('tribe')->nullable()->after('place_of_birth');
            $table->string('city')->nullable()->after('lga');
            $table->string('next_of_kin_name')->nullable()->after('city');
            $table->string('next_of_kin_relationship')->nullable()->after('next_of_kin_name');
            $table->string('next_of_kin_address')->nullable()->after('next_of_kin_relationship');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'marital_status', 'occupation', 'religion', 'place_of_birth',
                'tribe', 'city', 'next_of_kin_name', 'next_of_kin_relationship',
                'next_of_kin_address',
            ]);
        });
    }
};
