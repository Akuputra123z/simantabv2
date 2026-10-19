<?php

namespace App\Http\Controllers;

use App\Exports\DetailProgramExport;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_program', 'like', "%{$search}%")
                    ->orWhereHas('details', function ($dq) use ($search) {
                        $dq->where('nama_detail_program', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $status = strtolower($request->status);
            $isDetailSelesai = function ($q) {
                $q->where(function ($sq) {
                    $sq->whereHas('assignments.lhps', function ($lq) {
                        $lq->whereIn('status', ['final', 'ditandatangani'])
                            ->whereHas('statistik', fn ($s) => $s->where('persen_selesai_gabungan', 100));
                    })->orWhereHas('assignments', fn ($aq) => $aq->where('status', 'selesai'));
                });
            };

            if ($status === 'draft') {
                $query->whereDoesntHave('details');
            } elseif ($status === 'selesai') {
                $query->whereHas('details')
                    ->whereDoesntHave('details', function ($dq) use ($isDetailSelesai) {
                        $dq->whereNot($isDetailSelesai);
                    });
            } elseif ($status === 'berjalan') {
                $query->whereHas('details', function ($dq) use ($isDetailSelesai) {
                    $dq->whereNot($isDetailSelesai);
                });
            }
        }

        // Per page & Sorting dinamis
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 8, 10, 20, 25, 30, 50, 100])) {
            $perPage = 10;
        }

        $sortableColumns = ['tahun', 'nama_program', 'kategori'];
        $sort = in_array($request->sort, $sortableColumns) ? $request->sort : 'tahun';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $data = $query->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $kategoris = AuditProgram::KATEGORI;

        $tahuns = AuditProgram::select('tahun')
            ->whereNotNull('tahun')
            ->distinct()
            ->pluck('tahun')
            ->merge(range((int) date('Y') + 1, (int) date('Y') - 3))
            ->unique()
            ->sortDesc()
            ->values();

        return view('pages.audit-program.index', compact('data', 'kategoris', 'tahuns', 'sort', 'direction'));
    }

    /**
     * Form tambah PKPT Induk
     */
    public function create()
    {
        $kategoriOptions = AuditProgram::KATEGORI;

        return view('pages.audit-program.create', compact('kategoriOptions'));
    }

    /**
     * Simpan PKPT Induk Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|integer|digits:4',
            'kategori' => 'required|string|in:'.implode(',', AuditProgram::KATEGORI),
        ]);

        $program = AuditProgram::create(array_merge($validated, [
            'status' => 'draft',
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
        // Enforce canonical UUID in URL
        $rawParam = request()->route()?->originalParameter('auditProgram') ?? request()->segment(2);
        if ($rawParam && ($rawParam !== $auditProgram->uuid || is_numeric($rawParam))) {
            return redirect()->route('audit-program.show', $auditProgram, 301);
        }

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
        // Enforce canonical UUID in URL
        $rawParam = request()->route()?->originalParameter('auditProgram') ?? request()->segment(2);
        if ($rawParam && ($rawParam !== $auditProgram->uuid || is_numeric($rawParam))) {
            return redirect()->route('audit-program.edit', $auditProgram, 301);
        }

        $program = $auditProgram;
        $kategoriOptions = AuditProgram::KATEGORI;

        return view('pages.audit-program.edit', compact('program', 'kategoriOptions'));
    }

    /**
     * Update data Induk PKPT
     */
    public function update(Request $request, AuditProgram $auditProgram)
    {
        $validated = $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|integer|digits:4',
            'kategori' => 'required|string|in:'.implode(',', AuditProgram::KATEGORI),
            'status' => 'required|in:draft,berjalan,selesai',
        ]);

        $auditProgram->update(array_merge($validated, [
            'updated_by' => auth()->id(),
        ]));

        // Cek jika request datang dari halaman edit atau modal di index/show
        $referer = (string) $request->header('referer');
        if (str_contains($referer, '/edit')) {
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

    public function preview(AuditProgram $auditProgram)
    {
        // Enforce canonical UUID in URL
        $rawParam = request()->route()?->originalParameter('auditProgram') ?? request()->segment(2);
        if ($rawParam && ($rawParam !== $auditProgram->uuid || is_numeric($rawParam))) {
            return redirect()->route('audit-program.preview', $auditProgram, 301);
        }

        $auditProgram->load('approver');

        return view('pages.audit-program.preview', compact('auditProgram'));
    }

    public function previewPdf(AuditProgram $auditProgram)
    {
        $auditProgram->load('approver');
        $details = $this->getFilteredDetails($auditProgram);

        $pdf = Pdf::loadView('pages.audit-program.pdf.detail', compact('auditProgram', 'details'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        return $pdf->stream('preview-'.str_replace('/', '-', $auditProgram->nama_program).'-'.now()->format('Ymd').'.pdf');
    }

    /**
     * Export PDF daftar sub-program
     */
    public function exportPdf(AuditProgram $auditProgram)
    {
        $auditProgram->load('approver');
        $details = $this->getFilteredDetails($auditProgram);

        $pdf = Pdf::loadView('pages.audit-program.pdf.detail', compact('auditProgram', 'details'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $slug = \Illuminate\Support\Str::slug($auditProgram->nama_program) ?: 'pkpt-'.$auditProgram->id;
        $filename = 'sub-program-'.$slug.'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export Excel daftar sub-program
     */
    public function exportExcel(AuditProgram $auditProgram)
    {
        $details = $this->getFilteredDetails($auditProgram);

        $slug = \Illuminate\Support\Str::slug($auditProgram->nama_program) ?: 'pkpt-'.$auditProgram->id;
        $filename = 'sub-program-'.$slug.'-'.now()->format('Ymd').'.xlsx';

        return Excel::download(new DetailProgramExport($auditProgram, $details), $filename);
    }

    public function downloadApprovedPdf(AuditProgram $auditProgram)
    {
        if (! $auditProgram->isApproved()) {
            return back()->with('error', 'PKPT ini belum disetujui.');
        }

        $auditProgram->load('approver');

        if ($auditProgram->approved_pdf && Storage::disk('public')->exists($auditProgram->approved_pdf)) {
            $path = Storage::disk('public')->path($auditProgram->approved_pdf);
            $filename = basename($auditProgram->approved_pdf);

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.str_replace(' ', '-', $filename).'"',
            ]);
        }

        $details = $this->getFilteredDetails($auditProgram);

        $pdf = Pdf::loadView('pages.audit-program.pdf.detail', compact('auditProgram', 'details'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $filename = 'approved-'.str_replace('/', '-', $auditProgram->nama_program).'-'.now()->format('Ymd').'.pdf';

        return $pdf->stream(str_replace(' ', '-', $filename));
    }

    public function approve(AuditProgram $auditProgram)
    {
        if (! auth()->user()->hasRole('kepala_inspektorat') && ! auth()->user()->hasRole('super_admin')) {
            return back()->with('error', 'Hanya Kepala Inspektorat yang dapat menyetujui PKPT.');
        }

        if ($auditProgram->isApproved()) {
            return back()->with('error', 'PKPT ini sudah disetujui.');
        }

        $auditProgram->update([
            'approval_status' => AuditProgram::APPROVAL_DISETUJUI,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $auditProgram = AuditProgram::with('approver')->find($auditProgram->id);
        $details = $this->getFilteredDetails($auditProgram);

        $filename = 'approved-'.str_replace('/', '-', $auditProgram->nama_program).'-'.now()->format('Ymd').'.pdf';

        $pdf = Pdf::loadView('pages.audit-program.pdf.detail', compact('auditProgram', 'details'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        Storage::disk('public')->put('approved-pdfs/'.$filename, $pdf->output());

        $auditProgram->update(['approved_pdf' => 'approved-pdfs/'.$filename]);

        return back()->with('success', 'PKPT berhasil disetujui.');
    }

    public function batalSetujui(AuditProgram $auditProgram)
    {
        if (! auth()->user()->hasRole('kepala_inspektorat') && ! auth()->user()->hasRole('super_admin')) {
            return back()->with('error', 'Hanya Kepala Inspektorat yang dapat membatalkan persetujuan PKPT.');
        }

        if (! $auditProgram->isApproved()) {
            return back()->with('error', 'PKPT ini belum disetujui.');
        }

        if ($auditProgram->approved_pdf && Storage::disk('public')->exists($auditProgram->approved_pdf)) {
            Storage::disk('public')->delete($auditProgram->approved_pdf);
        }

        $auditProgram->update([
            'approval_status' => AuditProgram::APPROVAL_DRAFT,
            'approved_by' => null,
            'approved_at' => null,
            'approved_pdf' => null,
        ]);

        return back()->with('success', 'Persetujuan PKPT berhasil dibatalkan.');
    }

    public function reject(AuditProgram $auditProgram)
    {
        if (! auth()->user()->hasRole('kepala_inspektorat') && ! auth()->user()->hasRole('super_admin')) {
            return back()->with('error', 'Hanya Kepala Inspektorat yang dapat menolak PKPT.');
        }

        $auditProgram->update([
            'approval_status' => AuditProgram::APPROVAL_DITOLAK,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'PKPT ditolak.');
    }

    /**
     * Ambil detail terfilter berdasarkan request (ids / search)
     */
    private function getFilteredDetails(AuditProgram $auditProgram)
    {
        $ids = request('ids');
        $search = request('search');

        $query = $auditProgram->details()->withCount('assignments');

        if (! empty($ids)) {
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
