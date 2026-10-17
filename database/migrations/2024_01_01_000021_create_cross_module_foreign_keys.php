<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->foreign('lhr_asal_id')
                ->references('id')
                ->on('lhrs')
                ->nullOnDelete();
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->foreign('lhr_catatan_id')
                ->references('id')
                ->on('lhr_catatans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropForeign(['lhr_catatan_id']);
        });

        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropForeign(['lhr_asal_id']);
        });
    }
};
