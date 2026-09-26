<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->foreignId('temuan_id')->nullable()->change();
            $table->foreignId('lhr_catatan_id')
                ->nullable()
                ->after('temuan_id')
                ->constrained('lhr_catatans')
                ->nullOnDelete();

            $table->string('status_tl', 30)
                ->default('belum_selesai')
                ->after('status')
                ->index();
            $table->text('alasan_tidak_dapat')->nullable()->after('status_tl');
            $table->string('pihak_terkait', 150)->nullable()->after('alasan_tidak_dapat');
            $table->text('kendala')->nullable()->after('pihak_terkait');
        });

        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->foreignId('lhr_asal_id')
                ->nullable()
                ->after('audit_program_detail_id')
                ->constrained('lhrs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropForeign(['lhr_asal_id']);
            $table->dropColumn('lhr_asal_id');
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropForeign(['lhr_catatan_id']);
            $table->dropColumn([
                'lhr_catatan_id',
                'status_tl',
                'alasan_tidak_dapat',
                'pihak_terkait',
                'kendala',
            ]);
            $table->foreignId('temuan_id')->nullable(false)->change();
        });
    }
};
