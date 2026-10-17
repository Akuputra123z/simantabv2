<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lhrs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('audit_assignment_id')
                ->constrained('audit_assignments')
                ->restrictOnDelete();
            $table->foreignId('unit_diperiksa_id')
                ->constrained('unit_diperiksas')
                ->restrictOnDelete();
            $table->foreignId('jenis_dokumen_reviu_id')
                ->constrained('jenis_dokumen_reviu')
                ->restrictOnDelete();
            $table->foreignId('lhr_template_id')
                ->constrained('lhr_templates')
                ->restrictOnDelete();
            $table->unsignedSmallInteger('tahun_anggaran');
            $table->unsignedSmallInteger('objek_tahun_awal')->nullable();
            $table->unsignedSmallInteger('objek_tahun_akhir')->nullable();
            $table->string('permohonan_nomor', 100)->nullable();
            $table->date('permohonan_tanggal')->nullable();
            $table->unsignedSmallInteger('tahun_nomor')->nullable();
            $table->unsignedInteger('nomor_urut')->nullable();
            $table->string('sufiks', 10)->default('');
            $table->string('nomor_lhr', 64)->nullable()->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->date('tanggal_lhr')->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedSmallInteger('versi')->default(1);
            $table->foreignId('revisi_dari_id')
                ->nullable()
                ->constrained('lhrs')
                ->nullOnDelete();
            $table->unsignedInteger('row_version')->default(1);
            $table->foreignId('ditandatangani_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->dateTime('ditandatangani_pada')->nullable();
            $table->string('dokumen_final_path', 255)->nullable();
            $table->char('dokumen_final_sha256', 64)->nullable();
            $table->json('snapshot_terbit')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['audit_assignment_id', 'unit_diperiksa_id', 'jenis_dokumen_reviu_id', 'versi'],
                'lhr_unique_assignment_unit_jenis_versi'
            );
            $table->index(
                ['unit_diperiksa_id', 'tahun_anggaran', 'jenis_dokumen_reviu_id'],
                'lhr_lookup_unit_tahun_jenis'
            );
            $table->index(['status', 'updated_at'], 'lhr_status_updated_idx');
        });

        Schema::create('lhr_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_id')
                ->constrained('lhrs')
                ->cascadeOnDelete();
            $table->string('kunci', 50);
            $table->string('judul', 255);
            $table->mediumText('isi')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->unique(['lhr_id', 'kunci']);
        });

        Schema::create('lhr_kategori_catatans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('lhr_pengujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_id')
                ->constrained('lhrs')
                ->cascadeOnDelete();
            $table->foreignId('template_pengujian_id')
                ->nullable()
                ->constrained('lhr_template_pengujians')
                ->nullOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('lhr_pengujians')
                ->nullOnDelete();
            $table->string('kode', 30);
            $table->string('judul', 255);
            $table->string('grup', 20)->default('substansi');
            $table->unsignedInteger('urutan')->default(1);
            $table->string('status_simpulan', 30)->default('belum_dinilai');
            $table->text('teks_simpulan')->nullable();
            $table->foreignId('ditugaskan_ke')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->dateTime('selesai_pada')->nullable();
            $table->timestamps();

            $table->index(['lhr_id', 'urutan']);
            $table->index(['lhr_id', 'status_simpulan']);
        });

        Schema::create('lhr_catatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_pengujian_id')
                ->constrained('lhr_pengujians')
                ->cascadeOnDelete();
            $table->foreignId('kategori_id')
                ->nullable()
                ->constrained('lhr_kategori_catatans')
                ->nullOnDelete();
            $table->string('referensi_bagian', 120)->nullable();
            $table->text('isi');
            $table->unsignedInteger('urutan')->default(1);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lhr_pengujian_id', 'urutan']);
            $table->index('kategori_id');
        });

        Schema::create('lhr_dasar_hukums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_id')
                ->constrained('lhrs')
                ->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->text('teks');
            $table->timestamps();
        });

        Schema::create('lhr_statistiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhr_id')
                ->unique()
                ->constrained('lhrs')
                ->cascadeOnDelete();
            $table->unsignedInteger('total_rekomendasi')->default(0);
            $table->unsignedInteger('selesai')->default(0);
            $table->unsignedInteger('belum')->default(0);
            $table->unsignedInteger('proses')->default(0);
            $table->unsignedInteger('dikirim_opd')->default(0);
            $table->unsignedInteger('draft_opd')->default(0);
            $table->unsignedInteger('ditolak_opd')->default(0);
            $table->decimal('persen', 5, 2)->default(0.00);
            $table->decimal('persen_tl', 5, 2)->default(0.00);
            $table->dateTime('dihitung_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lhr_statistiks');
        Schema::dropIfExists('lhr_dasar_hukums');
        Schema::dropIfExists('lhr_catatans');
        Schema::dropIfExists('lhr_pengujians');
        Schema::dropIfExists('lhr_kategori_catatans');
        Schema::dropIfExists('lhr_sections');
        Schema::dropIfExists('lhrs');
    }
};
