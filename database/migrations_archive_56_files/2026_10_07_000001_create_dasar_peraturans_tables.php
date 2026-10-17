<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dasar_peraturans', function (Blueprint $table) {
            $table->id();
            $table->text('nama');
            $table->string('nomor', 150)->nullable();
            $table->string('tahun', 10)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('audit_assignment_dasar_peraturan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->foreignId('dasar_peraturan_id')->constrained('dasar_peraturans')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['audit_assignment_id', 'dasar_peraturan_id'], 'aa_dp_unique');
        });

        // Seed 5 default peraturan
        $defaults = [
            [
                'nama' => 'Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah Kabupaten Rembang.',
                'nomor' => 'Nomor 6 Tahun 2021',
                'tahun' => '2021',
                'urutan' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Peraturan Daerah Kabupaten Rembang Nomor 2 Tahun 2024 tentang Anggaran Pendapatan dan Belanja Daerah Kabupaten Rembang Tahun Anggaran 2025.',
                'nomor' => 'Nomor 2 Tahun 2024',
                'tahun' => '2024',
                'urutan' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Kabupaten Rembang.',
                'nomor' => 'Nomor 54 Tahun 2021',
                'tahun' => '2021',
                'urutan' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Peraturan Bupati Rembang Nomor 2 Tahun 2024 tentang Pembinaan dan Pengawasan Perangkat Daerah dan Desa.',
                'nomor' => 'Nomor 2 Tahun 2024',
                'tahun' => '2024',
                'urutan' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Keputusan Bupati Rembang Nomor 100.3.3.2/0132/2025 tanggal 9 Januari 2025 tentang Program Kerja Pengawasan Tahunan Berbasis Resiko Inspektorat Kabupaten Rembang Tahun 2025.',
                'nomor' => 'Nomor 100.3.3.2/0132/2025',
                'tahun' => '2025',
                'urutan' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('dasar_peraturans')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_assignment_dasar_peraturan');
        Schema::dropIfExists('dasar_peraturans');
    }
};
