<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temuan_id')
                ->nullable()
                ->constrained('temuans')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('lhr_catatan_id')->nullable();
            $table->foreignId('kode_rekomendasi_id')
                ->nullable()
                ->constrained('kode_rekomendasis')
                ->nullOnDelete();
            $table->text('uraian_rekom');
            $table->decimal('nilai_rekom', 18, 2)->default(0.00)->comment('Nilai rekomendasi auditor');
            $table->decimal('nilai_tl_selesai', 18, 2)->default(0.00)->comment('Cache SUM nilai_bayar WHERE status=diterima');
            $table->decimal('nilai_sisa', 18, 2)->default(0.00)->comment('nilai_rekom - nilai_tl_selesai');
            $table->date('batas_waktu')->nullable()->index()->comment('Deadline tindak lanjut');
            $table->string('status', 30)->default('belum_ditindaklanjuti')->index()->comment('belum_ditindaklanjuti | dalam_proses | selesai');
            $table->string('status_tl', 30)->default('belum_selesai')->index();
            $table->text('alasan_tidak_dapat')->nullable();
            $table->string('pihak_terkait', 150)->nullable();
            $table->text('kendala')->nullable();
            $table->string('jenis_rekomendasi', 255)->default('uang');
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

            $table->index('temuan_id');
            $table->index(['temuan_id', 'status', 'deleted_at'], 'rekom_temuan_status_deleted_idx');
            $table->index(['status', 'batas_waktu'], 'rekom_status_deadline_idx');
            $table->index(['temuan_id', 'nilai_rekom'], 'rekom_temuan_nilai_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
