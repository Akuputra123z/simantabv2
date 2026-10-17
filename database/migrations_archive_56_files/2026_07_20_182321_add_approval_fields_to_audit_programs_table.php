<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_programs', function (Blueprint $table) {
            $table->string('approval_status', 30)->default('draft')->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('approved_pdf', 255)->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('audit_programs', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approved_pdf']);
        });
    }
};
