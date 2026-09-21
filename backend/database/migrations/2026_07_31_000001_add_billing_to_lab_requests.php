<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laboratory tests are billable: give the catalogue a price, and link each lab
 * request to the invoice that must be paid at the cashier before the sample is
 * processed (mirrors the existing pharmacy prescription -> invoice -> dispense
 * gate).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_tests', function (Blueprint $table) {
            if (! Schema::hasColumn('lab_tests', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('category');
            }
        });

        Schema::table('lab_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('lab_requests', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->after('staff_id')
                    ->constrained()->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_requests', function (Blueprint $table) {
            if (Schema::hasColumn('lab_requests', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
        });

        Schema::table('lab_tests', function (Blueprint $table) {
            if (Schema::hasColumn('lab_tests', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
