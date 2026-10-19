<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_program_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('audit_program_id')
                ->constrained('audit_programs')
                ->cascadeOnDelete();
            $table->string('nama_detail_program', 255);
            $table->string('jenis_kegiatan', 100)->index();
            $table->string('objek_pengawasan', 255)->nullable();
            $table->text('ruang_lingkup')->nullable();
            $table->text('tujuan')->nullable();
            $table->string('personil', 100)->nullable()->index();
            $table->decimal('anggaran', 15, 2)->default(0.00);
            $table->string('tingkat_resiko', 50)->nullable();
            $table->string('laporan_akhir', 50)->nullable();
            $table->string('jadwal', 100)->nullable();
            $table->string('tim', 255)->nullable()->index();
            $table->string('status', 50)->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_program_details');
    }
};
