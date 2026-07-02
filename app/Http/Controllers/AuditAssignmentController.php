<?php

namespace App\Http\Controllers;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditAssignmentController extends Controller
{
    // ── Shared view data ──────────────────────────────────────────────

    private function sharedViewData($assignmentId = null): array
    {
        return [
            // Filter program yang memiliki detail yang masih tersedia (belum ditugaskan)
            // Namun jika sedang EDIT, detail yang sedang dipakai tetap harus muncul
            'programs'        => AuditProgram::orderBy('tahun', 'desc')->get(),
            'ketuaTim'        => User::orderBy('name')->get(),
            'members'         => User::orderBy('name')->get(),
            'kategoriOptions' => ['BUMD', 'Sekolah', 'OPD', 'Desa', 'BLUD'],
            'units' => UnitDiperiksa::orderBy('nama_unit')
                ->get(['id', 'nama_unit as name', 'kategori', 'nama_kecamatan'])
                ->map(fn($u) => [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'kategori'       => $u->kategori,
                    'kecamatan_nama' => $u->nama_kecamatan,
                ]),
        ];
    }

    // ── index ─────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $assignments = AuditAssignment::query()
            ->with([
                'auditProgramDetail.auditProgram',
                'unitDiperiksas',
                'ketuaTim',
            ])
            ->when($request->tahun, fn($q, $v) =>
                $q->whereHas('auditProgramDetail.auditProgram', fn($q) => $q->where('tahun', $v))
            )
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, fn($q, $v) =>
                $q->where(fn($q) => $q
                    ->where('nomor_surat', 'like', "%{$v}%")
                    ->orWhereHas('unitDiperiksas', fn($q) =>
                        $q->where('nama_unit', 'like', "%{$v}%")
                    )
                )
            )
            ->latest()
            ->paginate(20);

        return view('pages.audit-assignment.index', compact('assignments'));
    }

    // ── create ────────────────────────────────────────────────────────

    public function create()
    {
        return view('pages.audit-assignment.create', $this->sharedViewData());
    }

    // ── store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Tambahkan unique untuk mencegah duplikasi PKPT di database
            'audit_program_detail_id' => 'required|exists:audit_program_details,id|unique:audit_assignments,audit_program_detail_id',
            'unit_diperiksa_ids'      => 'required|array|min:1',
            'unit_diperiksa_ids.*'    => 'exists:unit_diperiksas,id',
            'ketua_tim_id'            => 'required|exists:users,id',
            'nomor_surat'             => 'required|string|max:255|unique:audit_assignments,nomor_surat',
            'tanggal_mulai'           => 'required|date',
            'tanggal_selesai'         => 'required|date|after_or_equal:tanggal_mulai',
            'nama_tim'                => 'nullable|string|max:255',
            'jenis_pengawasan'        => ['nullable', 'string', Rule::in(AuditAssignment::JENIS_PENGAWASAN)],
            'status'                  => 'required|in:draft,berjalan,selesai',
            'members'                 => 'nullable|array',
            'members.*'               => 'exists:users,id',
            'attachments'             => 'nullable|array',
            'attachments.*'           => 'file|mimes:jpg,jpeg,png,pdf,docx|max:5120',
        ], [
            'audit_program_detail_id.unique' => 'PKPT/Detail Program ini sudah pernah dibuatkan penugasannya.'
        ]);

        $detailId = null;

        DB::transaction(function () use ($validated, $request, &$detailId) {
            $unitIds = $validated['unit_diperiksa_ids'];
            $members = $validated['members'] ?? [];
            unset($validated['unit_diperiksa_ids'], $validated['members']);

            $assignment = AuditAssignment::create([
                ...$validated,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $assignment->unitDiperiksas()->sync($unitIds);
            $assignment->members()->sync($members);

            $detailId = $assignment->audit_program_detail_id;

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('audit-assignments/attachments', 'public');
                    $assignment->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }
        });

        // Sinkron status program setelah assignment dibuat
        if ($detailId) {
            $detail = AuditProgramDetail::find($detailId);
            if ($detail) {
                app(LhpStatistikService::class)->sinkronStatusProgram($detail->audit_program_id);
            }
        }

        return redirect()->route('audit-assignment.index')
            ->with('success', 'Audit assignment berhasil dibuat.');
    }

    // ── edit ──────────────────────────────────────────────────────────

    public function edit(AuditAssignment $auditAssignment)
    {
        $data = $auditAssignment->load([
            'members',
            'attachments',
            'auditProgramDetail.auditProgram',
            'unitDiperiksas',
        ]);

        $viewData = $this->sharedViewData($auditAssignment->id);

        return view('pages.audit-assignment.edit', array_merge($viewData, [
            'data'             => $data,
            'preselectedUnits' => $data->unitDiperiksas,
            'currentProgId'    => $data->auditProgramDetail->audit_program_id ?? '',
            'currentDetId'     => $data->audit_program_detail_id ?? '',
        ]));
    }

    // ── update ────────────────────────────────────────────────────────

    public function update(Request $request, AuditAssignment $auditAssignment)
    {
        $validated = $request->validate([
            // Unique kecuali untuk data yang sedang di-edit itu sendiri
            'audit_program_detail_id' => 'required|exists:audit_program_details,id|unique:audit_assignments,audit_program_detail_id,' . $auditAssignment->id,
            'unit_diperiksa_ids'      => 'required|array|min:1',
            'unit_diperiksa_ids.*'    => 'exists:unit_diperiksas,id',
            'ketua_tim_id'            => 'required|exists:users,id',
            'nomor_surat'             => 'required|string|max:255|unique:audit_assignments,nomor_surat,' . $auditAssignment->id,
            'nama_tim'                => 'nullable|string|max:255',
            'jenis_pengawasan'        => 'required|string|max:255',
            'tanggal_mulai'           => 'required|date',
            'tanggal_selesai'         => 'required|date|after_or_equal:tanggal_mulai',
            'status'                  => 'required|in:draft,berjalan,selesai',
            'members'                 => 'nullable|array',
            'members.*'               => 'exists:users,id',
            'delete_attachments'      => 'nullable|array',
            'delete_attachments.*'    => 'exists:audit_attachments,id',
        ]);

        DB::transaction(function () use ($validated, $request, $auditAssignment) {
            $unitIds = $validated['unit_diperiksa_ids'];
            $members = $validated['members'] ?? [];
            unset($validated['unit_diperiksa_ids'], $validated['members']);

            $auditAssignment->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            $auditAssignment->unitDiperiksas()->sync($unitIds);
            $auditAssignment->members()->sync($members);

            if ($request->filled('delete_attachments')) {
                $toDelete = $auditAssignment->attachments()
                    ->whereIn('id', $request->delete_attachments)
                    ->get();
                foreach ($toDelete as $att) {
                    Storage::disk('public')->delete($att->file_path);
                    $att->delete();
                }
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('audit-assignments/attachments', 'public');
                    $auditAssignment->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }
        });

        // Sinkron status program setelah assignment diupdate
        $detailId = $auditAssignment->fresh()->audit_program_detail_id;
        if ($detailId) {
            $detail = AuditProgramDetail::find($detailId);
            if ($detail) {
                app(LhpStatistikService::class)->sinkronStatusProgram($detail->audit_program_id);
            }
        }

        return redirect()->route('audit-assignment.index')
            ->with('success', 'Audit assignment berhasil diperbarui.');
    }

    // ── AJAX (KUNCI PERBAIKAN DI SINI) ────────────────────────────────

  public function getProgramDetails(Request $request, $programId)
{
    $excludeId = $request->query('exclude_assignment');

    // Pastikan menggunakan nama relasi 'assignments' (jamak) sesuai model
    $details = AuditProgramDetail::where('audit_program_id', $programId)
        ->where(function ($q) use ($excludeId) {
            // Tampilkan yang BELUM punya penugasan
            $q->whereDoesntHave('assignments');
            
            // ATAU jika sedang edit, tampilkan yang penugasannya adalah ID ini
            if ($excludeId) {
                $q->orWhereHas('assignments', function ($sub) use ($excludeId) {
                    $sub->where('id', $excludeId);
                });
            }
        })
        ->orderBy('nama_detail_program')
        ->get(['id', 'nama_detail_program', 'jenis_kegiatan', 'tim', 'anggaran']);

    return response()->json($details);
}

    // ── Sisa fungsi AJAX tetap sama ──────────────────────────────────
    
    public function getKecamatan(string $kategori)
    {
        $kecamatan = UnitDiperiksa::where('kategori', $kategori)->distinct()->orderBy('nama_kecamatan')->pluck('nama_kecamatan');
        return response()->json($kecamatan);
    }

    public function getUnit(string $kecamatan)
    {
        $units = UnitDiperiksa::where('nama_kecamatan', trim($kecamatan))->orderBy('nama_unit')->get(['id', 'nama_unit']);
        return response()->json($units);
    }

    public function print($id)
{
    // Eager load relasi sesuai nama fungsi di Model AuditAssignment
    $assignment = AuditAssignment::with([
        'auditProgramDetail.auditProgram',
        'unitDiperiksas', 
        'ketuaTim',
        'members' // Sesuai dengan public function members() di model
    ])->findOrFail($id);

    return view('pages.audit-assignment.print', compact('assignment'));
}

    // ── Print PDF (stream) ────────────────────────────────────────────────

    public function printPdf($id)
    {
        $assignment = AuditAssignment::with([
            'auditProgramDetail.auditProgram',
            'unitDiperiksas',
            'ketuaTim',
            'members',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pages.audit-assignment.print-pdf', compact('assignment'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'   => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $filename = 'surat-tugas-' . $assignment->nomor_surat . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->stream(str_replace('/', '-', $filename));
    }
    
    public function show(AuditAssignment $auditAssignment)
    {
        // Load semua relasi agar data tampil lengkap di halaman detail
        $data = $auditAssignment->load([
            'auditProgramDetail.auditProgram',
            'unitDiperiksas',
            'ketuaTim',
            'members',
            'attachments',
            'lhps'
        ]);

        return view('pages.audit-assignment.show', compact('data'));
    }


    // ── destroy ───────────────────────────────────────────────────────

   public function destroy(AuditAssignment $auditAssignment)
{
    $detailId = $auditAssignment->audit_program_detail_id;

    try {
        DB::beginTransaction();

        foreach ($auditAssignment->attachments as $att) {
            Storage::disk('public')->delete($att->file_path);
            $att->delete();
        }

        $auditAssignment->unitDiperiksas()->detach();
        $auditAssignment->members()->detach();

        $auditAssignment->forceDelete();

        DB::commit();

        // Sinkron status program setelah assignment dihapus
        if ($detailId) {
            $detail = AuditProgramDetail::find($detailId);
            if ($detail) {
                app(LhpStatistikService::class)->sinkronStatusProgram($detail->audit_program_id);
            }
        }

        return redirect()->back()->with('success', 'Data berhasil dihapus');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Gagal hapus AuditAssignment: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal menghapus data. Silakan coba lagi.');
    }
}
}