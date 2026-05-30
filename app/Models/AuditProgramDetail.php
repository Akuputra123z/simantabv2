<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditProgramDetail extends Model
{
    protected $table = 'audit_program_details';

    protected $fillable = [
        'audit_program_id',
        'nama_detail_program',
        'jenis_kegiatan',
        'objek_pengawasan',
        'ruang_lingkup',
        'personil',
        'tujuan',
        'anggaran',
        'tingkat_resiko',
        'laporan_akhir',
        'jadwal',
        'tim',
        'status',
    ];

    protected $casts = [
        'anggaran' => 'decimal:2',
    ];

    /**
     * Relasi ke Program Kerja Utama (PKPT)
     */
    public function parentProgram(): BelongsTo
    {
        return $this->belongsTo(AuditProgram::class, 'audit_program_id');
    }

    /**
     * Relasi ke Surat Tugas / Assignment
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class, 'audit_program_detail_id');
    }

    /**
     * Accessor Progress Otomatis — pakai assignments_count bila sudah di-load
     */
    public function getProgressAttribute(): float
    {
        $total = array_key_exists('assignments_count', $this->attributes)
            ? (int) $this->attributes['assignments_count']
            : $this->assignments()->count();

        if ($total === 0) return 0;

        $selesai = array_key_exists('assignments_selesai_count', $this->attributes)
            ? (int) $this->attributes['assignments_selesai_count']
            : $this->assignments()->where('status', 'selesai')->count();

        return round(($selesai / $total) * 100, 2);
    }
    public function auditProgram()
{
    return $this->belongsTo(AuditProgram::class, 'audit_program_id');
}
public function assignment()
{
    return $this->hasOne(AuditAssignment::class, 'audit_program_detail_id');
}
}