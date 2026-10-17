<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->text('keterangan_pendukung_opd')->nullable()->after('catatan_verifikasi');
            $table->foreignId('upload_opd_oleh')
                ->nullable()
                ->after('keterangan_pendukung_opd')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->dropForeign(['upload_opd_oleh']);
            $table->dropColumn(['keterangan_pendukung_opd', 'upload_opd_oleh']);
        });
    }
};
