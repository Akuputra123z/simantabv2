<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lhp_unit_diperiksa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhp_id')->constrained('lhps')->cascadeOnDelete();
            $table->foreignId('unit_diperiksa_id')->constrained('unit_diperiksas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lhp_id', 'unit_diperiksa_id'], 'lhp_unit_unique');
        });

        // Backfill data dari lhps.unit_diperiksa_id ke pivot table
        $lhps = DB::table('lhps')
            ->whereNotNull('unit_diperiksa_id')
            ->select('id', 'unit_diperiksa_id')
            ->get();

        foreach ($lhps as $lhp) {
            DB::table('lhp_unit_diperiksa')->insertOrIgnore([
                'lhp_id' => $lhp->id,
                'unit_diperiksa_id' => $lhp->unit_diperiksa_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhp_unit_diperiksa');
    }
};
