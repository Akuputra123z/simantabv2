<?php

namespace App\Http\Controllers;

use App\Http\Requests\Opd\OpdUploadTindakLanjutRequest;
use App\Models\Attachment;
use App\Models\AuditProgram;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Services\LhpStatistikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OpdTindakLanjutController extends Controller
{
    public function __construct(
        private readonly LhpStatistikService $statistikService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $isSuperOrAdmin = $user->hasAnyRole(['super_admin', 'admin', 'admin_inspektorat', 'kepala_inspektorat']);

        // Target unit IDs
        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
        if ($unitIds->isNotEmpty()) {
            $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
            $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
        } elseif ($isSuperOrAdmin) {
            $unitIds = UnitDiperiksa::pluck('id');
        }

        // Auto-ensure default TindakLanjut stubs exist for all recommendations of these units
        if ($unitIds->isNotEmpty() || $isSuperOrAdmin) {
            $missingQuery = Recommendation::doesntHave('tindakLanjuts');
            if (! $isSuperOrAdmin) {
                $missingQuery->whereHas('temuan.lhp', function ($q) use ($unitIds) {
                    $q->whereIn('unit_diperiksa_id', $unitIds);
                });
            }
            $missingRekomIds = $missingQuery->pluck('id');

            foreach ($missingRekomIds as $rekomId) {
                TindakLanjut::firstOrCreate(
                    ['recommendation_id' => $rekomId],
                    [
                        'status_verifikasi'   => 'menunggu_verifikasi',
                        'nilai_tindak_lanjut' => 0,
                        'total_terbayar'      => 0,
                        'sisa_belum_bayar'    => 0,
                    ]
                );
            }
        }

        // Filters (Default 'PKPT')
        $kategori  = $request->input('kategori', 'PKPT');
        $statusOpd = $request->input('status_opd');
        $status    = $request->input('status');
        $search    = $request->input('search');

        $query = Lhp::query();
        if (! $isSuperOrAdmin) {
            $query->whereIn('unit_diperiksa_id', $unitIds);
        }

        $query->with([
            'unitDiperiksa',
            'auditAssignment.auditProgramDetail.auditProgram',
            'statistik',
            'temuans.recommendations.tindakLanjuts',
            'temuans.recommendations.kodeRekomendasi',
        ]);

        // Filter Kategori Program Audit (Bila bukan 'semua')
        if ($kategori && $kategori !== 'semua') {
            $query->whereHas('auditAssignment.auditProgramDetail.auditProgram', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        // Filter Status OPD
        if ($statusOpd === 'belum_upload') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->whereNull('status_opd');
            });
        } elseif ($statusOpd === 'draft') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->where('status_opd', 'draft')->whereNull('alasan_tolak_opd');
            });
        } elseif ($statusOpd === 'dikirim') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->where('status_opd', 'dikirim');
            });
        } elseif ($statusOpd === 'ditolak') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->where('status_opd', 'draft')->whereNotNull('alasan_tolak_opd');
            });
        }

        // Filter Status Verifikasi
        if ($status) {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) use ($status) {
                $q->where('status_verifikasi', $status);
            });
        }

        // Filter Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                  ->orWhere('catatan_umum', 'like', "%{$search}%")
                  ->orWhereHas('temuans', function ($qT) use ($search) {
                      $qT->where('uraian_temuan', 'like', "%{$search}%")
                         ->orWhereHas('recommendations', function ($qR) use ($search) {
                             $qR->where('uraian_rekom', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $lhps = (clone $query)->latest('tanggal_lhp')->paginate(15)->withQueryString();

        // High-level OPD stats across unit recommendations
        if ($isSuperOrAdmin) {
            $baseUnitTlQuery = TindakLanjut::query();
        } else {
            $baseUnitTlQuery = TindakLanjut::query()
                ->whereHas('recommendation.temuan.lhp', function ($q) use ($unitIds) {
                    $q->whereIn('unit_diperiksa_id', $unitIds);
                });
        }

        $stats = (clone $baseUnitTlQuery)
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) AS total_lunas,
                SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) AS total_berjalan,
                SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' OR status_verifikasi IS NULL THEN 1 ELSE 0 END) AS total_menunggu
            ")
            ->first();

        $opdStats = (clone $baseUnitTlQuery)
            ->selectRaw("
                SUM(CASE WHEN status_opd IS NULL THEN 1 ELSE 0 END) AS total_belum_upload,
                SUM(CASE WHEN status_opd = 'draft' AND alasan_tolak_opd IS NULL THEN 1 ELSE 0 END) AS total_draft,
                SUM(CASE WHEN status_opd = 'dikirim' THEN 1 ELSE 0 END) AS total_dikirim,
                SUM(CASE WHEN status_opd = 'draft' AND alasan_tolak_opd IS NOT NULL THEN 1 ELSE 0 END) AS total_ditolak
            ")
            ->first();

        $listKategori = AuditProgram::KATEGORI;

        return view('pages.opd.tindak-lanjut.index', compact(
            'lhps',
            'stats',
            'opdStats',
            'kategori',
            'statusOpd',
            'status',
            'search',
            'listKategori'
        ));
    }

    public function showLhp(Lhp $lhp): View
    {
        $user = auth()->user();
        $isSuperOrAdmin = $user->hasAnyRole(['super_admin', 'admin', 'admin_inspektorat', 'kepala_inspektorat']);

        if (! $isSuperOrAdmin) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            if ($unitIds->isNotEmpty()) {
                $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
                $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
            }

            if (! $unitIds->contains($lhp->unit_diperiksa_id)) {
                abort(403, 'Anda tidak memiliki akses ke LHP ini.');
            }
        }

        // Auto-ensure default TindakLanjut stubs exist for all recommendations in this LHP
        $missingRekomIds = Recommendation::whereHas('temuan', fn($q) => $q->where('lhp_id', $lhp->id))
            ->doesntHave('tindakLanjuts')
            ->pluck('id');

        foreach ($missingRekomIds as $rekomId) {
            TindakLanjut::firstOrCreate(
                ['recommendation_id' => $rekomId],
                [
                    'status_verifikasi'   => 'menunggu_verifikasi',
                    'nilai_tindak_lanjut' => 0,
                    'total_terbayar'      => 0,
                    'sisa_belum_bayar'    => 0,
                ]
            );
        }

        $lhp->load([
            'unitDiperiksa',
            'auditAssignment.ketuaTim',
            'auditAssignment.members',
            'auditAssignment.auditProgramDetail.auditProgram',
            'statistik',
            'temuans.kodeTemuan',
            'temuans.recommendations.kodeRekomendasi',
            'temuans.recommendations.tindakLanjuts.attachments',
            'temuans.recommendations.tindakLanjuts.cicilans.diverifikator',
            'temuans.recommendations.tindakLanjuts.cicilans.attachments',
            'temuans.recommendations.tindakLanjuts.uploadOpdOleh',
        ]);

        return view('pages.opd.tindak-lanjut.lhp_detail', compact('lhp'));
    }

    public function kirimSemuaLhp(Lhp $lhp): RedirectResponse
    {
        $user = auth()->user();
        $isSuperOrAdmin = $user->hasAnyRole(['super_admin', 'admin', 'admin_inspektorat', 'kepala_inspektorat']);

        if (! $isSuperOrAdmin) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            if ($unitIds->isNotEmpty()) {
                $userUnitNames = $user->opdUnits()->pluck('nama_unit')->filter()->unique();
                $unitIds = UnitDiperiksa::whereIn('nama_unit', $userUnitNames)->pluck('id');
            }

            if (! $unitIds->contains($lhp->unit_diperiksa_id)) {
                abort(403, 'Anda tidak memiliki akses ke LHP ini.');
            }
        }

        $draftTls = TindakLanjut::whereHas('recommendation.temuan', function ($q) use ($lhp) {
            $q->where('lhp_id', $lhp->id);
        })->where(function ($q) {
            $q->where('status_opd', 'draft')->orWhereNull('status_opd');
        })->get();

        if ($draftTls->isEmpty()) {
            return back()->with('info', 'Tidak ada rekomendasi bertanda draft yang dapat dikirim.');
        }

        foreach ($draftTls as $tl) {
            $tl->update([
                'status_opd'       => 'dikirim',
                'dikirim_pada'     => now(),
                'alasan_tolak_opd' => null,
            ]);
        }

        return redirect()
            ->route('opd.tindak-lanjut.lhp', $lhp)
            ->with('success', 'Seluruh tindak lanjut pada LHP ini berhasil dikirim ke Inspektorat.');
    }

    public function show(TindakLanjut $tindakLanjut): View
    {
        $this->authorize('uploadOpd', $tindakLanjut);

        $tindakLanjut->load([
            'recommendation.temuan.lhp.unitDiperiksa',
            'recommendation.temuan.lhp.auditAssignment.ketuaTim',
            'recommendation.temuan.lhp.auditAssignment.members',
            'recommendation.temuan.kodeTemuan',
            'recommendation.kodeRekomendasi',
            'verifikator',
            'attachments',
            'uploadOpdOleh',
        ]);

        return view('pages.opd.tindak-lanjut.show', compact('tindakLanjut'));
    }

    public function upload(OpdUploadTindakLanjutRequest $request, TindakLanjut $tindakLanjut)
    {
        $this->authorize('uploadOpd', $tindakLanjut);

        try {
            DB::beginTransaction();

            $rekom     = $tindakLanjut->recommendation;
            $jenis     = $request->input('jenis_penyelesaian');
            $keterangan = trim($request->input('keterangan_pendukung') ?? '');

            $updateData = [
                'upload_opd_oleh'          => auth()->id(),
                'status_opd'               => 'draft',
                'alasan_tolak_opd'         => null,
            ];

            if ($jenis) {
                $updateData['jenis_penyelesaian'] = $jenis;
                $updateData['is_cicilan'] = ($jenis === 'cicilan');
            }

            $cicilanRecord = null;

            if ($jenis === 'cicilan') {
                $targetNilai = (float) ($rekom?->nilai_rekom ?? 0);
                if ((float) $tindakLanjut->nilai_tindak_lanjut <= 0 && $targetNilai > 0) {
                    $updateData['nilai_tindak_lanjut'] = $targetNilai;
                }

                $nominalBayar = (float) ($request->input('nilai_tindak_lanjut') ?? 0);
                if ($nominalBayar > 0) {
                    $cicilanRecord = $tindakLanjut->cicilans()->create([
                        'nilai_bayar'   => $nominalBayar,
                        'tanggal_bayar' => $request->input('tanggal_bayar') ?? now(),
                        'nomor_bukti'   => $request->input('nomor_bukti'),
                        'keterangan'    => $keterangan,
                        'jenis_bayar'   => 'setor_kas',
                        'status'        => \App\Models\TindakLanjutCicilan::STATUS_MENUNGGU,
                        'created_by'    => auth()->id(),
                    ]);
                }
            } elseif ($jenis === 'setor_kas') {
                $nominalBayar = (float) ($request->input('nilai_tindak_lanjut') ?? $rekom?->nilai_rekom ?? 0);
                $updateData['nilai_tindak_lanjut'] = $nominalBayar;

                if ($request->filled('nomor_bukti')) {
                    $buktiPrefix = 'No. Bukti / STS: ' . $request->input('nomor_bukti');
                    if (! str_contains($keterangan, $buktiPrefix)) {
                        $keterangan = trim($buktiPrefix . ($keterangan ? "\n" . $keterangan : ''));
                    }
                }
            } elseif (in_array($jenis, ['pengembalian_barang', 'perbaikan_administrasi'])) {
                $updateData['nilai_tindak_lanjut'] = 0;

                if ($request->filled('nomor_bukti')) {
                    $label = ($jenis === 'pengembalian_barang') ? 'No. BAST / Aset: ' : 'No. Dokumen: ';
                    $buktiPrefix = $label . $request->input('nomor_bukti');
                    if (! str_contains($keterangan, $buktiPrefix)) {
                        $keterangan = trim($buktiPrefix . ($keterangan ? "\n" . $keterangan : ''));
                    }
                }
            }

            $updateData['keterangan_pendukung_opd'] = $keterangan;
            $tindakLanjut->update($updateData);

            if ($request->hasFile('attachments')) {
                $existingCount = $tindakLanjut->attachments()->count();
                foreach ($request->file('attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/opd-upload', 'public');
                        $tindakLanjut->attachments()->create([
                            'file_path'   => $path,
                            'file_name'   => $file->getClientOriginalName(),
                            'file_type'   => $file->getMimeType(),
                            'file_size'   => $file->getSize(),
                            'jenis_bukti' => 'opd_upload',
                            'urutan'      => $existingCount + $i,
                            'visibilitas' => 'internal',
                            'uploaded_by' => auth()->id(),
                        ]);

                        if ($cicilanRecord) {
                            $cicilanRecord->attachments()->create([
                                'file_path'   => $path,
                                'file_name'   => $file->getClientOriginalName(),
                                'file_type'   => $file->getMimeType(),
                                'file_size'   => $file->getSize(),
                                'jenis_bukti' => 'cicilan_bukti',
                                'urutan'      => $i,
                                'visibilitas' => 'internal',
                                'uploaded_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            $tindakLanjut->syncCalculations(fromCascade: false)->saveQuietly();
            $tindakLanjut->recommendation?->syncStatus();

            DB::commit();

            $lhpId = $tindakLanjut->recommendation?->temuan?->lhp_id;
            if ($lhpId) {
                $this->statistikService->updateStatistik($lhpId);
            }

            return back()->with('success', 'Bukti tindak lanjut berhasil disimpan sebagai draft.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OPD upload error: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Gagal mengupload bukti: ' . $e->getMessage());
        }
    }

    public function hapusLampiran(TindakLanjut $tindakLanjut, Attachment $attachment): JsonResponse|RedirectResponse
    {
        $this->authorize('uploadOpd', $tindakLanjut);

        if (! in_array($attachment->jenis_bukti, ['opd_upload', 'tindak_lanjut'])) {
            return request()->wantsJson()
                ? response()->json(['message' => 'Lampiran tidak valid.'], 422)
                : back()->with('error', 'Lampiran tidak valid.');
        }

        if ($attachment->attachable_id !== $tindakLanjut->id) {
            return request()->wantsJson()
                ? response()->json(['message' => 'Lampiran tidak ditemukan.'], 404)
                : back()->with('error', 'Lampiran tidak ditemukan.');
        }

        try {
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $attachment->forceDelete();

            return request()->wantsJson()
                ? response()->json(['message' => 'Lampiran berhasil dihapus.'])
                : back()->with('success', 'Lampiran berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Hapus lampiran OPD error: ' . $e->getMessage());

            return request()->wantsJson()
                ? response()->json(['message' => 'Gagal menghapus lampiran.'], 500)
                : back()->with('error', 'Gagal menghapus lampiran.');
        }
    }

    public function kirim(TindakLanjut $tindakLanjut): RedirectResponse
    {
        $this->authorize('kirim', $tindakLanjut);

        try {
            $tindakLanjut->update([
                'status_opd'       => 'dikirim',
                'dikirim_pada'     => now(),
                'alasan_tolak_opd' => null, // Reset rejection reason when OPD re-submits
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($tindakLanjut)
                ->withProperties(['status_opd' => 'dikirim'])
                ->log('OPD mengirim tindak lanjut');

            return back()->with('success', 'Tindak lanjut berhasil dikirim.');
        } catch (\Throwable $e) {
            Log::error('OPD kirim error: ' . $e->getMessage());

            return back()->with('error', 'Gagal mengirim tindak lanjut.');
        }
    }
}

