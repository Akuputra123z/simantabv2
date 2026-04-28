<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

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
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Constants — daftar role yang valid di sistem ───────────────────────────

    public const ROLE_SUPER_ADMIN         = 'super_admin';
    public const ROLE_KEPALA_INSPEKTORAT  = 'kepala_inspektorat';
    public const ROLE_KETUA_TIM          = 'ketua_tim';
    public const ROLE_ANGGOTA            = 'anggota';
    public const ROLE_STAFF_INSPEKTORAT  = 'staff_inspektorat';

    public const ROLES = [
        self::ROLE_SUPER_ADMIN        => 'Super Admin',
        self::ROLE_KEPALA_INSPEKTORAT => 'Kepala Inspektorat',
        self::ROLE_KETUA_TIM         => 'Ketua Tim',
        self::ROLE_ANGGOTA           => 'Anggota',
        self::ROLE_STAFF_INSPEKTORAT => 'Staff Inspektorat',
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
            self::ROLE_KETUA_TIM,
        ]);
    }
}