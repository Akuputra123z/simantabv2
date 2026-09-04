<?php

    namespace App\Http\Controllers;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\KodeRekomendasi;
use App\Models\KodeTemuan;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

    class LhpController extends Controller
    {
        public function __construct(private LhpStatistikService $statistikService) {}

        public function index(Request $request)
{
    $query = Lhp::with([
        'auditAssignment.auditProgramDetail.auditProgram', // ✅ FIX: rantai lengkap
        'statistik',
        'creator',
        'unitDiperiksa',
    ])
    ->forUser(auth()->user());

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('nomor_lhp', 'like', "%{$search}%")
              ->orWhereHas(
                  'auditAssignment.auditProgramDetail.auditProgram',
                  fn($sq) => $sq->where('nama_program', 'like', "%{$search}%")
              );
        });
    }

    if ($request->filled('tahun')) {
        $query->whereYear('tanggal_lhp', $request->tahun);
    }

    if ($request->filled('kategori')) {
        $query->whereHas('auditAssignment.auditProgramDetail.auditProgram', fn($q) =>
            $q->where('kategori', $request->kategori)
        );
    }

    $lhps = $query->latest()->paginate(10)->withQueryString();

    $kategoris = AuditProgram::KATEGORI;

    return view('pages.lhps.index', compact('lhps', 'kategoris'));
}

        public function create()
{
    $user = auth()->user();

    $assignments = AuditAssignment::query()
        ->with([
            'auditProgramDetail.auditProgram',  // <-- PENTING: auditProgram (bukan parentProgram)
            'unitDiperiksas',
        ])
        ->when(! $user->hasRole('super_admin'), function ($q) use ($user) {
            $q->where('ketua_tim_id', $user->id)
              ->orWhereHas('members', fn($q2) => $q2->where('user_id', $user->id));
        })
        ->latest()
        ->limit(100)
        ->get();

    // Map (assignment_id → [unit_diperiksa_id]) yang sudah punya LHP non-dibatalkan
    $usedUnitMap = Lhp::whereIn('audit_assignment_id', $assignments->pluck('id'))
        ->where('status', '!=', 'dibatalkan')
        ->select('audit_assignment_id', 'unit_diperiksa_id')
        ->get()
        ->groupBy('audit_assignment_id')
        ->map(fn($items) => $items->pluck('unit_diperiksa_id')->toArray());

    $kodeTemuans = KodeTemuan::orderBy('kode')->get();
    $kodeRekoms  = KodeRekomendasi::where('is_active', true)->orderBy('kode')->get();

    return view('pages.lhps.create', compact('assignments', 'kodeTemuans', 'kodeRekoms', 'usedUnitMap'));
}
        // Contoh di Controller yang menghandle API /lhp/{id}/temuans
