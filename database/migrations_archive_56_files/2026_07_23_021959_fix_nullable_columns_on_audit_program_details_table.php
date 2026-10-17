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
        Schema::table('audit_program_details', function (Blueprint $table) {
            $table->string('personil', 100)->nullable()->change();
            $table->string('tingkat_resiko', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_program_details', function (Blueprint $table) {
            $table->string('personil', 100)->nullable(false)->change();
            $table->string('tingkat_resiko', 50)->nullable(false)->change();
        });
    }
};
