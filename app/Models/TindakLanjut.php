<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasActivityLog;

class TindakLanjut extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'tindak_lanjuts';

    protected $fillable = [
        'recommendation_id', 'jenis_penyelesaian', 'is_cicilan', 'nilai_tindak_lanjut',
        'jumlah_cicilan_rencana', 'tanggal_mulai_cicilan', 'tanggal_jatuh_tempo',
        'nilai_per_cicilan_rencana', 'jumlah_cicilan_realisasi', 'total_terbayar',
        'sisa_belum_bayar', 'catatan_tl', 'hambatan', 'status_verifikasi',
        'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_cicilan'                => 'boolean',
            'jumlah_cicilan_rencana'    => 'integer',
            'jumlah_cicilan_realisasi'  => 'integer',
            'tanggal_mulai_cicilan'     => 'date',
            'tanggal_jatuh_tempo'       => 'date',
            'nilai_tindak_lanjut'       => 'decimal:2',
            'nilai_per_cicilan_rencana' => 'decimal:2',
            'total_terbayar'            => 'decimal:2',
            'sisa_belum_bayar'          => 'decimal:2',
            'diverifikasi_pada'         => 'datetime',
        ];
    }

    protected $attributes = [
        'nilai_tindak_lanjut' => 0,
        'total_terbayar'      => 0,
        'sisa_belum_bayar'    => 0,
        'status_verifikasi'   => 'menunggu_verifikasi',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function cicilans(): HasMany
    {
        return $this->hasMany(TindakLanjutCicilan::class, 'tindak_lanjut_id')->orderBy('ke');
    }

    public function cicilanDiterima(): HasMany
    {
        return $this->cicilans()->where('status', 'diterima');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser($query, $user)
    {
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->whereHas('recommendation.temuan.lhp.auditAssignment', function ($q) use ($user) {
            $q->where('ketua_tim_id', $user->id)
              ->orWhereHas('members', function ($q2) use ($user) {
                  $q2->where('audit_assignment_members.user_id', $user->id);
              });
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function nextKeCicilan(): int
    {
        return ($this->cicilans()->max('ke') ?? 0) + 1;
    }

    /**
     * Hitung ulang nilai kalkulasi di memory (belum disimpan).
     * Dipanggil dari booted()::saving dan dari TindakLanjutCicilan::$cascade.
     *
     * FIX: pisahkan logika uang vs non-uang (barang/administrasi).
     * Untuk non-uang, status ditentukan HANYA dari status_verifikasi
     * yang sudah di-set user — jangan di-override berdasarkan nilai uang.
     */
    public function syncCalculations(): static
    {
        $rekom     = $this->recommendation;
        $isNonUang = $rekom?->isNonUang() ?? false;
        $isCicilan = $this->jenis_penyelesaian === 'cicilan';

        if ($isNonUang) {
            // ── BARANG / ADMINISTRASI ─────────────────────────────────────────
            $isLunas = $this->status_verifikasi === 'lunas';
            $this->total_terbayar           = $isLunas ? 1 : 0;
            $this->sisa_belum_bayar         = $isLunas ? 0 : 1;
            $this->jumlah_cicilan_realisasi = 0;
        } else {
            // ── UANG ──────────────────────────────────────────────────────────
            // FIX: Gunakan nilai_tindak_lanjut (input user) sebagai basis target, 
            // jika kosong baru fallback ke nilai_rekom.
            $targetNilai = (float) ($this->nilai_tindak_lanjut > 0 
                            ? $this->nilai_tindak_lanjut 
                            : ($rekom?->nilai_rekom ?? 0));

            if ($isCicilan) {
                // Total terbayar hanya dari cicilan yang sudah diverifikasi (diterima)
                $this->total_terbayar = (float) $this->cicilanDiterima()->sum('nilai_bayar');
                $this->jumlah_cicilan_realisasi = $this->cicilanDiterima()->count();
            } else {
                // Jika Langsung, cek status verifikasi. Jika lunas, maka terbayar = target.
                if ($this->status_verifikasi === 'lunas') {
                    $this->total_terbayar = $targetNilai;
                }
                $this->jumlah_cicilan_realisasi = 0;
            }

            // Hitung sisa berdasarkan target yang ditentukan
            $this->sisa_belum_bayar = max(0, $targetNilai - $this->total_terbayar);

            // Auto-status berdasarkan pembayaran (Hanya jika bukan diset manual oleh user)
            // Jika sisa 0 dan target > 0, otomatis Lunas.
            if ($targetNilai > 0 && $this->sisa_belum_bayar <= 0) {
                $this->status_verifikasi = 'lunas';
            } elseif ($this->total_terbayar > 0 && $this->sisa_belum_bayar > 0) {
                $this->status_verifikasi = 'berjalan';
            }
        }

        return $this;
    }

    public function getIsCicilanAttribute(): bool
    {
        return $this->jenis_penyelesaian === 'cicilan';
    }

    /**
     * FIX: progress() untuk non-uang pakai status_verifikasi, bukan nilai uang.
     */
    public function progress(): float
    {
        $rekom = $this->recommendation;
        if (! $rekom) return 0;

        if ($rekom->isNonUang()) {
            // Untuk barang/administrasi: lunas = 100%, lainnya = 0%
            return $this->status_verifikasi === 'lunas' ? 100 : 0;
        }

        // Untuk uang: hitung dari total_terbayar vs nilai_rekom
        if ($this->status_verifikasi === 'lunas') return 100;

        $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);

        return $nilaiRekom > 0
            ? round($this->total_terbayar / $nilaiRekom * 100, 2)
            : 0;
    }

    public function getUraianAttribute()
    {
        return $this->catatan_tl;
    }

    public function getNilaiAttribute()
    {
        return $this->nilai_tindak_lanjut;
    }

    public function getTanggalAttribute()
    {
        return $this->tanggal_jatuh_tempo;
    }

    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Hitung kalkulasi sebelum disimpan ke DB
        static::saving(function (self $model) {
            $model->syncCalculations();
        });

        /**
         * Setelah disimpan, cascade ke Recommendation via syncStatus().
         * syncStatus() → temuan->syncStatus() → updateStatistik()
         */
        static::saved(function (self $model) {
            $rekom = $model->relationLoaded('recommendation')
                ? $model->recommendation
                : $model->recommendation()->first();

            $rekom?->syncStatus();
        });

        /**
         * Saat dihapus (soft delete), cascade agar status rekomendasi
         * dan statistik LHP ikut terupdate.
         */
        static::deleted(function (self $model) {
            $rekom = $model->recommendation()->first();
            $rekom?->syncStatus();
        });
    }
}