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
        Schema::create('audit_program_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_program_id')->constrained('audit_programs')->cascadeOnDelete();
            $table->string('nama_detail_program', 255); // Program 1, Program 2, dst
            $table->string('jenis_kegiatan', 100)->index();
            $table->string('objek_pengawasan', 255)->nullable();
            $table->text('ruang_lingkup')->nullable();
           $table->text('tujuan')->nullable();
            $table->string('personil', 100)->index();
            $table->decimal('anggaran', 15, 2)->default(0);
            $table->string('tingkat_resiko', 20);
            $table->string('laporan_akhir', 20)->nullable();
            $table->string('jadwal', 20)->nullable();
            $table->string('tim', 20)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
