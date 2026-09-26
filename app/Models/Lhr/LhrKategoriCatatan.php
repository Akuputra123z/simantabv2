<?php

namespace App\Models\Lhr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LhrKategoriCatatan extends Model
{
    protected $table = 'lhr_kategori_catatans';

    protected $fillable = [
        'kode',
        'nama',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function catatans(): HasMany
    {
        return $this->hasMany(LhrCatatan::class, 'kategori_id');
    }
}
