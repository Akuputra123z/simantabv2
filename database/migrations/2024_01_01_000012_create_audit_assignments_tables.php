<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('audit_program_detail_id')
                ->constrained('audit_program_details')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('lhr_asal_id')->nullable();
            $table->string('nama_tim', 255)->nullable();
            $table->string('jenis_pengawasan', 50)->nullable();
            $table->foreignId('ketua_tim_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('nomor_surat', 255)->unique();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status', 20)->default('draft')->index();
            $table->decimal('anggaran_disetujui', 15, 2)->default(0.00);
            $table->string('approval_status', 30)->default('draft')->index();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('pengendali_teknis', 150)->nullable();
            $table->foreignId('pengendali_teknis_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
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

            $table->index(['audit_program_detail_id', 'status'], 'idx_assignments_detail_status');
            $table->index(['approval_status', 'deleted_at'], 'audit_assignments_approval_status_deleted_at_index');
            $table->index('ketua_tim_id', 'idx_assignments_ketua_tim');
        });

        Schema::create('assignment_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')
                ->constrained('audit_assignments')
                ->cascadeOnDelete();
            $table->foreignId('unit_diperiksa_id')
                ->constrained('unit_diperiksas')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['audit_assignment_id', 'unit_diperiksa_id']);
            $table->index('unit_diperiksa_id');
        });

        Schema::create('audit_assignment_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')
                ->constrained('audit_assignments')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('jabatan_tim', 50)->nullable()->comment('Ketua | Anggota | Pengendali Teknis');
            $table->timestamps();

            $table->unique(['audit_assignment_id', 'user_id'], 'member_assign_user_unique');
            $table->index('user_id', 'member_user_idx');
        });

        Schema::create('audit_assignment_dasar_peraturan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')
                ->constrained('audit_assignments')
                ->cascadeOnDelete();
            $table->foreignId('dasar_peraturan_id')
                ->constrained('dasar_peraturans')
                ->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['audit_assignment_id', 'dasar_peraturan_id'], 'aa_dp_unique');
            $table->index('dasar_peraturan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_assignment_dasar_peraturan');
        Schema::dropIfExists('audit_assignment_members');
        Schema::dropIfExists('assignment_unit');
        Schema::dropIfExists('audit_assignments');
    }
};
