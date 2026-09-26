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
        Schema::create('nomor_formats', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 30)->unique();
            $table->string('pola', 100);
            $table->string('keterangan', 255)->nullable();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomor_sequences');
        Schema::dropIfExists('nomor_formats');
    }
};
