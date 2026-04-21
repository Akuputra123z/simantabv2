<?php

namespace App\Http\Controllers;

use App\Models\Temuan;
use App\Models\Lhp;
use App\Models\KodeTemuan;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemuanController extends Controller
{
    public function __construct(private LhpStatistikService $statistikService) {}

    public function index(Request $request)
    {
        $query = Temuan::with(['lhp', 'kodeTemuan', 'recommendations']);

        if ($request->has('lhp_id')) {
            $query->where('lhp_id', $request->lhp_id);
        }

        $temuans = $query->latest()->paginate(10);

        return view('pages.temuan.index', compact('temuans'));
    }

    public function create(Request $request)
    {
        $lhpId = $request->query('lhp_id');
        $lhp   = Lhp::findOrFail($lhpId);

        $kodeTemuans = KodeTemuan::orderBy('kode', 'asc')->get();

        return view('pages.temuan.create', compact('lhp', 'kodeTemuans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lhp_id'                  => 'required|exists:lhps,id',
            'kode_temuan_id'          => 'required|exists:kode_temuans,id',
            'kondisi'                 => 'required|string',
            'sebab'                   => 'nullable|string',
            'akibat'                  => 'nullable|string',
            'nilai_temuan'            => 'nullable|numeric|min:0',
            'nilai_kerugian_negara'   => 'nullable|numeric|min:0',
            'nilai_kerugian_daerah'   => 'nullable|numeric|min:0',
            'nilai_kerugian_desa'     => 'nullable|numeric|min:0',
            'nilai_kerugian_bos_blud' => 'nullable|numeric|min:0',
            'nama_barang'             => 'nullable|string',
            'jumlah_barang'           => 'nullable|numeric|min:0',
            'kondisi_barang'          => 'nullable|string',
            'lokasi_barang'           => 'nullable|string',
            'attachments.*.file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'attachments.*.name'      => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $temuan = Temuan::create($validated);

            if ($request->has('attachments')) {
                foreach ($request->attachments as $index => $attach) {
                    if (isset($attach['file'])) {
                        $path = $attach['file']->store('attachments/temuan', 'public');
                        $temuan->attachments()->create([
                            'file_path'   => $path,
                            'file_name'   => $attach['name'] ?? $attach['file']->getClientOriginalName(),
                            'jenis_bukti' => 'temuan',
                            'urutan'      => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            // Setelah commit — data pasti tersimpan, statistik pasti baca data terbaru
            $this->statistikService->updateStatistik($temuan->lhp_id);

            return redirect()->route('lhps.show', $temuan->lhp_id)
                ->with('success', 'Temuan berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Store Temuan: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan temuan.');
        }
    }

    public function show(Temuan $temuan)
    {
        $temuan->load(['lhp', 'kodeTemuan', 'recommendations', 'attachments']);

        return view('pages.temuan.show', compact('temuan'));
    }

    public function edit(Temuan $temuan)
    {
        $lhp         = $temuan->lhp;
        $kodeTemuans = KodeTemuan::orderBy('kode', 'asc')->get();
        $temuan->load('attachments');

        return view('pages.temuan.edit', compact('temuan', 'lhp', 'kodeTemuans'));
    }

    public function update(Request $request, Temuan $temuan)
    {
        $validated = $request->validate([
            'kode_temuan_id'          => 'required|exists:kode_temuans,id',
            'kondisi'                 => 'required|string',
            'sebab'                   => 'nullable|string',
            'akibat'                  => 'nullable|string',
            'nilai_temuan'            => 'nullable|numeric|min:0',
            'nilai_kerugian_negara'   => 'nullable|numeric|min:0',
            'nilai_kerugian_daerah'   => 'nullable|numeric|min:0',
            'nilai_kerugian_desa'     => 'nullable|numeric|min:0',
            'nilai_kerugian_bos_blud' => 'nullable|numeric|min:0',
            'nama_barang'             => 'nullable|string',
            'jumlah_barang'           => 'nullable|numeric|min:0',
            'kondisi_barang'          => 'nullable|string',
            'lokasi_barang'           => 'nullable|string',
            'attachments.*.file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'attachments.*.name'      => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $temuan->update($validated);

            if ($request->has('attachments')) {
                foreach ($request->attachments as $index => $attach) {
                    if (isset($attach['file'])) {
                        $path = $attach['file']->store('attachments/temuan', 'public');
                        $temuan->attachments()->create([
                            'file_path'   => $path,
                            'file_name'   => $attach['name'] ?? $attach['file']->getClientOriginalName(),
                            'jenis_bukti' => 'temuan',
                            'urutan'      => $temuan->attachments()->count() + $index,
                        ]);
                    }
                }
            }

            DB::commit();

            $this->statistikService->updateStatistik($temuan->lhp_id);

            return redirect()->route('lhps.show', $temuan->lhp_id)
                ->with('success', 'Temuan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Update Temuan: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui temuan.');
        }
    }

    public function destroy(Temuan $temuan)
    {
        $lhpId = $temuan->lhp_id; // simpan sebelum dihapus

        $temuan->delete();

        // Hitung ulang setelah hapus — temuan sudah soft-deleted
        $this->statistikService->updateStatistik($lhpId);

        return redirect()->route('lhps.show', $lhpId)
            ->with('success', 'Temuan berhasil dihapus.');
    }
}