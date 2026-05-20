<?php

namespace App\Models;

use App\Models\AuditProgramDetail;
use App\Models\AuditAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_program', // Contoh: PKPT 2026
        'tahun',
        'status',
       
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Relasi ke 10 program detail
     */
    public function details(): HasMany
    {
        return $this->hasMany(AuditProgramDetail::class);
    }

    /**
     * Relasi tidak langsung ke Assignments melalui Details
     */
    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(
            AuditAssignment::class,
            AuditProgramDetail::class,
            'audit_program_id',        // Foreign key di tabel audit_program_details
            'audit_program_detail_id', // Foreign key di tabel audit_assignments
            'id',                      // Local key di tabel audit_programs
            'id'                       // Local key di tabel audit_program_details
        );
    }

    // ─── Accessors (Logic Perhitungan Progress Otomatis) ─────────────────────

    /**
     * Target otomatis dihitung dari total baris penugasan yang sudah diinput
     */
    public function getTargetAssignmentAttribute(): int
    {
        return $this->details()->count();
    }

    /**
     * Realisasi dihitung dari jumlah penugasan yang statusnya 'selesai' atau 'LHP'
     * Sesuaikan string status ('selesai'/'lhp') dengan data di database Anda
     */
    public function getSudahLhpAttribute(): int
    {
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
}