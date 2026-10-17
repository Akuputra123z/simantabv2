<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 100)->nullable()->comment('MIME type, e.g. application/pdf | image/jpeg');
            $table->unsignedBigInteger('file_size')->nullable()->comment('Size in bytes');
            $table->morphs('attachable');
            $table->string('jenis_bukti', 100)->nullable()->index();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->enum('visibilitas', ['internal', 'publik'])->default('internal');
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['attachable_type', 'attachable_id', 'jenis_bukti'], 'attachable_jenis_idx');
            $table->index(['attachable_type', 'attachable_id', 'urutan'], 'attachable_urutan_idx');
            $table->index('uploaded_by', 'attach_uploaded_by_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
