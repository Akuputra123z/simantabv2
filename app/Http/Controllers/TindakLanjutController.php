<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\User;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TindakLanjutController extends Controller
{
    public function __construct(private LhpStatistikService $statistikService) {}

    public function index(Request $request)
    {
        $tindakLanjuts = TindakLanjut::forUser(auth()->user())
            ->with([
                'recommendation:id,temuan_id,uraian_rekom,nilai_rekom,status,jenis_rekomendasi',
                'recommendation.temuan:id,lhp_id,kondisi',
                'recommendation.temuan.lhp:id,nomor_lhp',
                'verifikator:id,name',
            ])
            ->when($request->filled('status'), fn($q) => $q->where('status_verifikasi', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Hitung stats dari DB langsung — bukan dari paginated collection
        $stats = TindakLanjut::forUser(auth()->user())
            ->selectRaw("
                COUNT(*) as total,
                SUM(status_verifikasi = 'lunas') as total_lunas,
                SUM(status_verifikasi = 'berjalan') as total_berjalan,
                SUM(status_verifikasi = 'menunggu_verifikasi') as total_menunggu
            ")
            ->first();

        return view('pages.tindak-lanjuts.index', compact('tindakLanjuts', 'stats'));
    }

    public function create()
    {
        $recommendations = Recommendation::query()
            ->select('id', 'temuan_id', 'uraian_rekom', 'nilai_rekom', 'status', 'jenis_rekomendasi')
            ->where('status', '!=', Recommendation::STATUS_SELESAI)
            ->with('temuan.lhp:id,nomor_lhp')
            ->latest()
            ->get();

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('pages.tindak-lanjuts.create', compact('recommendations', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recommendation_id'      => 'required|exists:recommendations,id',
            'jenis_penyelesaian'     => 'required|in:langsung,cicilan',
            'nilai_tindak_lanjut'    => 'nullable|numeric|min:0',
            'status_verifikasi'      => 'required|in:menunggu_verifikasi,berjalan,lunas',
            'tanggal_jatuh_tempo'    => 'required|date',
            'jumlah_cicilan_rencana' => 'nullable|integer|min:1',
            'tanggal_mulai_cicilan'  => 'nullable|date',
            'catatan_tl'             => 'nullable|string',
            'diverifikasi_oleh'      => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Model booted()::saving otomatis panggil syncCalculations()
            // yang sudah aware terhadap jenis_rekomendasi (uang vs non-uang)
            $tindak = TindakLanjut::create([
                'recommendation_id'      => $validated['recommendation_id'],
                'jenis_penyelesaian'     => $validated['jenis_penyelesaian'],
                'nilai_tindak_lanjut'    => (float) ($validated['nilai_tindak_lanjut'] ?? 0),
                'status_verifikasi'      => $validated['status_verifikasi'],
                'tanggal_jatuh_tempo'    => $validated['tanggal_jatuh_tempo'],
                'jumlah_cicilan_rencana' => $validated['jumlah_cicilan_rencana'] ?? null,
                'tanggal_mulai_cicilan'  => $validated['tanggal_mulai_cicilan'] ?? null,
                'catatan_tl'             => $validated['catatan_tl'] ?? null,
                'diverifikasi_oleh'      => $validated['diverifikasi_oleh'] ?? null,
                'created_by'             => auth()->id(),
            ]);

            DB::commit();

            $lhpId = $tindak->recommendation?->temuan?->lhp_id;
            if ($lhpId) {
                $this->statistikService->updateStatistik($lhpId);
            }

            return redirect()->route('tindak-lanjuts.index')
                ->with('success', 'Tindak lanjut berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

   public function show(TindakLanjut $tindakLanjut)
{
    $tindakLanjut->load([
        'recommendation.temuan.lhp',
        'recommendation.kodeRekomendasi',
        'cicilans' => fn($q) => $q->orderBy('ke'), // Memuat riwayat cicilan
        'verifikator:id,name',
        'creator:id,name',
    ]);

    return view('pages.tindak-lanjuts.show', compact('tindakLanjut'));
}

    public function edit(TindakLanjut $tindakLanjut)
    {
        $tindakLanjut->load([
            'recommendation.temuan.lhp',
            'recommendation.kodeRekomendasi',
        ]);

        $rekoms = Recommendation::where(function ($q) use ($tindakLanjut) {
                $q->where('status', '!=', Recommendation::STATUS_SELESAI)
                  ->orWhere('id', $tindakLanjut->recommendation_id);
            })
            ->get(['id', 'uraian_rekom', 'nilai_rekom', 'jenis_rekomendasi']);

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('pages.tindak-lanjuts.edit', compact('tindakLanjut', 'rekoms', 'users'));
    }

    public function update(Request $request, TindakLanjut $tindakLanjut)
    {
        $validated = $request->validate([
            'recommendation_id'      => 'required|exists:recommendations,id',
            'jenis_penyelesaian'     => 'required|in:langsung,cicilan',
            'nilai_tindak_lanjut'    => 'nullable|numeric|min:0',
            'status_verifikasi'      => 'required|in:menunggu_verifikasi,berjalan,lunas',
            'jumlah_cicilan_rencana' => 'nullable|integer|min:1',
            'tanggal_mulai_cicilan'  => 'nullable|date',
            'tanggal_jatuh_tempo'    => 'required|date',
            'catatan_tl'             => 'nullable|string',
            'hambatan'               => 'nullable|string',
            'diverifikasi_oleh'      => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $tindakLanjut->update([
                'recommendation_id'      => $validated['recommendation_id'],
                'jenis_penyelesaian'     => $validated['jenis_penyelesaian'],
                'nilai_tindak_lanjut'    => (float) ($validated['nilai_tindak_lanjut'] ?? 0),
                'status_verifikasi'      => $validated['status_verifikasi'],
                'jumlah_cicilan_rencana' => $validated['jumlah_cicilan_rencana'] ?? null,
                'tanggal_mulai_cicilan'  => $validated['tanggal_mulai_cicilan'] ?? null,
                'tanggal_jatuh_tempo'    => $validated['tanggal_jatuh_tempo'],
                'catatan_tl'             => $validated['catatan_tl'] ?? null,
                'hambatan'               => $validated['hambatan'] ?? null,
                'diverifikasi_oleh'      => $validated['diverifikasi_oleh'] ?? null,
                'updated_by'             => auth()->id(),
            ]);

            DB::commit();

            $lhpId = $tindakLanjut->recommendation?->temuan?->lhp_id;
            if ($lhpId) {
                $this->statistikService->updateStatistik($lhpId);
            }

            return redirect()->route('tindak-lanjuts.index')
                ->with('success', 'Tindak lanjut berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(TindakLanjut $tindakLanjut)
    {
        $lhpId = $tindakLanjut->recommendation?->temuan?->lhp_id;

        DB::transaction(fn() => $tindakLanjut->delete());

        if ($lhpId) {
            $this->statistikService->updateStatistik($lhpId);
        }

        return redirect()->route('tindak-lanjuts.index')
            ->with('success', 'Tindak lanjut berhasil dihapus.');
    }
}