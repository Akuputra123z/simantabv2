<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk forUser scope di Lhp & TindakLanjut (ketua_tim_id sering di-join)
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->index('ketua_tim_id', 'idx_assignments_ketua_tim');
            $table->index('status', 'idx_assignments_status');
        });

        // Index untuk orderBy tim + whereNotNull di LaporanController
        Schema::table('audit_program_details', function (Blueprint $table) {
            $table->index('tim', 'idx_detail_tim');
        });

        // Composite index untuk pivot audit_assignment_members (sering di-join user_id)
        Schema::table('audit_assignment_members', function (Blueprint $table) {
            $table->index('user_id', 'idx_members_user_id');
        });

        // Index untuk tindak_lanjut filter (status_verifikasi, tanggal_jatuh_tempo)
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->index('status_verifikasi', 'idx_tl_status_verifikasi');
            $table->index('tanggal_jatuh_tempo', 'idx_tl_jatuh_tempo');
        });

        // Index untuk temuan LIKE search (kondisi)
        Schema::table('temuans', function (Blueprint $table) {
            $table->index('lhp_id', 'idx_temuans_lhp_id');
            $table->index('status_tl', 'idx_temuans_status_tl');
        });

        // Index untuk pivot model_has_roles (sering di-join)
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->index('model_id', 'idx_model_has_roles_model_id');
        });
    }

    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_ketua_tim');
            $table->dropIndex('idx_assignments_status');
        });
        Schema::table('audit_program_details', function (Blueprint $table) {
            $table->dropIndex('idx_detail_tim');
        });
        Schema::table('audit_assignment_members', function (Blueprint $table) {
            $table->dropIndex('idx_members_user_id');
        });
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->dropIndex('idx_tl_status_verifikasi');
            $table->dropIndex('idx_tl_jatuh_tempo');
        });
        Schema::table('temuans', function (Blueprint $table) {
            $table->dropIndex('idx_temuans_lhp_id');
            $table->dropIndex('idx_temuans_status_tl');
        });
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex('idx_model_has_roles_model_id');
        });
    }
};
