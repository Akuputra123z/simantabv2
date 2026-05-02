<?php

namespace App\Http\Controllers;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditAssignmentController extends Controller
{
public function index(Request $request)
{
    $query = AuditAssignment::with(['ketuaTim', 'auditProgram', 'unitDiperiksa'])
        // ✅ Assignment selesai jika sudah punya minimal 1 LHP
        ->withCount('lhps');

    if ($search = $request->get('search')) {
        $query->where(function($q) use ($search) {
            $q->whereHas('auditProgram', fn($q) =>
                $q->where('nama_program', 'like', "%{$search}%")
            )->orWhereHas('ketuaTim', fn($q) =>
                $q->where('name', 'like', "%{$search}%")
            );
        });
    }

    if ($tahun = $request->get('tahun')) {
        $query->whereYear('tanggal_mulai', $tahun);
    }

    if ($status = $request->get('status')) {
        $query->where('status', $status);
    }

    $assignments = $query->latest()->get();

    return view('pages.audit-assignment.index', compact('assignments'));
}


public function edit($id)
{
    $data = AuditAssignment::with([
        'attachments',
        'unitDiperiksa',
        'members',
    ])->findOrFail($id);

    $currentKategori = $data->unitDiperiksa ? strtolower($data->unitDiperiksa->kategori) : '';

    $kategoriOptions = UnitDiperiksa::query()
        ->select('kategori')
        ->distinct()
        ->orderBy('kategori')
        ->pluck('kategori', 'kategori')
        ->toArray();

    return view('pages.audit-assignment.edit', [
        'data'            => $data,
        'currentKategori' => $currentKategori,
        'ketuaTim'        => User::orderBy('name')->get(),
        'members'         => User::orderBy('name')->get(),
        'programs'        => AuditProgram::orderBy('nama_program')->get(),
        'kategoriOptions' => $kategoriOptions, // ✅ tambahkan ini
    ]);
}


public function create()
{
    $units = UnitDiperiksa::orderBy('nama_unit')->get();
    $ketuaTim = User::orderBy('name')->get();
    $members  = User::orderBy('name')->get();

    $kategoriOptions = UnitDiperiksa::query()
        ->select('kategori')
        ->distinct()
        ->orderBy('kategori')
        ->pluck('kategori', 'kategori')
        ->toArray();

    $programs = AuditProgram::orderBy('nama_program')->get();

    return view('pages.audit-assignment.create', compact(
        'units',
        'ketuaTim',
        'members',
        'kategoriOptions',
        'programs'
    ));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'unit_diperiksa_id' => 'required|exists:unit_diperiksas,id',
        'tanggal_mulai'     => 'required|date',
        'jenis_pengawasan'  => ['required', Rule::in(AuditAssignment::listJenisPengawasan())],
        'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
        'ketua_tim_id'      => 'required|exists:users,id',
        'nama_tim'          => 'required|string|max:255',
        'nomor_surat'       => 'required|string|max:255',
        'audit_program_id'  => 'required|exists:audit_programs,id',
        'status'            => 'required|in:draft,berjalan,selesai',
        'members'           => 'nullable|array',
        'members.*'         => 'exists:users,id',
        'attachments.*'     => 'nullable|file|max:2048',
    ]);

    $assignment = AuditAssignment::create(
        collect($validated)->except('members')->toArray()
    );

    // ✅ Sync members dengan pivot jabatan_tim = 'anggota'
    if ($request->filled('members')) {
        $syncData = collect($request->members)
            ->mapWithKeys(fn($id) => [$id => ['jabatan_tim' => 'anggota']])
            ->toArray();
        $assignment->members()->sync($syncData);
    }

    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('attachments/audit', 'public');
            $assignment->attachments()->create([
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'file_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
                'jenis_bukti' => 'dokumen',
            ]);
        }
    }

    return redirect()
        ->route('audit-assignment.index')
        ->with('success', 'Data berhasil ditambahkan');
}

