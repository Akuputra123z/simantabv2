<?php

namespace App\Http\Controllers;

use App\Models\KodeRekomendasi;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecommendationController extends Controller
{
    public function __construct(private LhpStatistikService $statistikService) {}

    public function index(Request $request)
    {
        $recommendations = Recommendation::query()
    ->select([
        'id', 'temuan_id', 'kode_rekomendasi_id', 'uraian_rekom',
        'jenis_rekomendasi', 'nilai_rekom', 'nilai_sisa', 'status',
        'batas_waktu', 'created_at',
    ])
    ->with([
        'temuan:id,lhp_id,kode_temuan_id,kondisi',
        'temuan.lhp:id,nomor_lhp,tanggal_lhp',
        'temuan.kodeTemuan:id,kode',
        'kodeRekomendasi:id,kode,deskripsi',

        // 🔥 INI YANG PALING PENTING
        'tindakLanjuts:id,recommendation_id,total_terbayar',
    ])
    ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
    ->when($request->filled('jenis'),  fn($q) => $q->where('jenis_rekomendasi', $request->jenis))
    ->when($request->filled('search'), function ($q) use ($request) {
        $q->where('uraian_rekom', 'like', "%{$request->search}%")
          ->orWhereHas('temuan.lhp', fn($sq) =>
              $sq->where('nomor_lhp', 'like', "%{$request->search}%")
          );
    })
    ->latest()
    ->paginate(15)
    ->withQueryString();

        return view('pages.recommendations.index', compact('recommendations'));
    }

   public function create()
{
    // Filter LHP: Hanya ambil LHP yang memiliki setidaknya satu Temuan 
    // yang belum selesai (tidak memiliki rekomendasi berstatus 'selesai')
    $lhps = Lhp::query()
        ->select('id', 'nomor_lhp', 'tanggal_lhp')
        ->whereHas('temuans', function ($query) {
            $query->where(function ($q) {
                // Tampilkan jika temuan belum punya rekomendasi sama sekali
                $q->whereDoesntHave('recommendations')
                  // ATAU punya rekomendasi tapi ada yang belum selesai
                  ->orWhereHas('recommendations', function ($r) {
                      $r->where('status', '!=', Recommendation::STATUS_SELESAI);
                  });
            });
        })
        ->orderByDesc('tanggal_lhp')
        ->get();
$kodeRekoms = KodeRekomendasi::query()
        ->select('id', 'kode', 'deskripsi', 'kode_numerik') // TAMBAHKAN kode_numerik
        ->active()
        ->orderBy('kode')
        ->get();

    return view('pages.recommendations.create', compact('lhps', 'kodeRekoms'));
}

public function getTemuans($lhpId)
{
    $temuans = Temuan::query()
        ->select('id', 'lhp_id', 'kode_temuan_id', 'kondisi', 'nilai_temuan')
        ->with('kodeTemuan:id,kode,deskripsi,alternatif_rekom')
        ->where('lhp_id', $lhpId)
        ->get()
        ->map(function ($t) {
            $kodeTemuan = optional($t->kodeTemuan);

            return [
                'id' => $t->id,
                'kondisi' => \Str::limit($t->kondisi, 150),
                'nilai_temuan' => (float) ($t->nilai_temuan ?? 0),
                'alternatif_rekom' => $kodeTemuan->alternatif_rekom ?? [],
                'kode_label' => $kodeTemuan->kode
                    ? ($kodeTemuan->kode . ($kodeTemuan->deskripsi ? ' — ' . $kodeTemuan->deskripsi : ''))
                    : null,
            ];
        });

    return response()->json($temuans);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'temuan_id'           => 'required|exists:temuans,id',
            'kode_rekomendasi_id' => 'required|exists:kode_rekomendasis,id',
            'uraian_rekom'        => 'required|string',
            'jenis_rekomendasi'   => 'required|in:uang,barang,administrasi',
            'nilai_rekom' => [
    'nullable',
    'numeric',
    'min:0',
    function ($attribute, $value, $fail) use ($request) {
        if ($request->jenis_rekomendasi === 'uang' && empty($value)) {
            $fail('Nilai rekomendasi wajib diisi untuk jenis uang.');
        }
    },
],
            'batas_waktu'         => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $jenis = $validated['jenis_rekomendasi'];
                $nilaiRekom = in_array($jenis, ['barang', 'administrasi'])
                    ? 0  // barang & administrasi tidak pakai nilai uang
                    : (float) ($validated['nilai_rekom'] ?? 0);

                $rekom = Recommendation::create([
                    'temuan_id'           => $validated['temuan_id'],
                    'kode_rekomendasi_id' => $validated['kode_rekomendasi_id'],
                    'uraian_rekom'        => $validated['uraian_rekom'],
                    'jenis_rekomendasi'   => $jenis,
                    'nilai_rekom'         => $nilaiRekom,
                    'nilai_tl_selesai'    => 0,
                    'nilai_sisa'          => $nilaiRekom, // 0 untuk barang/administrasi
                    'batas_waktu'         => $validated['batas_waktu'],
                    'status'              => Recommendation::STATUS_BELUM,
                    'created_by'          => auth()->id(),
                ]);

            DB::commit();

            $lhpId = $rekom->temuan?->lhp_id;
            if ($lhpId) {
                $this->statistikService->updateStatistik($lhpId);
            }

            return redirect()->route('recommendations.index')
                ->with('success', 'Rekomendasi berhasil ditambahkan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan data.');
        }
    }

  public function show($id) // Gunakan $id, bukan Recommendation $recommendation
{
    // Cari data berdasarkan ID, jika tidak ada akan melempar error 404
    $recommendation = Recommendation::with([
        'temuan.lhp',
        'temuan.kodeTemuan',
        'kodeRekomendasi',
        'tindakLanjuts' => function($query) {
            $query->latest();
        }
    ])->findOrFail($id);

    return view('pages.recommendations.show', compact('recommendation'));
}

    public function edit(Recommendation $recommendation)
    {
        $recommendation->load([
            'temuan:id,lhp_id,kondisi',
            'temuan.lhp:id,nomor_lhp',
        ]);

        $kodeRekoms = KodeRekomendasi::query()
            ->select('id', 'kode', 'deskripsi')
            ->active()
            ->orderBy('kode')
            ->get();

        return view('pages.recommendations.edit', compact('recommendation', 'kodeRekoms'));
    }

    public function update(Request $request, Recommendation $recommendation)
    {
        $validated = $request->validate([
            'kode_rekomendasi_id' => 'required|exists:kode_rekomendasis,id',
            'uraian_rekom'        => 'required|string',
            'jenis_rekomendasi'   => 'required|in:uang,barang,administrasi',
            'nilai_rekom'         => 'nullable|numeric|min:0',
            'batas_waktu'         => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $jenis = $validated['jenis_rekomendasi'];
            $nilaiRekom = in_array($jenis, ['barang', 'administrasi'])
                ? 0
                : (float) ($validated['nilai_rekom'] ?? 0);

            $recommendation->update([
                'kode_rekomendasi_id' => $validated['kode_rekomendasi_id'],
                'uraian_rekom'        => $validated['uraian_rekom'],
                'jenis_rekomendasi'   => $jenis,
                'nilai_rekom'         => $nilaiRekom,
                'nilai_sisa'          => $nilaiRekom, // ← TAMBAH INI
                'batas_waktu'         => $validated['batas_waktu'],
                'updated_by'          => auth()->id(),
            ]);

            DB::commit();

            $lhpId = $recommendation->temuan?->lhp_id;
            if ($lhpId) {
                $this->statistikService->updateStatistik($lhpId);
            }

            return redirect()->route('recommendations.show', $recommendation)
                ->with('success', 'Rekomendasi berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function destroy(Recommendation $recommendation)
    {
        $lhpId = $recommendation->temuan?->lhp_id; // ambil sebelum dihapus

        $recommendation->delete();

        if ($lhpId) {
            $this->statistikService->updateStatistik($lhpId);
        }

        return redirect()->route('recommendations.index')
            ->with('success', 'Rekomendasi berhasil dihapus.');
    }
}