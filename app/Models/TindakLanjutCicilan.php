<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TindakLanjutCicilan extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'tindak_lanjut_cicilans';

    protected $fillable = [
        'tindak_lanjut_id',
        'ke',
        'nilai_bayar',
        'tanggal_bayar',
        'tanggal_jatuh_tempo_cicilan',
        'nomor_bukti',
        'keterangan',
        'jenis_bayar',
        'nilai_bayar_negara',
        'nilai_bayar_daerah',
        'nilai_bayar_desa',
        'nilai_bayar_bos_blud',
        'status',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'catatan_verifikasi',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'ke'                          => 'integer',
            'nilai_bayar'                 => 'decimal:2',
            'nilai_bayar_negara'          => 'decimal:2',
            'nilai_bayar_daerah'          => 'decimal:2',
            'nilai_bayar_desa'            => 'decimal:2',
            'nilai_bayar_bos_blud'        => 'decimal:2',
            'tanggal_bayar'               => 'date',
            'tanggal_jatuh_tempo_cicilan' => 'date',
            'diverifikasi_pada'           => 'datetime',
        ];
    }

    const STATUS_MENUNGGU = 'menunggu_verifikasi';
    const STATUS_DITERIMA = 'diterima';
    const STATUS_DITOLAK  = 'ditolak';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(TindakLanjut::class, 'tindak_lanjut_id');
    }

    public function diverifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Auto-set nomor urut cicilan
        static::creating(function (self $model) {
            if (! $model->ke) {
                $lastKe = \DB::table('tindak_lanjut_cicilans')
                    ->where('tindak_lanjut_id', $model->tindak_lanjut_id)
                    ->whereNull('deleted_at')
                    ->max('ke');
                $model->ke = ($lastKe ?? 0) + 1;
            }
        });

        /**
         * Cascade ke atas: Cicilan → TindakLanjut
         *
         * PERBAIKAN: panggil syncCalculations(fromCascade: true) agar auto-status
         * pada jenis cicilan berjalan dengan benar (status boleh di-override
         * berdasarkan hitungan nyata dari DB cicilan).
         */
        $cascade = function (self $model): void {
            $tl = TindakLanjut::where('id', $model->tindak_lanjut_id)->first();

            if ($tl) {
                // Bypass event saving agar tidak double-call syncCalculations,
                // lalu panggil manual dengan flag fromCascade = true
                $tl->syncCalculations(fromCascade: true);
                $tl->saveQuietly(); // saveQuietly agar tidak re-trigger booted::saving
                // Tetap trigger syncStatus rekomendasi secara manual
                $rekom = $tl->recommendation()->first();
                $rekom?->syncStatus();
            }
        };

        static::saved($cascade);
        static::deleted($cascade);
        static::restored($cascade);

        // Reorder nomor urut saat cicilan dihapus
        static::deleted(function (self $model) {
            static::where('tindak_lanjut_id', $model->tindak_lanjut_id)
                ->where('ke', '>', $model->ke)
                ->decrement('ke');
        });
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeDiterima($query)
    {
        return $query->where('status', self::STATUS_DITERIMA);
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isDiterima(): bool
    {
        return $this->status === self::STATUS_DITERIMA;
    }

    public function isTelat(): bool
    {
        return $this->tanggal_jatuh_tempo_cicilan?->isPast()
            && $this->status !== self::STATUS_DITERIMA;
    }

    public function isBreakdownValid(): bool
    {
        $sum = (float) ($this->nilai_bayar_negara   ?? 0)
             + (float) ($this->nilai_bayar_daerah   ?? 0)
             + (float) ($this->nilai_bayar_desa     ?? 0)
             + (float) ($this->nilai_bayar_bos_blud ?? 0);

        return $sum === 0.0 || abs($sum - (float) $this->nilai_bayar) < 0.01;
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => 'Menunggu Verifikasi',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DITOLAK  => 'Ditolak',
            default               => $this->status ?? '-',
        };
    }
}