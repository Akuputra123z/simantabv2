<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        'file_size' => 'integer',
        'urutan' => 'integer',
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

        // Delete file saat force deleted (storage public & local/private)
        static::deleted(function (self $attachment) {
            if (! $attachment->isForceDeleting()) {
                return;
            }
            if (! $attachment->file_path) {
                return;
            }

            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            if (Storage::disk('local')->exists($attachment->file_path)) {
                Storage::disk('local')->delete($attachment->file_path);
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
        if (! $this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < 3) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }

    public function getFileUrlAttribute(): string
    {
        if (! $this->id || ! $this->file_path) {
            return '#';
        }

        return route('attachments.show', $this->id);
    }

    public function getDiskPathAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if (Storage::disk('local')->exists($this->file_path)) {
            return Storage::disk('local')->path($this->file_path);
        }

        if (Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->path($this->file_path);
        }

        return null;
    }

    public function getBase64DataAttribute(): ?string
    {
        $path = $this->disk_path;
        if (! $path || ! file_exists($path)) {
            return null;
        }

        $mime = @mime_content_type($path) ?: ($this->file_type ?: 'image/jpeg');
        $content = @file_get_contents($path);
        if ($content === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }

    // ================= HELPER =================

    public function isImage(): bool
    {
        if (str_starts_with((string) $this->file_type, 'image/')) {
            return true;
        }

        $ext = strtolower(pathinfo((string) $this->file_name, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg']);
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf' || strtolower(pathinfo((string) $this->file_name, PATHINFO_EXTENSION)) === 'pdf';
    }
}
