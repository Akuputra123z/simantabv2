<?php

    namespace App\Http\Controllers;

use App\Models\AuditAssignment;
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
            $query = Lhp::with(['auditAssignment', 'statistik', 'creator'])
                ->forUser(auth()->user());

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_lhp', 'like', "%{$search}%")
                        ->orWhereHas('auditAssignment.auditProgram', function ($sq) use ($search) {
                            $sq->where('nama_program', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_lhp', $request->tahun);
            }

            $lhps = $query->latest()->paginate(10)->withQueryString();

            return view('pages.lhps.index', compact('lhps'));
        }

        public function create()
        {
            $user = auth()->user();

            $assignments = AuditAssignment::query()
                ->with(['auditProgram', 'unitDiperiksa'])
                ->when(! $user->hasRole('super_admin'), function ($q) use ($user) {
                    $q->where('ketua_tim_id', $user->id)
                        ->orWhereHas('members', fn ($q2) => $q2->where('user_id', $user->id));
                })
                ->latest()
                ->get();

            $kodeTemuans = KodeTemuan::orderBy('kode')->get();

            return view('pages.lhps.create', compact('assignments', 'kodeTemuans'));
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
            $validated = $request->validate([
                'audit_assignment_id'             => 'required|exists:audit_assignments,id',
                'nomor_lhp'                       => 'required|string|unique:lhps,nomor_lhp',
                'tanggal_lhp'                     => 'required|date',
                'semester'                        => 'required|in:1,2',
                'irban'                           => 'required|string',
                'jenis_pemeriksaan'               => 'nullable|string',
                'catatan_umum'                    => 'nullable|string',
                'temuans'                         => 'nullable|array',
                'temuans.*.kode_temuan_id'        => 'nullable|exists:kode_temuans,id',
                'temuans.*.kondisi'               => 'nullable|string',
                // Gunakan nullable agar string kosong tidak memicu error numeric
                'temuans.*.nilai_kerugian_negara' => 'nullable', 
                'temuans.*.nilai_kerugian_daerah' => 'nullable',
                'temuans.*.nilai_kerugian_desa'   => 'nullable',
                'temuans.*.nilai_kerugian_bos_blud' => 'nullable',
                'attachments'                     => 'nullable|array',
                'attachments.*.file_path'         => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
                'attachments.*.file_name'         => 'nullable|string',
            ]);

            try {
                DB::beginTransaction();

                $lhp = Lhp::create([
                    'audit_assignment_id' => $validated['audit_assignment_id'],
                    'nomor_lhp'           => $validated['nomor_lhp'],
                    'tanggal_lhp'         => $validated['tanggal_lhp'],
                    'semester'            => $validated['semester'],
                    'irban'               => $validated['irban'],
                    'jenis_pemeriksaan'   => $validated['jenis_pemeriksaan'] ?? null,
                    'catatan_umum'        => $validated['catatan_umum'] ?? null,
                    'status'              => 'draft',
                    'created_by'          => auth()->id(),
                ]);

                if (! empty($request->temuans)) {
                    foreach ($request->temuans as $temuan) {
                        // Skip jika baris temuan kosong sama sekali
                        if (empty($temuan['kode_temuan_id']) && empty($temuan['kondisi'])) continue;

                        // Pastikan nilai adalah integer/numeric (hapus karakter non-digit jika perlu)
                        $negara  = (int) ($temuan['nilai_kerugian_negara'] ?? 0);
                        $daerah  = (int) ($temuan['nilai_kerugian_daerah'] ?? 0);
                        $desa    = (int) ($temuan['nilai_kerugian_desa'] ?? 0); 
                        $bosBLud = (int) ($temuan['nilai_kerugian_bos_blud'] ?? 0); 

                        $lhp->temuans()->create([
                            'kode_temuan_id'        => $temuan['kode_temuan_id'] ?? null,
                            'kondisi'               => $temuan['kondisi'] ?? null,
                            'nilai_kerugian_negara' => $negara,
                            'nilai_kerugian_daerah' => $daerah,
                            'nilai_kerugian_desa'   => $desa,
                            'nilai_kerugian_bos_blud' => $bosBLud,
                            'nilai_temuan'          => $negara + $daerah + $desa + $bosBLud,
                            'status_tl'             => 'belum_ditindaklanjuti',
                        ]);
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

                $this->statistikService->updateStatistik($lhp->id);

                return redirect()->route('lhps.index')
                    ->with('success', "LHP nomor {$lhp->nomor_lhp} berhasil dibuat.");

            } catch (\Throwable $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
            }
        }

        public function show(Lhp $lhp)
        {
            $lhp->load([
                'temuans.kodeTemuan',
                'temuans.recommendations',       // progress rekomendasi per temuan
                'auditAssignment.auditProgram',
                'auditAssignment.unitDiperiksa',
                'attachments',
                'statistik',
                'creator',                        // FIX: dibutuhkan blade show
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

            $lhp->load([
                'temuans.kodeTemuan',
                'auditAssignment.auditProgram',
                'attachments',
            ]);

            return view('pages.lhps.edit', compact('lhp', 'assignments', 'kodeTemuans'));
        }
public function update(Request $request, Lhp $lhp)
{
    $validated = $request->validate([
        'nomor_lhp'         => 'required|string|unique:lhps,nomor_lhp,' . $lhp->id,
        'tanggal_lhp'       => 'required|date',
        'semester'          => 'required|in:1,2',
        'irban'             => 'required|string',
        'jenis_pemeriksaan' => 'nullable|string',
        'catatan_umum'      => 'nullable|string',
        'temuans'           => 'nullable|array',
        'temuans.*.id'      => 'nullable',
        'temuans.*.kode_temuan_id'        => 'nullable|exists:kode_temuans,id',
        'temuans.*.kondisi'               => 'nullable|string',
        'temuans.*.nilai_kerugian_negara' => 'nullable|numeric|min:0',
        'temuans.*.nilai_kerugian_daerah' => 'nullable|numeric|min:0',
        'temuans.*.nilai_kerugian_desa'   => 'nullable|numeric|min:0',
        'temuans.*.nilai_kerugian_bos_blud' => 'nullable|numeric|min:0',
    ]);

    try {
        DB::beginTransaction();

        $lhp->update(collect($validated)->except('temuans')->toArray());

        if ($request->has('temuans')) {
            $existingIds = collect($request->temuans)->pluck('id')->filter()->toArray();
            
            // Hapus temuan yang tidak ada di request (beserta relasinya)
            $lhp->temuans()->whereNotIn('id', $existingIds)->each(function($oldTemuan) {
                $oldTemuan->recommendations()->each(function($rekom) {
                    $rekom->tindakLanjuts()->delete();
                });
                $oldTemuan->delete();
            });

            foreach ($request->temuans as $temuan) {
                $negara  = (float) ($temuan['nilai_kerugian_negara'] ?? 0);
                $daerah  = (float) ($temuan['nilai_kerugian_daerah'] ?? 0);
                $desa    = (float) ($temuan['nilai_kerugian_desa']   ?? 0);  
                $bosBLud = (float) ($temuan['nilai_kerugian_bos_blud'] ?? 0); 
                $totalNilaiBaru = $negara + $daerah + $desa + $bosBLud;

                if (!empty($temuan['id'])) {
                    $existing = $lhp->temuans()->find($temuan['id']);
                    if ($existing) {
                        // Bandingkan dengan float untuk akurasi
                        $isChanged = (abs((float)$existing->nilai_temuan - $totalNilaiBaru) > 0.01);

                        $existing->update([
                            'kode_temuan_id'        => $temuan['kode_temuan_id'] ?? null,
                            'kondisi'               => $temuan['kondisi'] ?? null,
                            'nilai_kerugian_negara' => $negara,
                            'nilai_kerugian_daerah' => $daerah,
                            'nilai_kerugian_desa'     => $desa,    
                            'nilai_kerugian_bos_blud' => $bosBLud, 
                            'nilai_temuan'          => $totalNilaiBaru, 
                        ]);

                        if ($isChanged) {
                            $existing->load('recommendations.tindakLanjuts.cicilans');

                            foreach ($existing->recommendations as $rekom) {
                                $rekom->update([
                                    'status'           => Recommendation::STATUS_BELUM,
                                    'nilai_tl_selesai' => 0,
                                ]);

                                foreach ($rekom->tindakLanjuts as $tl) {
                                    if ($tl->jenis_penyelesaian === 'cicilan') {
                                        $tl->cicilans()->update([
                                            'status'             => 'menunggu',
                                            'diverifikasi_oleh'  => null,
                                            'diverifikasi_pada'  => null,
                                            'catatan_verifikasi' => null,
                                        ]);
                                    }

                                    $tl->update([
                                        'status_verifikasi'   => 'menunggu_verifikasi',
                                        'nilai_tindak_lanjut' => 0,
                                    ]);
                                }
                            }

                            $existing->fresh(['recommendations'])->syncStatus();
                        }
                    }
                } else {
                    // Buat temuan baru jika data minimal terisi
                    if (empty($temuan['kode_temuan_id']) && empty($temuan['kondisi'])) continue;
                    
                    $lhp->temuans()->create([
                        'kode_temuan_id'        => $temuan['kode_temuan_id'] ?? null,
                        'kondisi'               => $temuan['kondisi'] ?? null,
                        'nilai_kerugian_negara' => $negara,
                        'nilai_kerugian_daerah' => $daerah,
                        'nilai_kerugian_desa'     => $desa,    
                        'nilai_kerugian_bos_blud' => $bosBLud, 
                        'nilai_temuan'          => $totalNilaiBaru, 
                        'status_tl'             => 'belum_ditindaklanjuti',
                    ]);
                }
            }
        }

        DB::commit();

        // Trigger kalkulasi ulang statistik
        $this->statistikService->updateStatistik($lhp->id);

        return redirect()->route('lhps.show', $lhp->id)
            ->with('success', 'LHP berhasil diperbarui dan statistik disinkronkan.');

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
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

            Lhp::whereIn('id', $request->ids)->get()->each(function ($lhp) {
                foreach ($lhp->attachments as $file) {
                    Storage::disk('public')->delete($file->file_path);
                }
                $lhp->delete();
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
                'auditAssignment.unitDiperiksa',
                'auditAssignment.auditProgram',
                'temuans.kodeTemuan',
                'temuans.recommendations.tindakLanjuts',
                'statistik'
            ])
            ->withCount('temuans') // ✅ FIX: biar tidak pakai count() di blade
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