<?php

namespace App\Services;

use App\Models\Lhp;
use App\Models\LhpStatistik;
use Illuminate\Support\Facades\DB;

class AuditStatistikService
{
    public function updateStatistik(int $lhpId): void
{
    app(LhpStatistikService::class)->updateStatistik($lhpId);
}

    private function resetStatistik(int $lhpId): void
    {
        LhpStatistik::updateOrCreate(['lhp_id' => $lhpId], [
            'total_temuan' => 0,
            'total_rekomendasi' => 0,
            'persen_selesai_gabungan' => 0,
            'dihitung_pada' => now(),
        ]);
    }
}