public function getTemuans($lhpId) {
    return Temuan::with('kodeTemuan') // Pastikan relasi ini dipanggil
        ->where('lhp_id', $lhpId)
        ->get()
        ->map(function($t) {
            return [
                'id' => $t->id,
                'kondisi' => $t->kondisi,
                'nilai_temuan' => $t->nilai_temuan,
                // Ambil array dari relasi kodeTemuan
                'alternatif_rekom' => $t->kodeTemuan ? $t->kodeTemuan->alternatif_rekom : []
            ];
        });
}

    public function store(Request $request)
{
    // Helper untuk membersihkan titik ribuan dari input rupiah
    $cleanRupiah = function($value) {
        if (empty($value)) return 0;
        // Hapus semua karakter non-digit (seperti titik)
        return (float) preg_replace('/[^0-9]/', '', $value);
    };

    $validated = $request->validate([
        'audit_assignment_id'             => 'required|exists:audit_assignments,id',
        'unit_diperiksa_id'               => 'required|exists:unit_diperiksas,id',
        'nomor_lhp'                       => 'required|string|unique:lhps,nomor_lhp',
        'tanggal_lhp'                     => 'required|date',
        'catatan_umum'                    => 'nullable|string',
        'temuans'                         => 'nullable|array',
        'temuans.*.kode_temuan_id'        => 'nullable|exists:kode_temuans,id',
        'temuans.*.kondisi'               => 'nullable|string',
        'temuans.*.sebab'                 => 'nullable|string',
        'temuans.*.akibat'                => 'nullable|string',
        'temuans.*.nilai_kerugian_negara' => 'nullable', 
        'temuans.*.nilai_kerugian_daerah' => 'nullable',
        'temuans.*.nilai_kerugian_desa'   => 'nullable',
        'temuans.*.nilai_kerugian_bos_blud' => 'nullable',
        'temuans.*.recommendations'                        => 'nullable|array',
        'temuans.*.recommendations.*.kode_rekomendasi_id' => 'nullable|exists:kode_rekomendasis,id',
        'temuans.*.recommendations.*.uraian_rekom'        => 'nullable|string',
        'temuans.*.recommendations.*.jenis_rekomendasi'   => 'nullable|in:uang,barang,administrasi',
        'temuans.*.recommendations.*.nilai_rekom'         => 'nullable',
        'temuans.*.recommendations.*.batas_waktu'         => 'nullable|date',
        'attachments'                     => 'nullable|array',
        'attachments.*.file_path'         => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
        'attachments.*.file_name'         => 'nullable|string',
    ]);

    try {
        DB::beginTransaction();

        $lhp = Lhp::create([
            'audit_assignment_id' => $validated['audit_assignment_id'],
            'unit_diperiksa_id'   => $validated['unit_diperiksa_id'],
            'nomor_lhp'           => $validated['nomor_lhp'],
            'tanggal_lhp'         => $validated['tanggal_lhp'],
            'catatan_umum'        => $validated['catatan_umum'] ?? null,
            'status'              => 'draft',
            'created_by'          => auth()->id(),
        ]);

        if (! empty($request->temuans)) {
            foreach ($request->temuans as $temuan) {
                // Skip jika baris temuan kosong
                if (empty($temuan['kode_temuan_id']) && empty($temuan['kondisi'])) continue;

                // Bersihkan format Rupiah (titik) sebelum disimpan
                $negara  = $cleanRupiah($temuan['nilai_kerugian_negara'] ?? 0);
                $daerah  = $cleanRupiah($temuan['nilai_kerugian_daerah'] ?? 0);
                $desa    = $cleanRupiah($temuan['nilai_kerugian_desa'] ?? 0); 
                $bosBLud = $cleanRupiah($temuan['nilai_kerugian_bos_blud'] ?? 0); 
                $total   = $negara + $daerah + $desa + $bosBLud;

                $createdTemuan = $lhp->temuans()->create([
                    'kode_temuan_id'        => $temuan['kode_temuan_id'] ?? null,
                    'kondisi'               => $temuan['kondisi'] ?? null,
                    'sebab'                 => $temuan['sebab'] ?? null,
                    'akibat'                => $temuan['akibat'] ?? null,
                    'nilai_kerugian_negara' => $negara,
                    'nilai_kerugian_daerah' => $daerah,
                    'nilai_kerugian_desa'   => $desa,
                    'nilai_kerugian_bos_blud' => $bosBLud,
                    'nilai_temuan'          => $total,
                    'status_tl'             => 'belum_ditindaklanjuti',
                ]);

                // Simpan rekomendasi jika diisi
                if (! empty($temuan['recommendations']) && is_array($temuan['recommendations'])) {
                    foreach ($temuan['recommendations'] as $rekom) {
                        if (empty($rekom['kode_rekomendasi_id']) && empty($rekom['uraian_rekom'])) continue;

                        $nilaiRekom = $cleanRupiah($rekom['nilai_rekom'] ?? 0);
                        $createdTemuan->recommendations()->create([
                            'lhp_id'              => $lhp->id,
                            'kode_rekomendasi_id' => $rekom['kode_rekomendasi_id'] ?? null,
                            'uraian_rekom'        => $rekom['uraian_rekom'] ?? null,
                            'jenis_rekomendasi'   => $rekom['jenis_rekomendasi'] ?? 'administrasi',
                            'nilai_rekom'         => $nilaiRekom,
                            'nilai_sisa'          => $nilaiRekom,
                            'batas_waktu'         => !empty($rekom['batas_waktu']) ? $rekom['batas_waktu'] : now()->addDays(60)->toDateString(),
                            'status'              => 'belum_ditindaklanjuti',
                            'created_by'          => auth()->id(),
                        ]);
                    }
                }
            }
        }

        if (! empty($request->attachments)) {
            foreach ($request->attachments as $item) {
                if (isset($item['file_path']) && $item['file_path'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = $item['file_path']->store('lhp/attachments', 'public');
                    $lhp->attachments()->create([
                        'file_path'   => $path,
                        'file_name'   => $item['file_name'] ?? $item['file_path']->getClientOriginalName(),
                        'jenis_bukti' => 'lhp',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        DB::commit();

        // Update statistik LHP
        $this->statistikService->updateStatistik($lhp->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => "LHP nomor {$lhp->nomor_lhp} beserta temuan dan rekomendasi berhasil dibuat.",
                'redirect' => route('lhps.index'),
            ]);
        }

        return redirect()->route('lhps.index')
            ->with('success', "LHP nomor {$lhp->nomor_lhp} beserta temuan dan rekomendasi berhasil dibuat.");

    } catch (\Throwable $e) {
        DB::rollBack();
        // Log error jika diperlukan: \Log::error($e->getMessage());
        \Log::error('Gagal menyimpan LHP: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal menyimpan LHP. Silakan coba lagi.');
    }
}
       public function show(Lhp $lhp)
{
     $lhp->load([
        'temuans.kodeTemuan',
        'temuans.recommendations.kodeRekomendasi',  // ✅ load kodeRekomendasi untuk display di drawer
        'temuans.recommendations.tindakLanjuts',
        // ✅ Rantai relasi yang benar dan konsisten
        'auditAssignment.auditProgramDetail.auditProgram',
        'auditAssignment.unitDiperiksas', // tetap load untuk konteks jika perlu
        'unitDiperiksa',                  // ✅ unit spesifik yang dipilih saat buat LHP
        'attachments',
        'statistik',
        'creator',
    ]);

    return view('pages.lhps.show', compact('lhp'));
}

        public function edit(Lhp $lhp)
        {
            $user = auth()->user();

            $assignments = AuditAssignment::query()
                ->when(! $user->hasRole('super_admin'), function ($q) use ($user) {
                    $q->where('ketua_tim_id', $user->id)
                        ->orWhereHas('members', fn ($q2) => $q2->where('user_id', $user->id));
                })->get();

            $kodeTemuans = KodeTemuan::orderBy('kode')->get();
            $kodeRekoms  = KodeRekomendasi::where('is_active', true)->orderBy('kode')->get();

            $lhp->load([
                'temuans.kodeTemuan',
                'temuans.recommendations.kodeRekomendasi',
                'auditAssignment.auditProgramDetail.auditProgram',
                'attachments',
            ]);

            return view('pages.lhps.edit', compact('lhp', 'assignments', 'kodeTemuans', 'kodeRekoms'));
        }

public function update(Request $request, Lhp $lhp)
{
    $cleanRupiah = function($value) {
        if (empty($value)) return 0;
        return (float) preg_replace('/[^0-9]/', '', $value);
    };

    $validated = $request->validate([
        'nomor_lhp'                         => 'required|string|unique:lhps,nomor_lhp,' . $lhp->id,
        'tanggal_lhp'                       => 'required|date',
        'catatan_umum'                      => 'nullable|string',
        'temuans'                           => 'nullable|array',
        'temuans.*.id'                      => 'nullable',
        'temuans.*.kode_temuan_id'          => 'nullable|exists:kode_temuans,id',
        'temuans.*.kondisi'                 => 'nullable|string',
        'temuans.*.sebab'                   => 'nullable|string',
        'temuans.*.akibat'                  => 'nullable|string',
        'temuans.*.nilai_kerugian_negara'   => 'nullable',
        'temuans.*.nilai_kerugian_daerah'   => 'nullable',
        'temuans.*.nilai_kerugian_desa'     => 'nullable',
        'temuans.*.nilai_kerugian_bos_blud' => 'nullable',
        'temuans.*.recommendations'                         => 'nullable|array',
        'temuans.*.recommendations.*.id'                  => 'nullable',
        'temuans.*.recommendations.*.kode_rekomendasi_id' => 'nullable|exists:kode_rekomendasis,id',
        'temuans.*.recommendations.*.uraian_rekom'        => 'nullable|string',
        'temuans.*.recommendations.*.jenis_rekomendasi'   => 'nullable|in:uang,barang,administrasi',
        'temuans.*.recommendations.*.nilai_rekom'         => 'nullable',
        'temuans.*.recommendations.*.batas_waktu'         => 'nullable|date',
        'attachments'                       => 'nullable|array',
        'attachments.*.file_path'           => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
        'attachments.*.file_name'           => 'nullable|string',
    ]);

    try {
        DB::beginTransaction();

        $lhp->update(collect($validated)->except(['temuans', 'attachments'])->toArray());

        if ($request->has('temuans')) {
            $existingIds = collect($request->temuans)->pluck('id')->filter()->toArray();

            $lhp->temuans()->whereNotIn('id', $existingIds)->each(function ($oldTemuan) {
                $oldTemuan->recommendations()->each(function ($rekom) {
                    $rekom->tindakLanjuts()->delete();
                });
                $oldTemuan->delete();
            });

            foreach ($request->temuans as $temuan) {
                $negara   = $cleanRupiah($temuan['nilai_kerugian_negara']   ?? 0);
                $daerah   = $cleanRupiah($temuan['nilai_kerugian_daerah']   ?? 0);
                $desa     = $cleanRupiah($temuan['nilai_kerugian_desa']     ?? 0);
                $bosBLud  = $cleanRupiah($temuan['nilai_kerugian_bos_blud'] ?? 0);
                $totalNilaiBaru = $negara + $daerah + $desa + $bosBLud;

                $targetTemuan = null;

                if (!empty($temuan['id'])) {
                    $existing = $lhp->temuans()->find($temuan['id']);
                    if ($existing) {
                        $existing->update([
                            'kode_temuan_id'          => $temuan['kode_temuan_id'] ?? null,
                            'kondisi'                 => $temuan['kondisi'] ?? null,
                            'sebab'                   => $temuan['sebab'] ?? null,
                            'akibat'                  => $temuan['akibat'] ?? null,
                            'nilai_kerugian_negara'   => $negara,
                            'nilai_kerugian_daerah'   => $daerah,
                            'nilai_kerugian_desa'     => $desa,
                            'nilai_kerugian_bos_blud' => $bosBLud,
                            'nilai_temuan'            => $totalNilaiBaru,
                        ]);
                        $targetTemuan = $existing;
                    }
                } else {
                    if (empty($temuan['kode_temuan_id']) && empty($temuan['kondisi'])) continue;

                    $targetTemuan = $lhp->temuans()->create([
                        'kode_temuan_id'          => $temuan['kode_temuan_id'] ?? null,
                        'kondisi'                 => $temuan['kondisi'] ?? null,
                        'sebab'                   => $temuan['sebab'] ?? null,
                        'akibat'                  => $temuan['akibat'] ?? null,
                        'nilai_kerugian_negara'   => $negara,
                        'nilai_kerugian_daerah'   => $daerah,
                        'nilai_kerugian_desa'     => $desa,
                        'nilai_kerugian_bos_blud' => $bosBLud,
                        'nilai_temuan'            => $totalNilaiBaru,
                        'status_tl'               => 'belum_ditindaklanjuti',
                    ]);
                }

                // Handle nested recommendations update/create
                if (! empty($temuan['recommendations']) && is_array($temuan['recommendations']) && $targetTemuan) {
                    foreach ($temuan['recommendations'] as $rekom) {
                        if (empty($rekom['kode_rekomendasi_id']) && empty($rekom['uraian_rekom'])) continue;

                        $nilaiRekom = $cleanRupiah($rekom['nilai_rekom'] ?? 0);
                        if (!empty($rekom['id'])) {
                            $existingRekom = $targetTemuan->recommendations()->find($rekom['id']);
                            if ($existingRekom) {
                                $existingRekom->update([
                                    'kode_rekomendasi_id' => $rekom['kode_rekomendasi_id'] ?? null,
                                    'uraian_rekom'        => $rekom['uraian_rekom'] ?? null,
                                    'jenis_rekomendasi'   => $rekom['jenis_rekomendasi'] ?? 'administrasi',
                                    'nilai_rekom'         => $nilaiRekom,
                                    'nilai_sisa'          => max(0, $nilaiRekom - (float)($existingRekom->nilai_tl_selesai ?? 0)),
                                    'batas_waktu'         => !empty($rekom['batas_waktu']) ? $rekom['batas_waktu'] : $existingRekom->batas_waktu,
                                ]);
                            }
                        } else {
                            $targetTemuan->recommendations()->create([
                                'lhp_id'              => $lhp->id,
                                'kode_rekomendasi_id' => $rekom['kode_rekomendasi_id'] ?? null,
                                'uraian_rekom'        => $rekom['uraian_rekom'] ?? null,
                                'jenis_rekomendasi'   => $rekom['jenis_rekomendasi'] ?? 'administrasi',
                                'nilai_rekom'         => $nilaiRekom,
                                'nilai_sisa'          => $nilaiRekom,
                                'batas_waktu'         => !empty($rekom['batas_waktu']) ? $rekom['batas_waktu'] : now()->addDays(60)->toDateString(),
                                'status'              => 'belum_ditindaklanjuti',
                                'created_by'          => auth()->id(),
                            ]);
                        }
                    }
                }
            }
        }

        // ✅ BLOK INI YANG HILANG — SIMPAN ATTACHMENT BARU
        if ($request->has('attachments')) {
            foreach ($request->attachments as $item) {
                if (
                    isset($item['file_path']) &&
                    $item['file_path'] instanceof \Illuminate\Http\UploadedFile
                ) {
                    $path = $item['file_path']->store('lhp/attachments', 'public');
                    $lhp->attachments()->create([
                        'file_path'   => $path,
                        'file_name'   => $item['file_name'] ?? $item['file_path']->getClientOriginalName(),
                        'jenis_bukti' => 'lhp',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        DB::commit();

        $this->statistikService->updateStatistik($lhp->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'LHP berhasil diperbarui dan statistik disinkronkan.',
                'redirect' => route('lhps.index'),
            ]);
        }

        return redirect()->route('lhps.index')
            ->with('success', 'LHP berhasil diperbarui dan statistik disinkronkan.');

    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Gagal update LHP: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal memperbarui LHP. Silakan coba lagi.');
    }
}
        public function destroy(Lhp $lhp)
        {
            foreach ($lhp->attachments as $file) {
                Storage::disk('public')->delete($file->file_path);
            }
            $lhp->delete();

            return redirect()->route('lhps.index')
                ->with('success', 'LHP berhasil dihapus.');
        }

        public function bulkDelete(Request $request)
        {
            if (! $request->ids) {
                return back()->with('error', 'Pilih data dulu.');
            }

            Lhp::whereIn('id', $request->ids)->with('attachments')->chunk(50, function ($lhps) {
                foreach ($lhps as $lhp) {
                    foreach ($lhp->attachments as $file) {
                        Storage::disk('public')->delete($file->file_path);
                    }
                    $lhp->delete();
                }
            });

            return redirect()->route('lhps.index')
                ->with('success', count($request->ids) . ' data LHP berhasil dihapus.');
        }

        /**
         * Refresh statistik via POST — lebih aman daripada GET.
         * Pastikan route: Route::post('/lhps/{lhp}/refresh', ...)
         */
        public function refresh(Lhp $lhp)
        {
            $this->statistikService->updateStatistik($lhp->id);

            return back()->with('success', 'Statistik berhasil diperbarui.');
        }


public function tracking(Request $request)
{
    $search = trim($request->input('nomor_lhp'));
    $lhp = null;

    if ($search) {
        $lhp = Lhp::with([
                'unitDiperiksa',
                'auditAssignment.unitDiperiksas',
                'auditAssignment.auditProgram',
                'auditAssignment.auditProgramDetail',
                'temuans.kodeTemuan',
                'temuans.recommendations.tindakLanjuts',
                'statistik',
                'creator',
            ])
            ->withCount('temuans')
            ->where('nomor_lhp', $search)
            ->first();

        if (!$lhp) {
            return redirect()->route('tracking.public')
                ->with('error', 'Nomor LHP tidak ditemukan dalam sistem kami.')
                ->withInput();
        }
    }

    return view('pages.tracking', compact('lhp', 'search'));
}
    }