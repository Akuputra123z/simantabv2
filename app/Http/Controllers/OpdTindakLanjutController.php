<?php

namespace App\Http\Controllers;

use App\Http\Requests\Opd\OpdUploadTindakLanjutRequest;
use App\Models\Attachment;
use App\Models\TindakLanjut;
use App\Models\Recommendation;
use App\Services\LhpStatistikService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OpdTindakLanjutController extends Controller
{
    public function __construct(
        private readonly LhpStatistikService $statistikService
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        $tindakLanjuts = TindakLanjut::query()
            ->forOpd($user)
            ->with([
                'recommendation.temuan.lhp.unitDiperiksa',
                'recommendation.kodeRekomendasi',
                'attachments',
                'uploadOpdOleh',
            ])
            ->when(request('status_opd'), function ($query, $statusOpd) {
                if ($statusOpd === 'belum_upload') {
                    $query->opdBelumUpload();
                } elseif ($statusOpd === 'draft') {
                    $query->opdDraft();
                } elseif ($statusOpd === 'dikirim') {
                    $query->opdDikirim();
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = TindakLanjut::query()
            ->forOpd($user)
            ->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) AS total_lunas,
                SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) AS total_berjalan,
                SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' THEN 1 ELSE 0 END) AS total_menunggu
            ")
            ->first();

        $opdStats = TindakLanjut::query()
            ->forOpd($user)
            ->selectRaw("
                SUM(CASE WHEN status_opd IS NULL THEN 1 ELSE 0 END) AS total_belum_upload,
                SUM(CASE WHEN status_opd = 'draft' THEN 1 ELSE 0 END) AS total_draft,
                SUM(CASE WHEN status_opd = 'dikirim' THEN 1 ELSE 0 END) AS total_dikirim
            ")
            ->first();

        return view('pages.opd.tindak-lanjut.index', compact('tindakLanjuts', 'stats', 'opdStats'));
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

            $tindakLanjut->update([
                'keterangan_pendukung_opd' => $request->keterangan_pendukung,
                'upload_opd_oleh'          => auth()->id(),
                'status_opd'               => 'draft',
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/opd-upload', 'public');
                        $tindakLanjut->attachments()->create([
                            'file_path'   => $path,
                            'file_name'   => $file->getClientOriginalName(),
                            'file_type'   => $file->getMimeType(),
                            'file_size'   => $file->getSize(),
                            'jenis_bukti' => 'opd_upload',
                            'urutan'      => $i,
                            'visibilitas' => 'internal',
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('opd.tindak-lanjut.show', $tindakLanjut)
                ->with('success', 'Bukti tindak lanjut berhasil diupload.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OPD upload error: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Gagal mengupload bukti.');
        }
    }

    public function hapusLampiran(TindakLanjut $tindakLanjut, Attachment $attachment): JsonResponse|RedirectResponse
    {
        $this->authorize('uploadOpd', $tindakLanjut);

        if ($attachment->jenis_bukti !== 'opd_upload') {
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
                'status_opd'    => 'dikirim',
                'dikirim_pada'  => now(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($tindakLanjut)
                ->withProperties(['status_opd' => 'dikirim'])
                ->log('OPD mengirim tindak lanjut');

            return redirect()
                ->route('opd.tindak-lanjut.show', $tindakLanjut)
                ->with('success', 'Tindak lanjut berhasil dikirim.');
        } catch (\Throwable $e) {
            Log::error('OPD kirim error: ' . $e->getMessage());

            return back()->with('error', 'Gagal mengirim tindak lanjut.');
        }
    }
}
