<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AuditProgram extends Model
{
    use SoftDeletes;

    const KATEGORI = ['PKPT', 'BPK', 'BPKP', 'ITPROV', 'ITDA', 'LAINNYA'];

    const APPROVAL_DRAFT = 'draft';

    const APPROVAL_MENUNGGU = 'menunggu';

    const APPROVAL_DISETUJUI = 'disetujui';

    const APPROVAL_DITOLAK = 'ditolak';

    /**
     * Mendapatkan opsi kategori untuk form dan filter (PKPT dikecualikan secara default).
     */
    public static function getKategoriOptions(bool $includePkpt = false): array
    {
        if ($includePkpt) {
            return self::KATEGORI;
        }

        return array_values(array_diff(self::KATEGORI, ['PKPT']));
    }

    protected $fillable = [
        'uuid',
        'nama_program',
        'tahun',
        'kategori',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'approved_pdf',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected $attributes = [
        'kategori' => 'PKPT',
        'status' => 'draft',
    ];

    /**
     * Pastikan kategori selalu mengembalikan string valid (default 'PKPT').
     */
    public function getKategoriAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        $nama = strtoupper((string) $this->nama_program);
        foreach (self::KATEGORI as $kat) {
            if ($kat !== 'LAINNYA' && str_contains($nama, $kat)) {
                return $kat;
            }
        }

        return 'PKPT';
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function details(): HasMany
    {
        return $this->hasMany(AuditProgramDetail::class);
    }

    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(
            AuditAssignment::class,
            AuditProgramDetail::class,
            'audit_program_id',
            'audit_program_detail_id',
            'id',
            'id'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Accessors (Logic Perhitungan Progress Otomatis) ─────────────────────

    /**
     * Target otomatis dihitung dari total baris penugasan yang sudah diinput
     */
    public function getTargetAssignmentAttribute(): int
    {
        if (array_key_exists('details_count', $this->attributes)) {
            return (int) $this->attributes['details_count'];
        }

        return $this->details()->count();
    }

    /**
     * Realisasi dihitung dari jumlah penugasan yang statusnya 'selesai' atau 'LHP'
     * Sesuaikan string status ('selesai'/'lhp') dengan data di database Anda
     */
    public function getSudahLhpAttribute(): int
    {
        if (array_key_exists('sudah_lhp_count', $this->attributes)) {
            return (int) $this->attributes['sudah_lhp_count'];
        }

        return $this->details()
            ->where(function ($q) {
                $q->whereHas('assignments.lhps', function ($q) {
                    $q->whereIn('status', ['final', 'ditandatangani'])
                        ->whereHas('statistik', function ($s) {
                            $s->where('persen_selesai_gabungan', 100);
                        });
                })->orWhereHas('assignments', function ($q) {
                    $q->where('status', 'selesai');
                });
            })
            ->count();
    }

    /**
     * Progress Persen: (Penugasan Selesai / Total Penugasan Diinput) * 100
     */
    public function getProgressPersenAttribute(): int
    {
        $total = $this->target_assignment;

        if ($total <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->sudah_lhp / $total) * 100));
    }

    /**
     * Status Dinamis: Otomatis berubah berdasarkan progress
     */
    public function getStatusDinamisAttribute(): string
    {
        $progress = $this->progress_persen;
        $total = $this->target_assignment;

        if ($total === 0) {
            return 'draft';
        }
        if ($progress >= 100) {
            return 'selesai';
        }

        return 'berjalan';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_DISETUJUI;
    }

    public function isWaiting(): bool
    {
        return $this->approval_status === self::APPROVAL_MENUNGGU;
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Program yang masih relevan untuk input tindak lanjut:
     * status kolom bukan 'selesai' DAN masih punya rekomendasi yang belum masuk TL.
     */
    public function scopeAktifUntukTindakLanjut(Builder $q): Builder
    {
        return $q
            ->where('status', '!=', 'selesai')
            ->whereHas(
                'details.assignments.lhps.temuans.recommendations',
                fn ($r) => $r->whereDoesntHave('tindakLanjuts')
            );
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

