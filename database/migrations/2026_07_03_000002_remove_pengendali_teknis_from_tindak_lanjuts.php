<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->dropColumn('pengendali_teknis');
        });
    }

    public function down(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->string('pengendali_teknis', 150)->nullable()->after('hambatan');
        });
    }
};
