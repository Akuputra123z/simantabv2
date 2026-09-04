<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitDiperiksa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unit_diperiksas';

    protected $appends = ['label'];

    protected $fillable = [
        'nama_unit',
        'kategori',
        'nama_kecamatan',
        'alamat',
        'telepon',
        'keterangan',
    ];

    protected function casts(): array
    {
        return ['deleted_at' => 'datetime'];
    }

    public function scopeKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeKecamatan(Builder $query, string $kecamatan): Builder
    {
        return $query->where('nama_kecamatan', $kecamatan);
    }

    public function lhps(): HasMany
    {
        return $this->hasMany(Lhp::class, 'unit_diperiksa_id');
    }

    public function auditAssignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class, 'unit_diperiksa_id');
    }

    public function opdUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'opd_user_units',
            'unit_diperiksa_id',
            'user_id'
        )->withTimestamps();
    }

    public function getLabelAttribute(): string
    {
        return implode(' — ', array_filter([
            $this->nama_kecamatan,
            $this->nama_unit,
        ]));
    }
}