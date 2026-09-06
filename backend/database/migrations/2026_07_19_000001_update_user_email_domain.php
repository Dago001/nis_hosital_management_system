<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate all staff/user login emails from the legacy @nishms.gov.ng domain
     * to the official @immigration.gov.ng domain.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'like', '%@nishms.gov.ng')
            ->update([
                'email' => DB::raw("REPLACE(email, '@nishms.gov.ng', '@immigration.gov.ng')"),
            ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'like', '%@immigration.gov.ng')
            ->update([
                'email' => DB::raw("REPLACE(email, '@immigration.gov.ng', '@nishms.gov.ng')"),
            ]);
    }
};
