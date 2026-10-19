<?php

namespace App\Models;

use App\Models\Lhr\Lhr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AuditAssignment extends Model
{
    use SoftDeletes;

    public const JENIS_PENGAWASAN = [
        'reguler',
        'khusus',
        'investigasi',
        'reviu',
        'monitoring',
    ];

    protected $fillable = [
        'uuid',
        'audit_program_detail_id',
        'lhr_asal_id',
        'nama_tim',
        'jenis_pengawasan',
        'ketua_tim_id',
        'nomor_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'anggaran_disetujui',
        'pengendali_teknis',
        'pengendali_teknis_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'anggaran_disetujui' => 'integer',
        'approved_at' => 'datetime',
    ];

    // ─── Static Helpers ───────────────────────────────────────────────

    public static function listJenisPengawasan(): array
    {
        return self::JENIS_PENGAWASAN;
    }

    // ─── Relations ────────────────────────────────────────────────────

    /** Relasi ke AuditProgramDetail (nama konsisten dipakai di controller & blade) */
    public function auditProgramDetail(): BelongsTo
    {
        return $this->belongsTo(AuditProgramDetail::class, 'audit_program_detail_id');
    }

    /** Shortcut ke AuditProgram (induk) lewat AuditProgramDetail */
    public function auditProgram()
    {
        return $this->hasOneThrough(
            AuditProgram::class,
            AuditProgramDetail::class,
            'id',                      // FK di audit_program_details
            'id',                      // FK di audit_programs
            'audit_program_detail_id', // local key di audit_assignments
            'audit_program_id'         // local key di audit_program_details
        );
    }

    /** Many-to-many ke UnitDiperiksa lewat pivot assignment_unit */
    public function unitDiperiksas(): BelongsToMany
    {
        return $this->belongsToMany(
            UnitDiperiksa::class,
            'assignment_unit',
            'audit_assignment_id',
            'unit_diperiksa_id'
        )->withTimestamps();
    }

    public function ketuaTim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_tim_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'audit_assignment_members',
            'audit_assignment_id',
            'user_id'
        )
            ->withPivot('jabatan_tim')
            ->withTimestamps();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function lhps(): HasMany
    {
        return $this->hasMany(Lhp::class);
    }

    public function lhrs(): HasMany
    {
        return $this->hasMany(Lhr::class, 'audit_assignment_id');
    }

    public function lhrAsal(): BelongsTo
    {
        return $this->belongsTo(Lhr::class, 'lhr_asal_id');
    }

    public function pengendaliTeknis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengendali_teknis_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isSigned(): bool
    {
        return $this->approved_by !== null;
    }

    public function dasarPeraturans(): BelongsToMany
    {
        return $this->belongsToMany(
            DasarPeraturan::class,
            'audit_assignment_dasar_peraturan',
            'audit_assignment_id',
            'dasar_peraturan_id'
        )
            ->withPivot('urutan')
            ->orderByPivot('urutan', 'asc')
            ->orderBy('dasar_peraturans.urutan', 'asc');
    }

    public function getEffectiveDasarPeraturansAttribute()
    {
        if ($this->relationLoaded('dasarPeraturans') && $this->dasarPeraturans->isNotEmpty()) {
            return $this->dasarPeraturans;
        }

        $assigned = $this->dasarPeraturans()->get();
        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        return DasarPeraturan::where('is_active', true)->orderBy('urutan')->get();
    }

    /**
     * Dapatkan nama Irban pembina/pelaksana penugasan ini jika ada.
     */
    public function getIrbanAttribute(): ?string
    {
        $tim = $this->auditProgramDetail?->tim;
        if (! empty($tim) && $tim !== '-') {
            return \Illuminate\Support\Str::startsWith(strtolower(trim($tim)), ['irban', 'tim']) ? trim($tim) : 'Irban ' . trim($tim);
        }

        $namaTim = $this->nama_tim;
        if (! empty($namaTim) && $namaTim !== '-') {
            return \Illuminate\Support\Str::startsWith(strtolower(trim($namaTim)), ['irban', 'tim']) ? trim($namaTim) : 'Irban ' . trim($namaTim);
        }

        $dalnis = $this->pengendaliTeknis;
        if ($dalnis) {
            $unit = $dalnis->unit_kerja ?: $dalnis->jabatan;
            if ($unit && str_contains(strtolower($unit), 'irban')) {
                return $unit;
            }
        }

        if (! empty($this->pengendali_teknis) && str_contains(strtolower($this->pengendali_teknis), 'irban')) {
            return $this->pengendali_teknis;
        }

        return null;
    }

    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['super_admin', 'kepala_inspektorat', 'staff_inspektorat'])) {
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

            return $query->where(function ($q) use ($unitIds) {
                $q->whereHas('unitDiperiksas', fn ($uq) => $uq->whereIn('unit_diperiksas.id', $unitIds))
                    ->orWhereHas('lhps', fn ($lq) => $lq->whereIn('unit_diperiksa_id', $unitIds));
            });
        }

        // Jika Irban / Pengendali Teknis
        if ($user->hasAnyRole([User::ROLE_PENGENDALI_TEKNIS, 'irban']) || $user->isPengendaliTeknis() || $user->isIrban()) {
            $irbanNames = $user->getIrbanTeamNames();

            return $query->where(function ($q) use ($user, $irbanNames) {
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
                    ->orWhereHas('members', fn ($mq) => $mq->where('user_id', $user->id))
                    ->orWhereHas('lhps', fn ($lq) => $lq->where('created_by', $user->id));
            });
        }

        // Auditor (Ketua Tim / Anggota / Staf)
        return $query->where(function ($q) use ($user) {
            $q->where('ketua_tim_id', $user->id)
                ->orWhere('pengendali_teknis_id', $user->id)
                ->orWhere('pengendali_teknis', $user->name)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('members', fn ($mq) => $mq->where('user_id', $user->id))
                ->orWhereHas('lhps', fn ($lq) => $lq->where('created_by', $user->id));
        });
    }

    // ── Route Model Binding & UUID ────────────────────────────────────────────

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

        // Backward compatibility fallback jika dipanggil dengan numeric ID
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
    }
}

