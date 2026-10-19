<?php

namespace App\Http\Controllers;

use App\Actions\Lhr\ApplyStandardRkpdFindings;
use App\Actions\Lhr\ApproveAndIssueLhr;
use App\Actions\Lhr\CreateLhrFromAssignment;
use App\Actions\Lhr\GenerateLhrPdf;
use App\Actions\Lhr\GenerateLhrWord;
use App\Actions\Lhr\ReviewLhr;
use App\Actions\Lhr\SubmitLhr;
use App\Enums\LhrStatus;
use App\Enums\SimpulanStatus;
use App\Models\AuditAssignment;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\Lhr\LhrCatatan;
use App\Models\Lhr\LhrKategoriCatatan;
use App\Models\Lhr\LhrPengujian;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Services\Lhr\LhrDocumentBuilder;
use App\Services\Lhr\LhrLinter;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LhrController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            function ($request, Closure $next) {
                $user = auth()->user();
                if ($user && $user->hasRole(User::ROLE_OPD)) {
                    abort(403, 'Akses Modul Laporan Hasil Reviu (LHR) hanya untuk auditor dan staf Inspektorat.');
                }

                return $next($request);
            },
        ];
    }

    /**
     * Daftar dokumen LHR dengan filter tahun, OPD, jenis dokumen, dan status.
     */
    public function index(Request $request)
    {
        $query = Lhr::forUser(auth()->user())->with([
            'unitDiperiksa',
            'jenisDokumen',
            'auditAssignment.auditProgramDetail',
            'auditAssignment.pengendaliTeknis',
            'statistik',
            'signer',
        ]);

        // Filter Tahun
        if ($request->filled('tahun')) {
            $query->where('tahun_anggaran', $request->tahun);
        }

        // Filter Jenis Dokumen (by ID or by Kode)
        if ($request->filled('jenis_kode')) {
            $query->whereHas('jenisDokumen', fn ($j) => $j->where('kode', $request->jenis_kode));
        } elseif ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_reviu_id', $request->jenis_dokumen_id);
        }

        // Filter OPD
        if ($request->filled('unit_id')) {
            $query->where('unit_diperiksa_id', $request->unit_id);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search text
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhr', 'like', "%{$search}%")
                    ->orWhereHas('unitDiperiksa', fn ($u) => $u->where('nama_unit', 'like', "%{$search}%"))
                    ->orWhereHas('jenisDokumen', fn ($j) => $j->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment', fn ($a) => $a->where('nomor_surat', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 8, 10, 20, 25, 30, 50, 100])) {
            $perPage = 10;
        }

        $lhrs = $query->latest()->paginate($perPage)->withQueryString();

        // Pastikan baris yang belum memiliki catatan statistik di-inisialisasi
        foreach ($lhrs as $lhr) {
            if (! $lhr->statistik || ! $lhr->statistik->dihitung_pada) {
                $lhr->refreshStatistik();
            }
        }

        $jenisDokumens = JenisDokumenReviu::where('aktif', true)
            ->orderByRaw("CASE WHEN kode = 'RKPD' THEN 1 WHEN kode = 'KUA_PPAS' THEN 2 ELSE 3 END, nama ASC")
            ->get();
        $units = UnitDiperiksa::orderBy('nama_unit')->get();

        // Opsi tahun
        $tahunList = Lhr::select('tahun_anggaran')
            ->distinct()
            ->pluck('tahun_anggaran')
            ->merge([date('Y'), date('Y') + 1, date('Y') - 1])
            ->unique()
            ->sortDesc()
            ->values();

        return view('pages.lhr.index', compact('lhrs', 'jenisDokumens', 'units', 'tahunList'));
    }

    /**
     * Form wizard pembuatan LHR baru.
     */
    public function create(\App\Services\Lhr\LhrNumberingService $numberingService)
    {
        $assignments = AuditAssignment::with(['ketuaTim', 'pengendaliTeknis', 'auditProgramDetail.auditProgram'])
            ->where(function ($q) {
                $q->where('jenis_pengawasan', 'reviu')
                    ->orWhereNull('jenis_pengawasan');
            })
            ->where(function ($q) {
                $q->whereDoesntHave('lhrs', function ($lhrQuery) {
                    $lhrQuery->where('status', '!=', LhrStatus::Dibatalkan->value);
                });
                if (old('audit_assignment_id')) {
                    $q->orWhere('id', old('audit_assignment_id'));
                }
            })
            ->latest()
            ->get();

        $jenisDokumens = JenisDokumenReviu::where('aktif', true)
            ->orderByRaw("CASE WHEN kode = 'RKPD' THEN 1 WHEN kode = 'KUA_PPAS' THEN 2 ELSE 3 END, nama ASC")
            ->get();

        // Dahulukan BAPPEDA dan OPD untuk dokumen perencanaan
        $units = UnitDiperiksa::where('kategori', 'OPD')
            ->orderByRaw("CASE WHEN nama_unit LIKE '%Perencanaan Pembangunan%' THEN 1 WHEN nama_unit LIKE '%Pendapatan%' THEN 2 ELSE 3 END, nama_unit ASC")
            ->get();

        $signers = User::role(['kepala_inspektorat', 'super_admin'])->orderBy('name')->get();

        $suggestedNomorLhr = $numberingService->getNextSuggestedNomor(\App\Services\Lhr\LhrNumberingService::JENIS_LHR);

        return view('pages.lhr.create', compact('assignments', 'jenisDokumens', 'units', 'signers', 'suggestedNomorLhr'));
    }

    /**
     * Simpan LHR baru dari SPT.
     */
    public function store(Request $request, CreateLhrFromAssignment $action)
    {
        // Sinkronisasi otomatis Tahun Anggaran & Jadwal Pelaksanaan dari Penugasan Audit (SPT)
        if ($request->filled('audit_assignment_id')) {
            $asg = AuditAssignment::with(['auditProgramDetail.auditProgram'])->find($request->audit_assignment_id);
            if ($asg) {
                $tahun = $request->input('tahun_anggaran')
                    ?: ($asg->auditProgram?->tahun
                        ?: ($asg->auditProgramDetail?->auditProgram?->tahun
                            ?: ($asg->tanggal_mulai ? (int) $asg->tanggal_mulai->format('Y') : (int) date('Y'))));

                $mergeData = [
                    'tahun_anggaran' => (int) $tahun,
                ];
                if (! $request->filled('tanggal_mulai') && $asg->tanggal_mulai) {
                    $mergeData['tanggal_mulai'] = $asg->tanggal_mulai->format('Y-m-d');
                }
                if (! $request->filled('tanggal_selesai') && $asg->tanggal_selesai) {
                    $mergeData['tanggal_selesai'] = $asg->tanggal_selesai->format('Y-m-d');
                }
                $request->merge($mergeData);
            }
        }

        $validated = $request->validate([
            'audit_assignment_id' => [
                'required',
                'exists:audit_assignments,id',
                Rule::unique('lhrs', 'audit_assignment_id')
                    ->whereNull('deleted_at')
                    ->where(fn ($query) => $query->where('status', '!=', LhrStatus::Dibatalkan->value)),
            ],
            'unit_diperiksa_id' => 'required|exists:unit_diperiksas,id',
            'jenis_dokumen_reviu_id' => 'required|exists:jenis_dokumen_reviu,id',
            'tahun_anggaran' => 'required|integer|min:2020|max:2035',
            'nomor_lhr' => 'nullable|string|max:100|unique:lhrs,nomor_lhr',
            'tanggal_lhr' => 'nullable|date',
            'ditandatangani_oleh' => 'nullable|exists:users,id',
            'permohonan_nomor' => 'nullable|string|max:100',
            'permohonan_tanggal' => 'nullable|date',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,xlsx,xls,docx,doc|max:15360',
            'attachment_notes' => 'nullable|array',
            'attachment_notes.*' => 'nullable|string|max:255',
        ], [
            'audit_assignment_id.required' => 'Surat Perintah Tugas (SPT) Reviu wajib dipilih.',
            'audit_assignment_id.exists' => 'Surat Perintah Tugas (SPT) yang dipilih tidak valid atau tidak ditemukan.',
            'audit_assignment_id.unique' => 'Surat Perintah Tugas (SPT) ini sudah memiliki dokumen LHR. Tidak dapat membuat LHR duplikat untuk SPT yang sama.',
            'unit_diperiksa_id.required' => 'Perangkat Daerah Pengampu (Auditee) wajib dipilih.',
            'unit_diperiksa_id.exists' => 'Perangkat Daerah yang dipilih tidak valid atau tidak ditemukan.',
            'jenis_dokumen_reviu_id.required' => 'Jenis Dokumen Reviu wajib dipilih.',
            'jenis_dokumen_reviu_id.exists' => 'Jenis Dokumen Reviu yang dipilih tidak valid.',
            'tahun_anggaran.required' => 'Tahun Anggaran Dokumen wajib diisi.',
            'tahun_anggaran.integer' => 'Tahun Anggaran harus berupa angka (contoh: 2026).',
            'tahun_anggaran.min' => 'Tahun Anggaran minimal tahun :min.',
            'tahun_anggaran.max' => 'Tahun Anggaran maksimal tahun :max.',
            'nomor_lhr.unique' => 'Nomor LHR :input sudah terdaftar pada dokumen lain. Silakan ubah nomor atau kosongkan agar terisi otomatis.',
            'nomor_lhr.max' => 'Nomor LHR maksimal :max karakter.',
            'tanggal_lhr.date' => 'Format Tanggal Pengesahan LHR tidak valid.',
            'ditandatangani_oleh.exists' => 'Pejabat Penandatangan yang dipilih tidak ditemukan dalam sistem.',
            'permohonan_tanggal.date' => 'Format Tanggal Surat Permohonan OPD tidak valid.',
            'tanggal_mulai.date' => 'Format Tanggal Mulai Reviu tidak valid.',
            'tanggal_selesai.date' => 'Format Tanggal Selesai Reviu tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal Selesai Reviu tidak boleh lebih awal dari Tanggal Mulai Reviu.',
            'attachments.*.file' => 'Berkas lampiran yang diunggah harus berupa file yang valid.',
            'attachments.*.mimes' => 'Format berkas lampiran harus berupa PDF, Word (.docx/.doc), Excel (.xlsx/.xls), atau Gambar (.jpg/.jpeg/.png).',
            'attachments.*.max' => 'Ukuran setiap berkas lampiran tidak boleh melebihi 10MB.',
        ]);

        $assignment = AuditAssignment::findOrFail($validated['audit_assignment_id']);
        $unit = UnitDiperiksa::findOrFail($validated['unit_diperiksa_id']);
        $jenisDokumen = JenisDokumenReviu::findOrFail($validated['jenis_dokumen_reviu_id']);

        $lhr = $action->execute(
            assignment: $assignment,
            unit: $unit,
            jenisDokumen: $jenisDokumen,
            tahunAnggaran: (int) $validated['tahun_anggaran'],
            permohonanNomor: $validated['permohonan_nomor'] ?? null,
            permohonanTanggal: ! empty($validated['permohonan_tanggal']) ? Carbon::parse($validated['permohonan_tanggal']) : null,
            tanggalMulai: ! empty($validated['tanggal_mulai']) ? Carbon::parse($validated['tanggal_mulai']) : null,
            tanggalSelesai: ! empty($validated['tanggal_selesai']) ? Carbon::parse($validated['tanggal_selesai']) : null,
            nomorLhr: $validated['nomor_lhr'] ?? null,
            tanggalLhr: ! empty($validated['tanggal_lhr']) ? Carbon::parse($validated['tanggal_lhr']) : null,
            ditandatanganiOleh: ! empty($validated['ditandatangani_oleh']) ? (int) $validated['ditandatangani_oleh'] : null,
            creator: auth()->user()
        );

        if ($request->hasFile('attachments')) {
            $notes = $request->input('attachment_notes', []);
            foreach ($request->file('attachments') as $idx => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('attachments/lhr', 'local');
                    $keterangan = ! empty($notes[$idx]) ? trim($notes[$idx]) : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    $lhr->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                        'file_size' => $file->getSize() ?: 0,
                        'jenis_bukti' => 'dokumen_pendukung',
                        'urutan' => $idx + 1,
                        'keterangan' => $keterangan,
                        'visibilitas' => 'internal',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        return redirect()->route('lhr.show', $lhr->uuid)
            ->with('success', 'Dokumen LHR berhasil dibuat. Silakan lengkapi pengujian dan catatan reviu.');
    }

    /**
     * Ruang Kerja LHR (Workspace 3-kolom).
     */
    public function show(Request $request, string $uuid, LhrLinter $linter)
    {
        $lhr = Lhr::where('uuid', $uuid)
            ->with([
                'unitDiperiksa',
                'jenisDokumen',
                'auditAssignment.ketuaTim',
                'auditAssignment.pengendaliTeknis',
                'auditAssignment.members',
                'sections',
                'dasarHukums',
                'pengujians.children.catatans.rekomendasis.tindakLanjuts.attachments',
                'pengujians.children.catatans.rekomendasis.tindakLanjuts.verifikator',
                'pengujians.children.catatans.rekomendasis.tindakLanjuts.uploadOpdOleh',
                'pengujians.children.catatans.kategori',
                'pengujians.parent',
                'pengujians.catatans.rekomendasis.tindakLanjuts.attachments',
                'pengujians.catatans.rekomendasis.tindakLanjuts.verifikator',
                'pengujians.catatans.rekomendasis.tindakLanjuts.uploadOpdOleh',
                'pengujians.catatans.kategori',
                'statistik',
                'signer',
                'attachments.uploadedBy',
            ])
            ->firstOrFail();

        // Pastikan stub TindakLanjut ada untuk seluruh rekomendasi dokumen LHR ini jika berstatus terbit / tindak lanjut
        if (in_array($lhr->status, [LhrStatus::Terbit, LhrStatus::TindakLanjut, LhrStatus::Selesai], true)) {
            $rekomendasiIds = Recommendation::whereHas('lhrCatatan.pengujian', function ($q) use ($lhr) {
                $q->where('lhr_id', $lhr->id);
            })->pluck('id');

            if ($rekomendasiIds->isNotEmpty()) {
                $missingIds = Recommendation::whereIn('id', $rekomendasiIds)
                    ->doesntHave('tindakLanjuts')
                    ->pluck('id');

                foreach ($missingIds as $mId) {
                    TindakLanjut::firstOrCreate(
                        ['recommendation_id' => $mId],
                        [
                            'status_verifikasi' => 'menunggu_verifikasi',
                            'nilai_tindak_lanjut' => 0,
                            'total_terbayar' => 0,
                            'sisa_belum_bayar' => 0,
                        ]
                    );
                }
            }
        }

        // Urutkan pengujian secara hierarkis: Format dahulu, lalu Substansi (S1 s/d S8)
        // Sub-pengujian (seperti S6.a s/d e) tepat bersarang di bawah induknya
        $parents = $lhr->pengujians->whereNull('parent_id')->sortBy([
            ['grup', 'asc'],
            ['urutan', 'asc'],
        ]);
        $orderedPengujians = collect();
        foreach ($parents as $parent) {
            $orderedPengujians->push($parent);
            foreach ($parent->children->sortBy('urutan') as $child) {
                $orderedPengujians->push($child);
            }
        }
        $remaining = $lhr->pengujians->diff($orderedPengujians);
        $orderedPengujians = $orderedPengujians->concat($remaining)->values();
        $lhr->setRelation('pengujians', $orderedPengujians);

        $lintResult = $linter->lint($lhr);
        $kategoris = LhrKategoriCatatan::where('aktif', true)->orderBy('nama')->get();
        $rawStep = (int) $request->get('step', 1);
        $currentStep = min(3, max(1, $rawStep));

        $units = UnitDiperiksa::where('kategori', 'OPD')
            ->orderByRaw("CASE WHEN nama_unit LIKE '%Perencanaan Pembangunan%' THEN 1 WHEN nama_unit LIKE '%Pendapatan%' THEN 2 ELSE 3 END, nama_unit ASC")
            ->get();

        $signers = User::role(['kepala_inspektorat', 'super_admin'])->orderBy('name')->get();

        return view('pages.lhr.workspace', compact('lhr', 'lintResult', 'kategoris', 'currentStep', 'units', 'signers'));
    }

    /**
     * Form edit metadata dokumen LHR.
     */
    public function edit(string $uuid)
    {
        $lhr = Lhr::where('uuid', $uuid)
            ->with(['unitDiperiksa', 'jenisDokumen', 'auditAssignment.ketuaTim', 'signer', 'attachments'])
            ->firstOrFail();

        if (! $lhr->canEdit()) {
            return redirect()->route('lhr.show', $lhr->uuid)
                ->with('error', 'Dokumen LHR berstatus ' . $lhr->status->label() . ' terkunci dan tidak dapat diubah informasinya.');
        }

        $units = UnitDiperiksa::where('kategori', 'OPD')
            ->orderByRaw("CASE WHEN nama_unit LIKE '%Perencanaan Pembangunan%' THEN 1 WHEN nama_unit LIKE '%Pendapatan%' THEN 2 ELSE 3 END, nama_unit ASC")
            ->get();

        $signers = User::role(['kepala_inspektorat', 'super_admin'])->orderBy('name')->get();

        return view('pages.lhr.edit', compact('lhr', 'units', 'signers'));
    }

    /**
     * Update metadata dokumen LHR.
     */
    public function update(Request $request, string $uuid)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        if (! $lhr->canEdit()) {
            return back()->with('error', 'Dokumen LHR terkunci dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'unit_diperiksa_id' => 'required|exists:unit_diperiksas,id',
            'tahun_anggaran' => 'required|integer|min:2020|max:2035',
            'nomor_lhr' => ['nullable', 'string', 'max:100', \Illuminate\Validation\Rule::unique('lhrs', 'nomor_lhr')->ignore($lhr->id)],
            'tanggal_lhr' => 'nullable|date',
            'ditandatangani_oleh' => 'nullable|exists:users,id',
            'permohonan_nomor' => 'nullable|string|max:100',
            'permohonan_tanggal' => 'nullable|date',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'next_step' => 'nullable|integer|min:1|max:3',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,xlsx,xls,docx,doc|max:15360',
            'attachment_notes' => 'nullable|array',
            'attachment_notes.*' => 'nullable|string|max:255',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'integer|exists:attachments,id',
        ], [
            'unit_diperiksa_id.required' => 'Perangkat Daerah Pengampu (Auditee) wajib dipilih.',
            'unit_diperiksa_id.exists' => 'Perangkat Daerah yang dipilih tidak valid atau tidak ditemukan.',
            'tahun_anggaran.required' => 'Tahun Anggaran Dokumen wajib diisi.',
            'tahun_anggaran.integer' => 'Tahun Anggaran harus berupa angka (contoh: 2026).',
            'tahun_anggaran.min' => 'Tahun Anggaran minimal tahun :min.',
            'tahun_anggaran.max' => 'Tahun Anggaran maksimal tahun :max.',
            'nomor_lhr.unique' => 'Nomor LHR :input sudah terdaftar pada dokumen lain.',
            'nomor_lhr.max' => 'Nomor LHR maksimal :max karakter.',
            'tanggal_lhr.date' => 'Format Tanggal Pengesahan LHR tidak valid.',
            'ditandatangani_oleh.exists' => 'Pejabat Penandatangan yang dipilih tidak ditemukan dalam sistem.',
            'permohonan_tanggal.date' => 'Format Tanggal Surat Permohonan OPD tidak valid.',
            'tanggal_mulai.date' => 'Format Tanggal Mulai Reviu tidak valid.',
            'tanggal_selesai.date' => 'Format Tanggal Selesai Reviu tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal Selesai Reviu tidak boleh lebih awal dari Tanggal Mulai Reviu.',
            'attachments.*.file' => 'Berkas lampiran yang diunggah harus berupa file yang valid.',
            'attachments.*.mimes' => 'Format berkas lampiran harus berupa PDF, Word (.docx/.doc), Excel (.xlsx/.xls), atau Gambar (.jpg/.jpeg/.png).',
            'attachments.*.max' => 'Ukuran setiap berkas lampiran tidak boleh melebihi 10MB.',
        ]);

        $lhr->update([
            'unit_diperiksa_id' => $validated['unit_diperiksa_id'],
            'tahun_anggaran' => (int) $validated['tahun_anggaran'],
            'nomor_lhr' => $validated['nomor_lhr'] ?? null,
            'tanggal_lhr' => ! empty($validated['tanggal_lhr']) ? Carbon::parse($validated['tanggal_lhr']) : null,
            'ditandatangani_oleh' => ! empty($validated['ditandatangani_oleh']) ? (int) $validated['ditandatangani_oleh'] : null,
            'permohonan_nomor' => $validated['permohonan_nomor'] ?? null,
            'permohonan_tanggal' => ! empty($validated['permohonan_tanggal']) ? Carbon::parse($validated['permohonan_tanggal']) : null,
            'tanggal_mulai' => ! empty($validated['tanggal_mulai']) ? Carbon::parse($validated['tanggal_mulai']) : null,
            'tanggal_selesai' => ! empty($validated['tanggal_selesai']) ? Carbon::parse($validated['tanggal_selesai']) : null,
        ]);

        // Hapus lampiran yang ditandai untuk dihapus
        $deleteAttachmentIds = $request->input('delete_attachments');
        if (! empty($deleteAttachmentIds) && is_array($deleteAttachmentIds)) {
            $attachmentsToDelete = $lhr->attachments()->whereIn('id', $deleteAttachmentIds)->get();
            foreach ($attachmentsToDelete as $att) {
                if ($att->file_path && Storage::disk('local')->exists($att->file_path)) {
                    Storage::disk('local')->delete($att->file_path);
                }
                $att->forceDelete();
            }
        }

        // Tambah berkas lampiran baru jika ada
        if ($request->hasFile('attachments')) {
            $notes = $request->input('attachment_notes', []);
            $currentMaxOrder = (int) $lhr->attachments()->max('urutan');
            foreach ($request->file('attachments') as $idx => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('attachments/lhr', 'local');
                    $keterangan = ! empty($notes[$idx]) ? trim($notes[$idx]) : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    $lhr->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                        'file_size' => $file->getSize() ?: 0,
                        'jenis_bukti' => 'dokumen_pendukung',
                        'urutan' => $currentMaxOrder + $idx + 1,
                        'keterangan' => $keterangan,
                        'visibilitas' => 'internal',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        if ($request->filled('next_step')) {
            return redirect()->route('lhr.show', ['uuid' => $lhr->uuid, 'step' => $request->input('next_step')])
                ->with('success', 'Informasi dokumen LHR berhasil diperbarui.');
        }

        return redirect()->route('lhr.show', $lhr->uuid)
            ->with('success', 'Informasi dokumen LHR berhasil diperbarui.');
    }

    /**
     * Hapus dokumen LHR berstatus Draft.
     */
    public function destroy(string $uuid)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        $user = auth()->user();
        if (! $user->hasAnyRole([User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_INSPEKTORAT, User::ROLE_KETUA_TIM, 'admin'])) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus dokumen LHR.');
        }

        if ($lhr->isTerbit()) {
            return back()->with('error', 'Dokumen LHR yang sudah terbit resmi tidak dapat dihapus.');
        }

        DB::transaction(function () use ($lhr) {
            foreach ($lhr->pengujians as $pengujian) {
                foreach ($pengujian->catatans as $catatan) {
                    $catatan->rekomendasis()->delete();
                    $catatan->delete();
                }
                $pengujian->delete();
            }
            $lhr->sections()->delete();
            $lhr->dasarHukums()->delete();
            $lhr->statistik()->delete();
            $lhr->delete();
        });

        return redirect()->route('lhr.index')
            ->with('success', 'Dokumen LHR draft berhasil dihapus.');
    }

    /**
     * Terapkan 14 catatan & simpulan standar template Rembang 2027 ke LHR ini.
     */
    public function applyStandardTemplate(string $uuid, ApplyStandardRkpdFindings $action)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        if (! $lhr->canEdit()) {
            return back()->with('error', 'Dokumen LHR terkunci dan tidak dapat diubah.');
        }

        $action->execute($lhr);

        return redirect()->route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 3])
            ->with('success', '14 Catatan temuan & rekomendasi standar template Rembang 2027 berhasil disinkronkan ke ruang kerja.');
    }

    /**
     * Update simpulan pada pengujian LHR.
     */
    public function updatePengujian(Request $request, string $uuid, LhrPengujian $pengujian)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        if (! $lhr->canEdit()) {
            return back()->with('error', 'Dokumen LHR terkunci dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'status_simpulan' => ['required', Rule::enum(SimpulanStatus::class)],
            'teks_simpulan' => 'nullable|string',
        ]);

        $pengujian->update([
            'status_simpulan' => $validated['status_simpulan'],
            'teks_simpulan' => $validated['teks_simpulan'],
            'selesai_pada' => now(),
        ]);

        return back()->with('success', "Simpulan pengujian '{$pengujian->kode}' berhasil disimpan.");
    }

    /**
     * Tambah catatan & rekomendasi pada pengujian LHR.
     */
    public function storeCatatan(Request $request, string $uuid, LhrPengujian $pengujian)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        if (! $lhr->canEdit()) {
            return back()->with('error', 'Dokumen LHR terkunci dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'kategori_id' => 'required|exists:lhr_kategori_catatans,id',
            'referensi_bagian' => 'nullable|string|max:255',
            'isi' => 'required|string',
            'uraian_rekom' => 'required|string',
            'batas_waktu' => 'nullable|date',
        ]);

        DB::transaction(function () use ($lhr, $pengujian, $validated) {
            $catatan = LhrCatatan::create([
                'lhr_pengujian_id' => $pengujian->id,
                'kategori_id' => $validated['kategori_id'],
                'referensi_bagian' => $validated['referensi_bagian'] ?? null,
                'isi' => $validated['isi'],
                'urutan' => $pengujian->catatans()->count() + 1,
                'created_by' => auth()->id(),
            ]);

            Recommendation::create([
                'lhr_catatan_id' => $catatan->id,
                'uraian_rekom' => $validated['uraian_rekom'],
                'jenis_rekomendasi' => Recommendation::JENIS_ADMIN,
                'batas_waktu' => ! empty($validated['batas_waktu']) ? Carbon::parse($validated['batas_waktu']) : null,
                'status' => Recommendation::STATUS_BELUM,
                'status_tl' => Recommendation::STATUS_TL_BELUM_SELESAI,
                'created_by' => auth()->id(),
            ]);

            $this->syncStatistik($lhr);
        });

        return redirect()->route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 3])
            ->with('success', 'Catatan temuan dan rekomendasi berhasil ditambahkan.');
    }

    /**
     * Update catatan & rekomendasi pada pengujian LHR.
     */
    public function updateCatatan(Request $request, string $uuid, LhrCatatan $catatan)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        if (! $lhr->canEdit()) {
            return back()->with('error', 'Dokumen LHR terkunci dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'kategori_id' => 'required|exists:lhr_kategori_catatans,id',
            'referensi_bagian' => 'nullable|string|max:255',
            'isi' => 'required|string',
            'uraian_rekom' => 'required|string',
            'batas_waktu' => 'nullable|date',
        ]);

        DB::transaction(function () use ($lhr, $catatan, $validated) {
            $catatan->update([
                'kategori_id' => $validated['kategori_id'],
                'referensi_bagian' => $validated['referensi_bagian'] ?? null,
                'isi' => $validated['isi'],
            ]);

            $rekom = $catatan->rekomendasis()->first();
            if ($rekom) {
                $rekom->update([
                    'uraian_rekom' => $validated['uraian_rekom'],
                    'batas_waktu' => ! empty($validated['batas_waktu']) ? Carbon::parse($validated['batas_waktu']) : null,
                ]);
            } else {
                Recommendation::create([
                    'lhr_catatan_id' => $catatan->id,
                    'audit_assignment_id' => $lhr->audit_assignment_id,
                    'uraian_rekom' => $validated['uraian_rekom'],
                    'jenis_rekomendasi' => Recommendation::JENIS_ADMIN,
                    'batas_waktu' => ! empty($validated['batas_waktu']) ? Carbon::parse($validated['batas_waktu']) : null,
                    'status' => Recommendation::STATUS_BELUM,
                    'status_tl' => Recommendation::STATUS_TL_BELUM_SELESAI,
                    'created_by' => auth()->id(),
                ]);
            }

            $this->syncStatistik($lhr);
        });

        return redirect()->route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 3])
            ->with('success', 'Catatan temuan dan rekomendasi berhasil diperbarui.');
    }

    /**
     * Hapus catatan.
     */
    public function destroyCatatan(string $uuid, LhrCatatan $catatan)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        if (! $lhr->canEdit()) {
            return back()->with('error', 'Dokumen LHR terkunci dan tidak dapat diubah.');
        }

        $catatan->rekomendasis()->delete();
        $catatan->delete();

        $this->syncStatistik($lhr);

        return redirect()->route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 3])
            ->with('success', 'Catatan reviu berhasil dihapus.');
    }

    /**
     * Hitung ulang statistik rekomendasi LHR.
     */
    protected function syncStatistik(Lhr $lhr): void
    {
        $catatanIds = $lhr->catatans()->pluck('lhr_catatans.id');
        $totalRekom = Recommendation::whereIn('lhr_catatan_id', $catatanIds)->count();
        $selesaiRekom = Recommendation::whereIn('lhr_catatan_id', $catatanIds)
            ->where('status_tl', Recommendation::STATUS_TL_SELESAI)
            ->count();

        $persen = $totalRekom > 0 ? round(($selesaiRekom / $totalRekom) * 100, 2) : 0.00;

        $lhr->statistik()->updateOrCreate(
            ['lhr_id' => $lhr->id],
            [
                'total_rekomendasi' => $totalRekom,
                'selesai' => $selesaiRekom,
                'persen' => $persen,
                'dihitung_pada' => now(),
            ]
        );
    }

    /**
     * Pratinjau Dokumen HTML.
     */
    public function preview(string $uuid, LhrDocumentBuilder $builder)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();
        $data = $builder->build($lhr);

        $view = match ($lhr->jenisDokumen?->kode) {
            'RKPD', 'KUA_PPAS' => 'lhr.pdf.rkpd_kua',
            default => 'lhr.pdf.shsd',
        };

        return view($view, $data);
    }

    /**
     * Unduh PDF Dokumen LHR.
     */
    public function downloadPdf(string $uuid, GenerateLhrPdf $generator)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();
        $res = $generator->execute($lhr, $lhr->isTerbit());

        $filename = 'LHR-'.str($lhr->nomor_lhr ?? 'DRAFT')->slug().'.pdf';

        return response($res['binary'])
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }

    /**
     * Unduh Word (.docx) Dokumen LHR.
     */
    public function downloadWord(string $uuid, GenerateLhrWord $generator)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();
        $res = $generator->execute($lhr, false);

        $filename = 'LHR-'.str($lhr->nomor_lhr ?? 'DRAFT')->slug().'.docx';

        return response($res['binary'], 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($res['binary']),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Ajukan LHR ke Pengendali Teknis (Irban).
     */
    public function submit(string $uuid, SubmitLhr $action)
    {
        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        try {
            $action->execute($lhr);

            return back()->with('success', 'LHR berhasil diajukan ke Pengendali Teknis untuk penelaahan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Telaah LHR oleh Pengendali Teknis (Irban).
     */
    public function review(Request $request, string $uuid, ReviewLhr $action)
    {
        $user = auth()->user();
        if (! $user->hasAnyRole([User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_INSPEKTORAT, User::ROLE_IRBAN, User::ROLE_PENGENDALI_TEKNIS, 'admin', 'admin_inspektorat'])) {
            abort(403, 'Hanya Irban / Pengendali Teknis, Kepala Inspektorat, atau Administrator yang berhak menelaah LHR.');
        }

        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'action' => 'required|in:setujui,kembalikan',
            'catatan' => 'nullable|string',
        ]);

        try {
            $action->execute($lhr, $user, $validated['action'], $validated['catatan'] ?? null);
            $msg = ($validated['action'] === 'setujui')
                ? 'LHR telah disetujui Pengendali Teknis dan diteruskan ke Kepala Inspektorat.'
                : 'LHR dikembalikan ke Ketua Tim untuk dilakukan perbaikan.';

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Persetujuan akhir dan penerbitan nomor atomik LHR oleh Inspektur.
     */
    public function approve(string $uuid, ApproveAndIssueLhr $action)
    {
        $user = auth()->user();
        if (! $user->hasAnyRole([User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_INSPEKTORAT])) {
            abort(403, 'Hanya Kepala Inspektorat atau Super Admin yang berhak menyetujui dan menerbitkan LHR.');
        }

        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        try {
            $action->execute($lhr, $user);

            return back()->with('success', "LHR berhasil disetujui dan diterbitkan dengan Nomor: {$lhr->fresh()->nomor_lhr}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Verifikasi & setujui tindak lanjut revisi OPD untuk rekomendasi LHR (Irban / Dalnis / Tim Auditor).
     */
    public function verifikasiTindakLanjut(Request $request, string $uuid, TindakLanjut $tindakLanjut)
    {
        $user = auth()->user();
        if (! $user->hasAnyRole([User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_INSPEKTORAT, User::ROLE_IRBAN, User::ROLE_PENGENDALI_TEKNIS, 'admin', 'admin_inspektorat', User::ROLE_KETUA_TIM])) {
            abort(403, 'Hanya Irban / Pengendali Teknis, Ketua Tim, atau Administrator yang berhak memverifikasi revisi tindak lanjut.');
        }

        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        $rekom = $tindakLanjut->recommendation;
        if (! $rekom || ! $rekom->lhrCatatan || $rekom->lhrCatatan->pengujian?->lhr_id !== $lhr->id) {
            abort(403, 'Rekomendasi ini tidak terafiliasi dengan dokumen LHR ini.');
        }

        $validated = $request->validate([
            'status_verifikasi' => 'required|in:lunas,berjalan',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        $newStatus = $validated['status_verifikasi'];

        DB::beginTransaction();
        try {
            $tindakLanjut->status_verifikasi = $newStatus;
            $tindakLanjut->catatan_verifikasi = $validated['catatan_verifikasi'] ?? null;
            $tindakLanjut->diverifikasi_oleh = $user->id;
            $tindakLanjut->diverifikasi_pada = now();
            $tindakLanjut->alasan_tolak_opd = null;

            if ($newStatus === 'lunas') {
                $tindakLanjut->total_terbayar = 1;
                $tindakLanjut->sisa_belum_bayar = 0;
            }

            $tindakLanjut->saveQuietly();

            $rekom->refresh();
            $rekom->syncStatus();

            $stat = $lhr->refreshStatistik();

            if ($stat->total_rekomendasi > 0 && $stat->selesai === $stat->total_rekomendasi) {
                $lhr->update(['status' => LhrStatus::Selesai]);
            }

            DB::commit();

            return redirect()
                ->route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 3])
                ->with('success', 'Naskah revisi tindak lanjut OPD berhasil disetujui / diverifikasi (Selesai).');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memverifikasi tindak lanjut: ' . $e->getMessage());
        }
    }

    /**
     * Tolak naskah revisi OPD dan kembalikan ke OPD disertai catatan/alasan perbaikan.
     */
    public function tolakTindakLanjut(Request $request, string $uuid, TindakLanjut $tindakLanjut)
    {
        $user = auth()->user();
        if (! $user->hasAnyRole([User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_INSPEKTORAT, User::ROLE_IRBAN, User::ROLE_PENGENDALI_TEKNIS, 'admin', 'admin_inspektorat', User::ROLE_KETUA_TIM])) {
            abort(403, 'Hanya Irban / Pengendali Teknis, Ketua Tim, atau Administrator yang berhak mengembalikan revisi tindak lanjut.');
        }

        $lhr = Lhr::where('uuid', $uuid)->firstOrFail();

        $rekom = $tindakLanjut->recommendation;
        if (! $rekom || ! $rekom->lhrCatatan || $rekom->lhrCatatan->pengujian?->lhr_id !== $lhr->id) {
            abort(403, 'Rekomendasi ini tidak terafiliasi dengan dokumen LHR ini.');
        }

        $validated = $request->validate([
            'alasan_tolak' => 'required|string|max:2000',
        ], [
            'alasan_tolak.required' => 'Catatan / alasan penolakan perbaikan wajib diisi agar OPD dapat memperbaiki naskah.',
        ]);

        DB::beginTransaction();
        try {
            $tindakLanjut->update([
                'status_opd' => 'draft',
                'status_verifikasi' => 'menunggu_verifikasi',
                'alasan_tolak_opd' => $validated['alasan_tolak'],
                'dikirim_pada' => null,
            ]);

            $rekom->refresh();
            $rekom->syncStatus();

            $lhr->refreshStatistik();

            DB::commit();

            return redirect()
                ->route('lhr.show', ['uuid' => $lhr->uuid, 'step' => 3])
                ->with('success', 'Naskah revisi OPD dikembalikan ke OPD dengan catatan perbaikan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak tindak lanjut: ' . $e->getMessage());
        }
    }
}
