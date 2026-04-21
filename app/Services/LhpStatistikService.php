<?php

namespace App\Services;

use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Events\LhpStatistikUpdated;
use Illuminate\Support\Facades\DB;

class LhpStatistikService
{
    /**
     * Guard mencegah kalkulasi berulang dalam satu request.
     * Tanpa ini, satu aksi user bisa trigger updateStatistik() 3-5x.
     */
    private static array $processing = [];

    public function hitung($lhp): void
    {
        $lhpId = $lhp instanceof Lhp ? $lhp->id : $lhp;
        $this->updateStatistik($lhpId);
    }

    public function updateStatistik(int $lhpId): void
    {
        // Cegah double-call dalam satu request
        if (isset(self::$processing[$lhpId])) {
            return;
        }

        self::$processing[$lhpId] = true;

        try {
            DB::transaction(function () use ($lhpId) {
                $lhp = Lhp::with([
                    'temuans.recommendations.tindakLanjuts.cicilans',
                ])->find($lhpId);

                if (! $lhp) {
                    return;
                }

                $allRekom   = $lhp->temuans->flatMap->recommendations;
                $totalRekom = $allRekom->count();

                if ($totalRekom === 0) {
                    $this->resetStatistik($lhpId);
                    event(new LhpStatistikUpdated($lhp));
                    return;
                }

                $bobotPerItem        = 100 / $totalRekom;
                $totalProgress       = 0;
                $rekomUangTotal      = 0;
                $rekomUangSelesai    = 0;
                $rekomNonuangTotal   = 0;
                $rekomNonuangSelesai = 0;

                foreach ($allRekom as $rekom) {
                    $tls        = $rekom->tindakLanjuts;
                    $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);

                    if ($rekom->isUang()) {
                        $rekomUangTotal++;
                        $nilaiLunas = 0;

                        foreach ($tls as $tl) {
                            if ($tl->jenis_penyelesaian === 'cicilan') {
                                $nilaiLunas += (float) $tl->cicilans
                                    ->where('status', 'diterima')
                                    ->sum('nilai_bayar');
                            } elseif ($tl->status_verifikasi === 'lunas') {
                                $nilaiLunas += (float) ($tl->nilai_tindak_lanjut ?? 0);
                            }
                        }

                        if ($nilaiRekom > 0) {
                            $persen         = min($nilaiLunas / $nilaiRekom, 1.0);
                            $totalProgress += $persen * $bobotPerItem;
                        }

                        if ($nilaiRekom > 0 && $nilaiLunas >= $nilaiRekom) {
                            $rekomUangSelesai++;
                        }

                    } else {
                        $rekomNonuangTotal++;
                        $hasLunas = $tls->where('status_verifikasi', 'lunas')->isNotEmpty();

                        if ($hasLunas) {
                            $totalProgress += $bobotPerItem;
                            $rekomNonuangSelesai++;
                        }
                    }
                }

                // Hitung rekom_selesai & rekom_proses dari data TL langsung
                // (bukan dari kolom status yang mungkin stale)
                $rekomSelesai = $rekomUangSelesai + $rekomNonuangSelesai;

                $rekomProses = $allRekom->filter(function ($r) {
                    $tls = $r->tindakLanjuts;
                    $hasLunas = $tls->where('status_verifikasi', 'lunas')->isNotEmpty();
                    if ($hasLunas) return false; // sudah selesai, bukan proses
                    return $tls->whereIn('status_verifikasi', [
                        'menunggu_verifikasi', 'berjalan',
                    ])->isNotEmpty();
                })->count();

                $rekomBelum = max(0, $totalRekom - $rekomSelesai - $rekomProses);

                $totalKerugian = (float) $lhp->temuans->sum(fn ($t) =>
                    (float) $t->nilai_kerugian_negara   +
                    (float) $t->nilai_kerugian_daerah   +
                    (float) $t->nilai_kerugian_desa     +
                    (float) $t->nilai_kerugian_bos_blud
                );

                $persenCount = $totalRekom > 0
                    ? round($rekomSelesai / $totalRekom * 100, 2)
                    : 0;

                LhpStatistik::updateOrCreate(
                    ['lhp_id' => $lhpId],
                    [
                        'total_temuan'            => $lhp->temuans->count(),
                        'total_rekomendasi'       => $totalRekom,
                        'rekom_selesai'           => $rekomSelesai,
                        'rekom_proses'            => $rekomProses,
                        'rekom_belum'             => $rekomBelum,
                        'rekom_uang_total'        => $rekomUangTotal,
                        'rekom_uang_selesai'      => $rekomUangSelesai,
                        'rekom_nonutang_total'    => $rekomNonuangTotal,
                        'rekom_nonutang_selesai'  => $rekomNonuangSelesai,
                        'total_kerugian'          => $totalKerugian,
                        'total_nilai_tl_selesai'  => (float) $allRekom->sum('nilai_tl_selesai'),
                        'total_sisa_kerugian'     => (float) $allRekom->sum('nilai_sisa'),
                        'persen_selesai'          => $persenCount,
                        'persen_selesai_gabungan' => round(min($totalProgress, 100), 2),
                        'dihitung_pada'           => now(),
                    ]
                );

                // Langsung pakai $lhp — jangan fresh() agar tidak load data stale
                event(new LhpStatistikUpdated($lhp));
            });
        } finally {
            unset(self::$processing[$lhpId]);
        }
    }

    private function resetStatistik(int $lhpId): void
    {
        LhpStatistik::updateOrCreate(
            ['lhp_id' => $lhpId],
            [
                'total_temuan'            => 0,
                'total_rekomendasi'       => 0,
                'rekom_selesai'           => 0,
                'rekom_proses'            => 0,
                'rekom_belum'             => 0,
                'rekom_uang_total'        => 0,
                'rekom_uang_selesai'      => 0,
                'rekom_nonutang_total'    => 0,
                'rekom_nonutang_selesai'  => 0,
                'total_kerugian'          => 0,
                'total_nilai_tl_selesai'  => 0,
                'total_sisa_kerugian'     => 0,
                'persen_selesai'          => 0,
                'persen_selesai_gabungan' => 0,
                'dihitung_pada'           => now(),
            ]
        );
    }
}