<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhrTemplateDasarHukum extends Model
{
    protected $table = 'lhr_template_dasar_hukums';

    protected $fillable = [
        'lhr_template_id',
        'urutan',
        'teks',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LhrTemplate::class, 'lhr_template_id');
    }
}
