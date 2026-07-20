<?php

namespace App\Http\Controllers;

use App\Exports\TemplateAuditDetailExport;
use App\Helpers\DateHelper;
use App\Imports\AuditProgramDetailImport;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AuditProgramDetailController extends Controller
{
    /**
     * Fitur Import Data dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'             => 'required|mimes:xlsx,xls,csv|max:10240',
            'audit_program_id' => 'required|exists:audit_programs,id',
            'mode'             => 'required|in:add,replace',
        ]);

        $auditProgramId = $request->audit_program_id;

        DB::beginTransaction();

        try {
            $skippedNames = [];
            $deletedCount = 0;

            if ($request->mode === 'replace') {
                $allDetails = AuditProgramDetail::where('audit_program_id', $auditProgramId)
                    ->withExists('assignments')
                    ->get();

                foreach ($allDetails as $detail) {
                    if ($detail->assignments_exists) {
                        $skippedNames[] = $detail->nama_detail_program;
                    } else {
                        $detail->delete();
                        $deletedCount++;
                    }
                }
            }

            Excel::import(new AuditProgramDetailImport($auditProgramId), $request->file('file'));

            DB::commit();

            $importedCount = AuditProgramDetail::where('audit_program_id', $auditProgramId)->count();

            if ($request->mode === 'replace') {
                $msg = "$deletedCount data lama diganti.";
                if (!empty($skippedNames)) {
                    $msg .= ' ' . count($skippedNames) . ' data dilewati (sudah punya penugasan): ' . implode(', ', $skippedNames) . '.';
                }
            } else {
                $msg = "$importedCount data sub-program berhasil ditambahkan.";
            }

            return redirect()
                ->route('audit-program.show', $auditProgramId)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
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
        $kategori = $auditProgram->kategori;
        return view('pages.audit-program-detail.create', compact('auditProgram', 'kategori'));
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
            'tim'                 => 'nullable|string|max:255',
            'laporan_akhir'       => 'nullable|string|max:100',
        ]);

        if ($request->filled('jadwal')) {
            $validated['jadwal'] = DateHelper::toStorage($request->jadwal);
        }

        try {
            $detail = AuditProgramDetail::create($validated);
            return redirect()->route('audit-program.show', $detail->audit_program_id)->with('success', 'Sub-program berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $detail = AuditProgramDetail::with(['parentProgram', 'assignments.ketuaTim', 'assignments.unitDiperiksas'])->findOrFail($id);
        $assignments = $detail->assignments;
        return view('pages.audit-program-detail.show', compact('detail', 'assignments'));
    }

    public function edit(AuditProgramDetail $auditProgramDetail)
    {
        $detail = $auditProgramDetail;
        $auditProgram = $detail->parentProgram;
        $kategori = $auditProgram->kategori;
        return view('pages.audit-program-detail.edit', compact('detail', 'auditProgram', 'kategori'));
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
            'tim'                 => 'nullable|string|max:255',
            'laporan_akhir'       => 'nullable|string|max:100',
        ]);

        if ($request->filled('jadwal')) {
            $validated['jadwal'] = DateHelper::toStorage($request->jadwal);
        }

        try {
            $auditProgramDetail->update($validated);
            return redirect()->route('audit-program-detail.show', $auditProgramDetail->id)->with('success', "Data diperbarui.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:audit_program_details,id',
        ]);

        $ids = $request->ids;
        $auditProgramId = AuditProgramDetail::whereIn('id', $ids)->value('audit_program_id');

        $blocked = AuditProgramDetail::whereIn('id', $ids)->whereHas('assignments')->pluck('id');
        $allowed = array_diff($ids, $blocked->toArray());

        if (!empty($allowed)) {
            AuditProgramDetail::whereIn('id', $allowed)->delete();
        }

        $total = count($ids);
        $deleted = count($allowed);
        $skipped = count($blocked);

        if ($skipped > 0 && $deleted === 0) {
            return redirect()->route('audit-program.show', $auditProgramId)
                ->with('error', "Tidak dapat menghapus $skipped data karena sudah memiliki penugasan.");
        }

        $msg = "$deleted data berhasil dihapus.";
        if ($skipped > 0) {
            $msg .= " $skipped data dilewati karena sudah memiliki penugasan.";
        }

        return redirect()->route('audit-program.show', $auditProgramId)
            ->with('success', $msg);
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