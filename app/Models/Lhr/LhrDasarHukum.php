<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhrDasarHukum extends Model
{
    protected $table = 'lhr_dasar_hukums';

    protected $fillable = [
        'lhr_id',
        'urutan',
        'teks',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function lhr(): BelongsTo
    {
        return $this->belongsTo(Lhr::class, 'lhr_id');
    }
}
