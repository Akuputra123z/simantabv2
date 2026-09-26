<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhrSection extends Model
{
    protected $table = 'lhr_sections';

    protected $fillable = [
        'lhr_id',
        'kunci',
        'judul',
        'isi',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function lhr(): BelongsTo
    {
        return $this->belongsTo(Lhr::class, 'lhr_id');
    }
}
