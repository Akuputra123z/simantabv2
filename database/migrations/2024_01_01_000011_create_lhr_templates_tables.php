<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lhr_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_dokumen_reviu_id')
                ->nullable()
                ->constrained('jenis_dokumen_reviu')
                ->nullOnDelete();
            $table->string('jenis_laporan', 15)->default('LHR');
            $table->unsignedSmallInteger('versi')->default(1);
            $table->string('status', 15)->default('draft');
            $table->date('berlaku_mulai')->nullable();
            $table->string('dasar_perubahan', 255)->nullable();
            $table->timestamps();

            $table->unique(['jenis_laporan', 'jenis_dokumen_reviu_id', 'versi'], 'lhr_tpl_unique_versi');
        });

        Schema::create('lhr_template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_template_id')
                ->constrained('lhr_templates')
                ->cascadeOnDelete();
            $table->string('kunci', 50);
            $table->string('judul', 255);
            $table->mediumText('isi_default')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('wajib')->default(true);
            $table->timestamps();
        });

        Schema::create('lhr_template_pengujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_template_id')
                ->constrained('lhr_templates')
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('lhr_template_pengujians')
                ->nullOnDelete();
            $table->string('kode', 30);
            $table->string('judul', 255);
            $table->string('grup', 20)->default('substansi');
            $table->unsignedInteger('urutan')->default(1);
            $table->json('opsi_simpulan')->nullable();
            $table->text('teks_simpulan_default')->nullable();
            $table->timestamps();
        });

        Schema::create('lhr_template_dasar_hukums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_template_id')
                ->constrained('lhr_templates')
                ->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->text('teks');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lhr_template_dasar_hukums');
        Schema::dropIfExists('lhr_template_pengujians');
        Schema::dropIfExists('lhr_template_sections');
        Schema::dropIfExists('lhr_templates');
    }
};
