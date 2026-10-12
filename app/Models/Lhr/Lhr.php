<?php

namespace App\Models\Lhr;

use App\Enums\LhrStatus;
use App\Models\AuditAssignment;
use App\Models\Recommendation;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lhr extends Model
{
    use HasAttachments, SoftDeletes;

    protected $table = 'lhrs';

    protected $fillable = [
        'uuid',
        'audit_assignment_id',
        'unit_diperiksa_id',
        'jenis_dokumen_reviu_id',
        'lhr_template_id',
        'tahun_anggaran',
        'objek_tahun_awal',
        'objek_tahun_akhir',
        'permohonan_nomor',
        'permohonan_tanggal',
        'tahun_nomor',
        'nomor_urut',
        'sufiks',
        'nomor_lhr',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_lhr',
        'status',
        'versi',
        'revisi_dari_id',
        'row_version',
        'ditandatangani_oleh',
        'ditandatangani_pada',
        'dokumen_final_path',
        'dokumen_final_sha256',
        'snapshot_terbit',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tahun_anggaran' => 'integer',
        'objek_tahun_awal' => 'integer',
        'objek_tahun_akhir' => 'integer',
        'tahun_nomor' => 'integer',
        'nomor_urut' => 'integer',
        'versi' => 'integer',
        'row_version' => 'integer',
        'permohonan_tanggal' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_lhr' => 'date',
        'ditandatangani_pada' => 'datetime',
        'status' => LhrStatus::class,
        'snapshot_terbit' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getIrbanAttribute(): ?string
    {
        return $this->auditAssignment?->irban;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function auditAssignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class, 'audit_assignment_id');
    }

    public function unitDiperiksa(): BelongsTo
    {
        return $this->belongsTo(UnitDiperiksa::class, 'unit_diperiksa_id');
    }

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(JenisDokumenReviu::class, 'jenis_dokumen_reviu_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LhrTemplate::class, 'lhr_template_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LhrSection::class, 'lhr_id')->orderBy('urutan');
    }

    public function dasarHukums(): HasMany
    {
        return $this->hasMany(LhrDasarHukum::class, 'lhr_id')->orderBy('urutan');
    }

    public function pengujians(): HasMany
    {
        return $this->hasMany(LhrPengujian::class, 'lhr_id')->orderBy('urutan');
    }

    public function catatans(): HasManyThrough
    {
        return $this->hasManyThrough(
            LhrCatatan::class,
            LhrPengujian::class,
            'lhr_id',
            'lhr_pengujian_id',
            'id',
            'id'
        );
    }

    public function statistik(): HasOne
    {
        return $this->hasOne(LhrStatistik::class, 'lhr_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditandatangani_oleh');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function revisiDari(): BelongsTo
    {
        return $this->belongsTo(self::class, 'revisi_dari_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    public function isTerbit(): bool
    {
        return in_array($this->status, [
            LhrStatus::Terbit,
            LhrStatus::TindakLanjut,
            LhrStatus::Selesai,
        ], true);
    }

    public function progressPengujian(): float
    {
        if ($this->relationLoaded('pengujians')) {
            $total = $this->pengujians->count();
            if ($total === 0) {
                return 0.0;
            }

            $completed = $this->pengujians
                ->where('status_simpulan', '!=', 'belum_dinilai')
                ->count();

            return round(($completed / $total) * 100, 2);
        }

        $total = $this->pengujians()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->pengujians()
            ->where('status_simpulan', '!=', 'belum_dinilai')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Hitung ulang dan simpan statistik tindak lanjut / revisi LHR secara sinkron.
     */
    public function refreshStatistik(): LhrStatistik
    {
        $rekomendasis = Recommendation::whereHas('lhrCatatan.pengujian', function ($q) {
            $q->where('lhr_id', $this->id);
        })->with(['tindakLanjuts'])->get();

        $total = $rekomendasis->count();
        $selesai = 0;
        $dikirimOpd = 0;
        $draftOpd = 0;
        $ditolakOpd = 0;
        $proses = 0;
        $belum = 0;

        foreach ($rekomendasis as $rekom) {
            $tl = $rekom->tindakLanjuts->first();
            $isLunas = $tl && $tl->status_verifikasi === 'lunas';
            $isDikirim = $tl && $tl->status_opd === 'dikirim' && empty($tl->alasan_tolak_opd);
            $isDraft = $tl && $tl->status_opd === 'draft' && empty($tl->alasan_tolak_opd);
            $isDitolak = $tl && ! empty($tl->alasan_tolak_opd);

            if ($isLunas || $rekom->status === Recommendation::STATUS_SELESAI) {
                $selesai++;
            } elseif ($isDikirim) {
                $dikirimOpd++;
            } elseif ($isDitolak) {
                $ditolakOpd++;
                $proses++;
            } elseif ($isDraft) {
                $draftOpd++;
                $proses++;
            } elseif ($rekom->status === Recommendation::STATUS_PROSES) {
                $proses++;
            } else {
                $belum++;
            }
        }

        $persenSelesai = $total > 0 ? round(($selesai / $total) * 100, 2) : 0.00;
        $persenTl = $total > 0 ? round((($selesai + $dikirimOpd) / $total) * 100, 2) : 0.00;

        $stat = LhrStatistik::updateOrCreate(
            ['lhr_id' => $this->id],
            [
                'total_rekomendasi' => $total,
                'selesai'           => $selesai,
                'belum'             => $belum,
                'proses'            => $proses,
                'dikirim_opd'       => $dikirimOpd,
                'draft_opd'         => $draftOpd,
                'ditolak_opd'       => $ditolakOpd,
                'persen'            => $persenSelesai,
                'persen_tl'         => $persenTl,
                'dihitung_pada'     => now(),
            ]
        );

        $this->setRelation('statistik', $stat);

        // Sinkronisasi status LHR otomatis jika ada revisi dari OPD
        if (in_array($this->status, [LhrStatus::Terbit, LhrStatus::TindakLanjut])) {
            if ($total > 0 && $selesai === $total) {
                $this->updateQuietly(['status' => LhrStatus::Selesai]);
            } elseif ($dikirimOpd > 0 && $this->status === LhrStatus::Terbit) {
                $this->updateQuietly(['status' => LhrStatus::TindakLanjut]);
            }
        } elseif ($this->status === LhrStatus::Selesai && $total > 0 && $selesai < $total) {
            $this->updateQuietly(['status' => LhrStatus::TindakLanjut]);
        }

        return $stat;
    }

    /**
     * Ringkasan progres terintegrasi untuk tampilan tabel /lhr/index.
     */
    public function getProgresReviuData(): object
    {
        $stat = $this->statistik;
        if (! $stat || ! $stat->dihitung_pada) {
            $stat = $this->refreshStatistik();
        }

        $totalRekom = (int) $stat->total_rekomendasi;
        $selesai = (int) $stat->selesai;
        $dikirimOpd = (int) $stat->dikirim_opd;
        $draftOpd = (int) $stat->draft_opd;
        $ditolakOpd = (int) $stat->ditolak_opd;
        $belum = (int) $stat->belum;
        $persenSelesai = (float) $stat->persen;
        $persenTl = (float) $stat->persen_tl;

        $isTerbit = $this->isTerbit();

        if (! $isTerbit) {
            $persenDraft = $this->progressPengujian();
            return (object) [
                'tahap'          => 'penyusunan',
                'persen'         => $persenDraft,
                'persen_reviu'   => $persenDraft,
                'total_rekom'    => $totalRekom,
                'selesai'        => 0,
                'dikirim_opd'    => 0,
                'draft_opd'      => 0,
                'ditolak_opd'    => 0,
                'belum'          => $totalRekom,
                'persen_tl'      => 0,
                'label'          => $persenDraft . '% Disusun',
            ];
        }

        return (object) [
            'tahap'          => 'tindak_lanjut',
            'persen'         => $totalRekom === 0 ? 100.0 : $persenSelesai,
            'persen_reviu'   => 100.0,
            'persen_tl'      => $totalRekom === 0 ? 100.0 : $persenTl,
            'total_rekom'    => $totalRekom,
            'selesai'        => $selesai,
            'dikirim_opd'    => $dikirimOpd,
            'draft_opd'      => $draftOpd,
            'ditolak_opd'    => $ditolakOpd,
            'belum'          => $belum,
            'label'          => $totalRekom === 0 ? '100% (Nihil)' : $persenSelesai . '% Selesai',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Scope dokumen LHR yang sudah terbit resmi (Terbit, TindakLanjut, Selesai).
     */
    public function scopeTerbit(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('status', [
            LhrStatus::Terbit,
            LhrStatus::TindakLanjut,
            LhrStatus::Selesai,
        ]);
    }

    /**
     * Scope data LHR sesuai hak akses pengguna.
     * Jika pengguna adalah OPD, filter otomatis ke unit binaan miliknya dan hanya status terbit.
     */
    public function scopeForUser(\Illuminate\Database\Eloquent\Builder $query, ?User $user): \Illuminate\Database\Eloquent\Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['super_admin', 'kepala_inspektorat'])) {
            return $query;
        }

        if ($user->hasRole('opd')) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            if ($unitIds->isNotEmpty()) {
                $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
                $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
            } elseif (! empty($user->unit_kerja)) {
                $unitIds = UnitDiperiksa::where('nama_unit', 'like', "%{$user->unit_kerja}%")->pluck('id');
            }

            if ($unitIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('unit_diperiksa_id', $unitIds)
                ->whereIn('status', [
                    LhrStatus::Terbit,
                    LhrStatus::TindakLanjut,
                    LhrStatus::Selesai,
                ]);
        }

        // Jika Irban / Pengendali Teknis
        if ($user->hasAnyRole([User::ROLE_PENGENDALI_TEKNIS, 'irban']) || $user->isPengendaliTeknis() || $user->isIrban()) {
            $irbanNames = $user->getIrbanTeamNames();

            return $query->where(function ($q) use ($user, $irbanNames) {
                $q->whereHas('auditAssignment', function ($aq) use ($user, $irbanNames) {
                    $aq->where('pengendali_teknis_id', $user->id)
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
                })->orWhere('created_by', $user->id);
            });
        }

        // Auditor (Ketua Tim / Anggota / Staf Inspektorat)
        return $query->where(function ($q) use ($user) {
            $q->whereHas('auditAssignment', function ($aq) use ($user) {
                $aq->where('ketua_tim_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('members', fn ($mq) => $mq->where('user_id', $user->id));
            })->orWhere('created_by', $user->id);
        });
    }
}
