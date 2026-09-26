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
        'persen',
        'dihitung_pada',
    ];

    protected $casts = [
        'total_rekomendasi' => 'integer',
        'selesai' => 'integer',
        'persen' => 'float',
        'dihitung_pada' => 'datetime',
    ];

    public function lhr(): BelongsTo
    {
        return $this->belongsTo(Lhr::class, 'lhr_id');
    }
}
