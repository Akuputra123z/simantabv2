<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_program_detail_id')
                  ->constrained('audit_program_details')
                  ->cascadeOnDelete();
            $table->foreignId('ketua_tim_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->string('nomor_surat', 255)->unique();
            $table->string('nama_tim', 255)->nullable();
            $table->string('jenis_pengawasan', 255)->index();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['audit_program_detail_id', 'status'], 'idx_assignments_detail_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_assignments');
    }
};