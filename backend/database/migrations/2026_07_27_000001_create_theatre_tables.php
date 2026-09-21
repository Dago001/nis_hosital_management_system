<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theatres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surgeon_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('theatre_id')->constrained()->cascadeOnDelete();
            $table->string('procedure_name');
            $table->timestamp('scheduled_start');
            $table->timestamp('scheduled_end');
            // scheduled -> in_progress -> completed / cancelled
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['theatre_id', 'scheduled_start']);
        });

        $now = now();
        foreach ([['Main Theatre 1', 'Surgical Block A'], ['Main Theatre 2', 'Surgical Block A'], ['Day-Case Theatre', 'Outpatient Wing']] as [$n, $loc]) {
            DB::table('theatres')->insert(['name' => $n, 'location' => $loc, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surgeries');
        Schema::dropIfExists('theatres');
    }
};
