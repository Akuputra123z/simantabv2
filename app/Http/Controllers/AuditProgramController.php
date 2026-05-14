<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditProgramController extends Controller
{
    /**
     * Menampilkan daftar Induk PKPT
     */
    public function index(Request $request)
    {
        $query = AuditProgram::query()
            ->with(['details'])
            ->withCount(['details', 'assignments']);

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
        $details = $auditProgram->details()
            ->withCount('assignments')
            ->paginate(10)
            ->onEachSide(1);

        return view('pages.audit-program.show', compact('auditProgram', 'details'));
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
            'status'       => 'required|in:draft,active,closed',
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