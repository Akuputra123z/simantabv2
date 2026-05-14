<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignment_unit', function (Blueprint $table) {
              $table->id();
            $table->foreignId('audit_assignment_id')
                  ->constrained('audit_assignments')
                  ->cascadeOnDelete();
            $table->foreignId('unit_diperiksa_id')
                  ->constrained('unit_diperiksas')
                  ->cascadeOnDelete();
            $table->timestamps(); // ← hanya sekali

            $table->unique(['audit_assignment_id', 'unit_diperiksa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_unit');
    }
};
