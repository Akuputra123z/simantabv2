<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'audit_program_detail_id',
        'nama_tim',
        'jenis_pengawasan',
        // unit_diperiksa_id DIHAPUS — relasi lewat pivot
        'ketua_tim_id',
        'nomor_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}