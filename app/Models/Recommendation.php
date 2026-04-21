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
     * Ini satu-satunya metode yang boleh menulis ke kolom status/nilai rekomendasi.
     *
     * Chain: TindakLanjut::saved → syncStatus() → temuan->syncStatus() → updateStatistik()
     */
    public function syncStatus(): void
{
    $query = $this->tindakLanjuts();

    if ($this->isUang()) {
        // ── Jenis UANG: hitung berdasarkan total_terbayar ──────────────────
        $totalBayar = (float) (clone $query)->sum('total_terbayar');
        $nilaiRekom = (float) ($this->nilai_rekom ?? 0);

        $this->nilai_tl_selesai = $totalBayar;
        $this->nilai_sisa       = max(0, $nilaiRekom - $totalBayar);

        $this->status = match (true) {
            $nilaiRekom > 0 && $totalBayar >= $nilaiRekom => self::STATUS_SELESAI,
            $totalBayar > 0                               => self::STATUS_PROSES,
            default                                       => self::STATUS_BELUM,
        };
    } else {
        // ── Jenis BARANG / ADMINISTRASI: hitung berdasarkan status_verifikasi ──
        $totalTl = (clone $query)->count();
        $lunas   = (clone $query)->where('status_verifikasi', 'lunas')->count();
        $hasProses = (clone $query)
            ->whereIn('status_verifikasi', ['menunggu_verifikasi', 'berjalan'])
            ->exists();

        $this->nilai_rekom      = 0;
        $this->nilai_tl_selesai = 0;
        $this->nilai_sisa       = 0;

        $this->status = match (true) {
            $totalTl > 0 && $lunas >= $totalTl => self::STATUS_SELESAI, // semua TL sudah lunas
            $hasProses                          => self::STATUS_PROSES,
            $totalTl > 0                        => self::STATUS_PROSES,  // ada TL tapi belum lunas
            default                             => self::STATUS_BELUM,
        };
    }

    $this->saveQuietly();

    // cascade ke temuan
   if ($this->temuan) {
        $this->temuan->syncStatus(); 
    }
}

  public function progress(): float
{
    if ($this->isUang()) {
        $totalBayar = $this->tindakLanjuts->sum('total_terbayar');
        $nilaiRekom = (float) $this->nilai_rekom;

        return $nilaiRekom > 0
            ? round(($totalBayar / $nilaiRekom) * 100, 2)
            : 0;
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
        // Statistik diupdate via chain: controller → updateStatistik()
        // atau via: TindakLanjut::saved → syncStatus() → temuan->syncStatus() → updateStatistik()
    }
}