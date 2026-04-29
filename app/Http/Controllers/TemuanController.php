<?php

namespace App\Http\Controllers;

use App\Models\KodeTemuan;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemuanController extends Controller
{
    public function __construct(private LhpStatistikService $statistikService) {}

   public function index(Request $request)
{
    // 1. Gunakan withCount jika Anda hanya ingin menampilkan jumlah rekomendasi di tabel.
    // 2. Gunakan select() untuk hanya mengambil kolom yang diperlukan (opsional, tapi bagus untuk performa).
    $query = Temuan::query()
        ->with(['lhp:id,nomor_lhp', 'kodeTemuan:id,kode,uraian']) 
        ->withCount('recommendations');

    // 3. Gunakan when() agar kode lebih bersih daripada if-statement manual.
    $query->when($request->filled('lhp_id'), function ($q) use ($request) {
        $q->where('lhp_id', $request->lhp_id);
    });

    // 4. Tambahkan fitur pencarian sederhana untuk meningkatkan UX.
    $query->when($request->filled('search'), function ($q) use ($request) {
        $q->where('kondisi', 'like', '%' . $request->search . '%');
    });

    // 5. withQueryString() memastikan pagination tidak hilang saat user melakukan filter/search.
    $temuans = $query->latest()->paginate(10)->withQueryString();

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
            $validated['nilai_temuan'] = ($validated['nilai_kerugian_negara'] ?? 0) + 
                             ($validated['nilai_kerugian_daerah'] ?? 0) + 
                             ($validated['nilai_kerugian_desa'] ?? 0) + 
                             ($validated['nilai_kerugian_bos_blud'] ?? 0);

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

        $totalNilaiBaru = (float)($validated['nilai_kerugian_negara']   ?? 0)
                        + (float)($validated['nilai_kerugian_daerah']   ?? 0)
                        + (float)($validated['nilai_kerugian_desa']     ?? 0)
                        + (float)($validated['nilai_kerugian_bos_blud'] ?? 0);

        $nilaiLama = (float) $temuan->nilai_temuan;
        $isChanged = abs($nilaiLama - $totalNilaiBaru) > 0.01;

        $validated['nilai_temuan'] = $totalNilaiBaru;
        $temuan->update($validated);

      if ($isChanged) {
    $temuan->load('recommendations.tindakLanjuts.cicilans');

    foreach ($temuan->recommendations as $rekom) {
        
        // ✅ Reset status rekomendasi ke nilai enum yang valid
        $rekom->update([
            'status'           => Recommendation::STATUS_BELUM, // konstanta yang sudah ada
            'nilai_tl_selesai' => 0,
            // ❌ JANGAN reset nilai_rekom — itu target bayar per rekom, bukan total temuan
        ]);

        foreach ($rekom->tindakLanjuts as $tl) {
            
            if ($tl->jenis_penyelesaian === 'cicilan') {
                // ✅ Reset cicilan: kembalikan status ke 'menunggu' (bukan 'diterima')
                $tl->cicilans()->update([
                    'status'             => 'menunggu', // enum valid di TindakLanjutCicilan
                    'diverifikasi_oleh'  => null,
                    'diverifikasi_pada'  => null,
                    'catatan_verifikasi' => null,
                ]);
            }
            
            // ✅ Reset TL ke enum yang valid sesuai TindakLanjutController
            $tl->update([
                'status_verifikasi' => 'menunggu_verifikasi', // ✅ bukan 'menunggu' atau 'berjalan'
                'nilai_tindak_lanjut' => 0,
            ]);
        }
    }

    // ✅ Sinkronkan status_tl di level Temuan
    $temuan->fresh(['recommendations'])->syncStatus();
}

        if ($request->has('attachments')) {
            $this->uploadAttachments($temuan, $request->attachments);
        }

        DB::commit();

        // 🔥 WAJIB: Hitung ulang statistik LHP agar Progress Bar di dashboard berubah
        $this->statistikService->updateStatistik($temuan->lhp_id);

        return redirect()->route('lhps.show', $temuan->lhp_id)
            ->with('success', 'Temuan diperbarui. Progres dihitung ulang berdasarkan nilai baru.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error Update Temuan: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
    }
}

   public function destroy(Temuan $temuan)
{
    $lhpId = $temuan->lhp_id;

    try {
        DB::beginTransaction();

        // 1. Hapus file fisik dan data attachment dari DB
        foreach ($temuan->attachments as $file) {
            // Hapus dari Storage
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
            }
            // Hapus record attachment (jika tidak cascade)
            $file->delete();
        }

        // 2. Hapus data Temuan
        $temuan->delete();

        DB::commit();

        // 3. Update statistik
        $this->statistikService->updateStatistik($lhpId);

        return redirect()->route('lhps.show', $lhpId)
            ->with('success', 'Temuan dan file lampiran berhasil dihapus.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
    }
}

    private function uploadAttachments($temuan, $attachments)
    {
        foreach ($attachments as $index => $attach) {
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
}