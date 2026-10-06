<?php

namespace App\Models\Lhr;

use App\Enums\LhrStatus;
use App\Models\AuditAssignment;
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
        $total = $this->pengujians()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->pengujians()
            ->where('status_simpulan', '!=', 'belum_dinilai')
            ->count();

        return round(($completed / $total) * 100, 2);
    }
}
