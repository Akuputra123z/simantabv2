<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DasarPeraturan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dasar_peraturans';

    protected $fillable = [
        'nama',
        'nomor',
        'tahun',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan', 'asc')->orderBy('id', 'asc');
    }

    public function auditAssignments(): BelongsToMany
    {
        return $this->belongsToMany(
            AuditAssignment::class,
            'audit_assignment_dasar_peraturan',
            'dasar_peraturan_id',
            'audit_assignment_id'
        )->withPivot('urutan');
    }
}
