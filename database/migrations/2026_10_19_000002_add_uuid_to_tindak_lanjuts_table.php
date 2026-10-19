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
        if (Schema::hasTable('tindak_lanjuts') && ! Schema::hasColumn('tindak_lanjuts', 'uuid')) {
            Schema::table('tindak_lanjuts', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });

            // Backfill otomatis data lama
            $tls = DB::table('tindak_lanjuts')->whereNull('uuid')->select('id')->get();
            foreach ($tls as $tl) {
                DB::table('tindak_lanjuts')
                    ->where('id', $tl->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tindak_lanjuts') && Schema::hasColumn('tindak_lanjuts', 'uuid')) {
            Schema::table('tindak_lanjuts', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
