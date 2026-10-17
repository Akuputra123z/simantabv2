<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->string('pengendali_teknis', 150)->nullable()->after('anggaran_disetujui');
        });
    }

    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropColumn('pengendali_teknis');
        });
    }
};
