<?php

namespace App\Http\Controllers;

use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Exports\RekapSemuaLhpExport;
use App\Exports\RekapPerLhpExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    // ── Halaman utama laporan ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lhp::with(['statistik', 'auditAssignment.auditProgram', 'auditAssignment.unitDiperiksa'])
            ->forUser($user);

        $this->applyFilters($query, $request);

        $lhps = $query->orderBy('tanggal_lhp', 'desc')->paginate(15)->withQueryString();

        // Ringkasan aggregate untuk header
        $lhpIds     = $query->pluck('id');
        $ringkasan  = $this->hitungRingkasan($lhpIds);

        $tahunList  = Lhp::forUser($user)->selectRaw('YEAR(tanggal_lhp) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');

        $irbanList  = Lhp::forUser($user)->distinct()->orderBy('irban')->pluck('irban');

        return view('pages.laporan.index', compact(
            'lhps', 'ringkasan', 'tahunList', 'irbanList'
        ));
    }

    // ── Rekap per LHP (detail satu LHP) ───────────────────────────────────

    public function rekapPerLhp(Request $request, Lhp $lhp)
    {
        $lhp->load([
            'statistik',
            'auditAssignment.auditProgram',
            'auditAssignment.unitDiperiksa',
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
            'auditAssignment.unitDiperiksa',
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
            'auditAssignment.unitDiperiksa',
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

    // ── Download Excel: Rekap Semua LHP ───────────────────────────────────

    public function downloadExcelSemua(Request $request)
    {
        $user  = auth()->user();
        $query = Lhp::with([
            'statistik',
            'auditAssignment.auditProgram',
            'auditAssignment.unitDiperiksa',
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
            'auditAssignment.unitDiperiksa',
            'temuans.kodeTemuan',
            'temuans.recommendations.tindakLanjuts',
            'temuans.recommendations.kodeRekomendasi',
        ]);

        $filename = 'rekap-lhp-' . str_replace('/', '-', $lhp->nomor_lhp) . '-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new RekapPerLhpExport($lhp), $filename);
    }

    // ── Helper: apply filter ke query ─────────────────────────────────────

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_lhp', $request->tahun);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('irban')) {
            $query->where('irban', $request->irban);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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