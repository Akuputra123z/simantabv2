<?php

namespace App\Models;

use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Traits\HasActivityLog;

class AuditProgram extends Model
{
    use HasFactory, SoftDeletes, HasCreatedUpdatedBy, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'nama_program', 'tahun', 'status',
        'created_by', 'updated_by', 'target_assignment',
    ];

    // ❌ HAPUS $appends — ini penyebab N+1 query
    // protected $appends = ['realisasi_assignment', 'sudah_lhp', 'sisa_target', 'progress'];

    protected function casts(): array
    {
        return [
            'tahun'             => 'integer',
            'target_assignment' => 'integer',
            'deleted_at'        => 'datetime',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->where('status', 'berjalan');
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function assignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class, 'audit_program_id');
    }

    public function lhps(): HasManyThrough
    {
        return $this->hasManyThrough(
            Lhp::class,
            AuditAssignment::class,
            'audit_program_id',
            'audit_assignment_id'
        );
    }

    // ── Accessors — hanya dipakai saat relasi sudah di-eager load ─────────────

    /**
     * Total assignment yang terealisasi.
     * Gunakan withCount('assignments') di controller agar tidak N+1.
     */
    public function getRealisasiAssignmentAttribute(): int
    {
        // ✅ Prioritaskan withCount hasil eager load
        if (isset($this->attributes['assignments_count'])) {
            return (int) $this->attributes['assignments_count'];
        }
        return $this->assignments()->count();
    }

    /**
     * Assignment yang sudah punya LHP.
     * Gunakan withCount(['assignments as sudah_lhp_count' => ...]) di controller.
     */
    public function getSudahLhpAttribute(): int
    {
        if (isset($this->attributes['sudah_lhp_count'])) {
            return (int) $this->attributes['sudah_lhp_count'];
        }
        return $this->assignments()->has('lhps')->count();
    }

    public function getSisaTargetAttribute(): int
    {
        return max(0, ($this->target_assignment ?? 0) - $this->realisasi_assignment);
    }

    /**
     * Progress = assignment selesai TL / total assignment (bukan vs target).
     * Gunakan withCount(['assignments as assignments_selesai_count' => ...]) di controller.
     */
    public function getProgressAttribute(): float
    {
        $total = $this->realisasi_assignment;
        if ($total === 0) return 0.0;

        $selesai = isset($this->attributes['assignments_selesai_count'])
            ? (int) $this->attributes['assignments_selesai_count']
            : $this->assignments()->where('status', 'selesai')->count();

        return round(($selesai / $total) * 100, 1);
    }
}