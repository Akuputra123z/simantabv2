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
            'programs'          => AuditProgram::where(fn($q) => $q
                ->where('approval_status', '!=', AuditProgram::APPROVAL_DITOLAK)
                ->orWhereHas('details.assignments', fn($q) => $q->where('id', $assignmentId))
            )->orderBy('tahun', 'desc')->orderBy('nama_program')->get(),
            'programCategories' => AuditProgram::KATEGORI,
            'ketuaTim'          => User::orderBy('name')->get(),
            'members'           => User::orderBy('name')->get(),
            'kategoriOptions'   => ['BUMD', 'Sekolah', 'OPD', 'Desa', 'BLUD'],
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
        $query = AuditAssignment::query()
            ->with([
                'auditProgramDetail.auditProgram',
                'unitDiperiksas',
                'ketuaTim',
            ])
            ->when($request->tahun, fn($q, $v) =>
                $q->whereHas('auditProgramDetail.auditProgram', fn($q) => $q->where('tahun', $v))
            )
            ->when($request->kategori, fn($q, $v) =>
                $q->whereHas('auditProgramDetail.auditProgram', fn($q) => $q->where('kategori', $v))
            )
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, fn($q, $v) =>
                $q->where(fn($q) => $q
                    ->where('nomor_surat', 'like', "%{$v}%")
                    ->orWhereHas('unitDiperiksas', fn($q) =>
                        $q->where('nama_unit', 'like', "%{$v}%")
                    )
                )
            );

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 8, 10, 20, 25, 50])) {
            $perPage = 10;
        }

        $assignments = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        $kategoriOptions = AuditProgram::KATEGORI;
        return view('pages.audit-assignment.index', compact('assignments', 'kategoriOptions'));
    }

    // ── create ────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $programId = null;
        $detailId  = null;

        if ($pid = $request->integer('program_detail_id')) {
            $detail = AuditProgramDetail::with('auditProgram')->find($pid);

            if ($detail && $detail->auditProgram?->approval_status !== AuditProgram::APPROVAL_DITOLAK) {
                if ($detail->assignment) {
                    return redirect()->route('audit-assignment.edit', $detail->assignment->id)
                        ->with('info', 'Sub-program ini sudah memiliki penugasan — dialihkan ke halaman edit penugasan yang ada.');
                }

                $programId = $detail->audit_program_id;
                $detailId  = $detail->id;
            }
        }

        return view('pages.audit-assignment.create', array_merge($this->sharedViewData(), [
            'currentProgId' => old('audit_program_id', $programId),
            'currentDetId'  => old('audit_program_detail_id', $detailId),
        ]));
    }

    // ── store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->merge([
            'pengendali_teknis_id' => $request->pengendali_teknis_id ?: null,
        ]);

        $detail = null;
        if ($request->audit_program_detail_id) {
            $detail = AuditProgramDetail::with('auditProgram')->find($request->audit_program_detail_id);
        }

        $programKategori = $detail?->auditProgram?->kategori ?? '';
        $isBpkBpkp = in_array(strtoupper($programKategori), ['BPK', 'BPKP']);

        if ($isBpkBpkp) {
            if (!$request->filled('tanggal_mulai')) {
                $request->merge(['tanggal_mulai' => now()->toDateString()]);
            }
            if (!$request->filled('tanggal_selesai')) {
                $request->merge(['tanggal_selesai' => $request->tanggal_mulai ?? now()->toDateString()]);
            }
            if (!$request->filled('status')) {
                $request->merge(['status' => 'berjalan']);
            }
            $request->merge(['pengendali_teknis_id' => null, 'members' => []]);
        }

        $rules = [
            'audit_program_detail_id' => 'required|exists:audit_program_details,id',
            'unit_diperiksa_ids'      => 'required|array|min:1',
            'unit_diperiksa_ids.*'    => 'exists:unit_diperiksas,id',
            'ketua_tim_id'            => 'required|exists:users,id',
            'nomor_surat'             => 'required|string|max:255|unique:audit_assignments,nomor_surat',
            'tanggal_mulai'           => $isBpkBpkp ? 'nullable|date' : 'required|date',
            'tanggal_selesai'         => $isBpkBpkp ? 'nullable|date|after_or_equal:tanggal_mulai' : 'required|date|after_or_equal:tanggal_mulai',
            'nama_tim'                => 'nullable|string|max:255',
            'jenis_pengawasan'        => ['nullable', 'string', Rule::in(AuditAssignment::JENIS_PENGAWASAN)],
            'anggaran_disetujui'      => 'nullable|numeric|min:0',
            'pengendali_teknis_id'    => 'nullable|exists:users,id',
            'status'                  => 'required|in:draft,berjalan,selesai',
            'members'                 => 'nullable|array',
            'members.*'               => 'exists:users,id',
        ];

        $validated = $request->validate($rules, [
            'audit_program_detail_id.unique' => 'PKPT/Detail Program ini sudah pernah dibuatkan penugasannya.',
            'ketua_tim_id.required'          => $isBpkBpkp ? 'Penanggungjawab tindak lanjut wajib dipilih.' : 'Ketua tim wajib dipilih.',
        ]);

        $detail = AuditProgramDetail::with('auditProgram')->find($validated['audit_program_detail_id']);
        if (!$detail || !$detail->auditProgram || $detail->auditProgram->approval_status === AuditProgram::APPROVAL_DITOLAK) {
            return back()->withInput()->with('error', 'Penugasan hanya dapat dibuat untuk PKPT yang sudah disetujui.');
        }

        $detailId = null;

        DB::transaction(function () use ($validated, &$detailId) {
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
        $request->merge([
            'pengendali_teknis_id' => $request->pengendali_teknis_id ?: null,
        ]);

        $detail = null;
        if ($request->audit_program_detail_id) {
            $detail = AuditProgramDetail::with('auditProgram')->find($request->audit_program_detail_id);
        } else {
            $detail = $auditAssignment->auditProgramDetail()->with('auditProgram')->first();
        }

        $programKategori = $detail?->auditProgram?->kategori ?? '';
        $isBpkBpkp = in_array(strtoupper($programKategori), ['BPK', 'BPKP']);

        if ($isBpkBpkp) {
            if (!$request->filled('tanggal_mulai')) {
                $request->merge(['tanggal_mulai' => $auditAssignment->tanggal_mulai?->toDateString() ?? now()->toDateString()]);
            }
            if (!$request->filled('tanggal_selesai')) {
                $request->merge(['tanggal_selesai' => $request->tanggal_mulai ?? ($auditAssignment->tanggal_selesai?->toDateString() ?? now()->toDateString())]);
            }
            if (!$request->filled('status')) {
                $request->merge(['status' => $auditAssignment->status ?? 'berjalan']);
            }
            $request->merge(['pengendali_teknis_id' => null, 'members' => []]);
        }

        $rules = [
            // Unique kecuali untuk data yang sedang di-edit itu sendiri
            'audit_program_detail_id' => 'required|exists:audit_program_details,id|unique:audit_assignments,audit_program_detail_id,' . $auditAssignment->id,
            'unit_diperiksa_ids'      => 'required|array|min:1',
            'unit_diperiksa_ids.*'    => 'exists:unit_diperiksas,id',
            'ketua_tim_id'            => 'required|exists:users,id',
            'nomor_surat'             => 'required|string|max:255|unique:audit_assignments,nomor_surat,' . $auditAssignment->id,
            'nama_tim'                => 'nullable|string|max:255',
            'jenis_pengawasan'        => ['nullable', 'string', Rule::in(AuditAssignment::JENIS_PENGAWASAN)],
            'anggaran_disetujui'      => 'nullable|numeric|min:0',
            'tanggal_mulai'           => $isBpkBpkp ? 'nullable|date' : 'required|date',
            'tanggal_selesai'         => $isBpkBpkp ? 'nullable|date|after_or_equal:tanggal_mulai' : 'required|date|after_or_equal:tanggal_mulai',
            'pengendali_teknis_id'    => 'nullable|exists:users,id',
            'status'                  => 'required|in:draft,berjalan,selesai',
            'members'                 => 'nullable|array',
            'members.*'               => 'exists:users,id',
        ];

        $validated = $request->validate($rules, [
            'ketua_tim_id.required' => $isBpkBpkp ? 'Penanggungjawab tindak lanjut wajib dipilih.' : 'Ketua tim wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated, $auditAssignment) {
            $unitIds = $validated['unit_diperiksa_ids'];
            $members = $validated['members'] ?? [];
            unset($validated['unit_diperiksa_ids'], $validated['members']);

            $auditAssignment->update([
                ...$validated,
                'updated_by' => auth()->id(),
            ]);

            $auditAssignment->unitDiperiksas()->sync($unitIds);
            $auditAssignment->members()->sync($members);
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
    $excludeId = $request->integer('exclude_assignment');

    $details = AuditProgramDetail::query()
        ->where('audit_program_id', $programId)

        // Hitung assignment yang sudah dimiliki
        ->withCount([
            'assignments as assigned_count' => function ($query) use ($excludeId) {
                if ($excludeId > 0) {
                    $query->where('id', '!=', $excludeId);
                }
            }
        ])

        ->orderBy('nama_detail_program')

        ->get([
            'id',
            'nama_detail_program',
            'jenis_kegiatan',
            'tim',
            'anggaran',
            'objek_pengawasan',
            'ruang_lingkup',
            'status',
        ])

        ->map(function ($detail) {
            return [
                'id'                  => $detail->id,
                'nama_detail_program' => $detail->nama_detail_program,
                'jenis_kegiatan'      => $detail->jenis_kegiatan,
                'tim'                 => $detail->tim,
                'anggaran'            => (float) ($detail->anggaran ?? 0),
                'objek_pengawasan'    => $detail->objek_pengawasan,
                'ruang_lingkup'       => $detail->ruang_lingkup,
                'status'              => $detail->status,

                // TRUE jika sudah digunakan assignment lain
                'assigned'            => $detail->assigned_count > 0,
            ];
        });

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
    $assignment = AuditAssignment::with([
        'auditProgramDetail.auditProgram',
        'unitDiperiksas', 
        'ketuaTim',
        'pengendaliTeknis',
        'signer',
        'members'
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
            'pengendaliTeknis',
            'signer',
            'members',
        ])->findOrFail($id);

        $signatureBase64 = null;
        if ($assignment->isSigned() && $assignment->signer && $assignment->signer->signature) {
            $path = Storage::disk('public')->path($assignment->signer->signature);
            if (file_exists($path)) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $signatureBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('pages.audit-assignment.print-pdf', compact('assignment', 'signatureBase64'))
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
            'pengendaliTeknis',
            'members',
            'attachments',
            'lhps',
            'signer',
        ]);

        return view('pages.audit-assignment.show', compact('data'));
    }

    public function sign(AuditAssignment $auditAssignment)
    {
        if (!auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('kepala_inspektorat')) {
            return back()->with('error', 'Hanya Super Admin dan Kepala Inspektorat yang dapat menandatangani Surat Tugas.');
        }

        if ($auditAssignment->isSigned()) {
            return back()->with('error', 'Surat Tugas ini sudah ditandatangani.');
        }

        $auditAssignment->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $redirectTo = request('from') === 'preview'
            ? route('audit-assignment.preview', $auditAssignment->id)
            : route('audit-assignment.show', $auditAssignment->id);

        return redirect($redirectTo)->with('success', 'Surat Tugas berhasil ditandatangani.');
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