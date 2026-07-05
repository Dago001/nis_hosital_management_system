<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('code')->unique();
            $table->string('category')->nullable(); // e.g., Antibiotics, Painkillers
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->integer('quantity_in_stock');
            $table->integer('reorder_level')->default(10);
            $table->decimal('price_per_unit', 10, 2);
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade'); // Prescribing doctor
            $table->string('status')->default('pending'); // pending, dispensed, cancelled
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->string('drug_name');
            $table->string('dosage'); // e.g., 500mg
            $table->string('frequency'); // e.g., Twice daily
            $table->integer('duration_days');
            $table->integer('quantity_prescribed');
            $table->integer('quantity_dispensed')->default(0);
            $table->text('instructions')->nullable();
            $table->string('status')->default('pending'); // pending, dispensed
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('visit_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->string('status')->default('unpaid'); // unpaid, partially_paid, paid, refunded
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('item_name'); // Consultation Fee, Paracetamol, Blood Test, etc.
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // Cash, POS, Bank Transfer, Insurance
            $table->string('transaction_reference')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // login, logout, create_patient, update_vitals, etc.
            $table->string('auditable_type')->nullable(); // Model name
            $table->unsignedBigInteger('auditable_id')->nullable(); // Model ID
            $table->json('payload')->nullable(); // Request parameters or diff details
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('pharmacy_items');
    }
};
