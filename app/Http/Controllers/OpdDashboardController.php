<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use App\Models\TindakLanjut;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\UnitDiperiksa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpdDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // ── Date & Category Filter Parsing ──────────────────────────────────────────
        $preset          = $request->input('preset', 'this_year');
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
            case 'all':
                $startDate = Carbon::create(2000, 1, 1)->startOfDay();
                $endDate   = $now->copy()->endOfYear();
                break;
            case 'this_year':
            default:
                $preset    = 'this_year';
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                break;
        }

        // Determine target unit IDs
        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
        if ($unitIds->isEmpty()) {
            if ($user->hasRole(['super_admin', 'admin', 'admin_inspektorat'])) {
                $unitIds = UnitDiperiksa::pluck('id');
            }
        }

        // Auto-ensure default TindakLanjut stubs exist for all recommendations of these units
        if ($unitIds->isNotEmpty()) {
            $missingRekomIds = Recommendation::whereHas('temuan.lhp', function ($q) use ($unitIds) {
                $q->whereIn('unit_diperiksa_id', $unitIds);
            })->doesntHave('tindakLanjuts')->pluck('id');

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

        // Base LHP query filtered by unit, date range, and Kategori Program Audit
        $lhpQuery = Lhp::whereIn('unit_diperiksa_id', $unitIds);
        if ($preset !== 'all') {
            $lhpQuery->whereBetween('tanggal_lhp', [$startDate, $endDate]);
        }
        if (!empty($kategoriProgram) && $kategoriProgram !== 'semua') {
            $lhpQuery->whereHas('auditAssignment.auditProgramDetail.auditProgram', function ($q) use ($kategoriProgram) {
                $q->where('kategori', $kategoriProgram);
            });
        }
        $lhpIds = $lhpQuery->pluck('id');

        // Tindak Lanjut query
        $tlQuery = TindakLanjut::query()
            ->whereHas('recommendation.temuan', fn ($q) => $q->whereIn('lhp_id', $lhpIds));

        // ── OPD Upload Stats (Belum Upload, Draft, Terkirim, Ditolak) ──────────
        $opdStats = (clone $tlQuery)->selectRaw("
            SUM(CASE WHEN status_opd IS NULL THEN 1 ELSE 0 END) AS belum_upload,
            SUM(CASE WHEN status_opd = 'draft' AND alasan_tolak_opd IS NULL THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN status_opd = 'dikirim' THEN 1 ELSE 0 END) AS dikirim,
            SUM(CASE WHEN status_opd = 'draft' AND alasan_tolak_opd IS NOT NULL THEN 1 ELSE 0 END) AS ditolak
        ")->first();

        // ── Verifikasi Stats (Lunas, Berjalan, Menunggu) ──────────────────────
        $verifikasiStats = (clone $tlQuery)->selectRaw("
            SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) AS lunas,
            SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) AS berjalan,
            SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' OR status_verifikasi IS NULL THEN 1 ELSE 0 END) AS menunggu
        ")->first();

        // ── Rekapitulasi Real-time ─────────────────────────────────────────────
        $rekomQuery = Recommendation::query()
            ->whereHas('temuan', fn ($q) => $q->whereIn('lhp_id', $lhpIds));

        $totalRekom       = (clone $rekomQuery)->count();
        $rekomSelesai     = (clone $rekomQuery)->where('status', Recommendation::STATUS_SELESAI)->count();
        $totalKerugian    = (float) (clone $rekomQuery)->sum('nilai_rekom');
        $totalTlSelesai   = (float) (clone $rekomQuery)->sum('nilai_tl_selesai');

        $rekapitulasi = (object) [
            'total_rekom'      => $totalRekom,
            'rekom_selesai'    => $rekomSelesai,
            'total_kerugian'   => $totalKerugian,
            'total_tl_selesai' => $totalTlSelesai,
        ];

        // ── Overdue Items ──────────────────────────────────────────────────────
        $overdue = (clone $tlQuery)
            ->whereNotNull('tanggal_jatuh_tempo')
            ->where('tanggal_jatuh_tempo', '<=', now()->addDays(7))
            ->where('status_verifikasi', '!=', 'lunas')
            ->with(['recommendation.temuan.lhp.unitDiperiksa', 'recommendation.kodeRekomendasi'])
            ->orderBy('tanggal_jatuh_tempo')
            ->limit(10)
            ->get();

        // ── Recent Activity ────────────────────────────────────────────────────
        $recent = (clone $tlQuery)
            ->whereNotNull('status_opd')
            ->with(['uploadOpdOleh', 'recommendation.temuan.lhp.unitDiperiksa', 'recommendation.kodeRekomendasi'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        // ── Breakdown Per Kategori Program Audit ─────────────────────────────────────
        $kategoriBreakdown = collect(AuditProgram::KATEGORI)->map(function ($kat) use ($unitIds, $startDate, $endDate, $preset) {
            $lhpQuery = Lhp::whereIn('unit_diperiksa_id', $unitIds)
                ->whereHas('auditAssignment.auditProgramDetail.auditProgram', function ($q) use ($kat) {
                    $q->where('kategori', $kat);
                });

            if ($preset !== 'all') {
                $lhpQuery->whereBetween('tanggal_lhp', [$startDate, $endDate]);
            }

            $lhps = $lhpQuery->with(['statistik', 'temuans.recommendations.tindakLanjuts'])->get();

            $lhpCount = $lhps->count();
            $allRekom = $lhps->flatMap->temuans->flatMap->recommendations;
            $rekomCount = $allRekom->count();
            $totalKerugian = $lhps->sum('total_kerugian');
            $totalSetor = $allRekom->flatMap->tindakLanjuts->sum('total_terbayar');
            $progres = $lhpCount > 0 ? round($lhps->avg('persen_selesai') ?? 0) : 0;

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
            'opdStats'           => $opdStats,
            'verifikasiStats'    => $verifikasiStats,
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
