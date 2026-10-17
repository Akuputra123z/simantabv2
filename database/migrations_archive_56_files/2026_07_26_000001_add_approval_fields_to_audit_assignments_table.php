<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->string('approval_status', 30)->default('draft')->after('anggaran_disetujui');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            $table->index('approval_status');
            $table->index(['approval_status', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropIndex(['approval_status', 'deleted_at']);
            $table->dropIndex(['approval_status']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at']);
        });
    }
};
