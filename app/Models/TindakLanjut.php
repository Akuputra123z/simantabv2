<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasActivityLog;

class TindakLanjut extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'tindak_lanjuts';

    protected $fillable = [
        'recommendation_id', 'jenis_penyelesaian', 'is_cicilan', 'nilai_tindak_lanjut',
        'jumlah_cicilan_rencana', 'tanggal_mulai_cicilan', 'tanggal_jatuh_tempo',
        'nilai_per_cicilan_rencana', 'jumlah_cicilan_realisasi', 'total_terbayar',
        'sisa_belum_bayar', 'catatan_tl', 'hambatan', 'status_verifikasi',
        'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_cicilan'                => 'boolean',
            'jumlah_cicilan_rencana'    => 'integer',
            'jumlah_cicilan_realisasi'  => 'integer',
            'tanggal_mulai_cicilan'     => 'date',
            'tanggal_jatuh_tempo'       => 'date',
            'nilai_tindak_lanjut'       => 'decimal:2',
            'nilai_per_cicilan_rencana' => 'decimal:2',
            'total_terbayar'            => 'decimal:2',
            'sisa_belum_bayar'          => 'decimal:2',
            'diverifikasi_pada'         => 'datetime',
        ];
    }

    protected $attributes = [
        'nilai_tindak_lanjut' => 0,
        'total_terbayar'      => 0,
        'sisa_belum_bayar'    => 0,
        'status_verifikasi'   => 'menunggu_verifikasi',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function cicilans(): HasMany
    {
        return $this->hasMany(TindakLanjutCicilan::class, 'tindak_lanjut_id')->orderBy('ke');
    }

    public function cicilanDiterima(): HasMany
    {
        return $this->cicilans()->where('status', 'diterima');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser($query, $user)
    {
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->whereHas('recommendation.temuan.lhp.auditAssignment', function ($q) use ($user) {
            $q->where('ketua_tim_id', $user->id)
              ->orWhereHas('members', function ($q2) use ($user) {
                  $q2->where('audit_assignment_members.user_id', $user->id);
              });
        });
    }

    // ── Validation Helper ────────────────────────────────────────────────────

    /**
     * Validasi apakah nilai_tindak_lanjut tidak melebihi nilai_rekom.
     * Panggil ini di controller sebelum store/update.
     *
     * @throws \InvalidArgumentException
     */
    public function validateNilai(): void
    {
        $rekom = $this->recommendation;

        if (! $rekom || ! $rekom->isUang()) {
            return; // Non-uang tidak perlu validasi nilai
        }

        $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);
        $nilaiTl    = (float) ($this->nilai_tindak_lanjut ?? 0);

        if ($nilaiRekom > 0 && $nilaiTl > $nilaiRekom) {
            throw new \InvalidArgumentException(
                'Nilai tindak lanjut (Rp ' . number_format($nilaiTl, 0, ',', '.') . ') ' .
                'tidak boleh melebihi nilai rekomendasi (Rp ' . number_format($nilaiRekom, 0, ',', '.') . ').'
            );
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function nextKeCicilan(): int
    {
        return ($this->cicilans()->max('ke') ?? 0) + 1;
    }

    /**
     * Hitung ulang nilai kalkulasi.
     *
     * PERBAIKAN UTAMA:
     * - Status yang di-set manual user (via form edit) TIDAK di-override oleh auto-logic,
     *   KECUALI untuk kasus cicilan uang yang bisa dihitung otomatis dari data nyata.
     * - Flag $fromCascade = true berarti dipanggil dari cicilan (bukan dari user edit),
     *   sehingga boleh auto-override status berdasarkan perhitungan.
     */
    public function syncCalculations(bool $fromCascade = false): static
    {
        $rekom     = $this->recommendation;
        $isNonUang = $rekom?->isNonUang() ?? false;
        $isCicilan = $this->jenis_penyelesaian === 'cicilan';

        if ($isNonUang) {
            // ── BARANG / ADMINISTRASI ─────────────────────────────────────────
            // Status ditentukan sepenuhnya oleh user (tidak di-override).
            // Hanya mapping sisa/terbayar untuk konsistensi data.
            $isLunas = $this->status_verifikasi === 'lunas';
            $this->total_terbayar           = $isLunas ? 1 : 0;
            $this->sisa_belum_bayar         = $isLunas ? 0 : 1;
            $this->jumlah_cicilan_realisasi = 0;

        } else {
            // ── UANG ──────────────────────────────────────────────────────────
            $nilaiRekom  = (float) ($rekom?->nilai_rekom ?? 0);
            $targetNilai = (float) ($this->nilai_tindak_lanjut > 0
                            ? $this->nilai_tindak_lanjut
                            : $nilaiRekom);

            if ($isCicilan) {
                // Total terbayar dari cicilan yang diterima (selalu dihitung dari DB)
                $terbayar = (float) $this->cicilanDiterima()->sum('nilai_bayar');
                // Cap total_terbayar agar tidak melebihi target → mencegah progress > 100%
                $this->total_terbayar           = min($terbayar, $targetNilai);
                $this->jumlah_cicilan_realisasi = $this->cicilanDiterima()->count();
            } else {
                // Jenis langsung: jika user set status = lunas, terbayar = target
                if ($this->status_verifikasi === 'lunas') {
                    $this->total_terbayar = $targetNilai;
                } elseif ($fromCascade === false) {
                    // Saat user edit dan status bukan lunas, jangan override total_terbayar
                    // (biarkan nilai yang ada)
                }
                $this->jumlah_cicilan_realisasi = 0;
            }

            // Hitung sisa — pastikan tidak negatif
            $sisa = $targetNilai - $this->total_terbayar;
            $this->sisa_belum_bayar = max(0, $sisa);

            // ── Auto-status HANYA untuk jenis cicilan (dihitung dari data nyata) ──
            // Untuk jenis 'langsung', status dikendalikan penuh oleh user.
            if ($isCicilan || $fromCascade) {
                if ($targetNilai > 0 && $this->sisa_belum_bayar <= 0) {
                    $this->status_verifikasi = 'lunas';
                } elseif ($this->total_terbayar > 0 && $this->sisa_belum_bayar > 0) {
                    $this->status_verifikasi = 'berjalan';
                }
                // Jika belum ada pembayaran sama sekali, biarkan status yang ada
            }
            // Untuk jenis 'langsung' yang diedit user (bukan cascade):
            // status_verifikasi TIDAK di-override → user bebas set lunas/berjalan/menunggu
        }

        return $this;
    }

    public function getIsCicilanAttribute(): bool
    {
        return $this->jenis_penyelesaian === 'cicilan';
    }

    /**
     * Progress capped di 100% untuk mencegah overflow.
     */
    public function progress(): float
    {
        $rekom = $this->recommendation;
        if (! $rekom) return 0;

        if ($rekom->isNonUang()) {
            return $this->status_verifikasi === 'lunas' ? 100 : 0;
        }

        if ($this->status_verifikasi === 'lunas') return 100;

        $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);

        if ($nilaiRekom <= 0) return 0;

        return min(100, round($this->total_terbayar / $nilaiRekom * 100, 2));
    }

    public function getUraianAttribute()
    {
        return $this->catatan_tl;
    }

    public function getNilaiAttribute()
    {
        return $this->nilai_tindak_lanjut;
    }

    public function getTanggalAttribute()
    {
        return $this->tanggal_jatuh_tempo;
    }

    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            // fromCascade = false → dipanggil dari user (store/update),
            // jadi status manual user tidak di-override untuk jenis 'langsung'
            $model->syncCalculations(fromCascade: false);
        });

        static::saved(function (self $model) {
            $rekom = $model->relationLoaded('recommendation')
                ? $model->recommendation
                : $model->recommendation()->first();

            $rekom?->syncStatus();
        });

        static::deleted(function (self $model) {
            $rekom = $model->recommendation()->first();
            $rekom?->syncStatus();
        });
    }
}