<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorSequence extends Model
{
    protected $fillable = [
        'jenis',
        'tahun',
        'nomor_terakhir',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'nomor_terakhir' => 'integer',
    ];
}
