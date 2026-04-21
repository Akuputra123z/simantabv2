<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\LhpStatistikService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasActivityLog;

class Lhp extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table      = 'lhps';
    protected $primaryKey = 'id';

    protected $fillable = [
        'audit_assignment_id', 'nomor_lhp', 'tanggal_lhp', 'semester',
        'jenis_pemeriksaan', 'catatan_umum', 'keterangan', 'irban',
        'status_batal_keterangan', 'status_batal_user_id', 'status_batal_at',
        'status', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lhp'    => 'date',
            'semester'       => 'integer',
            'deleted_at'     => 'datetime',
            'status_batal_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function auditAssignment(): BelongsTo { return $this->belongsTo(AuditAssignment::class); }
    public function creator(): BelongsTo         { return $this->belongsTo(User::class, 'created_by'); }
    public function batalUser(): BelongsTo        { return $this->belongsTo(User::class, 'status_batal_user_id'); }
    public function temuans(): HasMany            { return $this->hasMany(Temuan::class); }
    public function reports(): HasMany            { return $this->hasMany(LhpReport::class); }

    public function statistik(): HasOne
    {
        return $this->hasOne(LhpStatistik::class, 'lhp_id', 'id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function recommendations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Recommendation::class,
            Temuan::class,
            'lhp_id',
            'temuan_id',
            'id',
            'id'
        )->with('kodeRekomendasi');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeStatus(Builder $q, string $s): Builder  { return $q->where('status', $s); }
    public function scopeSemester(Builder $q, int $s): Builder   { return $q->where('semester', $s); }
    public function scopeIrban(Builder $q, string $i): Builder   { return $q->where('irban', $i); }

    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->whereHas('auditAssignment', function ($q) use ($user) {
            $q->where('ketua_tim_id', $user->id)
              ->orWhereHas('members', function ($q2) use ($user) {
                  $q2->where('user_id', $user->id);
              });
        });
    }

    public function scopeSelesai(Builder $q): Builder
    {
        return $q->whereIn('status', ['final', 'ditandatangani'])->has('auditAssignment');
    }

    public function scopeLaporanSelesai(Builder $q): Builder
    {
        return $q
            ->whereIn('status', ['final', 'ditandatangani'])
            ->has('auditAssignment')
            ->whereHas('statistik', fn (Builder $s) =>
                $s->where('persen_selesai_gabungan', 100)
            );
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Progress utama yang ditampilkan di index dan dashboard.
     * Selalu baca dari statistik (cache) jika sudah di-eager load.
     * Fallback ke 0 — jangan hitung manual agar tidak N+1.
     */
    public function getPersenSelesaiAttribute(): float
    {
        if ($this->relationLoaded('statistik') && $this->statistik) {
            return (float) $this->statistik->persen_selesai_gabungan;
        }

        // Fallback: hindari query N+1, kembalikan 0 dan load statistik di controller
        return 0.0;
    }

    public function getTotalKerugianAttribute(): float
    {
        if ($this->relationLoaded('statistik') && $this->statistik) {
            return (float) $this->statistik->total_kerugian;
        }

        if ($this->relationLoaded('temuans')) {
            return (float) $this->temuans->sum(function ($t) {
                return ($t->nilai_kerugian_negara  ?? 0)
                     + ($t->nilai_kerugian_daerah  ?? 0)
                     + ($t->nilai_kerugian_desa     ?? 0)
                     + ($t->nilai_kerugian_bos_blud ?? 0);
            });
        }

        return 0.0;
    }

    public function getStatusBatalInfoAttribute(): string
    {
        if (! $this->status_batal_at) return '-';
        $user = $this->batalUser?->name ?? 'Unknown';
        return "Dibatalkan oleh {$user} pada " . $this->status_batal_at->format('d M Y H:i') .
               ($this->status_batal_keterangan ? " (Alasan: {$this->status_batal_keterangan})" : '');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Pintu masuk tunggal untuk hitung ulang statistik.
     * Selalu panggil ini dari luar transaksi DB.
     */
    public function refreshStatistik(): void
    {
        app(LhpStatistikService::class)->updateStatistik($this->id);
    }

    /** @alias refreshStatistik() */
    public function hitung(): void
    {
        $this->refreshStatistik();
    }

    /** @deprecated Gunakan refreshStatistik() */
    public function updateStatistik(): void
    {
        $this->refreshStatistik();
    }

    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Sengaja kosong.
        // Statistik TIDAK boleh dihitung dari dalam booted() karena:
        // 1. booted() jalan di dalam transaksi — statistik baca data belum di-commit
        // 2. Menyebabkan kalkulasi berulang (3-5x per satu aksi user)
        //
        // Statistik selalu dihitung dari controller, SETELAH DB::commit().
    }
}