<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_dokumen_reviu', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->string('gaya_penomoran_bagian', 10)->default('alfabet');
            $table->string('tata_letak_hasil', 20)->default('tabel_dua_kolom');
            $table->unsignedSmallInteger('sla_tindak_lanjut_hari')->default(30);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('nomor_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 30);
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('nomor_terakhir')->default(0);
            $table->timestamps();

            $table->unique(['jenis', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_sequences');
        Schema::dropIfExists('jenis_dokumen_reviu');
    }
};
