<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasActivityLog;

class Recommendation extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'recommendations';

    protected $fillable = [
        'temuan_id', 'kode_rekomendasi_id', 'uraian_rekom', 'jenis_rekomendasi',
        'nilai_rekom', 'nilai_tl_selesai', 'nilai_sisa', 'batas_waktu', 'status',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_rekom'      => 'float',
            'nilai_tl_selesai' => 'float',
            'nilai_sisa'       => 'float',
            'batas_waktu'      => 'date',
        ];
    }

    // ── Constants ─────────────────────────────────────────────────────────────

    public const STATUS_BELUM   = 'belum_ditindaklanjuti';
    public const STATUS_PROSES  = 'proses';
    public const STATUS_SELESAI = 'selesai';

    public const JENIS_UANG   = 'uang';
    public const JENIS_BARANG = 'barang';
    public const JENIS_ADMIN  = 'administrasi';

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isUang(): bool    { return $this->jenis_rekomendasi === self::JENIS_UANG; }
    public function isNonUang(): bool { return ! $this->isUang(); }

    public function isJatuhTempo(): bool
    {
        if (! $this->batas_waktu) {
            return false;
        }
        return $this->batas_waktu->isPast()
            && $this->status !== self::STATUS_SELESAI;
    }

    /**
     * Sinkronisasi status & nilai berdasarkan data TindakLanjut terkini.
     *
     * PERBAIKAN:
     * - nilai_tl_selesai di-cap max nilai_rekom → mencegah nilai_sisa negatif
     *   dan progress > 100%.
     */
    public function syncStatus(): void
    {
        $query = $this->tindakLanjuts();

        if ($this->isUang()) {
            // ── Jenis UANG ────────────────────────────────────────────────────
            $totalBayar = (float) (clone $query)->sum('total_terbayar');
            $nilaiRekom = (float) ($this->nilai_rekom ?? 0);

            // Cap agar tidak melebihi nilai_rekom → progress tidak bisa >100%
            $totalBayarCapped = $nilaiRekom > 0 ? min($totalBayar, $nilaiRekom) : $totalBayar;

            $this->nilai_tl_selesai = $totalBayarCapped;
            $this->nilai_sisa       = max(0, $nilaiRekom - $totalBayarCapped);

            $this->status = match (true) {
                $nilaiRekom > 0 && $totalBayarCapped >= $nilaiRekom => self::STATUS_SELESAI,
                $totalBayar > 0                                      => self::STATUS_PROSES,
                default                                              => self::STATUS_BELUM,
            };
        } else {
            // ── Jenis BARANG / ADMINISTRASI ───────────────────────────────────
            $totalTl   = (clone $query)->count();
            $lunas     = (clone $query)->where('status_verifikasi', 'lunas')->count();
            $hasProses = (clone $query)
                ->whereIn('status_verifikasi', ['menunggu_verifikasi', 'berjalan'])
                ->exists();

            $this->nilai_rekom      = 0;
            $this->nilai_tl_selesai = 0;
            $this->nilai_sisa       = 0;

            $this->status = match (true) {
                $totalTl > 0 && $lunas >= $totalTl => self::STATUS_SELESAI,
                $hasProses                          => self::STATUS_PROSES,
                $totalTl > 0                        => self::STATUS_PROSES,
                default                             => self::STATUS_BELUM,
            };
        }

        $this->saveQuietly();

        if ($this->temuan) {
            $this->temuan->syncStatus();
        }
    }

    /**
     * Progress selalu dibatasi 0–100.
     */
    public function progress(): float
    {
        if ($this->isUang()) {
            $nilaiRekom = (float) $this->nilai_rekom;

            if ($nilaiRekom <= 0) return 0;

            // Ambil total_terbayar dari TL (sudah di-cap di syncStatus)
            $totalBayar = $this->tindakLanjuts->sum('total_terbayar');

            return min(100, round(($totalBayar / $nilaiRekom) * 100, 2));
        }

        return $this->status === self::STATUS_SELESAI ? 100 : 0;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function temuan(): BelongsTo       { return $this->belongsTo(Temuan::class); }
    public function tindakLanjuts(): HasMany  { return $this->hasMany(TindakLanjut::class); }
    public function kodeRekomendasi(): BelongsTo
    {
        return $this->belongsTo(KodeRekomendasi::class, 'kode_rekomendasi_id');
    }

    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Sengaja kosong.
    }
}