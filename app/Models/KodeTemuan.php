<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class KodeTemuan extends Model
{
    use SoftDeletes;

    protected $table = 'kode_temuans';

    protected $fillable = [
        'kode',
        'kode_numerik',
        'kel',
        'sub_kel',
        'jenis',
        'kelompok',
        'sub_kelompok',
        'deskripsi',
        'alternatif_rekom',
    ];

    protected function casts(): array
    {
        return [
            'kel'              => 'integer',
            'sub_kel'          => 'integer',
            'jenis'            => 'integer',
            // UBAH 'array' menjadi 'collection' agar bisa pakai ->isNotEmpty() di View
            'alternatif_rekom' => 'collection', 
            'deleted_at'       => 'datetime',
        ];
    }

    // ── Clean Code: UI Logic (Accessor) ───────────────────────────────────────
    
    /** Label Warna untuk UI (Sesuai Swiss Style / Minimalist) */
    public function getKlasifikasiColorAttribute(): string
    {
        return match ($this->kel) {
            1 => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
            2 => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
            3 => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
            default => 'bg-gray-50 text-gray-700',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeKelompok($query, int $kel)
    {
        return $query->where('kel', $kel);
    }

    public function scopeKetidakpatuhan($query) { return $query->kelompok(1); }
    public function scopeSpi($query)            { return $query->kelompok(2); }
    public function scopeTigaE($query)          { return $query->kelompok(3); }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function temuans(): HasMany
    {
        return $this->hasMany(Temuan::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getLabelKelompokAttribute(): string
    {
        return match ($this->kel) {
            1 => 'Ketidakpatuhan',
            2 => 'Kelemahan SPI',
            3 => '3E (Ekonomis, Efisien, Efektif)',
            default => 'Tidak Diketahui',
        };
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode} — {$this->deskripsi}";
    }
}