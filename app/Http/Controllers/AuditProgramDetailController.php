<?php

namespace App\Http\Controllers;

use App\Exports\TemplateAuditDetailExport;
use App\Imports\AuditProgramDetailImport; // Pastikan ini ada
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AuditProgramDetailController extends Controller
{
    /**
     * Fitur Import Data dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'audit_program_id' => 'required|exists:audit_programs,id',
        ]);

        try {
            $auditProgramId = $request->audit_program_id;
            
            // Eksekusi Import menggunakan Class Import
            Excel::import(new AuditProgramDetailImport($auditProgramId), $request->file('file'));
            
            return redirect()
                ->route('audit-program.show', $auditProgramId)
                ->with('success', 'Data sub-program berhasil di-import.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplateAuditDetailExport, 'template_sub_program.xlsx');
    }

    public function create($audit_program_id)
    {
        $auditProgram = AuditProgram::findOrFail($audit_program_id);
        return view('pages.audit-program-detail.create', compact('auditProgram'));
    }

    public function store(Request $request)
    {
        if ($request->has('anggaran')) {
            $request->merge(['anggaran' => str_replace(['.', ','], '', $request->anggaran)]);
        }

        $validated = $request->validate([
            'audit_program_id'    => 'required|exists:audit_programs,id',
            'nama_detail_program' => 'required|string|max:255',
            'jenis_kegiatan'      => 'required|string|max:100',
            'objek_pengawasan'    => 'nullable|string|max:255',
            'ruang_lingkup'       => 'nullable|string',
            'personil'            => 'nullable|string|max:100',
            'tujuan'              => 'required|string', 
            'anggaran'            => 'required|numeric|min:0',
            'tingkat_resiko'      => 'nullable|in:Tinggi,Sedang,Rendah',
            'jadwal'              => 'nullable|string|max:100',
            'status'              => 'required|in:aktif,rencana',
            'tim'                 => 'nullable|string|max:100',
            'laporan_akhir'       => 'nullable|string|max:100',
        ]);

        try {
            $detail = AuditProgramDetail::create($validated);
            return redirect()->route('audit-program.show', $detail->audit_program_id)->with('success', 'Sub-program berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $detail = AuditProgramDetail::with(['parentProgram', 'assignments.ketuaTim', 'assignments.unitDiperiksa'])->findOrFail($id);
        $assignments = $detail->assignments;
        return view('pages.audit-program-detail.show', compact('detail', 'assignments'));
    }

    public function edit(AuditProgramDetail $auditProgramDetail)
    {
        $detail = $auditProgramDetail;
        $auditProgram = $detail->parentProgram; 
        return view('pages.audit-program-detail.edit', compact('detail', 'auditProgram'));
    }

    public function update(Request $request, AuditProgramDetail $auditProgramDetail)
    {
        if ($request->has('anggaran')) {
            $request->merge(['anggaran' => str_replace(['.', ','], '', $request->anggaran)]);
        }

        $validated = $request->validate([
            'nama_detail_program' => 'required|string|max:255',
            'jenis_kegiatan'      => 'required|string|max:100',
            'objek_pengawasan'    => 'nullable|string|max:255',
            'ruang_lingkup'       => 'nullable|string',
            'personil'            => 'nullable|string|max:100',
            'tujuan'              => 'required|string',
            'anggaran'            => 'required|numeric|min:0',
            'tingkat_resiko'      => 'nullable|in:Tinggi,Sedang,Rendah',
            'jadwal'              => 'nullable|string|max:100',
            'status'              => 'required|in:aktif,rencana',
            'tim'                 => 'nullable|string|max:100',
            'laporan_akhir'       => 'nullable|string|max:100',
        ]);

        try {
            $auditProgramDetail->update($validated);
            return redirect()->route('audit-program-detail.show', $auditProgramDetail->id)->with('success', "Data diperbarui.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function destroy(AuditProgramDetail $auditProgramDetail)
    {
        if ($auditProgramDetail->assignments()->exists()) {
            return back()->with('error', 'Gagal menghapus! Sudah ada data penugasan.');
        }
        $parentId = $auditProgramDetail->audit_program_id;
        $auditProgramDetail->delete();
        return redirect()->route('audit-program.show', $parentId)->with('success', 'Sub-program berhasil dihapus.');
    }
}