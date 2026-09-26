<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LhrTemplate extends Model
{
    protected $table = 'lhr_templates';

    protected $fillable = [
        'jenis_dokumen_reviu_id',
        'jenis_laporan',
        'versi',
        'status',
        'berlaku_mulai',
        'dasar_perubahan',
    ];

    protected $casts = [
        'versi' => 'integer',
        'berlaku_mulai' => 'date',
    ];

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(JenisDokumenReviu::class, 'jenis_dokumen_reviu_id');
    }

    public function pengujians(): HasMany
    {
        return $this->hasMany(LhrTemplatePengujian::class, 'lhr_template_id')->orderBy('urutan');
    }

    public function dasarHukums(): HasMany
    {
        return $this->hasMany(LhrTemplateDasarHukum::class, 'lhr_template_id')->orderBy('urutan');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LhrTemplateSection::class, 'lhr_template_id')->orderBy('urutan');
    }
}
