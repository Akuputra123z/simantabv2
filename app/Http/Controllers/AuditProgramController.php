<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AuditProgramController extends Controller
{
    /**
     * Menampilkan daftar Induk PKPT
     */
    public function index(Request $request)
    {
        $query = AuditProgram::query()
            ->with(['details'])
            ->withCount([
                'details',
                'assignments',
                'details as sudah_lhp_count' => function ($q) {
                    $q->where(function ($q) {
                        $q->whereHas('assignments.lhps', function ($q) {
                            $q->whereIn('status', ['final', 'ditandatangani'])
                              ->whereHas('statistik', fn ($s) => $s->where('persen_selesai_gabungan', 100));
                        })->orWhereHas('assignments', fn ($q) => $q->where('status', 'selesai'));
                    });
                },
            ]);

        // Logika Filter
        if ($request->filled('search')) {
            $query->where('nama_program', 'like', "%{$request->search}%");
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting berdasarkan tahun terbaru dan ID terbaru
        $data = $query->latest('tahun')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.audit-program.index', compact('data'));
    }

    /**
     * Form tambah PKPT Induk
     */
    public function create()
    {
        return view('pages.audit-program.create');
    }

    /**
     * Simpan PKPT Induk Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun'        => 'required|integer|digits:4',
        ]);

        $program = AuditProgram::create(array_merge($validated, [
            'status'     => 'draft',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]));

        return redirect()->route('audit-program.index')
            ->with('success', 'PKPT Berhasil dibuat.');
    }

    /**
     * Menampilkan Detail Program & List PKPT di bawahnya
     */
    public function show(AuditProgram $auditProgram)
    {
        $auditProgram->loadCount([
            'details',
            'details as sudah_lhp_count' => function ($q) {
                $q->where(function ($q) {
                    $q->whereHas('assignments.lhps', function ($q) {
                        $q->whereIn('status', ['final', 'ditandatangani'])
                          ->whereHas('statistik', fn ($s) => $s->where('persen_selesai_gabungan', 100));
                    })->orWhereHas('assignments', fn ($q) => $q->where('status', 'selesai'));
                });
            },
        ]);

        $search = request('search');

        $details = $auditProgram->details()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nama_detail_program', 'like', "%{$search}%")
                  ->orWhere('jenis_kegiatan', 'like', "%{$search}%")
                  ->orWhere('objek_pengawasan', 'like', "%{$search}%")
                  ->orWhere('ruang_lingkup', 'like', "%{$search}%")
                  ->orWhere('tim', 'like', "%{$search}%");
            }))
            ->withCount([
                'assignments',
                'assignments as assignments_selesai_count' => fn ($q) => $q->where('status', 'selesai'),
            ])
            ->latest()
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        return view('pages.audit-program.show', compact('auditProgram', 'details', 'search'));
    }

    /**
     * FIXED: Fungsi Edit yang sebelumnya hilang
     */
    public function edit(AuditProgram $auditProgram)
    {

        $program = $auditProgram;
        return view('pages.audit-program.edit', compact('program'));
    }

    /**
     * Update data Induk PKPT
     */
    public function update(Request $request, AuditProgram $auditProgram)
    {
        $validated = $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun'        => 'required|integer|digits:4',
            'status'       => 'required|in:draft,berjalan,selesai',
        ]);

        $auditProgram->update(array_merge($validated, [
            'updated_by' => auth()->id(),
        ]));

        // Cek jika request datang dari halaman edit atau modal di index/show
        if ($request->header('referer') == route('audit-program.edit', $auditProgram->id)) {
            return redirect()->route('audit-program.index')->with('success', 'Data PKPT berhasil diperbarui.');
        }

        return back()->with('success', 'Data PKPT berhasil diperbarui.');
    }

    /**
     * Hapus Program Audit (Hanya jika belum ada penugasan)
     */
    public function destroy(AuditProgram $auditProgram)
    {
        if ($auditProgram->assignments()->exists()) {
            return back()->with('error', 'PKPT tidak bisa dihapus karena sudah ada penugasan yang berjalan.');
        }

        $auditProgram->delete();

        return redirect()->route('audit-program.index')
            ->with('success', 'Program Audit berhasil dihapus.');
    }

    /**
     * Export PDF daftar sub-program
     */
    public function exportPdf(AuditProgram $auditProgram)
    {
        $details = $this->getFilteredDetails($auditProgram);

        $pdf = Pdf::loadView('pages.audit-program.pdf.detail', compact('auditProgram', 'details'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $filename = 'sub-program-' . str_replace('/', '-', $auditProgram->nama_program) . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Excel daftar sub-program
     */
    public function exportExcel(AuditProgram $auditProgram)
    {
        $details = $this->getFilteredDetails($auditProgram);

        $filename = 'sub-program-' . str_replace('/', '-', $auditProgram->nama_program) . '-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new \App\Exports\DetailProgramExport($auditProgram, $details), $filename);
    }

    /**
     * Ambil detail terfilter berdasarkan request (ids / search)
     */
    private function getFilteredDetails(AuditProgram $auditProgram)
    {
        $ids    = request('ids');
        $search = request('search');

        $query = $auditProgram->details()->withCount('assignments');

        if (!empty($ids)) {
            $query->whereIn('id', (array) $ids);
        } elseif ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_detail_program', 'like', "%{$search}%")
                  ->orWhere('jenis_kegiatan', 'like', "%{$search}%")
                  ->orWhere('objek_pengawasan', 'like', "%{$search}%")
                  ->orWhere('ruang_lingkup', 'like', "%{$search}%")
                  ->orWhere('tim', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama_detail_program')->get();
    }

    /**
     * API untuk mengambil detail program (digunakan oleh AJAX di AuditAssignment)
     */
    public function getProgramDetails($programId)
    {
        $details = AuditProgramDetail::where('audit_program_id', $programId)
            ->select('id', 'nama_detail_program', 'jenis_kegiatan')
            ->orderBy('nama_detail_program')
            ->get();
            
        return response()->json($details);
    }
}