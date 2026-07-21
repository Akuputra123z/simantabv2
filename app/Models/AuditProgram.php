<?php

namespace App\Models;

use App\Models\AuditProgramDetail;
use App\Models\AuditAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditProgram extends Model
{
    use SoftDeletes;

    const KATEGORI = ['PKPT', 'BPK', 'BPKP', 'ITPROV', 'ITDA', 'LAINNYA'];

    const APPROVAL_DRAFT    = 'draft';
    const APPROVAL_MENUNGGU = 'menunggu';
    const APPROVAL_DISETUJUI = 'disetujui';
    const APPROVAL_DITOLAK  = 'ditolak';

    protected $fillable = [
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
        'tahun'       => 'integer',
        'approved_at' => 'datetime',
    ];

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
            ->where(function($q) {
                $q->whereHas('assignments.lhps', function($q) {
                    $q->whereIn('status', ['final', 'ditandatangani'])
                      ->whereHas('statistik', function($s) {
                          $s->where('persen_selesai_gabungan', 100);
                      });
                })->orWhereHas('assignments', function($q) {
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
        
        if ($total <= 0) return 0;

        return (int) min(100, round(($this->sudah_lhp / $total) * 100));
    }

    /**
     * Status Dinamis: Otomatis berubah berdasarkan progress
     */
    public function getStatusDinamisAttribute(): string
    {
        $progress = $this->progress_persen;
        $total = $this->target_assignment;

        if ($total === 0) return 'draft';
        if ($progress >= 100) return 'selesai';
        
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
}