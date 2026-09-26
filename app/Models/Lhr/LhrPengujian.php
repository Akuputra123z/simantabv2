<?php

namespace App\Models\Lhr;

use App\Enums\SimpulanStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LhrPengujian extends Model
{
    protected $table = 'lhr_pengujians';

    protected $fillable = [
        'lhr_id',
        'template_pengujian_id',
        'parent_id',
        'kode',
        'judul',
        'grup',
        'urutan',
        'status_simpulan',
        'teks_simpulan',
        'ditugaskan_ke',
        'selesai_pada',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'status_simpulan' => SimpulanStatus::class,
        'selesai_pada' => 'datetime',
    ];

    public function lhr(): BelongsTo
    {
        return $this->belongsTo(Lhr::class, 'lhr_id');
    }

    public function templatePengujian(): BelongsTo
    {
        return $this->belongsTo(LhrTemplatePengujian::class, 'template_pengujian_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan');
    }

    public function catatans(): HasMany
    {
        return $this->hasMany(LhrCatatan::class, 'lhr_pengujian_id')->orderBy('urutan');
    }

    public function ditugaskanKe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditugaskan_ke');
    }

    public function isSelesai(): bool
    {
        return $this->status_simpulan !== SimpulanStatus::BelumDinilai;
    }
}
