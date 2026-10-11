<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhrStatistik extends Model
{
    protected $table = 'lhr_statistiks';

    protected $fillable = [
        'lhr_id',
        'total_rekomendasi',
        'selesai',
        'belum',
        'proses',
        'dikirim_opd',
        'draft_opd',
        'ditolak_opd',
        'persen',
        'persen_tl',
        'dihitung_pada',
    ];

    protected function casts(): array
    {
        return [
            'total_rekomendasi' => 'integer',
            'selesai'           => 'integer',
            'belum'             => 'integer',
            'proses'            => 'integer',
            'dikirim_opd'       => 'integer',
            'draft_opd'         => 'integer',
            'ditolak_opd'       => 'integer',
            'persen'            => 'float',
            'persen_tl'         => 'float',
            'dihitung_pada'     => 'datetime',
        ];
    }

    public function lhr(): BelongsTo
    {
        return $this->belongsTo(Lhr::class, 'lhr_id');
    }
}
