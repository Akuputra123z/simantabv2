<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lhps', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke penugasan audit (SIMANTAP logic)
            $table->foreignId('audit_assignment_id')
                  ->constrained('audit_assignments')
                  ->cascadeOnDelete();
            $table->foreignId('unit_diperiksa_id')
              ->nullable()
              ->constrained('unit_diperiksas')
              ->nullOnDelete();

            // Identitas LHP
            $table->string('nomor_lhp', 100)->unique()
                  ->comment('Format: 700/009/001P/PKPT.2023');
            $table->date('tanggal_lhp'); // Sebaiknya jangan nullable agar data statistik valid
            
            $table->text('catatan_umum')->nullable()
                  ->comment('Ringkasan/simpulan audit');

            // Flow Status
            $table->string('status', 30)->default('draft')
                  ->comment('draft, final, ditandatangani, dibatalkan');
            
            // Audit Trail Pembatalan (Logika khusus jika LHP dianulir)
            $table->text('status_batal_keterangan')->nullable();
            $table->foreignId('status_batal_user_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('status_batal_at')->nullable();

            // Ownership & Timestamps
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
 
            // Indexing untuk performa Filament & Filtering
            $table->index('status');
            $table->index('tanggal_lhp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lhps');
    }
};