<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
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
                'recommendations.id', 'recommendations.temuan_id', 'recommendations.kode_rekomendasi_id',
                'recommendations.uraian_rekom', 'recommendations.jenis_rekomendasi',
                'recommendations.nilai_rekom', 'recommendations.nilai_sisa', 'recommendations.status',
                'recommendations.batas_waktu', 'recommendations.created_at',
                'ap.kategori',
                'audit_program_details.tim as irban',
                'ap.nama_program',
            ])
            ->leftJoin('temuans', 'recommendations.temuan_id', '=', 'temuans.id')
            ->leftJoin('lhps', 'temuans.lhp_id', '=', 'lhps.id')
            ->leftJoin('audit_assignments', 'lhps.audit_assignment_id', '=', 'audit_assignments.id')
            ->leftJoin('audit_program_details', 'audit_assignments.audit_program_detail_id', '=', 'audit_program_details.id')
            ->leftJoin('audit_programs as ap', 'audit_program_details.audit_program_id', '=', 'ap.id')
            ->with([
                'temuan:id,lhp_id,kode_temuan_id,kondisi',
                'temuan.lhp:id,nomor_lhp,tanggal_lhp,unit_diperiksa_id',
                'temuan.lhp.unitDiperiksa',
                'temuan.kodeTemuan:id,kode',
                'kodeRekomendasi:id,kode,deskripsi',
                'tindakLanjuts:id,recommendation_id,total_terbayar',
            ])
            ->when($request->filled('kategori'), fn($q) => $q->where('ap.kategori', $request->kategori))
            ->when($request->filled('status'), fn($q) => $q->where('recommendations.status', $request->status))
            ->when($request->filled('jenis'),  fn($q) => $q->where('recommendations.jenis_rekomendasi', $request->jenis))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('recommendations.uraian_rekom', 'like', "%{$request->search}%")
                          ->orWhereHas('temuan.lhp', fn($sq) =>
                              $sq->where('nomor_lhp', 'like', "%{$request->search}%")
                          );
                });
            })
            ->latest('recommendations.created_at')
            ->paginate(15)
            ->withQueryString();

        $kategoris = AuditProgram::KATEGORI;

        return view('pages.recommendations.index', compact('recommendations', 'kategoris'));
    }

   public function create()
{
        $auditPrograms = AuditProgram::query()
            ->select('id', 'nama_program', 'tahun')
            ->orderBy('tahun', 'desc')
            ->orderBy('nama_program')
            ->get();

        $kodeRekoms = KodeRekomendasi::query()
        ->select('id', 'kode', 'deskripsi', 'kode_numerik')
        ->active()
        ->orderBy('kode')
        ->get();

    return view('pages.recommendations.create', compact('auditPrograms', 'kodeRekoms'));
}

  public function getTemuans($lhpId)
{
    $temuans = Temuan::query()
        ->select('id', 'lhp_id', 'kode_temuan_id', 'kondisi', 'nilai_temuan')
        ->with('kodeTemuan:id,kode,deskripsi,alternatif_rekom')
        ->where('lhp_id', $lhpId)
        // ✅ Semua temuan muncul — user bisa tambah rekom kedua
        // ->whereDoesntHave('recommendations') // ← HAPUS baris ini
        ->get()
        ->map(function ($t) {
            $kodeTemuan = optional($t->kodeTemuan);
            return [
                'id'               => $t->id,
                'kondisi'          => \Str::limit($t->kondisi, 150),
                'nilai_temuan'     => (float) ($t->nilai_temuan ?? 0),
                'alternatif_rekom' => $kodeTemuan->alternatif_rekom ?? [],
                'kode_label'       => $kodeTemuan->kode
                    ? ($kodeTemuan->kode . ($kodeTemuan->deskripsi ? ' — ' . $kodeTemuan->deskripsi : ''))
                    : null,
            ];
        });

    return response()->json($temuans);
}

    public function getLhpsByProgram($programId)
    {
        $lhps = Lhp::query()
            ->select('id', 'nomor_lhp', 'tanggal_lhp')
            ->whereHas('auditAssignment.auditProgramDetail', fn($q) => $q->where('audit_program_id', $programId))
            ->has('temuans')
            ->orderByDesc('tanggal_lhp')
            ->limit(100)
            ->get()
            ->map(fn($lhp) => [
                'id'    => $lhp->id,
                'label' => "{$lhp->nomor_lhp} ({$lhp->tanggal_lhp->format('Y')})",
            ]);

        return response()->json($lhps);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'temuan_id'           => 'required|exists:temuans,id',
            'kode_rekomendasi_id' => 'required|exists:kode_rekomendasis,id',
            'uraian_rekom'        => 'required|string',
            'jenis_rekomendasi'   => 'required|in:uang,barang,administrasi',
            'nilai_rekom'         => [
                'nullable', 'numeric', 'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->jenis_rekomendasi === 'uang' && empty($value)) {
                        $fail('Nilai rekomendasi wajib diisi untuk jenis uang.');
                    }
                },
            ],
            'batas_waktu'         => 'required|date',
        ]);

        $allowedTags = '<p><br><b><strong><i><em><u><strike><s><del><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><span><a><table><thead><tbody><tr><th><td><hr>';
        $validated['uraian_rekom'] = strip_tags($validated['uraian_rekom'], $allowedTags);

        DB::beginTransaction();
        try {
            $jenis = $validated['jenis_rekomendasi'];
            $nilaiRekom = in_array($jenis, ['barang', 'administrasi']) ? 0 : (float) ($validated['nilai_rekom'] ?? 0);

            $rekom = Recommendation::create([
                'temuan_id'           => $validated['temuan_id'],
                'kode_rekomendasi_id' => $validated['kode_rekomendasi_id'],
                'uraian_rekom'        => $validated['uraian_rekom'],
                'jenis_rekomendasi'   => $jenis,
                'nilai_rekom'         => $nilaiRekom,
                'nilai_tl_selesai'    => 0,
                'nilai_sisa'          => $nilaiRekom,
                'batas_waktu'         => $validated['batas_waktu'],
                'status'              => Recommendation::STATUS_BELUM,
                'created_by'          => auth()->id(),
            ]);

            DB::commit();

            if ($rekom->temuan?->lhp_id) {
                $this->statistikService->updateStatistik($rekom->temuan->lhp_id);
            }

            return redirect()->route('recommendations.index')->with('success', 'Rekomendasi berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan data.');
        }
    }

    public function show($id)
    {
        $recommendation = Recommendation::with([
            'temuan.lhp',
            'temuan.kodeTemuan',
            'kodeRekomendasi',
            'tindakLanjuts' => fn($q) => $q->latest()
        ])->findOrFail($id);

        return view('pages.recommendations.show', compact('recommendation'));
    }

    public function edit(Recommendation $recommendation)
    {
        $recommendation->load(['temuan.lhp']);
        $kodeRekoms = KodeRekomendasi::active()->orderBy('kode')->get();

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

    $allowedTags = '<p><br><b><strong><i><em><u><strike><s><del><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><span><a><table><thead><tbody><tr><th><td><hr>';
    $validated['uraian_rekom'] = strip_tags($validated['uraian_rekom'], $allowedTags);

    DB::beginTransaction();
    try {
        $jenis      = $validated['jenis_rekomendasi'];
        $nilaiRekom = in_array($jenis, ['barang', 'administrasi'])
            ? 0
            : (float) ($validated['nilai_rekom'] ?? 0);

        $nilaiRekomLama = (float) $recommendation->nilai_rekom;
        $jenisLama      = $recommendation->jenis_rekomendasi;
        $nilaiBerubah   = abs($nilaiRekomLama - $nilaiRekom) > 0.01 || $jenisLama !== $jenis;

        // Update field dasar dulu — TANPA nilai_sisa dan status
        // karena keduanya akan dihitung ulang oleh syncStatus()
        $recommendation->update([
            'kode_rekomendasi_id' => $validated['kode_rekomendasi_id'],
            'uraian_rekom'        => $validated['uraian_rekom'],
            'jenis_rekomendasi'   => $jenis,
            'nilai_rekom'         => $nilaiRekom,
            'batas_waktu'         => $validated['batas_waktu'],
            'updated_by'          => auth()->id(),
        ]);

        // Jika nilai_rekom atau jenis berubah, reset semua TL terkait
        // agar tidak ada TL dengan nilai yang tidak konsisten
        if ($nilaiBerubah) {
            $recommendation->tindakLanjuts()->each(function ($tl) {
                // Reset status verifikasi TL ke menunggu
                $tl->update([
                    'status_verifikasi'   => 'menunggu_verifikasi',
                    'nilai_tindak_lanjut' => 0,
                ]);

                // Reset cicilan jika ada
                if ($tl->jenis_penyelesaian === 'cicilan') {
                    $tl->cicilans()->update([
                        'status'             => 'menunggu',
                        'diverifikasi_oleh'  => null,
                        'diverifikasi_pada'  => null,
                        'catatan_verifikasi' => null,
                    ]);
                }
            });
        }

        // ✅ WAJIB: syncStatus() menghitung ulang nilai_tl_selesai, nilai_sisa,
        // dan status dari kondisi TindakLanjut aktual, lalu menjalar ke Temuan
        $recommendation->refresh(); // pastikan relasi tidak stale
        $recommendation->load('tindakLanjuts.cicilans');
        $recommendation->syncStatus();

        DB::commit();

        // Setelah syncStatus() selesai, baru trigger kalkulasi LHP
        if ($recommendation->temuan?->lhp_id) {
            $this->statistikService->updateStatistik($recommendation->temuan->lhp_id);
        }

        return redirect()
            ->route('recommendations.show', $recommendation)
            ->with('success', 'Rekomendasi diperbarui dan status disinkronkan.');

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
    }
}
    public function destroy(Recommendation $recommendation)
    {
        $lhpId = $recommendation->temuan?->lhp_id;

        DB::beginTransaction();
        try {
            $recommendation->delete();
            DB::commit();

            if ($lhpId) {
                $this->statistikService->updateStatistik($lhpId);
            }

            return redirect()->route('recommendations.index')->with('success', 'Rekomendasi berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data.');
        }
    }
}