public function update(Request $request, $id)
{
    $data = AuditAssignment::findOrFail($id);
    $auditProgramIdLama = $data->audit_program_id; // simpan sebelum update

    $validated = $request->validate([
        'unit_diperiksa_id'    => 'required|exists:unit_diperiksas,id',
        'tanggal_mulai'        => 'required|date',
        'tanggal_selesai'      => 'required|date|after_or_equal:tanggal_mulai',
        'jenis_pengawasan'     => ['required', Rule::in(AuditAssignment::listJenisPengawasan())],
        'ketua_tim_id'         => 'required|exists:users,id',
        'nama_tim'             => 'required|string|max:255',
        'nomor_surat'          => 'required|string|max:255',
        'audit_program_id'     => 'required|exists:audit_programs,id',
        'status'               => 'required|in:draft,berjalan,selesai',
        'members'              => 'nullable|array',
        'members.*'            => 'exists:users,id',
        'attachments.*'        => 'nullable|file|max:2048',
        'delete_attachments'   => 'nullable|array',
        'delete_attachments.*' => 'exists:attachments,id',
    ]);

    $data->update(
        collect($validated)->except(['members', 'delete_attachments'])->toArray()
    );

    $syncData = collect($request->members ?? [])
        ->mapWithKeys(fn($id) => [$id => ['jabatan_tim' => 'anggota']])
        ->toArray();
    $data->members()->sync($syncData);

    if ($request->filled('delete_attachments')) {
        foreach ($request->delete_attachments as $idFile) {
            $file = $data->attachments()->where('id', $idFile)->first();
            if ($file) $file->delete();
        }
    }

    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('attachments/audit', 'public');
            $data->attachments()->create([
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'file_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
                'jenis_bukti' => 'dokumen',
            ]);
        }
    }

    // ✅ Sinkronkan status program induk setelah assignment diubah
    $this->sinkronStatusProgram($validated['audit_program_id']);

    // ✅ Jika program berubah (pindah program), sinkronkan program lama juga
    if ($auditProgramIdLama !== (int) $validated['audit_program_id']) {
        $this->sinkronStatusProgram($auditProgramIdLama);
    }

    return redirect()
        ->route('audit-assignment.show', $data->id)
        ->with('success', 'Data berhasil diupdate');
}

// ── Tambahkan method ini di AuditAssignmentController ──────────────────────
private function sinkronStatusProgram(int $auditProgramId): void
{
    $statuses = \App\Models\AuditAssignment::where('audit_program_id', $auditProgramId)
        ->pluck('status');

    if ($statuses->isEmpty()) return;

    $statusProgram = match(true) {
        $statuses->every(fn($s) => $s === 'selesai')                          => 'selesai',
        $statuses->contains(fn($s) => in_array($s, ['berjalan', 'selesai'])) => 'berjalan',
        default                                                                 => 'draft',
    };

    \App\Models\AuditProgram::where('id', $auditProgramId)
        ->where('status', '!=', $statusProgram)
        ->update(['status' => $statusProgram]);
}

public function show($id)
{
    $data = AuditAssignment::with([
        'unitDiperiksa',
        'auditProgram',
        'ketuaTim',
        'attachments'
    ])->findOrFail($id);

    return view('pages.audit-assignment.show', compact('data'));
}

public function bulkDelete(Request $request)
{
    $ids = $request->input('ids');

    if (!$ids || empty($ids)) {
        return back()->with('error', 'Pilih minimal satu data untuk dihapus.');
    }

    \App\Models\AuditAssignment::whereIn('id', $ids)->delete();

    return back()->with('success', count($ids) . ' data berhasil dihapus.');
}


    // =============================
    // AJAX CASCADE
    // =============================

   public function getKecamatan($kategori)
{
    return response()->json(
        UnitDiperiksa::where('kategori', $kategori) // ✅ exact match, bukan LOWER
            ->whereNotNull('nama_kecamatan')
            ->select('nama_kecamatan')
            ->distinct()
            ->orderBy('nama_kecamatan')
            ->pluck('nama_kecamatan')
    );
}


    public function getUnit($kecamatan)
{
    return response()->json(
        UnitDiperiksa::where('nama_kecamatan', $kecamatan)
            ->orderBy('nama_unit')
            ->get()
            ->map(fn($item) => [
                'id'    => $item->id,
                'label' => $item->label, // ✅ pakai accessor getLabelAttribute()
            ])
    );
}

public function destroy($id)
{
    $assignment = AuditAssignment::findOrFail($id);
    $auditProgramId = $assignment->audit_program_id; // simpan sebelum hapus

    foreach ($assignment->attachments as $file) {
        \Storage::disk('public')->delete($file->file_path);
        $file->delete();
    }

    $assignment->members()->detach();
    $assignment->delete();

    // ✅ Sinkronkan program setelah assignment dihapus
    if ($auditProgramId) {
        $this->sinkronStatusProgram($auditProgramId);
    }

    return redirect()
        ->route('audit-assignment.index')
        ->with('success', 'Audit Assignment berhasil dihapus');
}

}