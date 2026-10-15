<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('lhps', 'uuid')) {
            Schema::table('lhps', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });

            // Isi nilai uuid untuk data yang sudah ada
            $existingLhps = DB::table('lhps')->whereNull('uuid')->select('id')->get();
            foreach ($existingLhps as $row) {
                DB::table('lhps')->where('id', $row->id)->update([
                    'uuid' => (string) Str::uuid(),
                ]);
            }

            Schema::table('lhps', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lhps', 'uuid')) {
            Schema::table('lhps', function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};
