<?php

namespace App\Http\Controllers;

use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Exports\RekapSemuaLhpExport;
use App\Exports\RekapPerLhpExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    // ── Halaman utama laporan ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lhp::with(['statistik', 'auditAssignment.auditProgram', 'unitDiperiksa', 'auditAssignment.auditProgramDetail'])
            ->forUser($user);

        $this->applyFilters($query, $request);

        $lhpIds    = (clone $query)->withoutEagerLoads()->pluck('id');
        $ringkasan = $this->hitungRingkasan($lhpIds);

        $lhps = $query->orderBy('tanggal_lhp', 'desc')->paginate(15)->withQueryString();

        $irbanList = Cache::remember('laporan:irbanList:' . $user->id, 600, function () use ($user) {
            return AuditProgramDetail::whereHas('assignments', function ($q) use ($user) {
                    $q->whereHas('lhps');
                    if (!$user->hasRole('super_admin')) {
                        $q->where('ketua_tim_id', $user->id)
                          ->orWhereHas('members', fn($q2) => $q2->where('user_id', $user->id));
                    }
                })
                ->whereNotNull('tim')
                ->distinct()
                ->orderBy('tim')
                ->pluck('tim');
        });

        $kategoris  = AuditProgram::KATEGORI;

        return view('pages.laporan.index', compact(
            'lhps', 'ringkasan', 'irbanList', 'kategoris'
        ));
    }

    // ── Rekap per LHP (detail satu LHP) ───────────────────────────────────

    public function rekapPerLhp(Request $request, Lhp $lhp)
    {
        $lhp->load([
            'statistik',
            'auditAssignment.auditProgram',
            'unitDiperiksa',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts',
            'temuans.recommendations.kodeRekomendasi',
            'creator',
        ]);

        return view('pages.laporan.rekap-per-lhp', compact('lhp'));
    }

    // ── Download PDF: Rekap Semua LHP ─────────────────────────────────────

    public function downloadPdfSemua(Request $request)
    {
        $user  = auth()->user();
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
        $lhps      = $query->orderBy('tanggal_lhp', 'desc')->get();
        $lhpIds    = $lhps->pluck('id');
        $ringkasan = $this->hitungRingkasan($lhpIds);
        $filter    = $request->only(['tahun', 'semester', 'irban', 'status', 'dari', 'sampai']);

        $pdf = Pdf::loadView('pages.laporan.pdf.rekap-semua', compact('lhps', 'ringkasan', 'filter'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'   => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $filename = 'rekap-lhp-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }

    // ── Download PDF: Rekap Per LHP ───────────────────────────────────────

    public function downloadPdfPerLhp(Request $request, Lhp $lhp)
    {
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
                'defaultFont'   => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $filename = 'rekap-lhp-' . $lhp->nomor_lhp . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->download(str_replace('/', '-', $filename));
    }

    // ── Preview PDF: Rekap Per LHP (stream inline) ─────────────────────────

    public function previewPdfPerLhp(Request $request, Lhp $lhp)
    {
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
                'defaultFont'   => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $filename = 'rekap-lhp-' . $lhp->nomor_lhp . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->stream(str_replace('/', '-', $filename));
    }

    // ── Download Excel: Rekap Semua LHP ───────────────────────────────────

    public function downloadExcelSemua(Request $request)
    {
        $user  = auth()->user();
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

        $filename = 'rekap-semua-lhp-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new RekapSemuaLhpExport($lhps), $filename);
    }

    // ── Download Excel: Rekap Per LHP ─────────────────────────────────────

    public function downloadExcelPerLhp(Request $request, Lhp $lhp)
    {
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

        $filename = 'rekap-lhp-' . str_replace('/', '-', $lhp->nomor_lhp) . '-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new RekapPerLhpExport($lhp), $filename);
    }

    // ── Helper: apply filter ke query ─────────────────────────────────────

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                  ->orWhereHas('auditAssignment.auditProgram', fn($sq) => $sq->where('nama_program', 'like', "%{$search}%"))
                  ->orWhereHas('auditAssignment', fn($sq) => $sq->where('nomor_surat', 'like', "%{$search}%"));
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
            'total_lhp'      => $stat->total_lhp ?? 0,
            'total_temuan'   => $stat->total_temuan ?? 0,
            'total_rekom'    => $stat->total_rekom ?? 0,
            'rekom_selesai'  => $stat->rekom_selesai ?? 0,
            'rekom_proses'   => $stat->rekom_proses ?? 0,
            'rekom_belum'    => $stat->rekom_belum ?? 0,
            'total_kerugian' => $stat->total_kerugian ?? 0,
            'total_tl_selesai' => $stat->total_tl_selesai ?? 0,
            'total_sisa'     => $stat->total_sisa ?? 0,
            'avg_persen'     => round($stat->avg_persen ?? 0, 1),
        ];
    }
}