<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lhps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('audit_assignment_id')
                ->constrained('audit_assignments')
                ->cascadeOnDelete();
            $table->foreignId('unit_diperiksa_id')
                ->nullable()
                ->constrained('unit_diperiksas')
                ->nullOnDelete();
            $table->boolean('is_nihil')->default(false);
            $table->string('nomor_lhp', 100)->comment('Format: 700/009/001P/PKPT.2023');
            $table->date('tanggal_lhp')->index();
            $table->text('catatan_umum')->nullable()->comment('Ringkasan/simpulan audit');
            $table->string('status', 30)->default('draft')->index()->comment('draft, final, ditandatangani, dibatalkan');
            $table->text('status_batal_keterangan')->nullable();
            $table->foreignId('status_batal_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('status_batal_at')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['audit_assignment_id', 'unit_diperiksa_id'], 'lhps_assignment_unit_unique');
            $table->unique(['nomor_lhp', 'unit_diperiksa_id'], 'lhps_nomor_unit_unique');
        });

        Schema::create('lhp_unit_diperiksa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lhp_id')
                ->constrained('lhps')
                ->cascadeOnDelete();
            $table->foreignId('unit_diperiksa_id')
                ->constrained('unit_diperiksas')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lhp_id', 'unit_diperiksa_id'], 'lhp_unit_unique');
            $table->index('unit_diperiksa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lhp_unit_diperiksa');
        Schema::dropIfExists('lhps');
    }
};
