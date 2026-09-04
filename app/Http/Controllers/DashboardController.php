<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use App\Models\Lhp;
use App\Models\Temuan;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\LhpStatistik;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // ── 1. Filter Tanggal & Kategori Program Audit ──────────────────────────────
        $preset           = $request->input('preset', 'this_year');
        $startDateInput   = $request->input('start_date');
        $endDateInput     = $request->input('end_date');
        $kategoriProgram  = $request->input('kategori_program', 'semua');

        $now = Carbon::now();

        switch ($preset) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
            case '7days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
            case '30days':
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                break;
            case 'custom':
                $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : $now->copy()->startOfYear();
                $endDate   = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : $now->copy()->endOfYear();
                break;
            case 'this_year':
            default:
                $preset    = 'this_year';
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                break;
        }

        // Periode pembanding (Previous Period)
        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffDays);
        $prevEndDate   = $startDate->copy()->subDay()->endOfDay();

        $cacheKey = 'dashboard_v3:user:' . $user->id . ':' . $preset . ':' . $kategoriProgram . ':' . $startDate->format('Y-m-d') . ':' . $endDate->format('Y-m-d') . ':' . $user->updated_at->timestamp;

        $data = Cache::remember($cacheKey, 300, function () use ($user, $startDate, $endDate, $prevStartDate, $prevEndDate, $kategoriProgram) {
            // ── Base query LHP ──────────────────────────────────────────────────
            $baseLhpQuery = Lhp::forUser($user);

            if (!empty($kategoriProgram) && $kategoriProgram !== 'semua') {
                $baseLhpQuery->whereHas('auditAssignment.auditProgramDetail.auditProgram', function ($q) use ($kategoriProgram) {
                    $q->where('kategori', $kategoriProgram);
                });
            }

            // Filter LHP berdasarkan tanggal LHP
            $lhpFilterQuery = (clone $baseLhpQuery)
                ->whereBetween('tanggal_lhp', [$startDate, $endDate]);

            $totalLhp = (clone $lhpFilterQuery)->count();
            $prevTotalLhp = (clone $baseLhpQuery)
                ->whereBetween('tanggal_lhp', [$prevStartDate, $prevEndDate])
                ->count();
            $lhpTrendPct = $prevTotalLhp > 0 ? round((($totalLhp - $prevTotalLhp) / $prevTotalLhp) * 100, 1) : ($totalLhp > 0 ? 100 : 0);

            // Ambil semua LHP IDs dalam periode
            $lhpIds = (clone $lhpFilterQuery)->pluck('id');

            // ── Temuan Stats ────────────────────────────────────────────────────
            $temuanStats = Temuan::whereIn('lhp_id', $lhpIds)
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status_tl = 'belum_ditindaklanjuti' OR status_tl IS NULL THEN 1 ELSE 0 END) as belum,
                    SUM(CASE WHEN status_tl = 'dalam_proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status_tl = 'selesai' THEN 1 ELSE 0 END) as selesai
                ")->first();

            $totalTemuan = $temuanStats->total ?? 0;

            // Trend temuan
            $prevLhpIds = (clone $baseLhpQuery)->whereBetween('tanggal_lhp', [$prevStartDate, $prevEndDate])->pluck('id');
            $prevTotalTemuan = Temuan::whereIn('lhp_id', $prevLhpIds)->count();
            $temuanTrendPct = $prevTotalTemuan > 0 ? round((($totalTemuan - $prevTotalTemuan) / $prevTotalTemuan) * 100, 1) : ($totalTemuan > 0 ? 100 : 0);

            // ── Rekomendasi Stats ───────────────────────────────────────────────
            $rekomStats = Recommendation::whereHas('temuan', fn($q) => $q->whereIn('lhp_id', $lhpIds))
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'belum_ditindaklanjuti' OR status IS NULL THEN 1 ELSE 0 END) as belum,
                    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
                ")->first();

            $totalRekom   = $rekomStats->total ?? 0;
            $rekomBelum   = $rekomStats->belum ?? 0;
            $rekomProses  = $rekomStats->proses ?? 0;
            $rekomSelesai = $rekomStats->selesai ?? 0;
            $rekomPct     = $totalRekom > 0 ? round(($rekomSelesai / $totalRekom) * 100, 1) : 0;

            // ── Tindak Lanjut Stats ─────────────────────────────────────────────
            $tlStats = TindakLanjut::whereHas('recommendation.temuan', fn($q) => $q->whereIn('lhp_id', $lhpIds))
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) as lunas,
                    SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) as berjalan,
                    SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' THEN 1 ELSE 0 END) as menunggu
                ")->first();

            $totalTl   = $tlStats->total ?? 0;
            $tlSelesai = $tlStats->lunas ?? 0;
            $tlPct     = $totalRekom > 0 ? round(($rekomSelesai / $totalRekom) * 100, 1) : 0;

            // ── Trend Monthly Chart (Audit, Temuan, Rekomendasi) ────────────────
            $currentYear = $startDate->year;
            $bulanData = DB::table('lhps')
                ->whereIn('lhps.id', $lhpIds)
                ->leftJoin('temuans', 'temuans.lhp_id', '=', 'lhps.id')
                ->leftJoin('recommendations', 'recommendations.temuan_id', '=', 'temuans.id')
                ->selectRaw('
                    MONTH(lhps.tanggal_lhp) as bulan,
                    COUNT(DISTINCT lhps.id) as total_audit,
                    COUNT(DISTINCT temuans.id) as total_temuan,
                    COUNT(DISTINCT recommendations.id) as total_rekom
                ')
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get()
                ->keyBy('bulan');

            $chartMonths = [];
            $chartAudit  = [];
            $chartTemuan = [];
            $chartRekom  = [];

            for ($m = 1; $m <= 12; $m++) {
                $chartMonths[] = Carbon::create($currentYear, $m, 1)->translatedFormat('M');
                $item = $bulanData->get($m);
                $chartAudit[]  = $item ? (int)$item->total_audit : 0;
                $chartTemuan[] = $item ? (int)$item->total_temuan : 0;
                $chartRekom[]  = $item ? (int)$item->total_rekom : 0;
            }

            // ── 10 Unit Periksa Temuan Terbanyak (Single Grouped Query - Optimized) ──
            $unitKinerja = DB::table('unit_diperiksas')
                ->leftJoin('lhps', function ($join) use ($lhpIds) {
                    $join->on('lhps.unit_diperiksa_id', '=', 'unit_diperiksas.id')
                         ->whereIn('lhps.id', $lhpIds);
                })
                ->leftJoin('temuans', 'temuans.lhp_id', '=', 'lhps.id')
                ->leftJoin('recommendations', 'recommendations.temuan_id', '=', 'temuans.id')
                ->selectRaw('
                    unit_diperiksas.id,
                    unit_diperiksas.nama_unit as nama,
                    COUNT(DISTINCT lhps.id) as total_lhp,
                    COUNT(DISTINCT temuans.id) as total_temuan,
                    COUNT(DISTINCT recommendations.id) as total_rekom,
                    COUNT(DISTINCT CASE WHEN recommendations.status = "selesai" THEN recommendations.id END) as selesai_rekom
                ')
                ->groupBy('unit_diperiksas.id', 'unit_diperiksas.nama_unit')
                ->orderByDesc('total_temuan')
                ->limit(10)
                ->get()
                ->map(function ($unit) {
                    $rekomCount = (int) $unit->total_rekom;
                    $rekomSelesaiCount = (int) $unit->selesai_rekom;
                    $progress = $rekomCount > 0 ? round(($rekomSelesaiCount / $rekomCount) * 100) : 0;

                    return [
                        'id'            => $unit->id,
                        'nama'          => $unit->nama,
                        'total_lhp'     => (int) $unit->total_lhp,
                        'total_temuan'  => (int) $unit->total_temuan,
                        'total_rekom'   => $rekomCount,
                        'selesai_rekom' => $rekomSelesaiCount,
                        'progress'      => $progress
                    ];
                })
                ->values();

            // ── Prioritas Perhatian ──────────────────────────────────────────────
            // 🔴 1. Rekomendasi melewati batas waktu
            $overdueCount = TindakLanjut::whereHas('recommendation.temuan', fn($q) => $q->whereIn('lhp_id', $lhpIds))
                ->whereNotIn('status_verifikasi', ['lunas'])
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '<', now())
                ->count();

            // 🟠 2. Temuan belum ditindaklanjuti sama sekali
            $temuanBelumTlCount = Temuan::whereIn('lhp_id', $lhpIds)
                ->where(function ($q) {
                    $q->where('status_tl', 'belum_ditindaklanjuti')
                      ->orWhereNull('status_tl');
                })
                ->count();

            // 🟡 3. Unit dengan progres < 50%
            $unitLowProgressCount = $unitKinerja->filter(fn($u) => $u['total_rekom'] > 0 && $u['progress'] < 50)->count();

            // ── Rekomendasi Terbaru ─────────────────────────────────────────────
            $rekomendasiTerbaru = Recommendation::with([
                    'temuan.lhp.unitDiperiksa',
                    'kodeRekomendasi'
                ])
                ->whereHas('temuan', fn($q) => $q->whereIn('lhp_id', $lhpIds))
                ->latest()
                ->limit(5)
                ->get();

            // ── Recent Activities (Timeline) ────────────────────────────────────
            $recentActivities = (clone $lhpFilterQuery)
                ->with('unitDiperiksa')
                ->latest('tanggal_lhp')
                ->limit(5)
                ->get();

            // ── Total Kerugian Financial Stats ──────────────────────────────────
            $totalKerugian = (float) Temuan::whereIn('lhp_id', $lhpIds)
                ->selectRaw("SUM(CASE WHEN nilai_temuan > 0 THEN nilai_temuan ELSE (COALESCE(nilai_kerugian_negara, 0) + COALESCE(nilai_kerugian_daerah, 0) + COALESCE(nilai_kerugian_desa, 0) + COALESCE(nilai_kerugian_bos_blud, 0)) END) as total")
                ->value('total') ?? 0;

            // ── Total Penyelamatan Aset (Setoran / Dibayar / Ditindaklanjuti) ───
            $totalPenyelamatan = (float) LhpStatistik::whereIn('lhp_id', $lhpIds)->sum('total_nilai_tl_selesai');

            if ($totalPenyelamatan == 0 && count($lhpIds) > 0) {
                try {
                    $totalPenyelamatanCicilan = (float) DB::table('tindak_lanjut_cicilans')
                        ->join('tindak_lanjuts', 'tindak_lanjut_cicilans.tindak_lanjut_id', '=', 'tindak_lanjuts.id')
                        ->join('recommendations', 'tindak_lanjuts.recommendation_id', '=', 'recommendations.id')
                        ->join('temuans', 'recommendations.temuan_id', '=', 'temuans.id')
                        ->whereIn('temuans.lhp_id', $lhpIds)
                        ->where('tindak_lanjut_cicilans.status', 'diterima')
                        ->sum('tindak_lanjut_cicilans.nilai_bayar') ?? 0;

                    $totalPenyelamatanLunas = (float) DB::table('tindak_lanjuts')
                        ->join('recommendations', 'tindak_lanjuts.recommendation_id', '=', 'recommendations.id')
                        ->join('temuans', 'recommendations.temuan_id', '=', 'temuans.id')
                        ->whereIn('temuans.lhp_id', $lhpIds)
                        ->where('tindak_lanjuts.status_verifikasi', 'lunas')
                        ->where('tindak_lanjuts.jenis_penyelesaian', '!=', 'cicilan')
                        ->sum('tindak_lanjuts.nilai_tindak_lanjut') ?? 0;

                    $totalPenyelamatan = $totalPenyelamatanCicilan + $totalPenyelamatanLunas;
                } catch (\Throwable $e) {
                    $totalPenyelamatan = 0;
                }
            }

            // ── Monthly Financial Bar Chart Data (Kerugian vs Penyelamatan Aset) ──────────
            $financialBulan = DB::table('lhps')
                ->whereIn('lhps.id', $lhpIds)
                ->leftJoin('temuans', 'temuans.lhp_id', '=', 'lhps.id')
                ->leftJoin('lhp_statistik', 'lhp_statistik.lhp_id', '=', 'lhps.id')
                ->selectRaw('
                    MONTH(lhps.tanggal_lhp) as bulan,
                    SUM(CASE WHEN temuans.nilai_temuan > 0 THEN temuans.nilai_temuan ELSE (COALESCE(temuans.nilai_kerugian_negara, 0) + COALESCE(temuans.nilai_kerugian_daerah, 0) + COALESCE(temuans.nilai_kerugian_desa, 0) + COALESCE(temuans.nilai_kerugian_bos_blud, 0)) END) as total_kerugian,
                    SUM(COALESCE(lhp_statistik.total_nilai_tl_selesai, 0)) as total_penyelamatan
                ')
                ->groupBy('bulan')
                ->get()
                ->keyBy('bulan');

            $chartKerugian = [];
            $chartPenyelamatan = [];

            for ($m = 1; $m <= 12; $m++) {
                $item = $financialBulan->get($m);
                $chartKerugian[] = $item ? (float)$item->total_kerugian : 0;
                $chartPenyelamatan[] = $item ? (float)$item->total_penyelamatan : 0;
            }

            return compact(
                'totalLhp', 'lhpTrendPct',
                'totalTemuan', 'temuanTrendPct',
                'totalRekom', 'rekomBelum', 'rekomProses', 'rekomSelesai', 'rekomPct',
                'totalTl', 'tlSelesai', 'tlPct', 'totalKerugian', 'totalPenyelamatan',
                'chartMonths', 'chartAudit', 'chartTemuan', 'chartRekom',
                'chartKerugian', 'chartPenyelamatan',
                'unitKinerja',
                'overdueCount', 'temuanBelumTlCount', 'unitLowProgressCount',
                'rekomendasiTerbaru', 'recentActivities'
            );
        });

        $listKategori = AuditProgram::KATEGORI;

        return view('dashboard', array_merge($data, [
            'user' => $user,
            'preset' => $preset,
            'kategoriProgram' => $kategoriProgram,
            'listKategori' => $listKategori,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'startDateFormatted' => $startDate->translatedFormat('d M Y'),
            'endDateFormatted' => $endDate->translatedFormat('d M Y'),
        ]));
    }
}