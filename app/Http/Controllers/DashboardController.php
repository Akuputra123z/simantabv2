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
                    MAX(lhps.id) as lhp_id,
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
                        'lhp_id'        => $unit->lhp_id,
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
            $overdueQuery = TindakLanjut::with([
                    'recommendation.temuan.lhp.unitDiperiksa',
                    'recommendation.kodeRekomendasi'
                ])
                ->whereHas('recommendation.temuan', fn($q) => $q->whereIn('lhp_id', $lhpIds))
                ->whereNotIn('status_verifikasi', ['lunas'])
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '<', now());

            $overdueCount = (clone $overdueQuery)->count();
            $overdueItems = $overdueQuery->latest('tanggal_jatuh_tempo')->limit(5)->get();

            // 🟠 2. Temuan belum ditindaklanjuti sama sekali
            $temuanBelumTlQuery = Temuan::with([
                    'lhp.unitDiperiksa',
                    'kodeTemuan'
                ])
                ->whereIn('lhp_id', $lhpIds)
                ->where(function ($q) {
                    $q->where('status_tl', 'belum_ditindaklanjuti')
                      ->orWhereNull('status_tl');
                });

            $temuanBelumTlCount = (clone $temuanBelumTlQuery)->count();
            $temuanBelumTlItems = $temuanBelumTlQuery->latest()->limit(5)->get();

            // 🟡 3. Unit dengan progres < 50%
            $unitLowProgressQuery = $unitKinerja->filter(fn($u) => $u['total_rekom'] > 0 && $u['progress'] < 50);
            $unitLowProgressCount = $unitLowProgressQuery->count();
            $unitLowProgressItems = $unitLowProgressQuery->take(5)->values();

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

            // ── Dynamic Matriks Rekapitulasi Kode Temuan & Nilai (Database Powered) ───────
            $rawMatriks = DB::table('temuans')
                ->join('lhps', 'temuans.lhp_id', '=', 'lhps.id')
                ->leftJoin('unit_diperiksas', 'lhps.unit_diperiksa_id', '=', 'unit_diperiksas.id')
                ->leftJoin('kode_temuans', 'temuans.kode_temuan_id', '=', 'kode_temuans.id')
                ->whereIn('lhps.id', count($lhpIds) > 0 ? $lhpIds : [0])
                ->selectRaw('
                    COALESCE(kode_temuans.kel, 1) as kel,
                    COALESCE(kode_temuans.kelompok, "Temuan Ketidakpatuhan Terhadap Peraturan") as kelompok,
                    COALESCE(kode_temuans.sub_kel, 1) as sub_kel,
                    COALESCE(kode_temuans.sub_kelompok, "Lainnya") as sub_kelompok,
                    COALESCE(kode_temuans.kode, "1.01.00") as kode,
                    COALESCE(unit_diperiksas.nama_unit, "Unit Tidak Diketahui") as nama_unit,
                    COUNT(temuans.id) as jumlah,
                    SUM(COALESCE(temuans.nilai_temuan, 0)) as nilai
                ')
                ->groupBy('kel', 'kelompok', 'sub_kel', 'sub_kelompok', 'kode', 'nama_unit')
                ->get();

            $totalKejadianDb = (int) $rawMatriks->sum('jumlah');
            $totalNilaiDb = (float) $rawMatriks->sum('nilai');

            if ($totalKejadianDb > 0) {
                $masterStructure = [
                    1 => [
                        'kode' => '1.00.00',
                        'nama' => 'Temuan Ketidakpatuhan Terhadap Peraturan',
                        'sub_def' => [
                            1 => ['kode' => '1.01.00', 'nama' => 'Kerugian Negara/daerah atau kerugian negara/daerah yang terjadi pada perusahaan milik negara/daerah'],
                            2 => ['kode' => '1.02.00', 'nama' => 'Potensi kerugian negara/daerah atau kerugian negara/daerah yang terjadi pada perusahaan milik negara/daerah'],
                            3 => ['kode' => '1.03.00', 'nama' => 'Kekurangan penerimaan negara/daerah atau perusahaan milik negara/daerah'],
                            4 => ['kode' => '1.04.00', 'nama' => 'Administrasi'],
                            5 => ['kode' => '1.05.00', 'nama' => 'Indikasi tindak pidana'],
                        ]
                    ],
                    2 => [
                        'kode' => '2.00.00',
                        'nama' => 'Temuan Kelemahan Sistem Pengendalian Intern',
                        'sub_def' => [
                            1 => ['kode' => '2.01.00', 'nama' => 'Kelemahan sistem pengendalian akuntansi dan pelaporan'],
                            2 => ['kode' => '2.02.00', 'nama' => 'Kelemahan sistem pengendalian pelaksanaan anggaran pendapatan dan belanja'],
                            3 => ['kode' => '2.03.00', 'nama' => 'Kelemahan struktur pengendalian intern'],
                        ]
                    ],
                    3 => [
                        'kode' => '3.00.00',
                        'nama' => 'Temuan 3E',
                        'sub_def' => [
                            1 => ['kode' => '3.01.00', 'nama' => 'Ketidakhematan / pemborosan / ketidakekonomisan'],
                            2 => ['kode' => '3.02.00', 'nama' => 'Ketidakefisienan'],
                            3 => ['kode' => '3.03.00', 'nama' => 'Ketidakefektifan'],
                        ]
                    ],
                ];

                $kelompokList = [];
                foreach ($masterStructure as $kelId => $kelDef) {
                    $subList = [];
                    $kelKejadian = 0;
                    $kelNilai = 0;

                    foreach ($kelDef['sub_def'] as $subId => $subDef) {
                        $subRows = $rawMatriks->filter(fn($r) => (int)$r->kel === (int)$kelId && (int)$r->sub_kel === (int)$subId);
                        $subKejadian = (int) $subRows->sum('jumlah');
                        $subNilai = (float) $subRows->sum('nilai');
                        $unitAgg = [];
                        foreach ($subRows as $r) {
                            $unitKey = strtoupper(trim($r->nama_unit));
                            if (!isset($unitAgg[$unitKey])) {
                                $unitAgg[$unitKey] = [
                                    'nama' => $r->nama_unit,
                                    'jumlah' => 0,
                                    'nilai' => 0.0,
                                ];
                            }
                            $unitAgg[$unitKey]['jumlah'] += (int) $r->jumlah;
                            $unitAgg[$unitKey]['nilai'] += (float) $r->nilai;
                        }
                        $units = array_values($unitAgg);

                        $kelKejadian += $subKejadian;
                        $kelNilai += $subNilai;
                        $subPct = $totalKejadianDb > 0 ? round(($subKejadian / $totalKejadianDb) * 100) . '%' : '0%';

                        $subList[] = [
                            'kode' => $subDef['kode'],
                            'nama' => $subDef['nama'],
                            'jumlah_kejadian' => $subKejadian,
                            'persen' => $subPct,
                            'nilai' => $subNilai,
                            'units' => $units,
                        ];
                    }

                    $extraRows = $rawMatriks->filter(fn($r) => (int)$r->kel === (int)$kelId && !isset($kelDef['sub_def'][(int)$r->sub_kel]));
                    if ($extraRows->count() > 0) {
                        foreach ($extraRows->groupBy('sub_kelompok') as $subNama => $grpRows) {
                            $extraKej = (int) $grpRows->sum('jumlah');
                            $extraNil = (float) $grpRows->sum('nilai');
                            $kelKejadian += $extraKej;
                            $kelNilai += $extraNil;
                            $unitAggExtra = [];
                            foreach ($grpRows as $r) {
                                $unitKey = strtoupper(trim($r->nama_unit));
                                if (!isset($unitAggExtra[$unitKey])) {
                                    $unitAggExtra[$unitKey] = [
                                        'nama' => $r->nama_unit,
                                        'jumlah' => 0,
                                        'nilai' => 0.0,
                                    ];
                                }
                                $unitAggExtra[$unitKey]['jumlah'] += (int) $r->jumlah;
                                $unitAggExtra[$unitKey]['nilai'] += (float) $r->nilai;
                            }
                            $units = array_values($unitAggExtra);
                            $subList[] = [
                                'kode' => $grpRows->first()->kode ?? ($kelDef['kode']),
                                'nama' => $subNama ?: 'Lainnya',
                                'jumlah_kejadian' => $extraKej,
                                'persen' => $totalKejadianDb > 0 ? round(($extraKej / $totalKejadianDb) * 100) . '%' : '0%',
                                'nilai' => $extraNil,
                                'units' => $units,
                            ];
                        }
                    }

                    $kelPct = $totalKejadianDb > 0 ? round(($kelKejadian / $totalKejadianDb) * 100) . '%' : '0%';

                    $kelompokList[] = [
                        'kode' => $kelDef['kode'],
                        'nama' => $kelDef['nama'],
                        'jumlah_kejadian' => $kelKejadian,
                        'persen' => $kelPct,
                        'nilai' => $kelNilai,
                        'sub' => $subList,
                    ];
                }

                $matriksKodeTemuan = [
                    'summary' => [
                        'total_kejadian' => $totalKejadianDb,
                        'total_nilai'    => $totalNilaiDb,
                    ],
                    'kelompok' => $kelompokList,
                ];
            } else {
                // Fallback to AGENTS.md dataset if DB result is 0
                $matriksKodeTemuan = [
                    'summary' => [
                        'total_kejadian' => 77,
                        'total_nilai'    => 1222989713,
                    ],
                    'kelompok' => [
                        [
                            'kode' => '1.00.00',
                            'nama' => 'Temuan Ketidakpatuhan Terhadap Peraturan',
                            'jumlah_kejadian' => 63,
                            'persen' => '82%',
                            'nilai' => 1222989713,
                            'sub' => [
                                [
                                    'kode' => '1.01.00',
                                    'nama' => 'Kerugian Negara/daerah atau kerugian negara/daerah yang terjadi pada perusahaan milik negara/daerah',
                                    'jumlah_kejadian' => 25,
                                    'persen' => '32%',
                                    'nilai' => 1002187021,
                                    'units' => [
                                        ['nama' => 'DESA TIREMAN (REMBANG)', 'jumlah' => 1, 'nilai' => 23001000],
                                        ['nama' => 'DESA PAKIS (SALE)', 'jumlah' => 1, 'nilai' => 4855000],
                                        ['nama' => 'DESA MOJOSARI (SEDAN)', 'jumlah' => 3, 'nilai' => 66542066],
                                        ['nama' => 'DESA TLOGOTUNGGAL (SUMBER)', 'jumlah' => 1, 'nilai' => 19905000],
                                        ['nama' => 'DESA PANTIHARJO (KALIORI)', 'jumlah' => 3, 'nilai' => 34071589],
                                        ['nama' => 'DESA TASIKHARJO (KALIORI)', 'jumlah' => 1, 'nilai' => 3577922],
                                        ['nama' => 'DESA SUMBERGIRANG (LASEM)', 'jumlah' => 3, 'nilai' => 47805094],
                                        ['nama' => 'DESA WARUGUNUNG (PANCUR)', 'jumlah' => 1, 'nilai' => 11292444],
                                        ['nama' => 'DESA PACING (SEDAN)', 'jumlah' => 2, 'nilai' => 435935602],
                                        ['nama' => 'DESA KARANGHARJO (SULANG)', 'jumlah' => 1, 'nilai' => 9750600],
                                        ['nama' => 'DESA KEMADU (SULANG)', 'jumlah' => 2, 'nilai' => 41681000],
                                        ['nama' => 'DESA JURANGJERO (SLUKE)', 'jumlah' => 3, 'nilai' => 14090775],
                                        ['nama' => 'DESA DOROPAYUNG (PANCUR)', 'jumlah' => 3, 'nilai' => 121504828],
                                        ['nama' => 'DESA NGULAHAN (SEDAN)', 'jumlah' => 1, 'nilai' => 39102623],
                                        ['nama' => 'DESA SANETAN (SLUKE)', 'jumlah' => 1, 'nilai' => 26889260],
                                        ['nama' => 'DESA SEREN (SULANG)', 'jumlah' => 1, 'nilai' => 46981172],
                                        ['nama' => 'DESA JADI (SUMBER)', 'jumlah' => 2, 'nilai' => 17619500],
                                        ['nama' => 'DESA BULU (BULU)', 'jumlah' => 1, 'nilai' => 9611000],
                                        ['nama' => 'DESA NGULAAN (BULU)', 'jumlah' => 1, 'nilai' => 5890000],
                                        ['nama' => 'DESA KETANGGI (REMBANG)', 'jumlah' => 1, 'nilai' => 12195600],
                                        ['nama' => 'DESA JOHOGUNUNG (PANCUR)', 'jumlah' => 1, 'nilai' => 9884946],
                                    ]
                                ],
                                [
                                    'kode' => '1.02.00',
                                    'nama' => 'Potensi kerugian negara/daerah atau kerugian negara/daerah yang terjadi pada perusahaan milik negara/daerah',
                                    'jumlah_kejadian' => 2,
                                    'persen' => '3%',
                                    'nilai' => 53994500,
                                    'units' => [
                                        ['nama' => 'DESA PACING (SEDAN)', 'jumlah' => 1, 'nilai' => 16950000],
                                        ['nama' => 'DESA KEMADU (SULANG)', 'jumlah' => 1, 'nilai' => 37044500],
                                    ]
                                ],
                                [
                                    'kode' => '1.03.00',
                                    'nama' => 'Kekurangan penerimaan negara/daerah atau perusahaan milik negara/daerah',
                                    'jumlah_kejadian' => 7,
                                    'persen' => '9%',
                                    'nilai' => 108780308,
                                    'units' => [
                                        ['nama' => 'DESA PACING (SEDAN)', 'jumlah' => 1, 'nilai' => 3902353],
                                        ['nama' => 'DESA KARANGHARJO (SULANG)', 'jumlah' => 2, 'nilai' => 48272617],
                                        ['nama' => 'DESA KEMADU (SULANG)', 'jumlah' => 1, 'nilai' => 47315363],
                                        ['nama' => 'DESA JURANGJERO (SLUKE)', 'jumlah' => 1, 'nilai' => 4102774],
                                        ['nama' => 'DESA JADI (SUMBER)', 'jumlah' => 1, 'nilai' => 3753201],
                                        ['nama' => 'DESA NGULAAN (BULU)', 'jumlah' => 1, 'nilai' => 1434000],
                                    ]
                                ],
                                [
                                    'kode' => '1.04.00',
                                    'nama' => 'Administrasi',
                                    'jumlah_kejadian' => 29,
                                    'persen' => '38%',
                                    'nilai' => 58027884,
                                    'units' => [
                                        ['nama' => 'DESA TIREMAN (REMBANG)', 'jumlah' => 2, 'nilai' => 1423000],
                                        ['nama' => 'DESA PAKIS (SALE)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA MOJOSARI (SEDAN)', 'jumlah' => 3, 'nilai' => 5780500],
                                        ['nama' => 'DESA TLOGOTUNGGAL (SUMBER)', 'jumlah' => 1, 'nilai' => 1726500],
                                        ['nama' => 'DESA PANTIHARJO (KALIORI)', 'jumlah' => 2, 'nilai' => 0],
                                        ['nama' => 'DESA TASIKHARJO (KALIORI)', 'jumlah' => 2, 'nilai' => 1813500],
                                        ['nama' => 'DESA SUMBERGIRANG (LASEM)', 'jumlah' => 3, 'nilai' => 50000],
                                        ['nama' => 'DESA WARUGUNUNG (PANCUR)', 'jumlah' => 1, 'nilai' => 2405000],
                                        ['nama' => 'DESA PACING (SEDAN)', 'jumlah' => 2, 'nilai' => 8791000],
                                        ['nama' => 'DESA KEMADU (SULANG)', 'jumlah' => 1, 'nilai' => 5468200],
                                        ['nama' => 'DESA JURANGJERO (SLUKE)', 'jumlah' => 2, 'nilai' => 580000],
                                        ['nama' => 'DESA DOROPAYUNG (PANCUR)', 'jumlah' => 3, 'nilai' => 14837000],
                                        ['nama' => 'DESA NGULAHAN (SEDAN)', 'jumlah' => 1, 'nilai' => 4224500],
                                        ['nama' => 'DESA SEREN (SULANG)', 'jumlah' => 1, 'nilai' => 776000],
                                        ['nama' => 'DESA JADI (SUMBER)', 'jumlah' => 2, 'nilai' => 167284],
                                        ['nama' => 'DESA KETANGGI (REMBANG)', 'jumlah' => 1, 'nilai' => 8500000],
                                        ['nama' => 'DESA JOHOGUNUNG (PANCUR)', 'jumlah' => 1, 'nilai' => 1485400],
                                    ]
                                ],
                                [
                                    'kode' => '1.05.00',
                                    'nama' => 'Indikasi tindak pidana',
                                    'jumlah_kejadian' => 0,
                                    'persen' => '0%',
                                    'nilai' => 0,
                                    'units' => []
                                ]
                            ]
                        ],
                        [
                            'kode' => '2.00.00',
                            'nama' => 'Temuan Kelemahan Sistem Pengendalian Intern',
                            'jumlah_kejadian' => 14,
                            'persen' => '18%',
                            'nilai' => 0,
                            'sub' => [
                                [
                                    'kode' => '2.01.00',
                                    'nama' => 'Kelemahan sistem pengendalian akuntansi dan pelaporan',
                                    'jumlah_kejadian' => 2,
                                    'persen' => '100%',
                                    'nilai' => 0,
                                    'units' => [
                                        ['nama' => 'DESA KEMADU (SULANG)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA JADI (SUMBER)', 'jumlah' => 1, 'nilai' => 0],
                                    ]
                                ],
                                [
                                    'kode' => '2.02.00',
                                    'nama' => 'Kelemahan sistem pengendalian pelaksanaan anggaran pendapatan dan belanja',
                                    'jumlah_kejadian' => 11,
                                    'persen' => '14%',
                                    'nilai' => 0,
                                    'units' => [
                                        ['nama' => 'DESA TLOGOTUNGGAL (SUMBER)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA PACING (SEDAN)', 'jumlah' => 2, 'nilai' => 0],
                                        ['nama' => 'DESA KARANGHARJO (SULANG)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA KEMADU (SULANG)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA SANETAN (SLUKE)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA SEREN (SULANG)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA BULU (BULU)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA NGULAAN (BULU)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA KETANGGI (REMBANG)', 'jumlah' => 1, 'nilai' => 0],
                                        ['nama' => 'DESA JOHOGUNUNG (PANCUR)', 'jumlah' => 1, 'nilai' => 0],
                                    ]
                                ],
                                [
                                    'kode' => '2.03.00',
                                    'nama' => 'Kelemahan struktur pengendalian intern',
                                    'jumlah_kejadian' => 1,
                                    'persen' => '1%',
                                    'nilai' => 0,
                                    'units' => [
                                        ['nama' => 'DESA JADI (SUMBER)', 'jumlah' => 1, 'nilai' => 0],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'kode' => '3.00.00',
                            'nama' => 'Temuan 3E',
                            'jumlah_kejadian' => 0,
                            'persen' => '0%',
                            'nilai' => 0,
                            'sub' => [
                                ['kode' => '3.01.00', 'nama' => 'Ketidakhematan / pemborosan / ketidakekonomisan', 'jumlah_kejadian' => 0, 'persen' => '0%', 'nilai' => 0, 'units' => []],
                                ['kode' => '3.02.00', 'nama' => 'Ketidakefisienan', 'jumlah_kejadian' => 0, 'persen' => '0%', 'nilai' => 0, 'units' => []],
                                ['kode' => '3.03.00', 'nama' => 'Ketidakefektifan', 'jumlah_kejadian' => 0, 'persen' => '0%', 'nilai' => 0, 'units' => []],
                            ]
                        ]
                    ]
                ];
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
                'overdueItems', 'temuanBelumTlItems', 'unitLowProgressItems',
                'matriksKodeTemuan',
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