<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'pangkat_gol',
        'jabatan',
        'pendidikan_terakhir',
        'jenis_kelamin',
        'unit_kerja',
        'phone',
        'avatar',
        'signature',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Constants — daftar role yang valid di sistem ───────────────────────────

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_KEPALA_INSPEKTORAT = 'kepala_inspektorat';

    public const ROLE_IRBAN = 'irban';

    public const ROLE_PENGENDALI_TEKNIS = 'pengendali_teknis';

    public const ROLE_KETUA_TIM = 'ketua_tim';

    public const ROLE_ANGGOTA = 'anggota';

    public const ROLE_STAFF_INSPEKTORAT = 'staff_inspektorat';

    public const ROLE_OPD = 'opd';

    public const JABATAN_OPTIONS = [
        'Inspektur',
        'Sekdin',
        'Irban I',
        'Irban II',
        'Irban III',
        'Irban IV',
        'Irbansus',
        'Auditor',
        'Pengawas',
        'Staf',
    ];

    public const UNIT_KERJA_OPTIONS = [
        'Inspektur',
        'Sekretariat',
        'Irban I',
        'Irban II',
        'Irban III',
        'Irban IV',
        'Irban Sus',
    ];

    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_KEPALA_INSPEKTORAT => 'Kepala Inspektorat',
        self::ROLE_IRBAN => 'Irban (Inspektur Pembantu)',
        self::ROLE_PENGENDALI_TEKNIS => 'Pengendali Teknis (Irban)',
        self::ROLE_KETUA_TIM => 'Ketua Tim',
        self::ROLE_ANGGOTA => 'Anggota',
        self::ROLE_STAFF_INSPEKTORAT => 'Staff Inspektorat',
        self::ROLE_OPD => 'OPD',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->role($role); // Spatie scope
    }

    public function scopeInspektorat($query)
    {
        return $query->role([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_KEPALA_INSPEKTORAT,
            self::ROLE_IRBAN,
            self::ROLE_PENGENDALI_TEKNIS,
            self::ROLE_KETUA_TIM,
            self::ROLE_ANGGOTA,
            self::ROLE_STAFF_INSPEKTORAT,
        ]);
    }

    public function scopeOpd($query)
    {
        return $query->role(self::ROLE_OPD);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Inisial nama untuk avatar.
     */
    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn ($n) => Str::substr($n, 0, 1))
            ->take(2)
            ->implode('');
    }

    /**
     * Label role pertama yang dimiliki user.
     */
    public function getRoleNameAttribute(): string
    {
        $role = $this->getRoleNames()->first();

        return self::ROLES[$role] ?? ucfirst(str_replace('_', ' ', $role ?? '-'));
    }

    /**
     * Cek apakah user bisa mengakses fitur audit lapangan
     * (hanya ketua tim dan anggota yang terlibat langsung).
     */
    public function isAuditor(): bool
    {
        return $this->hasAnyRole([self::ROLE_KETUA_TIM, self::ROLE_ANGGOTA]);
    }

    /**
     * Cek apakah user bisa memverifikasi tindak lanjut.
     */
    public function canVerify(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_KEPALA_INSPEKTORAT,
            self::ROLE_IRBAN,
            self::ROLE_PENGENDALI_TEKNIS,
            self::ROLE_KETUA_TIM,
        ]);
    }

    public function isPengendaliTeknis(): bool
    {
        return $this->hasAnyRole([self::ROLE_IRBAN, self::ROLE_PENGENDALI_TEKNIS]);
    }

    public function isOpd(): bool
    {
        return $this->hasRole(self::ROLE_OPD);
    }

    public function isIrban(): bool
    {
        if ($this->hasAnyRole([self::ROLE_IRBAN, self::ROLE_PENGENDALI_TEKNIS])) {
            return true;
        }

        // Jika user memiliki role operasional ketua tim, anggota, staf, atau opd, maka dia bukan pimpinan Irban
        if ($this->hasAnyRole([self::ROLE_KETUA_TIM, self::ROLE_ANGGOTA, self::ROLE_STAFF_INSPEKTORAT, self::ROLE_OPD])) {
            return false;
        }

        $jabatan = strtoupper((string) $this->jabatan);

        return str_starts_with($jabatan, 'IRBAN')
            || str_contains($jabatan, 'INSPEKTUR PEMBANTU')
            || str_contains($jabatan, 'IRBANSUS');
    }

    /**
     * Dapatkan daftar variasi nama tim Irban untuk user ini jika user adalah Irban / Pengendali Teknis.
     * Digunakan untuk pencocokan kolom audit_program_details.tim.
     *
     * @return array<string>
     */
    public function getIrbanTeamNames(): array
    {
        $unit = trim((string) ($this->unit_kerja ?: $this->jabatan));
        $normalized = strtolower(str_replace([' ', '-', '_', '.'], '', $unit));

        if (str_contains($normalized, 'irbaniii') || str_contains($normalized, 'irban3')) {
            return ['Irban III', 'IRBAN III', 'Irban 3'];
        }

        if (str_contains($normalized, 'irbanii') || str_contains($normalized, 'irban2')) {
            return ['Irban II', 'IRBAN II', 'Irban 2'];
        }

        if (str_contains($normalized, 'irbaniv') || str_contains($normalized, 'irban4')) {
            return ['Irban IV', 'IRBAN IV', 'Irban 4'];
        }

        if (str_contains($normalized, 'irbansus') || str_contains($normalized, 'khusus')) {
            return ['Irban Sus', 'Irbansus', 'IRBANSUS', 'Irban-Sus', 'IRBAN SUS'];
        }

        if (str_contains($normalized, 'irbani') || str_contains($normalized, 'irban1')) {
            return ['Irban I', 'IRBAN I', 'Irban 1'];
        }

        return $unit ? [$unit] : [];
    }

    public function opdUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            UnitDiperiksa::class,
            'opd_user_units',
            'user_id',
            'unit_diperiksa_id'
        )->withTimestamps();
    }
}
