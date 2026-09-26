<?php

namespace App\Models\Lhr;

use App\Models\Attachment;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LhrCatatan extends Model
{
    use SoftDeletes;

    protected $table = 'lhr_catatans';

    protected $fillable = [
        'lhr_pengujian_id',
        'kategori_id',
        'referensi_bagian',
        'isi',
        'urutan',
        'created_by',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function pengujian(): BelongsTo
    {
        return $this->belongsTo(LhrPengujian::class, 'lhr_pengujian_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(LhrKategoriCatatan::class, 'kategori_id');
    }

    public function rekomendasis(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'lhr_catatan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
