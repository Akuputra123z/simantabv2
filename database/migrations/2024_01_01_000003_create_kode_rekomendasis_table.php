<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_rekomendasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->unsignedInteger('kode_numerik')->unique();
            $table->string('kategori', 100)->nullable()->index();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['kode_numerik', 'is_active'], 'rekom_numerik_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_rekomendasis');
    }
};
