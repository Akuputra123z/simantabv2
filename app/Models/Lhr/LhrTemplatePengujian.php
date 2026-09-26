<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LhrTemplatePengujian extends Model
{
    protected $table = 'lhr_template_pengujians';

    protected $fillable = [
        'lhr_template_id',
        'parent_id',
        'kode',
        'judul',
        'grup',
        'urutan',
        'opsi_simpulan',
        'teks_simpulan_default',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'opsi_simpulan' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LhrTemplate::class, 'lhr_template_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan');
    }
}
