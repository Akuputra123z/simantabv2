<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus duplikat (audit_assignment_id, unit_diperiksa_id) — keep record terbaru
        $duplicates = DB::table('lhps')
            ->select('audit_assignment_id', 'unit_diperiksa_id', DB::raw('MAX(id) as keep_id'))
            ->whereNotNull('unit_diperiksa_id')
            ->groupBy('audit_assignment_id', 'unit_diperiksa_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('lhps')
                ->where('audit_assignment_id', $dup->audit_assignment_id)
                ->where('unit_diperiksa_id', $dup->unit_diperiksa_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('lhps', function (Blueprint $table) {
            $table->unique(['audit_assignment_id', 'unit_diperiksa_id'], 'lhps_assignment_unit_unique');
        });
    }

    public function down(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            $table->dropUnique('lhps_assignment_unit_unique');
        });
    }
};
