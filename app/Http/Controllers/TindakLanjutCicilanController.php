<?php

namespace App\Http\Controllers;

use App\Http\Requests\TindakLanjutCicilan\StoreTindakLanjutCicilanRequest;
use App\Http\Requests\TindakLanjutCicilan\UpdateTindakLanjutCicilanRequest;
use App\Http\Requests\TindakLanjutCicilan\VerifikasiCicilanRequest;
use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use App\Services\LhpStatistikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TindakLanjutCicilanController extends Controller
{
    public function __construct(private readonly LhpStatistikService $statistikService) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(TindakLanjut $tindakLanjut): View
    {
        $tindakLanjut->load([
            'recommendation:id,temuan_id,uraian_rekom,nilai_rekom,nilai_sisa,jenis_rekomendasi,status',
            'recommendation.temuan:id,lhp_id,kondisi',
            'recommendation.temuan.lhp:id,nomor_lhp',
        ]);

        $cicilans = $tindakLanjut->cicilans()
            ->with('diverifikator:id,name')
            ->withTrashed() 
            ->orderBy('ke')
            ->get();

        // FIX: Menambahkan key 'total_rencana' dan 'total_realisasi' agar View tidak error
        $summary = [
            'total_rencana'   => $tindakLanjut->jumlah_cicilan_rencana ?? 0,
            'total_realisasi' => $cicilans->whereNull('deleted_at')->count(),
            'total_terbayar'  => $tindakLanjut->total_terbayar ?? 0,
            'sisa'            => $tindakLanjut->sisa_belum_bayar ?? 0,
            'nilai_rekom'     => $tindakLanjut->recommendation->nilai_rekom ?? 0,
            'is_uang'         => $tindakLanjut->recommendation?->isUang() ?? true,
        ];

        return view('pages.tindak-lanjuts.cicilans.index', compact('tindakLanjut', 'cicilans', 'summary'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(TindakLanjut $tindakLanjut): View
    {
        // Hitung nomor urut berikutnya berdasarkan cicilan yang ada (termasuk yang di-softdelete jika perlu)
        $nextKe = $tindakLanjut->cicilans()->withTrashed()->count() + 1;
        $tindakLanjut->load(['recommendation.temuan.lhp']);

        return view('pages.tindak-lanjuts.cicilans.create', compact('tindakLanjut', 'nextKe'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(StoreTindakLanjutCicilanRequest $request, TindakLanjut $tindakLanjut): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Satukan data dengan default breakdown 0 jika kosong
            $insertData = array_merge($data, [
                'created_by'           => auth()->id(),
                'nilai_bayar_negara'   => $request->nilai_bayar_negara ?? 0,
                'nilai_bayar_daerah'   => $request->nilai_bayar_daerah ?? 0,
                'nilai_bayar_desa'     => $request->nilai_bayar_desa ?? 0,
                'nilai_bayar_bos_blud' => $request->nilai_bayar_bos_blud ?? 0,
            ]);

            $cicilan = $tindakLanjut->cicilans()->create($insertData);

            DB::commit();

            $this->updateGlobalStatistik($tindakLanjut);

            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('success', "Cicilan ke-{$cicilan->ke} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan cicilan: ' . $e->getMessage());
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
        ]);

        return view('pages.tindak-lanjuts.cicilans.show', compact('tindakLanjut', 'cicilan'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): View
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);

        if ($cicilan->isDiterima()) {
            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('error', 'Cicilan yang sudah diterima tidak dapat diubah.');
        }

        $tindakLanjut->load([
            'recommendation:id,temuan_id,uraian_rekom,nilai_rekom,nilai_sisa,jenis_rekomendasi',
            'recommendation.temuan.lhp:id,nomor_lhp',
        ]);

        return view('pages.tindak-lanjuts.cicilans.edit', compact('tindakLanjut', 'cicilan'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(UpdateTindakLanjutCicilanRequest $request, TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): RedirectResponse
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);

        if ($cicilan->isDiterima()) {
            return back()->with('error', 'Cicilan yang sudah diterima tidak dapat diubah.');
        }

        DB::beginTransaction();
        try {
            $cicilan->update(array_merge($request->validated(), [
                'updated_by'           => auth()->id(),
                'nilai_bayar_negara'   => $request->nilai_bayar_negara   ?? 0,
                'nilai_bayar_daerah'   => $request->nilai_bayar_daerah   ?? 0,
                'nilai_bayar_desa'     => $request->nilai_bayar_desa     ?? 0,
                'nilai_bayar_bos_blud' => $request->nilai_bayar_bos_blud ?? 0,
            ]));

            DB::commit();

            $this->updateGlobalStatistik($tindakLanjut);

            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('success', "Cicilan ke-{$cicilan->ke} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui cicilan: ' . $e->getMessage());
        }
    }

    // ── Verifikasi ────────────────────────────────────────────────────────────

    public function verifikasi(VerifikasiCicilanRequest $request, TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): RedirectResponse
    {
        $this->authorizeCicilan($tindakLanjut, $cicilan);

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

            $this->updateGlobalStatistik($tindakLanjut);

            $label = $request->status === 'diterima' ? 'diterima' : 'ditolak';
            return redirect()
                ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
                ->with('success', "Cicilan ke-{$cicilan->ke} berhasil {$label}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memverifikasi cicilan: ' . $e->getMessage());
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

        $this->updateGlobalStatistik($tindakLanjut);

        return redirect()
            ->route('tindak-lanjuts.cicilans.index', $tindakLanjut)
            ->with('success', "Cicilan ke-{$ke} berhasil dihapus.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function updateGlobalStatistik(TindakLanjut $tindakLanjut): void
    {
        $lhpId = $tindakLanjut->recommendation?->temuan?->lhp_id;
        if ($lhpId) {
            $this->statistikService->updateStatistik($lhpId);
        }
    }

    private function authorizeCicilan(TindakLanjut $tindakLanjut, TindakLanjutCicilan $cicilan): void
    {
        abort_if(
            $cicilan->tindak_lanjut_id !== $tindakLanjut->id,
            403,
            'Cicilan tidak ditemukan pada tindak lanjut ini.'
        );
    }
}