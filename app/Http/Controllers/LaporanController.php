<?php

namespace App\Http\Controllers;

use App\Exports\RekapPerLhpExport;
use App\Exports\RekapSemuaLhpExport;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Models\UnitDiperiksa;
use App\Services\LaporanKepatuhanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    // ── Halaman utama laporan ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lhp::with(['statistik', 'auditAssignment.auditProgram', 'unitDiperiksa', 'auditAssignment.auditProgramDetail', 'auditAssignment.lhrs'])
            ->forUser($user);

        $this->applyFilters($query, $request);

        $lhpIds = (clone $query)->withoutEagerLoads()->pluck('id');
        $ringkasan = $this->hitungRingkasan($lhpIds);

        $lhps = $query->orderBy('tanggal_lhp', 'desc')->paginate(15)->withQueryString();

        $irbanList = Cache::remember('laporan:irbanList:'.$user->id, 600, function () use ($user) {
            return AuditProgramDetail::whereHas('assignments', function ($q) use ($user) {
                $q->whereHas('lhps');
                if (! $user->hasRole('super_admin') && ! $user->hasRole('kepala_inspektorat')) {
                    if ($user->hasRole(User::ROLE_PENGENDALI_TEKNIS) || $user->isPengendaliTeknis() || $user->isIrban()) {
                        $irbanNames = $user->getIrbanTeamNames();
                        $q->where(function ($subQ) use ($user, $irbanNames) {
                            $subQ->where('pengendali_teknis_id', $user->id)
                                ->orWhere('pengendali_teknis', $user->name)
                                ->orWhere(function ($sq) use ($irbanNames) {
                                    if (! empty($irbanNames)) {
                                        $sq->whereHas('auditProgramDetail', fn ($dq) => $dq->whereIn('tim', $irbanNames));
                                    }
                                })
                                ->orWhere('ketua_tim_id', $user->id)
                                ->orWhere('created_by', $user->id)
                                ->orWhereHas('members', fn ($q2) => $q2->where('user_id', $user->id));
                        });
                    } else {
                        $q->where(function ($subQ) use ($user) {
                            $subQ->where('ketua_tim_id', $user->id)
                                ->orWhere('created_by', $user->id)
                                ->orWhereHas('members', fn ($q2) => $q2->where('user_id', $user->id));
                        });
                    }
                }
            })
                ->whereNotNull('tim')
                ->distinct()
                ->orderBy('tim')
                ->pluck('tim');
        });

        $kategoris = AuditProgram::getKategoriOptions();

        return view('pages.laporan.index', compact(
            'lhps', 'ringkasan', 'irbanList', 'kategoris'
        ));
    }

    // ── Laporan Kepatuhan OPD (Strict 100% Progress / Nihil) ─────────────

    public function kepatuhanIndex(Request $request)
    {
        $user = auth()->user();

        $query = Lhp::with([
            'statistik',
            'unitDiperiksa',
            'auditAssignment.auditProgram',
            'auditAssignment.auditProgramDetail',
        ])->forUser($user);

        // Filter search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                    ->orWhereHas('unitDiperiksa', fn ($sq) => $sq->where('nama_unit', 'like', "%{$search}%")->orWhere('nama_kecamatan', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment.auditProgram', fn ($sq) => $sq->where('nama_program', 'like', "%{$search}%"));
            });
        }

        // Filter tahun LHP atau program
        if ($request->filled('tahun')) {
            $tahun = $request->tahun;
            $query->where(function ($q) use ($tahun) {
                $q->whereYear('tanggal_lhp', $tahun)
                    ->orWhereHas('auditAssignment.auditProgram', fn ($sq) => $sq->where('tahun', $tahun));
            });
        }

        // Filter status kepatuhan: 'patuh' (100%), 'proses' (<100%), atau 'semua'
        $statusKepatuhan = $request->get('status', 'semua');
        if ($statusKepatuhan === 'patuh') {
            $query->patuhLengkap();
        } elseif ($statusKepatuhan === 'proses') {
            $query->where('is_nihil', false)
                ->whereHas('statistik', function ($q) {
                    $q->where('persen_selesai_gabungan', '<', 100)
                        ->where('persen_selesai', '<', 100);
                });
        }

        // Ringkasan metrik kepatuhan
        $baseQuery = Lhp::forUser($user);
        if ($request->filled('tahun')) {
            $tahun = $request->tahun;
            $baseQuery->where(function ($q) use ($tahun) {
                $q->whereYear('tanggal_lhp', $tahun)
                    ->orWhereHas('auditAssignment.auditProgram', fn ($sq) => $sq->where('tahun', $tahun));
            });
        }
        $totalLhp = (clone $baseQuery)->count();
        $totalPatuh = (clone $baseQuery)->patuhLengkap()->count();
        $totalProses = max(0, $totalLhp - $totalPatuh);

        $perPage = in_array((int) $request->get('per_page', 15), [10, 15, 20, 25, 50, 100]) ? (int) $request->get('per_page', 15) : 15;
        $lhps = $query->orderBy('tanggal_lhp', 'desc')->paginate($perPage)->withQueryString();

        $tahunList = AuditProgram::distinct()->whereNotNull('tahun')->orderByDesc('tahun')->pluck('tahun');

        $summary = [
            'total_lhp' => $totalLhp,
            'total_patuh' => $totalPatuh,
            'total_proses' => $totalProses,
            'rate_patuh' => $totalLhp > 0 ? round(($totalPatuh / $totalLhp) * 100, 1) : 0,
        ];

        return view('pages.laporan.kepatuhan.index', compact('lhps', 'summary', 'tahunList', 'statusKepatuhan'));
    }

    public function kepatuhanShow(Lhp $lhp)
    {
        // Enforce canonical UUID in URL
        $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(3);
        if ($rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
            return redirect()->route('laporan.kepatuhan.show', $lhp, 301);
        }

        $user = auth()->user();
        if ($user && $user->hasRole('opd')) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            if ($unitIds->isNotEmpty()) {
                $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
                $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
            }
            $hasAccess = $unitIds->contains($lhp->unit_diperiksa_id) ||
                         $lhp->unitDiperiksas()->whereIn('unit_diperiksas.id', $unitIds)->exists();
            if (! $hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke Laporan Kepatuhan LHP ini.');
            }
        }

        // Strict Guard: Hanya jika 100% tuntas atau nihil
        if (! $lhp->isKepatuhanLengkap()) {
            return redirect()->route('laporan.kepatuhan')
                ->with('warning', "Laporan Kepatuhan OPD untuk Nomor LHP {$lhp->nomor_lhp} belum dapat ditampilkan karena progres tindak lanjut belum mencapai 100%.");
        }

        // Cek verifikasi password keamanan untuk show/preview dokumen
        $request = request();
        $isVerified = false;
        if ($request->hasValidSignature()) {
            $isVerified = true;
            $request->session()->put("pdf_access_verified.kepatuhan_show.{$lhp->id}", now()->addMinutes(15)->timestamp);
            $request->session()->put("pdf_access_verified.kepatuhan_show.{$lhp->uuid}", now()->addMinutes(15)->timestamp);
            $request->session()->put("pdf_access_verified.kepatuhan_pdf.{$lhp->id}", now()->addMinutes(15)->timestamp);
            $request->session()->put("pdf_access_verified.kepatuhan_pdf.{$lhp->uuid}", now()->addMinutes(15)->timestamp);
        } else {
            $sessKeys = array_filter([$lhp->id, $lhp->uuid]);
            foreach ($sessKeys as $k) {
                $sShow = session("pdf_access_verified.kepatuhan_show.{$k}");
                $sPdf = session("pdf_access_verified.kepatuhan_pdf.{$k}");
                if (($sShow && $sShow >= now()->timestamp) || ($sPdf && $sPdf >= now()->timestamp)) {
                    $isVerified = true;
                    break;
                }
            }
        }

        $isLocked = ! $isVerified;

        $lhp->load([
            'statistik',
            'unitDiperiksa',
            'auditAssignment.auditProgram',
            'auditAssignment.auditProgramDetail',
            'auditAssignment.ketuaTim',
            'temuans' => function ($q) {
                $q->with([
                    'kodeTemuan',
                    'recommendations' => function ($rq) {
                        $rq->with([
                            'kodeRekomendasi',
                            'tindakLanjuts' => function ($tq) {
                                $tq->with(['verifikator', 'cicilans'])->orderBy('created_at', 'asc');
                            },
                        ]);
                    },
                ]);
            },
            'creator',
        ]);

        $service = app(LaporanKepatuhanService::class);
        $analisisBerulang = $service->analisisTemuanBerulang($lhp);
        $masukanManajerial = $service->getMasukanManajerial($lhp, $analisisBerulang);
        $eskalasiSanksi = $service->getProtokolEskalasiSanksi();

        return view('pages.laporan.kepatuhan.show', compact('lhp', 'analisisBerulang', 'masukanManajerial', 'eskalasiSanksi', 'isLocked'));
    }

    public function kepatuhanPdf(Lhp $lhp)
    {
        // Enforce canonical UUID in URL
        if (! request()->hasValidSignature()) {
            $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(3);
            if ($rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
                return redirect()->route('laporan.kepatuhan.pdf', $lhp, 301);
            }
        }
        $user = auth()->user();
        if ($user && $user->hasRole('opd')) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            if ($unitIds->isNotEmpty()) {
                $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
                $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
            }
            $hasAccess = $unitIds->contains($lhp->unit_diperiksa_id) ||
                         $lhp->unitDiperiksas()->whereIn('unit_diperiksas.id', $unitIds)->exists();
            if (! $hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke Laporan Kepatuhan LHP ini.');
            }
        }

        // Strict Guard: Hanya jika 100% tuntas atau nihil
        if (! $lhp->isKepatuhanLengkap()) {
            abort(403, 'Laporan Kepatuhan OPD hanya dapat dicetak jika tindak lanjut telah tuntas 100%.');
        }

        $lhp->load([
            'statistik',
            'unitDiperiksa',
            'auditAssignment.auditProgram',
            'auditAssignment.auditProgramDetail',
            'auditAssignment.ketuaTim',
            'temuans' => function ($q) {
                $q->with([
                    'kodeTemuan',
                    'recommendations' => function ($rq) {
                        $rq->with([
                            'kodeRekomendasi',
                            'tindakLanjuts' => function ($tq) {
                                $tq->with(['verifikator', 'cicilans'])->orderBy('created_at', 'asc');
                            },
                        ]);
                    },
                ]);
            },
            'creator',
        ]);

        $service = app(LaporanKepatuhanService::class);
        $analisisBerulang = $service->analisisTemuanBerulang($lhp);
        $masukanManajerial = $service->getMasukanManajerial($lhp, $analisisBerulang);
        $eskalasiSanksi = $service->getProtokolEskalasiSanksi();

        $pdf = Pdf::loadView('pages.laporan.pdf.kepatuhan-opd', compact('lhp', 'analisisBerulang', 'masukanManajerial', 'eskalasiSanksi'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $filename = 'Laporan-Kepatuhan-OPD-'.str_replace(['/', '\\'], '-', $lhp->nomor_lhp).'.pdf';

        return $pdf->download($filename);
    }

    // ── Rekap per LHP (detail satu LHP) ───────────────────────────────────

    public function rekapPerLhp(Request $request, Lhp $lhp)
    {
        // 1. Canonical redirect ke URL UUID jika diakses dengan numeric ID (e.g. /laporan/59/rekap)
        $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(2);
        if ($rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
            return redirect()->route('laporan.rekap-per-lhp', $lhp, 301);
        }

        // 2. Proteksi password keamanan akun
        $sessionKeyId = "pdf_access_verified.rekap_show.{$lhp->id}";
        $sessionKeyUuid = "pdf_access_verified.rekap_show.{$lhp->uuid}";
        $isVerified = (
            $request->hasValidSignature() ||
            session($sessionKeyId) ||
            session($sessionKeyUuid) ||
            session("pdf_access_verified.lhp_preview.{$lhp->id}") ||
            session("pdf_access_verified.lhp_preview.{$lhp->uuid}") ||
            session("pdf_access_verified.lhp_download.{$lhp->id}") ||
            session("pdf_access_verified.lhp_download.{$lhp->uuid}")
        );

        if ($isVerified) {
            session([$sessionKeyId => true, $sessionKeyUuid => true]);
        }

        $isLocked = ! $isVerified;

        $lhp->load([
            'statistik',
            'auditAssignment.auditProgram',
            'unitDiperiksa',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts',
            'temuans.recommendations.kodeRekomendasi',
            'creator',
        ]);

        return view('pages.laporan.rekap-per-lhp', compact('lhp', 'isLocked'));
    }

    // ── Download PDF: Rekap Semua LHP ─────────────────────────────────────

    public function downloadPdfSemua(Request $request)
    {
        $user = auth()->user();
        $query = Lhp::with([
            'statistik',
            'auditAssignment.auditProgram',
            'unitDiperiksa',
            'auditAssignment.auditProgramDetail',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts.cicilans',
            'temuans.recommendations.kodeRekomendasi',
        ])->forUser($user);

        $this->applyFilters($query, $request);
        $lhps = $query->orderBy('tanggal_lhp', 'desc')->get();
        $lhpIds = $lhps->pluck('id');
        $ringkasan = $this->hitungRingkasan($lhpIds);
        $filter = $request->only(['tahun', 'semester', 'irban', 'status', 'dari', 'sampai']);

        $pdf = Pdf::loadView('pages.laporan.pdf.rekap-semua', compact('lhps', 'ringkasan', 'filter'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $filename = 'rekap-lhp-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($filename);
    }

    // ── Download PDF: Rekap Per LHP ───────────────────────────────────────

    public function downloadPdfPerLhp(Request $request, Lhp $lhp)
    {
        $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(4);
        if (! $request->hasValidSignature() && $rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
            return redirect()->route('laporan.download-pdf-per-lhp', $lhp, 301);
        }

        $lhp->load([
            'statistik',
            'auditAssignment.auditProgram',
            'unitDiperiksa',
            'auditAssignment.auditProgramDetail',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts',
            'temuans.recommendations.kodeRekomendasi',
            'creator',
        ]);

        $pdf = Pdf::loadView('pages.laporan.pdf.rekap-per-lhp', compact('lhp'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $filename = 'rekap-lhp-'.$lhp->nomor_lhp.'-'.now()->format('Ymd').'.pdf';

        return $pdf->download(str_replace('/', '-', $filename));
    }

    // ── Preview PDF: Rekap Per LHP (stream inline) ─────────────────────────

    public function previewPdfPerLhp(Request $request, Lhp $lhp)
    {
        $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(4);
        if (! $request->hasValidSignature() && $rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
            return redirect()->route('laporan.preview-pdf-per-lhp', $lhp, 301);
        }

        $lhp->load([
            'statistik',
            'auditAssignment.auditProgram',
            'unitDiperiksa',
            'auditAssignment.auditProgramDetail',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts',
            'temuans.recommendations.kodeRekomendasi',
            'creator',
        ]);

        $pdf = Pdf::loadView('pages.laporan.pdf.rekap-per-lhp', compact('lhp'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $filename = 'rekap-lhp-'.$lhp->nomor_lhp.'-'.now()->format('Ymd').'.pdf';

        return $pdf->stream(str_replace('/', '-', $filename));
    }

    // ── Download Excel: Rekap Semua LHP ───────────────────────────────────

    public function downloadExcelSemua(Request $request)
    {
        $user = auth()->user();
        $query = Lhp::with([
            'statistik',
            'auditAssignment.auditProgram',
            'unitDiperiksa',
            'auditAssignment.auditProgramDetail',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts.cicilans',
            'temuans.recommendations.kodeRekomendasi',
        ])->forUser($user);

        $this->applyFilters($query, $request);
        $lhps = $query->orderBy('tanggal_lhp', 'desc')->get();

        $filename = 'rekap-semua-lhp-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new RekapSemuaLhpExport($lhps), $filename);
    }

    // ── Download Excel: Rekap Per LHP ─────────────────────────────────────

    public function downloadExcelPerLhp(Request $request, Lhp $lhp)
    {
        $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(4);
        if ($rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
            return redirect()->route('laporan.download-excel-per-lhp', $lhp, 301);
        }

        $lhp->load([
            'statistik',
            'auditAssignment.auditProgram',
            'unitDiperiksa',
            'auditAssignment.auditProgramDetail',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts.cicilans',
            'temuans.recommendations.kodeRekomendasi',
            'creator',
        ]);

        $filename = 'rekap-lhp-'.str_replace('/', '-', $lhp->nomor_lhp).'-'.now()->format('Ymd').'.xlsx';

        return Excel::download(new RekapPerLhpExport($lhp), $filename);
    }

    // ── Helper: apply filter ke query ─────────────────────────────────────

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                    ->orWhereHas('auditAssignment.auditProgram', fn ($sq) => $sq->where('nama_program', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment.auditProgramDetail', fn ($sq) => $sq->where('nama_detail_program', 'like', "%{$search}%")->orWhere('tim', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment', fn ($sq) => $sq->where('nomor_surat', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment.lhrs', fn ($sq) => $sq->where('nomor_lhr', 'like', "%{$search}%"))
                    ->orWhereHas('unitDiperiksa', fn ($sq) => $sq->where('nama_unit', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('irban')) {
            $query->whereHas('auditAssignment.auditProgramDetail', function ($q) use ($request) {
                $q->where('tim', $request->irban);
            });
        }
        if ($request->filled('kategori')) {
            $query->whereHas('auditAssignment.auditProgram', function ($q) use ($request) {
                $q->where('kategori', $request->kategori);
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('statistik', function ($q) use ($request) {
                switch ($request->status) {
                    case 'lunas':
                        $q->where('persen_selesai_gabungan', '>=', 100);
                        break;
                    case 'sebagian':
                        $q->where('persen_selesai_gabungan', '>', 0)
                            ->where('persen_selesai_gabungan', '<', 100);
                        break;
                    case 'belum':
                        $q->where(function ($sq) {
                            $sq->whereNull('persen_selesai_gabungan')
                                ->orWhere('persen_selesai_gabungan', 0);
                        });
                        break;
                }
            });
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_lhp', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_lhp', '<=', $request->sampai);
        }
    }

    // ── Helper: hitung ringkasan aggregate ────────────────────────────────

    private function hitungRingkasan($lhpIds): array
    {
        $stat = LhpStatistik::whereIn('lhp_id', $lhpIds)
            ->selectRaw('
                COUNT(*) as total_lhp,
                SUM(total_temuan) as total_temuan,
                SUM(total_rekomendasi) as total_rekom,
                SUM(rekom_selesai) as rekom_selesai,
                SUM(rekom_proses) as rekom_proses,
                SUM(rekom_belum) as rekom_belum,
                SUM(total_kerugian) as total_kerugian,
                SUM(total_nilai_tl_selesai) as total_tl_selesai,
                SUM(total_sisa_kerugian) as total_sisa,
                AVG(persen_selesai_gabungan) as avg_persen
            ')
            ->first();

        return [
            'total_lhp' => $stat->total_lhp ?? 0,
            'total_temuan' => $stat->total_temuan ?? 0,
            'total_rekom' => $stat->total_rekom ?? 0,
            'rekom_selesai' => $stat->rekom_selesai ?? 0,
            'rekom_proses' => $stat->rekom_proses ?? 0,
            'rekom_belum' => $stat->rekom_belum ?? 0,
            'total_kerugian' => $stat->total_kerugian ?? 0,
            'total_tl_selesai' => $stat->total_tl_selesai ?? 0,
            'total_sisa' => $stat->total_sisa ?? 0,
            'avg_persen' => round($stat->avg_persen ?? 0, 1),
        ];
    }
}
