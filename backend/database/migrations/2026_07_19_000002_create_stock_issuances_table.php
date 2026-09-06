<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ledger of drug/consumable stock issued from the central store (by an
     * Inventory Officer) to the pharmacy dispensary (received by a Pharmacist).
     * Each issuance tops up the dispensary's pharmacy_items stock and is kept
     * as a permanent, auditable record.
     */
    public function up(): void
    {
        Schema::create('stock_issuances', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('pharmacy_item_id')->constrained('pharmacy_items')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            // Who issued (inventory officer) and who received (pharmacist).
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('received_by_staff_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->string('received_by_name')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();

            $table->index('pharmacy_item_id');
            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_issuances');
    }
};
