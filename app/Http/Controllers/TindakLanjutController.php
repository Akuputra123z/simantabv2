<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Models\Recommendation;
use App\Models\User;
use App\Models\Attachment;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TindakLanjutController extends Controller
{
    public function __construct(private readonly LhpStatistikService $statistikService) {}

    /**
     * Menampilkan daftar tindak lanjut dengan filter pencarian dan status.
     */
    public function index(Request $request)
    {
        // 1. Query Dasar dengan Scope forUser (Keamanan) & eager loading
        $query = TindakLanjut::query()
            ->forUser(auth()->user()) 
            ->with([
                'recommendation.temuan.lhp',
                'recommendation',
                'creator',
                'attachments',
            ]);

        // 2. Terapkan Filter (Scope yang kita buat di Model)
        $query->filter($request->only(['search', 'status']));

        // 3. Ambil data paginated
        $tindakLanjuts = $query->latest()->paginate(15)->withQueryString();

        // 4. Hitung Statistik (Menyesuaikan dengan filter yang sedang aktif)
        // Gunakan query clone agar tidak merusak query utama pagination
        $statsQuery = TindakLanjut::query()
            ->forUser(auth()->user())
            ->filter($request->only(['search'])); // Filter stats berdasarkan search saja, bukan status select

        $stats = $statsQuery->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) AS total_lunas,
                SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) AS total_berjalan,
                SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' THEN 1 ELSE 0 END) AS total_menunggu
            ")
            ->first();

        return view('pages.tindak-lanjuts.index', compact('tindakLanjuts', 'stats'));
    }

    public function create()
    {
        // Hanya tampilkan rekomendasi yang belum selesai (masih ada sisa)
        $recommendations = Recommendation::with(['temuan.lhp'])
            ->where('status', '!=', Recommendation::STATUS_SELESAI)
            ->latest()
            ->limit(100)
            ->get();

        $users = User::orderBy('name')->limit(100)->get();

        return view('pages.tindak-lanjuts.create', compact('recommendations', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recommendation_id'       => 'required|exists:recommendations,id',
            'jenis_penyelesaian'      => 'required|in:langsung,cicilan',
            'nilai_tindak_lanjut'     => 'nullable|numeric|min:0',
            'jumlah_cicilan_rencana'  => 'nullable|integer|min:1',
            'tanggal_mulai_cicilan'   => 'nullable|date',
            'tanggal_jatuh_tempo'     => 'required|date',
            'status_verifikasi'       => 'required|in:menunggu_verifikasi,berjalan,lunas',
            'diverifikasi_oleh'       => 'nullable|integer|exists:users,id',
            'catatan_tl'              => 'nullable|string|max:1000',
            'hambatan'                => 'nullable|string|max:1000',
            'pengendali_teknis'       => 'nullable|string|max:150',
        ]);

        $rekom    = Recommendation::findOrFail($validated['recommendation_id']);
        $nilaiTl  = (float) ($validated['nilai_tindak_lanjut'] ?? 0);
        $nilaiSisa = (float) ($rekom->nilai_sisa ?? 0);

        if ($rekom->isUang() && $nilaiSisa > 0 && $nilaiTl > $nilaiSisa) {
            return back()
                ->withInput()
                ->withErrors([
                    'nilai_tindak_lanjut' =>
                        'Nilai tindak lanjut (Rp ' . number_format($nilaiTl, 0, ',', '.') . ') ' .
                        'melebihi sisa rekomendasi (Rp ' . number_format($nilaiSisa, 0, ',', '.') . ').',
                ]);
        }

       try {
        DB::beginTransaction();
        $tindakLanjut = TindakLanjut::create($validated);

        // Simpan lampiran
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $i => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('attachments/tindak-lanjut', 'public');
                    $tindakLanjut->attachments()->create([
                        'file_path'   => $path,
                        'file_name'   => $file->getClientOriginalName(),
                        'jenis_bukti' => 'tindak_lanjut',
                        'urutan'      => $i,
                    ]);
                }
            }
        }

        DB::commit();

        // ✅ Sync status Recommendation DULU sebelum hitung statistik LHP
        $this->syncRekomendasi($tindakLanjut);
        $this->updateStatistik($tindakLanjut);

        return redirect()
            ->route('tindak-lanjuts.index')
            ->with('success', 'Tindak lanjut berhasil disimpan.');

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('TindakLanjut store error: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal menyimpan data.');
    }
    }

    public function show(TindakLanjut $tindakLanjut)
    {
        $tindakLanjut->load([
            'recommendation.temuan.lhp',
            'recommendation.temuan.kodeTemuan',
            'verifikator',
            'creator',
            'cicilans',
            'attachments',
        ]);

        return view('pages.tindak-lanjuts.show', compact('tindakLanjut'));
    }

    public function edit(TindakLanjut $tindakLanjut)
    {
        $tindakLanjut->load(['recommendation.temuan.lhp', 'attachments']);

        $recommendations = Recommendation::with(['temuan.lhp'])
            ->where(function ($q) use ($tindakLanjut) {
                $q->where('status', '!=', Recommendation::STATUS_SELESAI)
                  ->orWhere('id', $tindakLanjut->recommendation_id);
            })
            ->latest()
            ->get();

        $users = User::orderBy('name')->get();

        return view('pages.tindak-lanjuts.edit', compact('tindakLanjut', 'recommendations', 'users'));
    }

    public function update(Request $request, TindakLanjut $tindakLanjut)
    {
        $validated = $request->validate([
            'recommendation_id'       => 'required|exists:recommendations,id',
            'jenis_penyelesaian'      => 'required|in:langsung,cicilan',
            'nilai_tindak_lanjut'     => 'nullable|numeric|min:0',
            'jumlah_cicilan_rencana'  => 'nullable|integer|min:1',
            'tanggal_mulai_cicilan'   => 'nullable|date',
            'tanggal_jatuh_tempo'     => 'required|date',
            'status_verifikasi'       => 'required|in:menunggu_verifikasi,berjalan,lunas',
            'diverifikasi_oleh'       => 'nullable|integer|exists:users,id',
            'catatan_tl'              => 'nullable|string|max:1000',
            'hambatan'                => 'nullable|string|max:1000',
            'pengendali_teknis'       => 'nullable|string|max:150',
        ]);

        $rekom          = Recommendation::findOrFail($validated['recommendation_id']);
        $nilaiTlBaru    = (float) ($validated['nilai_tindak_lanjut'] ?? 0);
        $nilaiTlLama    = (float) ($tindakLanjut->nilai_tindak_lanjut ?? 0);

        if ($rekom->isUang()) {
            $sisaAvailable = (float) ($rekom->nilai_sisa ?? 0) + $nilaiTlLama;

            if ($sisaAvailable > 0 && $nilaiTlBaru > $sisaAvailable) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nilai_tindak_lanjut' =>
                            'Nilai tindak lanjut melebihi nilai tersedia (Rp ' . number_format($sisaAvailable, 0, ',', '.') . ').',
                    ]);
            }
        }

        try {
            DB::beginTransaction();

            $tindakLanjut->update($validated);

            // Hapus lampiran yang dicentang
            if ($request->has('delete_attachments')) {
                $deletes = Attachment::whereIn('id', (array) $request->delete_attachments)
                    ->where('attachable_id', $tindakLanjut->id)
                    ->where('attachable_type', get_class($tindakLanjut))
                    ->get();
                foreach ($deletes as $att) {
                    Storage::disk('public')->delete($att->file_path);
                    $att->delete();
                }
            }

            // Upload lampiran baru
            if ($request->hasFile('new_attachments')) {
                $existingCount = $tindakLanjut->attachments()->count();
                foreach ($request->file('new_attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/tindak-lanjut', 'public');
                        $tindakLanjut->attachments()->create([
                            'file_path'   => $path,
                            'file_name'   => $file->getClientOriginalName(),
                            'jenis_bukti' => 'tindak_lanjut',
                            'urutan'      => $existingCount + $i,
                        ]);
                    }
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('TindakLanjut update error: ' . $e->getMessage());

            $errMsg = app()->isLocal()
                ? 'Gagal memperbarui data. Error: ' . $e->getMessage()
                : 'Gagal memperbarui data.';

            return back()->withInput()->with('error', $errMsg);
        }

        // Sync parent status outside the atomic transaction so a failure here
        // doesn't lose the user's data — log & continue silently.
        try {
            $this->syncRekomendasi($tindakLanjut);
            $this->updateStatistik($tindakLanjut);
        } catch (\Throwable $e) {
            Log::error('TindakLanjut sync error after update: ' . $e->getMessage());
        }

        return redirect()
            ->route('tindak-lanjuts.index')
            ->with('success', 'Tindak lanjut diperbarui.');
    }

    public function destroy(TindakLanjut $tindakLanjut)
{
    $lhpId = $tindakLanjut->recommendation?->temuan?->lhp_id;
    
    // Hapus file lampiran dari storage
    foreach ($tindakLanjut->attachments as $att) {
        Storage::disk('public')->delete($att->file_path);
        $att->delete();
    }
    
    // Simpan recommendation sebelum TL dihapus untuk sync setelahnya
    $recommendation = $tindakLanjut->recommendation;
    
    $tindakLanjut->delete();

    // ✅ Sync recommendation setelah TL dihapus
    if ($recommendation) {
        $recommendation->refresh();
        $recommendation->load('tindakLanjuts.cicilans');
        $recommendation->syncStatus();
    }

    if ($lhpId) {
        $this->statistikService->updateStatistik($lhpId);
    }

    return redirect()
        ->route('tindak-lanjuts.index')
        ->with('success', 'Tindak lanjut dihapus.');
}

private function syncRekomendasi(TindakLanjut $tl): void
{
    $recommendation = $tl->recommendation;
    if (! $recommendation) {
        // Load jika belum ter-load
        $tl->load('recommendation.tindakLanjuts.cicilans');
        $recommendation = $tl->recommendation;
    }

    if (! $recommendation) return;

    // Refresh dari DB agar tidak baca cache lama, lalu load semua TL terkait
    $recommendation->refresh();
    $recommendation->load('tindakLanjuts.cicilans');
    
    // syncStatus() akan update: status, nilai_tl_selesai, nilai_sisa
    // lalu memanggil temuan->syncStatus() secara otomatis
    $recommendation->syncStatus();
}

private function updateStatistik(TindakLanjut $tl): void
{
    $lhpId = $tl->recommendation?->temuan?->lhp_id;
    if ($lhpId) {
        $this->statistikService->updateStatistik($lhpId);
    }
}
}