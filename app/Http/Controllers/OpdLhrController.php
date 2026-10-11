<?php

namespace App\Http\Controllers;

use App\Actions\Lhr\GenerateLhrPdf;
use App\Actions\Lhr\GenerateLhrWord;
use App\Enums\LhrStatus;
use App\Models\Attachment;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class OpdLhrController extends Controller
{
    /**
     * Dapatkan daftar ID UnitDiperiksa yang menjadi kewenangan user OPD yang sedang login.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function getTargetUnitIds(User $user)
    {
        $isSuperOrAdmin = $user->hasAnyRole(['super_admin', 'admin', 'admin_inspektorat', 'kepala_inspektorat']);

        if ($isSuperOrAdmin) {
            return UnitDiperiksa::pluck('id');
        }

        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
        if ($unitIds->isNotEmpty()) {
            $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
            return UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
        }

        return collect();
    }

    /**
     * Tampilkan daftar Laporan Hasil Reviu (LHR) resmi khusus untuk OPD yang login.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        // OPD hanya melihat dokumen LHR yang sudah resmi (Terbit, Tindak Lanjut, Selesai)
        $query = Lhr::whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [
                LhrStatus::Terbit,
                LhrStatus::TindakLanjut,
                LhrStatus::Selesai,
            ])
            ->with([
                'unitDiperiksa',
                'jenisDokumen',
                'auditAssignment',
                'statistik',
                'signer',
            ]);

        // Filter Tahun
        if ($request->filled('tahun')) {
            $query->where('tahun_anggaran', $request->tahun);
        }

        // Filter Jenis Dokumen
        if ($request->filled('jenis_kode')) {
            $query->whereHas('jenisDokumen', fn ($j) => $j->where('kode', $request->jenis_kode));
        } elseif ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_reviu_id', $request->jenis_dokumen_id);
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
                    ->orWhereHas('jenisDokumen', fn ($j) => $j->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment', fn ($a) => $a->where('nomor_surat', 'like', "%{$search}%"));
            });
        }

        $lhrs = $query->latest('tanggal_lhr')->paginate(10)->withQueryString();

        // Hitung statistik LHR khusus OPD
        $allOpdLhrs = Lhr::whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [
                LhrStatus::Terbit,
                LhrStatus::TindakLanjut,
                LhrStatus::Selesai,
            ])
            ->with('statistik')
            ->get();

        $stats = (object) [
            'total_lhr' => $allOpdLhrs->count(),
            'total_rekomendasi' => $allOpdLhrs->sum(fn ($l) => $l->statistik->total_rekomendasi ?? 0),
            'rekomendasi_selesai' => $allOpdLhrs->sum(fn ($l) => $l->statistik->rekomendasi_selesai ?? 0),
            'rekomendasi_proses' => $allOpdLhrs->sum(fn ($l) => $l->statistik->rekomendasi_dalam_proses ?? 0),
        ];

        $tahunList = Lhr::whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [LhrStatus::Terbit, LhrStatus::TindakLanjut, LhrStatus::Selesai])
            ->distinct()
            ->orderBy('tahun_anggaran', 'desc')
            ->pluck('tahun_anggaran')
            ->toArray();

        if (empty($tahunList)) {
            $tahunList = [date('Y') + 1, date('Y'), date('Y') - 1];
        }

        $jenisDokumens = JenisDokumenReviu::where('aktif', true)
            ->orderBy('nama')
            ->get();

        $userUnitName = $user->opdUnits()->first()?->nama_unit ?? $user->name;

        return view('pages.opd.lhr.index', compact('lhrs', 'stats', 'tahunList', 'jenisDokumens', 'userUnitName'));
    }

    /**
     * Tampilkan detail dokumen LHR resmi beserta catatan & rekomendasi perbaikan untuk OPD.
     */
    public function show(string $uuid): View
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        $lhr = Lhr::where('uuid', $uuid)
            ->whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [
                LhrStatus::Terbit,
                LhrStatus::TindakLanjut,
                LhrStatus::Selesai,
            ])
            ->with([
                'unitDiperiksa',
                'jenisDokumen',
                'auditAssignment.ketuaTim',
                'auditAssignment.pengendaliTeknis',
                'sections',
                'dasarHukums',
                'pengujians.children.catatans.rekomendasis.tindakLanjuts.attachments',
                'pengujians.children.catatans.rekomendasis.tindakLanjuts.uploadOpdOleh',
                'pengujians.children.catatans.kategori',
                'pengujians.catatans.rekomendasis.tindakLanjuts.attachments',
                'pengujians.catatans.rekomendasis.tindakLanjuts.uploadOpdOleh',
                'pengujians.catatans.kategori',
                'statistik',
                'signer',
                'attachments.uploadedBy',
            ])
            ->firstOrFail();

        // Auto-ensure default record TindakLanjut ada untuk semua rekomendasi di dokumen LHR ini
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

        // Urutkan pengujian
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

        return view('pages.opd.lhr.show', compact('lhr'));
    }

    /**
     * Simpan draf uraian revisi dan berkas bukti lampiran dari OPD untuk rekomendasi LHR.
     */
    public function uploadTindakLanjut(Request $request, string $uuid, TindakLanjut $tindakLanjut): RedirectResponse
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        $lhr = Lhr::where('uuid', $uuid)
            ->whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [LhrStatus::Terbit, LhrStatus::TindakLanjut, LhrStatus::Selesai])
            ->firstOrFail();

        // Validasi keterkaitan rekomendasi dengan LHR ini
        $rekom = $tindakLanjut->recommendation;
        if (! $rekom || ! $rekom->lhrCatatan || $rekom->lhrCatatan->pengujian?->lhr_id !== $lhr->id) {
            abort(403, 'Rekomendasi ini tidak terafiliasi dengan dokumen LHR yang dituju.');
        }

        // Kunci jika sedang dikirim dan belum ada catatan tolak dari inspektorat
        if ($tindakLanjut->status_opd === 'dikirim' && empty($tindakLanjut->alasan_tolak_opd)) {
            return back()->with('error', 'Tindak lanjut rekomendasi ini sedang ditelaah oleh tim Inspektorat dan terkunci dari perubahan.');
        }

        $validated = $request->validate([
            'uraian_tindak_lanjut'     => 'nullable|string|max:5000',
            'keterangan_pendukung_opd' => 'nullable|string|max:5000',
            'attachments'              => 'nullable|array|max:5',
            'attachments.*'            => 'file|mimes:pdf,docx,doc,xlsx,xls,jpg,jpeg,png,zip|max:20480',
        ], [
            'uraian_tindak_lanjut.max'     => 'Uraian tindak lanjut maksimal 5000 karakter.',
            'keterangan_pendukung_opd.max' => 'Uraian tindak lanjut maksimal 5000 karakter.',
            'attachments.max'              => 'Maksimal 5 berkas lampiran sekaligus.',
            'attachments.*.mimes'          => 'Format berkas harus berupa PDF, Word, Excel, Gambar, atau ZIP.',
            'attachments.*.max'            => 'Ukuran setiap berkas maksimal 20 MB.',
        ]);

        $uraian = $request->input('keterangan_pendukung_opd', $request->input('uraian_tindak_lanjut'));

        DB::beginTransaction();
        try {
            $tindakLanjut->update([
                'keterangan_pendukung_opd' => $uraian ?? $tindakLanjut->keterangan_pendukung_opd,
                'status_opd'               => 'draft',
                'upload_opd_oleh'          => $user->id,
            ]);

            if ($request->hasFile('attachments')) {
                $existingCount = $tindakLanjut->attachments()->count();
                foreach ($request->file('attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/tindak_lanjut_lhr', 'public');
                        $tindakLanjut->attachments()->create([
                            'file_name'     => $file->getClientOriginalName(),
                            'file_path'     => $path,
                            'file_size'     => $file->getSize(),
                            'file_type'     => $file->getClientOriginalExtension(),
                            'jenis_bukti'   => 'opd_upload',
                            'urutan'        => $existingCount + $i,
                            'visibilitas'   => 'internal',
                            'keterangan'    => 'Dokumen Bukti Revisi LHR oleh Perangkat Daerah',
                            'uploaded_by'   => $user->id,
                        ]);
                    }
                }
            }

            DB::commit();

            $lhr->refreshStatistik();

            return redirect()
                ->route('opd.lhr.show', $lhr->uuid)
                ->with('success', 'Draf revisi & berkas bukti rekomendasi berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan draf tindak lanjut: ' . $e->getMessage());
        }
    }

    /**
     * Kirim tindak lanjut / naskah revisi rekomendasi LHR ke Inspektorat untuk ditelaah.
     */
    public function kirimTindakLanjut(Request $request, string $uuid, TindakLanjut $tindakLanjut): RedirectResponse
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        $lhr = Lhr::where('uuid', $uuid)
            ->whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [LhrStatus::Terbit, LhrStatus::TindakLanjut, LhrStatus::Selesai])
            ->firstOrFail();

        $rekom = $tindakLanjut->recommendation;
        if (! $rekom || ! $rekom->lhrCatatan || $rekom->lhrCatatan->pengujian?->lhr_id !== $lhr->id) {
            abort(403, 'Rekomendasi ini tidak terafiliasi dengan dokumen LHR yang dituju.');
        }

        $hasUraian = ! empty(trim((string) ($tindakLanjut->keterangan_pendukung_opd ?? $tindakLanjut->uraian_tindak_lanjut)));
        $hasAttachment = $tindakLanjut->attachments()->exists();

        if (! $hasUraian && ! $hasAttachment) {
            return redirect()
                ->route('opd.lhr.show', $lhr->uuid)
                ->with('error', 'Mohon isi uraian tindak lanjut atau unggah berkas naskah perbaikan sebelum mengirim ke Inspektorat.');
        }

        $tindakLanjut->update([
            'status_opd'       => 'dikirim',
            'dikirim_pada'     => now(),
            'alasan_tolak_opd' => null,
            'status_verifikasi'=> 'menunggu_verifikasi',
        ]);

        if ($lhr->status === LhrStatus::Terbit) {
            $lhr->update(['status' => LhrStatus::TindakLanjut]);
        }

        $lhr->refreshStatistik();

        return redirect()
            ->route('opd.lhr.show', $lhr->uuid)
            ->with('success', 'Tindak lanjut rekomendasi berhasil dikirim ke Inspektorat untuk diverifikasi.');
    }

    /**
     * Kirim seluruh draf tindak lanjut rekomendasi pada LHR ini sekaligus ke Inspektorat.
     */
    public function kirimSemuaTindakLanjut(Request $request, string $uuid): RedirectResponse
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        $lhr = Lhr::where('uuid', $uuid)
            ->whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [LhrStatus::Terbit, LhrStatus::TindakLanjut, LhrStatus::Selesai])
            ->firstOrFail();

        $rekomIds = Recommendation::whereHas('lhrCatatan.pengujian', function ($q) use ($lhr) {
            $q->where('lhr_id', $lhr->id);
        })->pluck('id');

        $draftTls = TindakLanjut::whereIn('recommendation_id', $rekomIds)
            ->where(function ($q) {
                $q->where('status_opd', 'draft')
                    ->orWhereNotNull('alasan_tolak_opd')
                    ->orWhere(function ($sq) {
                        $sq->whereNull('status_opd')
                            ->where(function ($ssq) {
                                $ssq->where(function ($k) {
                                    $k->whereNotNull('keterangan_pendukung_opd')
                                      ->where('keterangan_pendukung_opd', '!=', '');
                                })->orWhereHas('attachments');
                            });
                    });
            })
            ->get();

        if ($draftTls->isEmpty()) {
            return back()->with('info', 'Tidak ada draf tindak lanjut yang siap dikirim.');
        }

        foreach ($draftTls as $tl) {
            $tl->update([
                'status_opd'       => 'dikirim',
                'dikirim_pada'     => now(),
                'alasan_tolak_opd' => null,
                'status_verifikasi'=> 'menunggu_verifikasi',
            ]);
        }

        if ($lhr->status === LhrStatus::Terbit) {
            $lhr->update(['status' => LhrStatus::TindakLanjut]);
        }

        $lhr->refreshStatistik();

        return redirect()
            ->route('opd.lhr.show', $lhr->uuid)
            ->with('success', 'Seluruh draf tindak lanjut (' . $draftTls->count() . ' butir) berhasil dikirim ke Inspektorat.');
    }

    /**
     * Hapus berkas lampiran tindak lanjut LHR selama masih berstatus draft.
     */
    public function hapusLampiran(string $uuid, TindakLanjut $tindakLanjut, Attachment $attachment): RedirectResponse
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        $lhr = Lhr::where('uuid', $uuid)
            ->whereIn('unit_diperiksa_id', $unitIds)
            ->firstOrFail();

        if ($tindakLanjut->status_opd === 'dikirim' && empty($tindakLanjut->alasan_tolak_opd)) {
            return back()->with('error', 'Lampiran terkunci dan tidak dapat dihapus karena sedang dalam verifikasi Inspektorat.');
        }

        if ($attachment->attachable_type !== TindakLanjut::class || (int) $attachment->attachable_id !== (int) $tindakLanjut->id) {
            abort(404, 'Berkas lampiran tidak ditemukan pada tindak lanjut ini.');
        }

        if ($attachment->file_path && Storage::disk('local')->exists($attachment->file_path)) {
            Storage::disk('local')->delete($attachment->file_path);
        }

        $attachment->forceDelete();

        return redirect()
            ->route('opd.lhr.show', $lhr->uuid)
            ->with('success', 'Berkas lampiran berhasil dihapus.');
    }

    /**
     * Unduh berkas PDF resmi LHR untuk OPD.
     */
    public function downloadPdf(string $uuid, GenerateLhrPdf $generator): Response
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        $lhr = Lhr::where('uuid', $uuid)
            ->whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [
                LhrStatus::Terbit,
                LhrStatus::TindakLanjut,
                LhrStatus::Selesai,
            ])
            ->firstOrFail();

        $result = $generator->execute($lhr, true);

        $filename = 'LHR-' . str_replace(['/', '\\'], '-', $lhr->nomor_lhr ?: $lhr->uuid) . '.pdf';

        return response($result['binary'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Unduh berkas Word (.docx) LHR untuk OPD.
     */
    public function downloadWord(string $uuid, GenerateLhrWord $generator): BinaryFileResponse
    {
        $user = auth()->user();
        $unitIds = $this->getTargetUnitIds($user);

        $lhr = Lhr::where('uuid', $uuid)
            ->whereIn('unit_diperiksa_id', $unitIds)
            ->whereIn('status', [
                LhrStatus::Terbit,
                LhrStatus::TindakLanjut,
                LhrStatus::Selesai,
            ])
            ->firstOrFail();

        $result = $generator->execute($lhr, true);

        $filename = 'LHR-' . str_replace(['/', '\\'], '-', $lhr->nomor_lhr ?: $lhr->uuid) . '.docx';

        return response()->download($result['path'], $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
