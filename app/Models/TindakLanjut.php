<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TindakLanjut extends Model
{
    use HasActivityLog, HasAttachments, HasCreatedUpdatedBy, SoftDeletes;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'tindak_lanjuts';

    protected $fillable = [
        'uuid',
        'recommendation_id', 'jenis_penyelesaian', 'is_cicilan', 'nilai_tindak_lanjut',
        'jumlah_cicilan_rencana', 'tanggal_mulai_cicilan', 'tanggal_jatuh_tempo',
        'nilai_per_cicilan_rencana', 'jumlah_cicilan_realisasi', 'total_terbayar',
        'sisa_belum_bayar', 'catatan_tl', 'hambatan', 'status_verifikasi',
        'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi',
        'keterangan_pendukung_opd', 'uraian_tindak_lanjut', 'upload_opd_oleh',
        'status_opd', 'dikirim_pada', 'alasan_tolak_opd',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_cicilan' => 'boolean',
            'jumlah_cicilan_rencana' => 'integer',
            'jumlah_cicilan_realisasi' => 'integer',
            'tanggal_mulai_cicilan' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'nilai_tindak_lanjut' => 'decimal:2',
            'nilai_per_cicilan_rencana' => 'decimal:2',
            'total_terbayar' => 'decimal:2',
            'sisa_belum_bayar' => 'decimal:2',
            'diverifikasi_pada' => 'datetime',
            'dikirim_pada' => 'datetime',
        ];
    }

    protected $attributes = [
        'nilai_tindak_lanjut' => 0,
        'total_terbayar' => 0,
        'sisa_belum_bayar' => 0,
        'status_verifikasi' => 'menunggu_verifikasi',
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

    public function uploadOpdOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upload_opd_oleh');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser($query, $user)
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['super_admin', 'kepala_inspektorat'])) {
            return $query;
        }

        if ($user->hasRole('opd')) {
            return $this->scopeForOpd($query, $user);
        }

        // Jika Irban / Pengendali Teknis
        if ($user->hasAnyRole([User::ROLE_PENGENDALI_TEKNIS, 'irban']) || $user->isPengendaliTeknis() || $user->isIrban()) {
            $irbanNames = $user->getIrbanTeamNames();

            return $query->whereHas('recommendation.temuan.lhp.auditAssignment', function ($q) use ($user, $irbanNames) {
                $q->where('pengendali_teknis_id', $user->id)
                    ->orWhere('pengendali_teknis', $user->name)
                    ->orWhere(function ($sq) use ($irbanNames) {
                        if (! empty($irbanNames)) {
                            $sq->whereHas('auditProgramDetail', function ($dq) use ($irbanNames) {
                                $dq->whereIn('tim', $irbanNames);
                            })->orWhere(function ($nq) use ($irbanNames) {
                                foreach ($irbanNames as $in) {
                                    $nq->orWhere('nama_tim', 'like', "%{$in}%");
                                }
                            });
                        }
                    })
                    ->orWhere('ketua_tim_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('members', fn ($mq) => $mq->where('user_id', $user->id));
            });
        }

        // Auditor (Ketua Tim / Anggota / Staf)
        return $query->whereHas('recommendation.temuan.lhp.auditAssignment', function ($q) use ($user) {
            $q->where('ketua_tim_id', $user->id)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('members', function ($q2) use ($user) {
                    $q2->where('audit_assignment_members.user_id', $user->id);
                });
        });
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForOpd($query, $user)
    {
        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
        if ($unitIds->isNotEmpty()) {
            $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
            $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
        } elseif (! empty($user->unit_kerja)) {
            $unitIds = UnitDiperiksa::where('nama_unit', 'like', "%{$user->unit_kerja}%")->pluck('id');
        }

        if ($unitIds->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereHas('recommendation.temuan.lhp', function ($q) use ($unitIds) {
            $q->where(function ($sub) use ($unitIds) {
                $sub->whereIn('unit_diperiksa_id', $unitIds)
                    ->orWhereHas('unitDiperiksas', fn ($uq) => $uq->whereIn('unit_diperiksas.id', $unitIds));
            });
        });
    }

    public function scopeOpdDraft($query)
    {
        return $query->where('status_opd', 'draft');
    }

    public function scopeOpdDikirim($query)
    {
        return $query->where('status_opd', 'dikirim');
    }

    public function scopeOpdBelumUpload($query)
    {
        return $query->whereNull('status_opd');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Scope untuk menangani filter pencarian dan status.
     */
    public function scopeFilter($query, array $filters)
    {
        // Filter berdasarkan pencarian teks (Uraian Rekomendasi atau Nomor LHP)
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                // Cari di tabel tindak_lanjuts sendiri (catatan)
                $q->where('catatan_tl', 'like', '%'.$search.'%')
                  // Atau cari di relasi Recommendation (uraian_rekom)
                    ->orWhereHas('recommendation', function ($qRekom) use ($search) {
                        $qRekom->where('uraian_rekom', 'like', '%'.$search.'%')
                               // Atau cari di relasi Temuan -> LHP (nomor_lhp)
                            ->orWhereHas('temuan.lhp', function ($qLhp) use ($search) {
                                $qLhp->where('nomor_lhp', 'like', '%'.$search.'%');
                            });
                    });
            });
        });

        // Filter berdasarkan status verifikasi
        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status_verifikasi', $status);
        });

        return $query;
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
        $nilaiTl = (float) ($this->nilai_tindak_lanjut ?? 0);

        if ($nilaiRekom > 0 && $nilaiTl > $nilaiRekom) {
            throw new \InvalidArgumentException(
                'Nilai tindak lanjut (Rp '.number_format($nilaiTl, 0, ',', '.').') '.
                'tidak boleh melebihi nilai rekomendasi (Rp '.number_format($nilaiRekom, 0, ',', '.').').'
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
        $rekom = $this->recommendation;
        $isNonUang = $rekom?->isNonUang() ?? false;
        $isCicilan = $this->jenis_penyelesaian === 'cicilan';

        if ($isNonUang) {
            // ── BARANG / ADMINISTRASI ─────────────────────────────────────────
            // Status ditentukan sepenuhnya oleh user (tidak di-override).
            // Hanya mapping sisa/terbayar untuk konsistensi data.
            $isLunas = $this->status_verifikasi === 'lunas';
            $this->total_terbayar = $isLunas ? 1 : 0;
            $this->sisa_belum_bayar = $isLunas ? 0 : 1;
            $this->jumlah_cicilan_realisasi = 0;

        } else {
            // ── UANG ──────────────────────────────────────────────────────────
            $nilaiRekom = (float) ($rekom?->nilai_rekom ?? 0);
            $targetNilai = $nilaiRekom > 0 ? $nilaiRekom : (float) $this->nilai_tindak_lanjut;

            if ($isCicilan) {
                // Total terbayar dari cicilan yang diterima (1 query agregat cepat)
                $agg = $this->cicilanDiterima()
                    ->selectRaw('COALESCE(SUM(nilai_bayar), 0) as total, COUNT(*) as jumlah')
                    ->first();
                $terbayar = (float) ($agg->total ?? 0);
                // Cap total_terbayar agar tidak melebihi target → mencegah progress > 100%
                $this->total_terbayar = min($terbayar, $targetNilai);
                $this->jumlah_cicilan_realisasi = (int) ($agg->jumlah ?? 0);
            } else {
                // Jenis setor kas / langsung:
                if ($this->status_verifikasi === 'lunas') {
                    $this->total_terbayar = max((float) $this->nilai_tindak_lanjut, $targetNilai);
                } elseif ($this->status_verifikasi === 'berjalan') {
                    $nominal = (float) $this->nilai_tindak_lanjut;
                    $this->total_terbayar = $nominal > 0 ? min($nominal, $targetNilai) : 0;
                } else {
                    $this->total_terbayar = 0;
                }
                $this->jumlah_cicilan_realisasi = 0;
            }

            // Hitung sisa — pastikan tidak negatif
            $sisa = $targetNilai - $this->total_terbayar;
            $this->sisa_belum_bayar = max(0, $sisa);

            // ── Auto-status HANYA untuk jenis cicilan (dihitung dari data nyata) ──
            // Untuk jenis 'langsung', status dikendalikan penuh oleh user.
            if ($isCicilan || $fromCascade) {
                if ($targetNilai > 0 && $this->sisa_belum_bayar <= 0 && $this->total_terbayar >= $targetNilai) {
                    $this->status_verifikasi = 'lunas';
                } elseif ($this->total_terbayar > 0 && $this->sisa_belum_bayar > 0) {
                    $this->status_verifikasi = 'berjalan';
                } else {
                    $this->status_verifikasi = 'menunggu_verifikasi';
                }
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
        if (! $rekom) {
            return 0;
        }

        if ($rekom->isNonUang()) {
            return $this->status_verifikasi === 'lunas' ? 100 : 0;
        }

        if ($this->status_verifikasi === 'lunas') {
            return 100;
        }

        $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);

        if ($nilaiRekom <= 0) {
            return 0;
        }

        return min(100, round($this->total_terbayar / $nilaiRekom * 100, 2));
    }

    public function getUraianAttribute()
    {
        return $this->catatan_tl;
    }

    public function getUraianTindakLanjutAttribute(): ?string
    {
        return $this->keterangan_pendukung_opd;
    }

    public function setUraianTindakLanjutAttribute(?string $value): void
    {
        $this->attributes['keterangan_pendukung_opd'] = $value;
    }

    public function getNilaiAttribute()
    {
        return $this->nilai_tindak_lanjut;
    }

    public function getTanggalAttribute()
    {
        return $this->tanggal_jatuh_tempo;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? 'uuid';
        $model = $this->where($field, $value)->first();

        if ($model) {
            return $model;
        }

        if ($field === 'uuid' && is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        abort(404);
    }

    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

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
