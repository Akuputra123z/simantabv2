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
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->string('nama_tim', 255)->nullable()->after('audit_program_detail_id');
            $table->string('jenis_pengawasan', 50)->nullable()->after('nama_tim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropColumn(['nama_tim', 'jenis_pengawasan']);
        });
    }
};
