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
        $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|integer',
            'target_assignment' => 'required|integer|min:1',
        ]);

        AuditProgram::create([
            'nama_program' => $request->nama_program,
            'tahun' => $request->tahun,
            'target_assignment' => $request->target_assignment,
            'status' => 'berjalan', // Default status
        ]);

        return redirect()->route('audit-program.index')
            ->with('success', 'Program Audit berhasil ditambahkan.');
    }

    // Edit data
    public function edit(AuditProgram $auditProgram)
    {
        return view('pages.audit-program.edit', compact('auditProgram'));
    }

    // Update data
    public function update(Request $request, AuditProgram $auditProgram)
    {
        $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|integer',
            'target_assignment' => 'required|integer',
            'status' => 'required|in:berjalan,selesai',
        ]);

        $auditProgram->update($request->all());

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
