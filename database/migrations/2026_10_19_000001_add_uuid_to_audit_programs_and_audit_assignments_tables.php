<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom uuid di audit_programs jika belum ada
        if (Schema::hasTable('audit_programs') && ! Schema::hasColumn('audit_programs', 'uuid')) {
            Schema::table('audit_programs', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });

            // Backfill data audit_programs yang sudah ada
            $programs = DB::table('audit_programs')->whereNull('uuid')->select('id')->get();
            foreach ($programs as $prog) {
                DB::table('audit_programs')
                    ->where('id', $prog->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        }

        // 2. Tambah kolom uuid di audit_assignments jika belum ada
        if (Schema::hasTable('audit_assignments') && ! Schema::hasColumn('audit_assignments', 'uuid')) {
            Schema::table('audit_assignments', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });

            // Backfill data audit_assignments yang sudah ada
            $assignments = DB::table('audit_assignments')->whereNull('uuid')->select('id')->get();
            foreach ($assignments as $assign) {
                DB::table('audit_assignments')
                    ->where('id', $assign->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_assignments') && Schema::hasColumn('audit_assignments', 'uuid')) {
            Schema::table('audit_assignments', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }

        if (Schema::hasTable('audit_programs') && Schema::hasColumn('audit_programs', 'uuid')) {
            Schema::table('audit_programs', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
