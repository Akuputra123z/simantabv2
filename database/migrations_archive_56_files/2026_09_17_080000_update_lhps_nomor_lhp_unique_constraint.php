<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah constraint unik nomor_lhp menjadi kombinasi (nomor_lhp, unit_diperiksa_id)
     * agar 1 Nomor LHP resmi (seperti BPK/BPKP/ITPROV) dapat digunakan untuk mencatat
     * lembar pemeriksaan mandiri per Unit Kerja.
     */
    public function up(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            // Drop index unik lama pada nomor_lhp jika ada
            $table->dropUnique('lhps_nomor_lhp_unique');

            // Tambahkan index unik gabungan (nomor_lhp + unit_diperiksa_id)
            $table->unique(['nomor_lhp', 'unit_diperiksa_id'], 'lhps_nomor_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            $table->dropUnique('lhps_nomor_unit_unique');
            $table->unique('nomor_lhp', 'lhps_nomor_lhp_unique');
        });
    }
};
