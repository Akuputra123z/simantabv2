<?php

namespace App\Http\Controllers;

use App\Http\Requests\TindakLanjutCicilan\StoreTindakLanjutCicilanRequest;
use App\Http\Requests\TindakLanjutCicilan\UpdateTindakLanjutCicilanRequest;
use App\Http\Requests\TindakLanjutCicilan\VerifikasiCicilanRequest;
use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use App\Services\LhpStatistikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TindakLanjutCicilanController extends Controller
{
    public function __construct(private readonly LhpStatistikService $statistikService) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(TindakLanjut $tindakLanjut): View
    {
        $this->authorize('view', $tindakLanjut);

        $tindakLanjut->load([
            'recommendation:id,temuan_id,uraian_rekom,nilai_rekom,nilai_sisa,jenis_rekomendasi,status',
            'recommendation.temuan:id,lhp_id,kondisi',
            'recommendation.temuan.lhp:id,nomor_lhp',
        ]);

        $cicilans = $tindakLanjut->cicilans()
            ->with([
                'diverifikator:id,name',
                'creator:id,name',
                'attachments',
            ])
            ->orderBy('ke')
            ->get();

        $summary = [
            'total_rencana'   => $tindakLanjut->jumlah_cicilan_rencana ?? 0,
            'total_realisasi' => $cicilans->count(),
            'total_terbayar'  => (float) ($tindakLanjut->total_terbayar ?? 0),
            'sisa'            => (float) ($tindakLanjut->sisa_belum_bayar ?? 0),
            'nilai_rekom'     => (float) ($tindakLanjut->recommendation->nilai_rekom ?? 0),
            'is_uang'         => $tindakLanjut->recommendation?->isUang() ?? true,
        ];

        return view('pages.tindak-lanjuts.cicilans.index', compact('tindakLanjut', 'cicilans', 'summary'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(TindakLanjut $tindakLanjut): View
    {
        $this->authorize('view', $tindakLanjut);

        $nextKe = $tindakLanjut->nextKeCicilan();
        $tindakLanjut->load(['recommendation.temuan.lhp']);

        return view('pages.tindak-lanjuts.cicilans.create', compact('tindakLanjut', 'nextKe'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(StoreTindakLanjutCicilanRequest $request, TindakLanjut $tindakLanjut): RedirectResponse
    {
        $this->authorize('view', $tindakLanjut);

        DB::beginTransaction();
        try {
            $data = $request->validated();

            $insertData = array_merge($data, [
                'created_by'           => auth()->id(),
                'nilai_bayar_negara'   => $request->nilai_bayar_negara ?? 0,
                'nilai_bayar_daerah'   => $request->nilai_bayar_daerah ?? 0,
                'nilai_bayar_desa'     => $request->nilai_bayar_desa ?? 0,
                'nilai_bayar_bos_blud' => $request->nilai_bayar_bos_blud ?? 0,
            ]);

            // Jika status langsung diterima oleh pemeriksa/admin saat input
            if (($insertData['status'] ?? null) === TindakLanjutCicilan::STATUS_DITERIMA) {
                $insertData['diverifikasi_oleh'] = auth()->id();
                $insertData['diverifikasi_pada'] = now();
            }

            $cicilan = $tindakLanjut->cicilans()->create($insertData);

            // Simpan bukti attachment jika diunggah
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/cicilans', 'public');
                        $cicilan->attachments()->create([
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

            // Pastikan flag cicilan pada tindak lanjut aktif
            if (! $tindakLanjut->is_cicilan || $tindakLanjut->jenis_penyelesaian !== 'cicilan') {
                $tindakLanjut->updateQuietly([
                    'is_cicilan'         => true,
                    'jenis_penyelesaian' => 'cicilan',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('success', "Cicilan ke-{$cicilan->ke} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan cicilan: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('error', 'Gagal menyimpan cicilan: '.$e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): View
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);

        $cicilan->load([
            'tindakLanjut.recommendation.temuan.lhp',
            'diverifikator:id,name',
            'creator:id,name',
            'attachments',
        ]);

        return view('pages.tindak-lanjuts.cicilans.show', compact('tindakLanjut', 'cicilan'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): View|RedirectResponse
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);

        if ($cicilan->isDiterima()) {
            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('error', 'Cicilan yang sudah diverifikasi diterima tidak dapat diubah.');
        }

        $tindakLanjut->load([
            'recommendation:id,temuan_id,uraian_rekom,nilai_rekom,nilai_sisa,jenis_rekomendasi',
            'recommendation.temuan.lhp:id,nomor_lhp',
        ]);

        $cicilan->load('attachments');

        return view('pages.tindak-lanjuts.cicilans.edit', compact('tindakLanjut', 'cicilan'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(UpdateTindakLanjutCicilanRequest $request, TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): RedirectResponse
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);

        if ($cicilan->isDiterima()) {
            return back()->with('error', 'Cicilan yang sudah diverifikasi diterima tidak dapat diubah.');
        }

        DB::beginTransaction();
        try {
            $data = $request->validated();

            $updateData = array_merge($data, [
                'updated_by'           => auth()->id(),
                'nilai_bayar_negara'   => $request->nilai_bayar_negara ?? 0,
                'nilai_bayar_daerah'   => $request->nilai_bayar_daerah ?? 0,
                'nilai_bayar_desa'     => $request->nilai_bayar_desa ?? 0,
                'nilai_bayar_bos_blud' => $request->nilai_bayar_bos_blud ?? 0,
            ]);

            if (($updateData['status'] ?? null) === TindakLanjutCicilan::STATUS_DITERIMA) {
                $updateData['diverifikasi_oleh'] = auth()->id();
                $updateData['diverifikasi_pada'] = now();
            }

            $cicilan->update($updateData);

            if ($request->hasFile('attachments')) {
                $existingCount = $cicilan->attachments()->count();
                foreach ($request->file('attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/cicilans', 'public');
                        $cicilan->attachments()->create([
                            'file_path'   => $path,
                            'file_name'   => $file->getClientOriginalName(),
                            'file_type'   => $file->getMimeType(),
                            'file_size'   => $file->getSize(),
                            'jenis_bukti' => 'cicilan_bukti',
                            'urutan'      => $existingCount + $i,
                            'visibilitas' => 'internal',
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('success', "Cicilan ke-{$cicilan->ke} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui cicilan: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('error', 'Gagal memperbarui cicilan: '.$e->getMessage());
        }
    }

    // ── Verifikasi ────────────────────────────────────────────────────────────

    public function verifikasi(VerifikasiCicilanRequest $request, TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): RedirectResponse
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);
        $this->authorize('verifikasi', $tindakLanjut);

        DB::beginTransaction();
        try {
            $cicilan->update([
                'status'             => $request->status,
                'diverifikasi_oleh'  => auth()->id(),
                'diverifikasi_pada'  => now(),
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'updated_by'         => auth()->id(),
            ]);

            DB::commit();

            $label = $request->status === TindakLanjutCicilan::STATUS_DITERIMA ? 'diterima' : 'ditolak';

            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('success', "Cicilan ke-{$cicilan->ke} berhasil {$label}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal memverifikasi cicilan: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Gagal memverifikasi cicilan. Silakan coba lagi.');
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): RedirectResponse
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);

        if ($cicilan->isDiterima()) {
            return back()->with('error', 'Cicilan yang sudah diterima tidak dapat dihapus.');
        }

        $ke = $cicilan->ke;
        DB::transaction(fn () => $cicilan->delete());

        return redirect()
            ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
            ->with('success', "Cicilan ke-{$ke} berhasil dihapus.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function authorizeCicilan(TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): void
    {
        if ((int) $cicilan->tindak_lanjut_id !== (int) $tindakLanjut->id) {
            abort(404, 'Data cicilan tidak sesuai dengan tindak lanjut yang dipilih.');
        }

        $this->authorize('view', $tindakLanjut);
    }
}
