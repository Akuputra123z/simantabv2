<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorFormat extends Model
{
    protected $fillable = [
        'jenis',
        'pola',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];
}
