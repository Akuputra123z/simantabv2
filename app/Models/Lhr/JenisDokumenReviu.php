<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JenisDokumenReviu extends Model
{
    protected $table = 'jenis_dokumen_reviu';

    protected $fillable = [
        'uuid',
        'kode',
        'nama',
        'gaya_penomoran_bagian',
        'tata_letak_hasil',
        'sla_tindak_lanjut_hari',
        'aktif',
    ];

    protected $casts = [
        'sla_tindak_lanjut_hari' => 'integer',
        'aktif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function templates(): HasMany
    {
        return $this->hasMany(LhrTemplate::class, 'jenis_dokumen_reviu_id');
    }

    public function activeTemplate()
    {
        return $this->hasOne(LhrTemplate::class, 'jenis_dokumen_reviu_id')
            ->where('status', 'terbit')
            ->latestOfMany('versi');
    }
}
