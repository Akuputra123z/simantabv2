<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Services\LhpStatistikService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class OpdDashboardController extends Controller
{
    public function __construct(
        private readonly LhpStatistikService $statistikService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');

        $lhpIds = \App\Models\Lhp::whereIn('unit_diperiksa_id', $unitIds)->pluck('id');
        $tlQuery = TindakLanjut::query()->forOpd($user);

        $opdStats = (clone $tlQuery)->selectRaw("
            SUM(CASE WHEN status_opd IS NULL THEN 1 ELSE 0 END) AS belum_upload,
            SUM(CASE WHEN status_opd = 'draft' THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN status_opd = 'dikirim' THEN 1 ELSE 0 END) AS dikirim,
            SUM(CASE WHEN alasan_tolak_opd IS NOT NULL THEN 1 ELSE 0 END) AS ditolak
        ")->first();

        $verifikasiStats = (clone $tlQuery)->selectRaw("
            SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) AS lunas,
            SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) AS berjalan,
            SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' THEN 1 ELSE 0 END) AS menunggu
        ")->first();

        $overdue = (clone $tlQuery)
            ->whereNotNull('tanggal_jatuh_tempo')
            ->where('tanggal_jatuh_tempo', '<=', now()->addDays(7))
            ->with(['recommendation.temuan.lhp.unitDiperiksa', 'recommendation.kodeRekomendasi'])
            ->orderBy('tanggal_jatuh_tempo')
            ->limit(10)
            ->get();

        $recent = (clone $tlQuery)
            ->whereNotNull('status_opd')
            ->with(['uploadOpdOleh', 'recommendation.temuan.lhp.unitDiperiksa'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $rekapitulasi = null;
        if ($lhpIds->isNotEmpty()) {
            $rekapitulasi = \App\Models\LhpStatistik::whereIn('lhp_id', $lhpIds)
                ->selectRaw("
                    COALESCE(SUM(total_rekomendasi), 0) AS total_rekom,
                    COALESCE(SUM(rekom_selesai), 0) AS rekom_selesai,
                    COALESCE(SUM(total_kerugian), 0) AS total_kerugian,
                    COALESCE(SUM(total_nilai_tl_selesai), 0) AS total_tl_selesai
                ")->first();
        }

        return view('pages.opd.dashboard', compact(
            'opdStats',
            'verifikasiStats',
            'overdue',
            'recent',
            'rekapitulasi',
        ));
    }
}
