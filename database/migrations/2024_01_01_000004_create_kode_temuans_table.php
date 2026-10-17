<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_temuans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique()->comment('e.g. 1.01.05');
            $table->string('kode_numerik', 20);
            $table->unsignedTinyInteger('kel')->comment('1=Ketidakpatuhan | 2=SPI | 3=3E');
            $table->unsignedTinyInteger('sub_kel');
            $table->unsignedTinyInteger('jenis');
            $table->string('kelompok', 150);
            $table->string('sub_kelompok', 150);
            $table->text('deskripsi');
            $table->json('alternatif_rekom')->nullable()->comment('Array kode_numerik rekomendasi relevan, e.g. [1,5,9,11]');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kel', 'sub_kel', 'jenis'], 'kode_temuans_kel_sub_kel_jenis_index');
            $table->index(['kode_numerik', 'deleted_at'], 'kode_temuans_kode_numerik_deleted_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_temuans');
    }
};
