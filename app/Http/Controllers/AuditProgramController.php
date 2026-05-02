<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use Illuminate\Http\Request;


class AuditProgramController extends Controller
{
public function index(Request $request)
{
    $query = AuditProgram::query()
        ->withCount([
            // Total assignment yang ada
            'assignments',

            // Assignment yang statusnya 'selesai' → untuk progress program
            'assignments as assignments_selesai_count' => fn($q) => $q->where('status', 'selesai'),

            // Assignment berjalan → untuk status dinamis
            'assignments as assignments_berjalan_count' => fn($q) => $q->where('status', 'berjalan'),
        ]);

    if ($request->filled('search')) {
        $query->where('nama_program', 'like', '%' . $request->search . '%');
    }

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
    $validated = $request->validate([
        'nama_program'      => 'required|string|max:255',
        'tahun'             => 'required|integer|digits:4',
        'target_assignment' => 'required|integer|min:1',
        'status'            => 'required|in:draft,berjalan,selesai',
    ]);

    $validated['updated_by'] = auth()->id();
    $statusLama = $auditProgram->status;

    // Update field non-status dulu
    $auditProgram->update(collect($validated)->except('status')->toArray());

    if ($statusLama !== $validated['status']) {
        if ($validated['status'] === 'draft') {
            // Reset semua assignment ke draft
            $auditProgram->assignments()->update(['status' => 'draft']);

            // Trigger recalculate semua LHP yang terkait agar
            // statistik, progress, dan status chain ikut ter-reset
            $lhpIds = \App\Models\Lhp::whereHas('auditAssignment', function ($q) use ($auditProgram) {
                $q->where('audit_program_id', $auditProgram->id);
            })->pluck('id');

            $statistikService = app(\App\Services\LhpStatistikService::class);
            foreach ($lhpIds as $lhpId) {
                $statistikService->updateStatistik($lhpId);
            }

            // Setelah chain recalculate, set status program ke draft secara eksplisit
            // karena sinkronStatusProgram() di dalam service mungkin override ke 'berjalan'
            $auditProgram->updateQuietly(['status' => 'draft']);

        } elseif ($validated['status'] === 'selesai') {
            // Cek apakah semua assignment memang sudah selesai sebelum allow
            $adaYangBelumSelesai = $auditProgram->assignments()
                ->where('status', '!=', 'selesai')
                ->exists();

            if ($adaYangBelumSelesai) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'status' => 'Program tidak dapat ditandai selesai karena masih ada assignment yang belum selesai.',
                    ]);
            }

            $auditProgram->updateQuietly(['status' => 'selesai']);

        } else {
            // 'berjalan' — sinkronkan dari kondisi aktual assignment
            $auditProgram->updateQuietly(['status' => $validated['status']]);
        }
    }

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
