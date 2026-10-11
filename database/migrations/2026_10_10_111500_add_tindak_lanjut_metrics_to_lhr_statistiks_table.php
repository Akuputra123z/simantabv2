<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lhr_statistiks', function (Blueprint $table) {
            $table->unsignedInteger('belum')->default(0)->after('selesai');
            $table->unsignedInteger('proses')->default(0)->after('belum');
            $table->unsignedInteger('dikirim_opd')->default(0)->after('proses');
            $table->unsignedInteger('draft_opd')->default(0)->after('dikirim_opd');
            $table->unsignedInteger('ditolak_opd')->default(0)->after('draft_opd');
            $table->decimal('persen_tl', 5, 2)->default(0.00)->after('persen');
        });
    }

    public function down(): void
    {
        Schema::table('lhr_statistiks', function (Blueprint $table) {
            $table->dropColumn([
                'belum',
                'proses',
                'dikirim_opd',
                'draft_opd',
                'ditolak_opd',
                'persen_tl',
            ]);
        });
    }
};
