<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use Illuminate\Http\Request;


class AuditProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditProgram::query()
            ->withCount('assignments'); // Mengambil realisasi_assignment secara efisien

        // Filter Pencarian
        if ($request->filled('search')) {
            $query->where('nama_program', 'like', '%' . $request->search . '%');
        }

        // Filter Tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return view('pages.audit-program.index', compact('data'));
    }

    public function create()
    {
        return view('pages.audit-program.create');
    }

    // Simpan data baru
   public function store(Request $request)
{

    $validated = $request->validate([
        'nama_program'      => 'required|string|max:255',
        'tahun'             => 'required|integer|digits:4',
        'target_assignment' => 'required|integer|min:1',
    ]);

    AuditProgram::create([
        'nama_program'      => $validated['nama_program'],
        'tahun'             => $validated['tahun'],
        'target_assignment' => $validated['target_assignment'],
        'status'            => 'berjalan',
        'created_by'        => auth()->id(),
        'updated_by'        => auth()->id(),
    ]);

    return redirect()->route('audit-program.index')
        ->with('success', 'Program Audit berhasil ditambahkan.');
}

    // Edit data
    public function edit(AuditProgram $auditProgram)
{
    // Rename the variable being passed to the view
    $program = $auditProgram; 
    
    return view('pages.audit-program.edit', compact('program'));
}

    // Update data
 public function update(Request $request, AuditProgram $auditProgram)
{
    // 1. Tampung hasil validasi ke dalam variabel $validated
    $validated = $request->validate([
        'nama_program'      => 'required|string|max:255',
        'tahun'             => 'required|integer|digits:4',
        'target_assignment' => 'required|integer|min:1',
        'status'            => 'required|in:draft,berjalan,selesai', // Tambahkan 'draft' jika ada di migrasi
    ]);

    // 2. Tambahkan informasi siapa yang mengubah data (Audit Trail)
    $validated['updated_by'] = auth()->id();

    // 3. Update HANYA menggunakan data yang telah divalidasi
    // Ini mencegah error "Field doesn't have a default value" dan celah keamanan
    $auditProgram->update($validated);

    return redirect()->route('audit-program.index')
        ->with('success', 'Program Audit berhasil diperbarui.');
}

    public function show(AuditProgram $auditProgram)
    {
        // Eager load relasi assignments dan unitDiperiksa agar tampilan detail cepat
        $auditProgram->load(['assignments.unitDiperiksa']);

        return view('pages.audit-program.show', compact('auditProgram'));
    }

    // Hapus data
    public function destroy(AuditProgram $auditProgram)
    {
        $auditProgram->delete();
        return redirect()->route('audit-program.index')
            ->with('success', 'Program Audit berhasil dihapus.');
    }
}
