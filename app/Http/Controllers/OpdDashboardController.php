<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use App\Models\TindakLanjut;
use App\Models\Lhp;
use App\Models\Temuan;
use App\Models\Recommendation;
use App\Models\LhpStatistik;
use App\Models\UnitDiperiksa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OpdDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // ── 1. Date & Category Filter Parsing ──────────────────────────────────────────
        $preset          = $request->input('preset', 'all');
        $startDateInput  = $request->input('start_date');
        $endDateInput    = $request->input('end_date');
        $kategoriProgram = $request->input('kategori_program', 'semua');

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
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                break;
            case 'all':
            default:
                $preset    = 'all';
                $startDate = Carbon::create(2000, 1, 1)->startOfDay();
                $endDate   = $now->copy()->endOfYear();
                break;
        }

        // Periode pembanding (Previous Period)
        $diffDays      = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffDays);
        $prevEndDate   = $startDate->copy()->subDay()->endOfDay();

        // ── 2. Determine target unit IDs ──────────────────────────────────────────────
        $isSuperOrAdmin = $user->hasAnyRole(['super_admin', 'admin', 'admin_inspektorat', 'inspektur', 'kepala_inspektorat']);
        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');

        if ($unitIds->isNotEmpty()) {
            $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
            $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
        } elseif ($isSuperOrAdmin) {
            $unitIds = UnitDiperiksa::pluck('id');
        }

        if ($unitIds->isEmpty()) {
            $unitIds = collect([0]);
        }

        // Auto-ensure default TindakLanjut stubs exist for all recommendations of these units
        if ($unitIds->isNotEmpty() || $isSuperOrAdmin) {
            $missingQuery = Recommendation::doesntHave('tindakLanjuts');
            if (! $isSuperOrAdmin) {
                $missingQuery->whereHas('temuan.lhp', function ($q) use ($unitIds) {
                    $q->whereIn('unit_diperiksa_id', $unitIds);
                });
            }
            $missingRekomIds = $missingQuery->pluck('id');

            foreach ($missingRekomIds as $rekomId) {
                TindakLanjut::firstOrCreate(
                    ['recommendation_id' => $rekomId],
                    [
                        'status_verifikasi'   => 'menunggu_verifikasi',
                        'nilai_tindak_lanjut' => 0,
                        'total_terbayar'      => 0,
                        'sisa_belum_bayar'    => 0,
                    ]
                );
            }
        }

        // ── 3. Base LHP Query ─────────────────────────────────────────────────────────
        $baseLhpQuery = Lhp::whereIn('unit_diperiksa_id', $unitIds);
        if (!empty($kategoriProgram) && $kategoriProgram !== 'semua') {
            $baseLhpQuery->whereHas('auditAssignment.auditProgramDetail.auditProgram', function ($q) use ($kategoriProgram) {
                $q->where('kategori', $kategoriProgram);
            });
        }

        $lhpFilterQuery = clone $baseLhpQuery;
        if ($preset !== 'all') {
            $lhpFilterQuery->whereBetween('tanggal_lhp', [$startDate, $endDate]);
        }

        $totalLhp = (clone $lhpFilterQuery)->count();
        $prevTotalLhp = (clone $baseLhpQuery)
            ->whereBetween('tanggal_lhp', [$prevStartDate, $prevEndDate])
            ->count();
        $lhpTrendPct = $prevTotalLhp > 0 ? round((($totalLhp - $prevTotalLhp) / $prevTotalLhp) * 100, 1) : ($totalLhp > 0 ? 100 : 0);

        $lhpIds = (clone $lhpFilterQuery)->pluck('id');
        $lhpIdsArray = count($lhpIds) > 0 ? $lhpIds->toArray() : [0];

        // ── 4. Temuan Stats ───────────────────────────────────────────────────────────
        $temuanStats = Temuan::whereIn('lhp_id', $lhpIdsArray)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status_tl = 'belum_ditindaklanjuti' OR status_tl IS NULL THEN 1 ELSE 0 END) as belum,
                SUM(CASE WHEN status_tl = 'dalam_proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status_tl = 'selesai' THEN 1 ELSE 0 END) as selesai
            ")->first();

        $totalTemuan = $temuanStats->total ?? 0;

        $prevLhpIds = (clone $baseLhpQuery)->whereBetween('tanggal_lhp', [$prevStartDate, $prevEndDate])->pluck('id');
        $prevTotalTemuan = Temuan::whereIn('lhp_id', count($prevLhpIds) > 0 ? $prevLhpIds : [0])->count();
        $temuanTrendPct = $prevTotalTemuan > 0 ? round((($totalTemuan - $prevTotalTemuan) / $prevTotalTemuan) * 100, 1) : ($totalTemuan > 0 ? 100 : 0);

        // ── 5. Rekomendasi Stats ──────────────────────────────────────────────────────
        $rekomStats = Recommendation::whereHas('temuan', fn($q) => $q->whereIn('lhp_id', $lhpIdsArray))
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

        // ── 6. Tindak Lanjut Stats ────────────────────────────────────────────────────
        $tlQuery = TindakLanjut::query()
            ->whereHas('recommendation.temuan', fn ($q) => $q->whereIn('lhp_id', $lhpIdsArray));

        $tlStats = (clone $tlQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) as lunas,
            SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) as berjalan,
            SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' OR status_verifikasi IS NULL THEN 1 ELSE 0 END) as menunggu
        ")->first();

        $totalTl   = $tlStats->total ?? 0;
        $tlSelesai = $tlStats->lunas ?? 0;
        $tlPct     = $totalRekom > 0 ? round(($rekomSelesai / $totalRekom) * 100, 1) : 0;

        // ── 7. OPD Upload & Verifikasi Stats ──────────────────────────────────────────
        $opdStats = (clone $tlQuery)->selectRaw("
            SUM(CASE WHEN status_opd IS NULL THEN 1 ELSE 0 END) AS belum_upload,
            SUM(CASE WHEN status_opd = 'draft' AND alasan_tolak_opd IS NULL THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN status_opd = 'dikirim' THEN 1 ELSE 0 END) AS dikirim,
            SUM(CASE WHEN status_opd = 'draft' AND alasan_tolak_opd IS NOT NULL THEN 1 ELSE 0 END) AS ditolak
        ")->first();

        $verifikasiStats = (clone $tlQuery)->selectRaw("
            SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) AS lunas,
            SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) AS berjalan,
            SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' OR status_verifikasi IS NULL THEN 1 ELSE 0 END) AS menunggu
        ")->first();

        // ── 8. Total Kerugian & Penyelamatan Aset (Synchronized Realtime Logic) ───────
        $totalKerugian = (float) Temuan::whereIn('lhp_id', $lhpIdsArray)
            ->selectRaw("SUM(CASE WHEN nilai_temuan > 0 THEN nilai_temuan ELSE (COALESCE(nilai_kerugian_negara, 0) + COALESCE(nilai_kerugian_daerah, 0) + COALESCE(nilai_kerugian_desa, 0) + COALESCE(nilai_kerugian_bos_blud, 0)) END) as total")
            ->value('total') ?? 0;

        if ($totalKerugian == 0 && count($lhpIdsArray) > 0) {
            $totalKerugian = (float) Recommendation::whereHas('temuan', fn($q) => $q->whereIn('lhp_id', $lhpIdsArray))->sum('nilai_rekom');
        }

        $totalPenyelamatanCicilan = (float) DB::table('tindak_lanjut_cicilans')
            ->join('tindak_lanjuts', 'tindak_lanjut_cicilans.tindak_lanjut_id', '=', 'tindak_lanjuts.id')
            ->join('recommendations', 'tindak_lanjuts.recommendation_id', '=', 'recommendations.id')
            ->join('temuans', 'recommendations.temuan_id', '=', 'temuans.id')
            ->whereIn('temuans.lhp_id', $lhpIdsArray)
            ->where('tindak_lanjut_cicilans.status', 'diterima')
            ->sum('tindak_lanjut_cicilans.nilai_bayar') ?? 0;

        $totalPenyelamatanLunas = (float) DB::table('tindak_lanjuts')
            ->join('recommendations', 'tindak_lanjuts.recommendation_id', '=', 'recommendations.id')
            ->join('temuans', 'recommendations.temuan_id', '=', 'temuans.id')
            ->whereIn('temuans.lhp_id', $lhpIdsArray)
            ->where('tindak_lanjuts.status_verifikasi', 'lunas')
            ->where(function($q) {
                $q->where('tindak_lanjuts.jenis_penyelesaian', '!=', 'cicilan')
                  ->orWhereNull('tindak_lanjuts.jenis_penyelesaian');
            })
            ->sum('tindak_lanjuts.nilai_tindak_lanjut') ?? 0;

        $totalPenyelamatanRealtime = $totalPenyelamatanCicilan + $totalPenyelamatanLunas;
        $totalPenyelamatanStat = (float) LhpStatistik::whereIn('lhp_id', $lhpIdsArray)->sum('total_nilai_tl_selesai');
        $totalPenyelamatan = max($totalPenyelamatanRealtime, $totalPenyelamatanStat);

        $sisaKerugian = max(0, $totalKerugian - $totalPenyelamatan);
        $recoveryRate = $totalKerugian > 0 ? round(($totalPenyelamatan / $totalKerugian) * 100, 1) : 0;

        $rekapitulasi = (object) [
            'total_rekom'      => $totalRekom,
            'rekom_selesai'    => $rekomSelesai,
            'rekom_proses'     => $rekomProses,
            'rekom_belum'      => $rekomBelum,
            'total_kerugian'   => $totalKerugian,
            'total_tl_selesai' => $totalPenyelamatan,
            'sisa_kerugian'    => $sisaKerugian,
            'recovery_rate'    => $recoveryRate,
        ];

        // ── 9. Monthly Charts Data (Audit, Temuan, Financial) ─────────────────────────
        $currentYear = $startDate->year;
        $monthExpr   = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%m', lhps.tanggal_lhp) AS INTEGER)" : "MONTH(lhps.tanggal_lhp)";
        $bulanData   = DB::table('lhps')
            ->whereIn('lhps.id', $lhpIdsArray)
            ->leftJoin('temuans', 'temuans.lhp_id', '=', 'lhps.id')
            ->leftJoin('recommendations', 'recommendations.temuan_id', '=', 'temuans.id')
            ->selectRaw("
                {$monthExpr} as bulan,
                COUNT(DISTINCT lhps.id) as total_audit,
                COUNT(DISTINCT temuans.id) as total_temuan,
                COUNT(DISTINCT recommendations.id) as total_rekom
            ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $kerugianBulan = DB::table('temuans')
            ->join('lhps', 'temuans.lhp_id', '=', 'lhps.id')
            ->whereIn('lhps.id', $lhpIdsArray)
            ->selectRaw("
                {$monthExpr} as bulan,
                SUM(CASE WHEN temuans.nilai_temuan > 0 THEN temuans.nilai_temuan ELSE (COALESCE(temuans.nilai_kerugian_negara, 0) + COALESCE(temuans.nilai_kerugian_daerah, 0) + COALESCE(temuans.nilai_kerugian_desa, 0) + COALESCE(temuans.nilai_kerugian_bos_blud, 0)) END) as total_kerugian
            ")
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $penyelamatanCicilanBulan = DB::table('tindak_lanjut_cicilans')
            ->join('tindak_lanjuts', 'tindak_lanjut_cicilans.tindak_lanjut_id', '=', 'tindak_lanjuts.id')
            ->join('recommendations', 'tindak_lanjuts.recommendation_id', '=', 'recommendations.id')
            ->join('temuans', 'recommendations.temuan_id', '=', 'temuans.id')
            ->join('lhps', 'temuans.lhp_id', '=', 'lhps.id')
            ->whereIn('lhps.id', $lhpIdsArray)
            ->where('tindak_lanjut_cicilans.status', 'diterima')
            ->selectRaw("
                {$monthExpr} as bulan,
                SUM(tindak_lanjut_cicilans.nilai_bayar) as total_penyelamatan
            ")
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $penyelamatanLunasBulan = DB::table('tindak_lanjuts')
            ->join('recommendations', 'tindak_lanjuts.recommendation_id', '=', 'recommendations.id')
            ->join('temuans', 'recommendations.temuan_id', '=', 'temuans.id')
            ->join('lhps', 'temuans.lhp_id', '=', 'lhps.id')
            ->whereIn('lhps.id', $lhpIdsArray)
            ->where('tindak_lanjuts.status_verifikasi', 'lunas')
            ->where(function($q) {
                $q->where('tindak_lanjuts.jenis_penyelesaian', '!=', 'cicilan')
                  ->orWhereNull('tindak_lanjuts.jenis_penyelesaian');
            })
            ->selectRaw("
                {$monthExpr} as bulan,
                SUM(tindak_lanjuts.nilai_tindak_lanjut) as total_penyelamatan
            ")
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $chartMonths       = [];
        $chartAudit        = [];
        $chartTemuan       = [];
        $chartRekom        = [];
        $chartKerugian     = [];
        $chartPenyelamatan = [];

        for ($m = 1; $m <= 12; $m++) {
            $chartMonths[]       = Carbon::create($currentYear, $m, 1)->translatedFormat('M');
            $item                = $bulanData->get($m);
            $kerugianItem        = $kerugianBulan->get($m);
            $cicilanItem         = $penyelamatanCicilanBulan->get($m);
            $lunasItem           = $penyelamatanLunasBulan->get($m);

            $chartAudit[]        = $item ? (int)$item->total_audit : 0;
            $chartTemuan[]       = $item ? (int)$item->total_temuan : 0;
            $chartRekom[]        = $item ? (int)$item->total_rekom : 0;
            $chartKerugian[]     = $kerugianItem ? (float)$kerugianItem->total_kerugian : 0;
            $penyelamatanM       = ($cicilanItem ? (float)$cicilanItem->total_penyelamatan : 0) + ($lunasItem ? (float)$lunasItem->total_penyelamatan : 0);
            $chartPenyelamatan[] = $penyelamatanM;
        }

        // ── 10. Matriks Rekapitulasi Kode Temuan & Nilai (Database Powered per AGENTS.md) ───
        $rawMatriks = DB::table('temuans')
            ->join('lhps', 'temuans.lhp_id', '=', 'lhps.id')
            ->leftJoin('unit_diperiksas', 'lhps.unit_diperiksa_id', '=', 'unit_diperiksas.id')
            ->leftJoin('kode_temuans', 'temuans.kode_temuan_id', '=', 'kode_temuans.id')
            ->whereIn('lhps.id', $lhpIdsArray)
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
        $totalNilaiDb    = (float) $rawMatriks->sum('nilai');

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
            $matriksKodeTemuan = [
                'summary' => [
                    'total_kejadian' => 0,
                    'total_nilai'    => 0,
                ],
                'kelompok' => [],
            ];
        }

        // ── 11. Overdue Items & Recent Activities ────────────────────────────────────
        $overdue = (clone $tlQuery)
            ->whereNotNull('tanggal_jatuh_tempo')
            ->where('tanggal_jatuh_tempo', '<=', now()->addDays(7))
            ->where('status_verifikasi', '!=', 'lunas')
            ->with(['recommendation.temuan.lhp.unitDiperiksa', 'recommendation.kodeRekomendasi'])
            ->orderBy('tanggal_jatuh_tempo')
            ->limit(10)
            ->get();

        $recent = (clone $tlQuery)
            ->whereNotNull('status_opd')
            ->with(['uploadOpdOleh', 'recommendation.temuan.lhp.unitDiperiksa', 'recommendation.kodeRekomendasi'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        // ── 12. Breakdown Per Kategori Program Audit ────────────────────────────────
        $kategoriBreakdown = collect(AuditProgram::KATEGORI)->map(function ($kat) use ($unitIds, $startDate, $endDate, $preset) {
            $lhpQuery = Lhp::whereIn('unit_diperiksa_id', $unitIds)
                ->whereHas('auditAssignment.auditProgramDetail.auditProgram', function ($q) use ($kat) {
                    $q->where('kategori', $kat);
                });

            if ($preset !== 'all') {
                $lhpQuery->whereBetween('tanggal_lhp', [$startDate, $endDate]);
            }

            $lhps = $lhpQuery->with(['statistik', 'temuans.recommendations.tindakLanjuts'])->get();

            $lhpCount      = $lhps->count();
            $allRekom      = $lhps->flatMap->temuans->flatMap->recommendations;
            $rekomCount    = $allRekom->count();
            $totalKerugian = $lhps->sum('total_kerugian');
            $totalSetor    = $allRekom->flatMap->tindakLanjuts->sum('total_terbayar');
            $progres       = $lhpCount > 0 ? round($lhps->avg('persen_selesai') ?? 0) : 0;

            return (object) [
                'kategori'       => $kat,
                'total_lhp'      => $lhpCount,
                'total_rekom'    => $rekomCount,
                'total_kerugian' => $totalKerugian,
                'total_setor'    => $totalSetor,
                'progres'        => $progres,
            ];
        });

        $listKategori = AuditProgram::KATEGORI;

        return view('pages.opd.dashboard', [
            'totalLhp'           => $totalLhp,
            'lhpTrendPct'        => $lhpTrendPct,
            'totalTemuan'        => $totalTemuan,
            'temuanTrendPct'     => $temuanTrendPct,
            'totalRekom'         => $totalRekom,
            'rekomBelum'         => $rekomBelum,
            'rekomProses'        => $rekomProses,
            'rekomSelesai'       => $rekomSelesai,
            'rekomPct'           => $rekomPct,
            'totalTl'            => $totalTl,
            'tlSelesai'          => $tlSelesai,
            'tlPct'              => $tlPct,
            'totalKerugian'      => $totalKerugian,
            'totalPenyelamatan'  => $totalPenyelamatan,
            'sisaKerugian'       => $sisaKerugian,
            'recoveryRate'       => $recoveryRate,
            'opdStats'           => $opdStats,
            'verifikasiStats'    => $verifikasiStats,
            'chartMonths'        => $chartMonths,
            'chartAudit'         => $chartAudit,
            'chartTemuan'        => $chartTemuan,
            'chartRekom'         => $chartRekom,
            'chartKerugian'      => $chartKerugian,
            'chartPenyelamatan'  => $chartPenyelamatan,
            'matriksKodeTemuan'  => $matriksKodeTemuan,
            'overdue'            => $overdue,
            'recent'             => $recent,
            'rekapitulasi'       => $rekapitulasi,
            'kategoriBreakdown'  => $kategoriBreakdown,
            'preset'             => $preset,
            'kategoriProgram'    => $kategoriProgram,
            'listKategori'       => $listKategori,
            'startDate'          => $startDate->format('Y-m-d'),
            'endDate'            => $endDate->format('Y-m-d'),
            'startDateFormatted' => $startDate->translatedFormat('d M Y'),
            'endDateFormatted'   => $endDate->translatedFormat('d M Y'),
        ]);
    }
}
