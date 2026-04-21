<?php

namespace App\Http\Controllers;

use App\Models\Lhp;
use App\Models\Temuan;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\LhpStatistik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // ── Base query LHP sesuai role ─────────────────────────────────────
        $lhpQuery = Lhp::with('statistik')->forUser($user);

        // ── Statistik Utama LHP ────────────────────────────────────────────
        $totalLhp     = (clone $lhpQuery)->count();
        $lhpDraft     = (clone $lhpQuery)->where('status', 'draft')->count();
        $lhpFinal     = (clone $lhpQuery)->whereIn('status', ['final', 'ditandatangani'])->count();

        // Ambil semua lhp_id yang bisa diakses user
        $lhpIds = (clone $lhpQuery)->pluck('id');

        // ── Statistik Temuan ───────────────────────────────────────────────
        $temuanQuery      = Temuan::whereIn('lhp_id', $lhpIds);
        $totalTemuan      = (clone $temuanQuery)->count();
        $temuanBelum      = (clone $temuanQuery)->where('status_tl', 'belum_ditindaklanjuti')->count();
        $temuanProses     = (clone $temuanQuery)->where('status_tl', 'dalam_proses')->count();
        $temuanSelesai    = (clone $temuanQuery)->where('status_tl', 'selesai')->count();

        // ── Statistik Rekomendasi ──────────────────────────────────────────
        $rekomIds         = Temuan::whereIn('lhp_id', $lhpIds)->pluck('id');
        $rekomQuery       = Recommendation::whereIn('temuan_id', $rekomIds);
        $totalRekom       = (clone $rekomQuery)->count();
        $rekomBelum       = (clone $rekomQuery)->where('status', 'belum_ditindaklanjuti')->count();
        $rekomProses      = (clone $rekomQuery)->where('status', 'proses')->count();
        $rekomSelesai     = (clone $rekomQuery)->where('status', 'selesai')->count();

        // ── Total Kerugian (dari statistik cache) ─────────────────────────
        $totalKerugian    = LhpStatistik::whereIn('lhp_id', $lhpIds)->sum('total_kerugian');
        $totalTlSelesai   = LhpStatistik::whereIn('lhp_id', $lhpIds)->sum('total_nilai_tl_selesai');
        $totalSisa        = LhpStatistik::whereIn('lhp_id', $lhpIds)->sum('total_sisa_kerugian');

        // ── Progress rata-rata ─────────────────────────────────────────────
        $avgProgress      = LhpStatistik::whereIn('lhp_id', $lhpIds)->avg('persen_selesai_gabungan') ?? 0;

        // ── Tabel LHP Terbaru ─────────────────────────────────────────────
        $lhpTerbaru = (clone $lhpQuery)
            ->with(['auditAssignment.auditProgram', 'statistik'])
            ->latest('tanggal_lhp')
            ->limit(5)
            ->get();

        // ── TL Jatuh Tempo (7 hari ke depan) ─────────────────────────────
        $tlJatuhTempo = TindakLanjut::with([
                'recommendation.temuan.lhp',
                'recommendation:id,temuan_id,uraian_rekom,nilai_rekom,jenis_rekomendasi',
            ])
            ->whereHas('recommendation.temuan', fn ($q) => $q->whereIn('lhp_id', $lhpIds))
            ->whereNotIn('status_verifikasi', ['lunas'])
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(30))
            ->orderBy('tanggal_jatuh_tempo')
            ->limit(8)
            ->get();

        // ── Grafik Progress per Bulan (12 bulan terakhir) ─────────────────
        $grafikData = LhpStatistik::whereIn('lhp_id', $lhpIds)
            ->join('lhps', 'lhps.id', '=', 'lhp_statistik.lhp_id')
            ->whereYear('lhps.tanggal_lhp', now()->year)
            ->selectRaw('MONTH(lhps.tanggal_lhp) as bulan, AVG(persen_selesai_gabungan) as avg_persen, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Format 12 bulan (isi 0 jika tidak ada data)
        $bulanLabels  = [];
        $bulanPersen  = [];
        $bulanJumlah  = [];
        for ($m = 1; $m <= 12; $m++) {
            $bulanLabels[] = \Carbon\Carbon::create(null, $m)->translatedFormat('M');
            $bulanPersen[] = round($grafikData->get($m)?->avg_persen ?? 0, 1);
            $bulanJumlah[] = $grafikData->get($m)?->jumlah ?? 0;
        }

        // ── Data tambahan untuk super_admin ───────────────────────────────
        $totalUser    = null;
        $userPerRole  = null;

        if ($user->hasRole('super_admin')) {
            $totalUser   = User::count();
            $userPerRole = User::select('name')
                ->get()
                ->groupBy(fn ($u) => $u->getRoleNames()->first() ?? 'tanpa_role')
                ->map->count();
        }

        return view('dashboard', compact(
            'user',
            'totalLhp', 'lhpDraft', 'lhpFinal',
            'totalTemuan', 'temuanBelum', 'temuanProses', 'temuanSelesai',
            'totalRekom', 'rekomBelum', 'rekomProses', 'rekomSelesai',
            'totalKerugian', 'totalTlSelesai', 'totalSisa',
            'avgProgress',
            'lhpTerbaru', 'tlJatuhTempo',
            'bulanLabels', 'bulanPersen', 'bulanJumlah',
            'totalUser', 'userPerRole',
        ));
    }
}