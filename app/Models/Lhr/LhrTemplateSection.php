<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhrTemplateSection extends Model
{
    protected $table = 'lhr_template_sections';

    protected $fillable = [
        'lhr_template_id',
        'kunci',
        'judul',
        'isi_default',
        'urutan',
        'wajib',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'wajib' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LhrTemplate::class, 'lhr_template_id');
    }
}
