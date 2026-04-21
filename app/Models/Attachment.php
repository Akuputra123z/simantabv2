<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'jenis_bukti',
        'urutan',
        'keterangan',
        'visibilitas',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size'  => 'integer',
        'urutan'     => 'integer',
        'deleted_at' => 'datetime',
    ];


public function attachments(): MorphMany
{
    return $this->morphMany(Attachment::class, 'attachable');
}
    // ================= BOOT =================

    protected static function booted(): void
    {
        // Auto isi uploaded_by
        static::creating(function (self $attachment) {
            if (auth()->check()) {
                $attachment->uploaded_by ??= auth()->id();
            }
        });

        // 🔥 FIX UTAMA: COPY saat CREATE (INI YANG KAMU BUTUH)
        static::created(function (self $attachment) {

            if (! $attachment->file_path) return;

            $source = storage_path('app/public/' . $attachment->file_path);
            $destination = public_path('storage/' . $attachment->file_path);

            if (! file_exists($source)) return;

            // hindari overwrite
            if (file_exists($destination)) return;

            if (! is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0775, true);
            }

            copy($source, $destination);
        });

        // 🔥 DELETE file (storage + public)
        static::deleted(function (self $attachment) {

            if (! $attachment->isForceDeleting()) return;
            if (! $attachment->file_path) return;

            $storagePath = storage_path('app/public/' . $attachment->file_path);
            $publicPath  = public_path('storage/' . $attachment->file_path);

            if (file_exists($storagePath)) {
                @unlink($storagePath);
            }

            if (file_exists($publicPath)) {
                @unlink($publicPath);
            }
        });
    }

    // ================= RELATION =================

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ================= ACCESSOR =================

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size) return '-';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = $this->file_size;
        $unit  = 0;

        while ($size >= 1024 && $unit < 3) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getFileUrlAttribute(): string
    {
        return $this->file_path
            ? asset('storage/' . $this->file_path)
            : '#';
    }

    // ================= HELPER =================

    public function isImage(): bool
    {
        return str_starts_with((string) $this->file_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }
}