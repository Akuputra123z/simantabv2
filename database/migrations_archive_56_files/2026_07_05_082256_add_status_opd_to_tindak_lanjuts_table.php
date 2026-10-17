<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->string('status_opd', 20)->nullable()->after('upload_opd_oleh');
            $table->datetime('dikirim_pada')->nullable()->after('status_opd');
            $table->index('status_opd');
        });
    }

    public function down(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->dropIndex(['status_opd']);
            $table->dropColumn(['status_opd', 'dikirim_pada']);
        });
    }
};
