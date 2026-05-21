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
        $lhpQuery = Lhp::forUser($user);

        // Combined LHP stats — 1 query instead of 3
        $lhpStats = (clone $lhpQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status IN ('final','ditandatangani') THEN 1 ELSE 0 END) as final
        ")->first();
        $totalLhp = $lhpStats->total;
        $lhpDraft = $lhpStats->draft;
        $lhpFinal = $lhpStats->final;

        // Ambil semua lhp_id yang bisa diakses user (1 query)
        $lhpIds = (clone $lhpQuery)->pluck('id');

        // Combined Temuan stats — 1 query instead of 4
        $temuanStats = Temuan::whereIn('lhp_id', $lhpIds)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status_tl = 'belum_ditindaklanjuti' THEN 1 ELSE 0 END) as belum,
                SUM(CASE WHEN status_tl = 'dalam_proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status_tl = 'selesai' THEN 1 ELSE 0 END) as selesai
            ")->first();
        $totalTemuan  = $temuanStats->total;
        $temuanBelum  = $temuanStats->belum;
        $temuanProses = $temuanStats->proses;
        $temuanSelesai = $temuanStats->selesai;

        // Combined Rekomendasi stats — 1 query instead of 5
        $rekomStats = Recommendation::whereHas('temuan', fn($q) => $q->whereIn('lhp_id', $lhpIds))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'belum_ditindaklanjuti' THEN 1 ELSE 0 END) as belum,
                SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
            ")->first();
        $totalRekom  = $rekomStats->total;
        $rekomBelum  = $rekomStats->belum;
        $rekomProses = $rekomStats->proses;
        $rekomSelesai = $rekomStats->selesai;

        // Combined LhpStatistik — 1 query instead of 4
        $statAgg = LhpStatistik::whereIn('lhp_id', $lhpIds)
            ->selectRaw("
                SUM(total_kerugian) as total_kerugian,
                SUM(total_nilai_tl_selesai) as total_tl_selesai,
                SUM(total_sisa_kerugian) as total_sisa,
                AVG(persen_selesai_gabungan) as avg_persen
            ")->first();
        $totalKerugian  = $statAgg->total_kerugian ?? 0;
        $totalTlSelesai = $statAgg->total_tl_selesai ?? 0;
        $totalSisa      = $statAgg->total_sisa ?? 0;
        $avgProgress    = round($statAgg->avg_persen ?? 0, 1);

        // ── Tabel LHP Terbaru ─────────────────────────────────────────────
        $lhpTerbaru = (clone $lhpQuery)
            ->with('statistik')
            ->latest('tanggal_lhp')
            ->limit(5)
            ->get();

        // ── TL Jatuh Tempo (30 hari ke depan) ─────────────────────────────
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
        $totalUser   = null;
        $userPerRole = null;

        if ($user->hasRole('super_admin')) {
            $totalUser = User::count();
            $userPerRole = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', (new User)->getMorphClass())
                ->select('roles.name', DB::raw('COUNT(*) as total'))
                ->groupBy('roles.name')
                ->pluck('total', 'name');
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