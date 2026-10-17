<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_diperiksas', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->nullable()->comment('SKPD | Sekolah | Puskesmas | Desa | BLUD');
            $table->string('nama_kecamatan', 100)->nullable();
            $table->string('nama_unit', 200)->index();
            $table->string('alamat', 500)->nullable()->comment('Alamat lengkap unit diperiksa');
            $table->string('telepon', 20)->nullable()->comment('Nomor telepon/WA unit');
            $table->text('keterangan')->nullable()->comment('Catatan tambahan tentang unit');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kategori', 'nama_kecamatan'], 'unit_kategori_kec_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_diperiksas');
    }
};
