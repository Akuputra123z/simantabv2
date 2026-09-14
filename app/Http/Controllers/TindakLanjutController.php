<?php

namespace App\Http\Controllers;

use App\Models\AuditProgram;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Models\Attachment;
use App\Services\LhpStatistikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TindakLanjutController extends Controller
{
    public function __construct(private readonly LhpStatistikService $statistikService) {}

    /**
     * Menampilkan daftar LHP yang memiliki temuan dan rekomendasi tindak lanjut.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Pastikan stub TindakLanjut terbuat untuk semua rekomendasi yang belum memiliki TL
        $missingRekomIds = Recommendation::whereHas('temuan.lhp')
            ->doesntHave('tindakLanjuts')
            ->pluck('id');

        foreach ($missingRekomIds as $rekomId) {
            TindakLanjut::firstOrCreate(
                ['recommendation_id' => $rekomId],
                [
                    'status_verifikasi'   => 'menunggu_verifikasi',
                    'nilai_tindak_lanjut' => 0,
                    'total_terbayar'      => 0,
                    'sisa_belum_bayar'    => 0,
                ]
            );
        }

        $search    = $request->input('search');
        $tahun     = $request->input('tahun');
        $kategori  = $request->input('kategori');
        $status    = $request->input('status');
        $statusOpd = $request->input('status_opd');
        $unitId    = $request->input('unit_id');

        $query = Lhp::query()
            ->whereHas('temuans.recommendations')
            ->with([
                'unitDiperiksa',
                'auditAssignment.auditProgramDetail.auditProgram',
                'auditAssignment.ketuaTim',
                'statistik',
                'temuans.recommendations.tindakLanjuts.attachments',
                'temuans.recommendations.kodeRekomendasi',
            ]);

        // Filter hak akses jika bukan super admin atau kepala inspektorat
        if (! $user->hasRole(['super_admin', 'kepala_inspektorat', 'admin', 'admin_inspektorat'])) {
            $query->whereHas('auditAssignment', function ($q) use ($user) {
                $q->where('ketua_tim_id', $user->id)
                  ->orWhereHas('members', fn($m) => $m->where('user_id', $user->id));
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                  ->orWhere('catatan_umum', 'like', "%{$search}%")
                  ->orWhereHas('unitDiperiksa', fn($u) => $u->where('nama_unit', 'like', "%{$search}%"))
                  ->orWhereHas('auditAssignment.auditProgramDetail.auditProgram', fn($ap) => $ap->where('nama_program', 'like', "%{$search}%"));
            });
        }

        if ($tahun) {
            $query->where(function ($q) use ($tahun) {
                $q->whereYear('tanggal_lhp', $tahun)
                  ->orWhereHas('auditAssignment.auditProgramDetail.auditProgram', fn($ap) => $ap->where('tahun', $tahun));
            });
        }

        if ($kategori) {
            $query->whereHas('auditAssignment.auditProgramDetail.auditProgram', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        if ($unitId) {
            $query->where('unit_diperiksa_id', $unitId);
        }

        if ($status) {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) use ($status) {
                $q->where('status_verifikasi', $status);
            });
        }

        if ($statusOpd === 'belum_upload') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->whereNull('status_opd');
            });
        } elseif ($statusOpd === 'draft') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->where('status_opd', 'draft')->whereNull('alasan_tolak_opd');
            });
        } elseif ($statusOpd === 'dikirim') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->where('status_opd', 'dikirim');
            });
        } elseif ($statusOpd === 'ditolak') {
            $query->whereHas('temuans.recommendations.tindakLanjuts', function ($q) {
                $q->where('status_opd', 'draft')->whereNotNull('alasan_tolak_opd');
            });
        }

        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sort) {
            switch ($sort) {
                case 'nomor_lhp':
                    $query->orderBy('lhps.nomor_lhp', $direction);
                    break;
                case 'nama_program':
                    $query->orderBy(
                        AuditProgram::select('nama_program')
                            ->join('audit_program_details', 'audit_program_details.audit_program_id', '=', 'audit_programs.id')
                            ->join('audit_assignments', 'audit_assignments.audit_program_detail_id', '=', 'audit_program_details.id')
                            ->whereColumn('audit_assignments.id', 'lhps.audit_assignment_id')
                            ->limit(1),
                        $direction
                    )->orderBy('lhps.nomor_lhp', $direction);
                    break;
                case 'unit_diperiksa':
                    $query->orderBy(
                        UnitDiperiksa::select('nama_unit')
                            ->whereColumn('unit_diperiksas.id', 'lhps.unit_diperiksa_id')
                            ->limit(1),
                        $direction
                    );
                    break;
                case 'tanggal_lhp':
                    $query->orderBy('lhps.tanggal_lhp', $direction);
                    break;
                case 'kategori':
                    $query->orderBy(
                        AuditProgram::select('kategori')
                            ->join('audit_program_details', 'audit_program_details.audit_program_id', '=', 'audit_programs.id')
                            ->join('audit_assignments', 'audit_assignments.audit_program_detail_id', '=', 'audit_program_details.id')
                            ->whereColumn('audit_assignments.id', 'lhps.audit_assignment_id')
                            ->limit(1),
                        $direction
                    );
                    break;
                default:
                    $query->latest('lhps.created_at')->latest('lhps.id');
                    break;
            }
        } else {
            $query->latest('lhps.created_at')->latest('lhps.id');
        }

        $lhps = $query->paginate(10)->withQueryString();

        $stats = (object) [
            'total_lhp'         => Lhp::whereHas('temuans.recommendations')->count(),
            'total_rekomendasi' => Recommendation::whereHas('temuan.lhp')->count(),
            'total_lunas'       => TindakLanjut::where('status_verifikasi', 'lunas')->count(),
            'total_berjalan'    => TindakLanjut::where('status_verifikasi', 'berjalan')->count(),
            'total_menunggu'    => TindakLanjut::where('status_verifikasi', 'menunggu_verifikasi')->count(),
            'total_nilai_rekom' => (float) Recommendation::whereHas('temuan.lhp')->sum('nilai_rekom'),
            'total_terbayar'    => (float) TindakLanjut::sum('total_terbayar'),
        ];

        $kategoris = AuditProgram::KATEGORI;
        $units = UnitDiperiksa::orderBy('nama_unit')->get();

        return view('pages.tindak-lanjuts.index', compact(
            'lhps',
            'stats',
            'kategoris',
            'units',
            'search',
            'tahun',
            'kategori',
            'status',
            'statusOpd',
            'unitId'
        ));
    }

    /**
     * Menampilkan detail LHP untuk tindak lanjut dengan seluruh temuan & rekomendasi (accordion/dropdown).
     */
    public function showLhp(Lhp $lhp)
    {
        $user = auth()->user();

        // Hak akses
        if (! $user->hasRole(['super_admin', 'kepala_inspektorat', 'admin', 'admin_inspektorat'])) {
            $hasAccess = $lhp->auditAssignment && (
                $lhp->auditAssignment->ketua_tim_id === $user->id ||
                $lhp->auditAssignment->members()->where('user_id', $user->id)->exists()
            );
            if (! $hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke LHP ini.');
            }
        }

        // Pastikan stub TindakLanjut terbuat untuk semua rekomendasi di LHP ini
        $missingRekomIds = Recommendation::whereHas('temuan', fn($q) => $q->where('lhp_id', $lhp->id))
            ->doesntHave('tindakLanjuts')
            ->pluck('id');

        foreach ($missingRekomIds as $rekomId) {
            TindakLanjut::firstOrCreate(
                ['recommendation_id' => $rekomId],
                [
                    'status_verifikasi'   => 'menunggu_verifikasi',
                    'nilai_tindak_lanjut' => 0,
                    'total_terbayar'      => 0,
                    'sisa_belum_bayar'    => 0,
                ]
            );
        }

        $lhp->load([
            'unitDiperiksa',
            'auditAssignment.ketuaTim',
            'auditAssignment.members',
            'auditAssignment.auditProgramDetail.auditProgram',
            'statistik',
            'temuans.kodeTemuan',
            'temuans.recommendations.kodeRekomendasi',
            'temuans.recommendations.tindakLanjuts.attachments',
            'temuans.recommendations.tindakLanjuts.cicilans.diverifikator',
            'temuans.recommendations.tindakLanjuts.cicilans.attachments',
            'temuans.recommendations.tindakLanjuts.uploadOpdOleh',
            'temuans.recommendations.tindakLanjuts.verifikator',
        ]);

        return view('pages.tindak-lanjuts.lhp_detail', compact('lhp'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        $lhps = Lhp::query()
            ->whereHas('temuans.recommendations')
            ->with(['unitDiperiksa', 'auditAssignment.auditProgramDetail.auditProgram'])
            ->latest('tanggal_lhp')
            ->get();

        $selectedLhpId = $request->input('lhp_id');
        $selectedRekomId = $request->input('recommendation_id');

        // Jika ada recommendation_id, cari LHP id-nya
        if ($selectedRekomId && ! $selectedLhpId) {
            $r = Recommendation::with('temuan')->find($selectedRekomId);
            $selectedLhpId = $r?->temuan?->lhp_id;
        }

        $initialRekomendasis = $selectedLhpId ? $this->getRekomendasisData($selectedLhpId) : collect();

        $users = User::orderBy('name')->get();
        $verifikatorUsers = $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();

        return view('pages.tindak-lanjuts.create', compact(
            'lhps',
            'selectedLhpId',
            'selectedRekomId',
            'initialRekomendasis',
            'verifikatorUsers'
        ));
    }

    public function getRekomendasisData($lhpId)
    {
        return Recommendation::with(['temuan.kodeTemuan', 'kodeRekomendasi', 'tindakLanjuts'])
            ->whereHas('temuan', fn($q) => $q->where('lhp_id', $lhpId))
            ->orderBy('id')
            ->get()
            ->map(function ($r) {
                $tl = $r->tindakLanjuts->first();
                $target = (float) $r->nilai_rekom;
                $terbayar = (float) ($tl?->total_terbayar ?? 0);
                $sisa = max(0, $target - $terbayar);

                return [
                    'id'                => $r->id,
                    'kode'              => $r->kodeRekomendasi?->kode_rekomendasi ?? ('Rekom #' . $r->id),
                    'temuan_kode'       => $r->temuan?->kodeTemuan?->kode ?? '-',
                    'temuan_kondisi'    => \Str::limit(strip_tags($r->temuan?->kondisi ?? ''), 120),
                    'uraian'            => strip_tags($r->uraian_rekom),
                    'jenis'             => $r->jenis_rekomendasi,
                    'nilai_rekom'       => $target,
                    'nilai_sisa'        => $sisa,
                    'total_terbayar'    => $terbayar,
                    'status'            => $r->status,
                    'status_verifikasi' => $tl?->status_verifikasi ?? 'belum',
                    'label'             => '[' . ($r->kodeRekomendasi?->kode_rekomendasi ?? 'REKOM') . '] '
                        . \Str::limit(strip_tags($r->uraian_rekom), 120)
                        . ($r->jenis_rekomendasi === 'uang' ? ' — (Sisa: Rp' . number_format($sisa, 0, ',', '.') . ')' : ''),
                ];
            });
    }

    public function getRekomendasisByLhp($lhpId)
    {
        return response()->json($this->getRekomendasisData($lhpId));
    }

    public function getRekomendasisByProgram($programId)
    {
        $recommendations = Recommendation::with(['temuan.lhp:id,nomor_lhp'])
            ->select('id', 'temuan_id', 'uraian_rekom', 'nilai_rekom', 'nilai_sisa', 'jenis_rekomendasi')
            ->whereHas('temuan.lhp.auditAssignment.auditProgramDetail', fn($q) => $q->where('audit_program_id', $programId))
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn($r) => [
                'id'    => $r->id,
                'sisa'  => (int) $r->nilai_sisa,
                'rekom' => (int) $r->nilai_rekom,
                'jenis' => $r->jenis_rekomendasi,
                'label' => '[' . ($r->temuan?->lhp?->nomor_lhp ?? 'LHP') . '] '
                    . \Str::limit(strip_tags($r->uraian_rekom), 200)
                    . ' — (Sisa: Rp' . number_format($r->nilai_sisa, 0, ',', '.') . ')',
            ]);

        return response()->json($recommendations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recommendation_id'       => 'required|exists:recommendations,id',
            'jenis_penyelesaian'      => 'required|in:setor_kas,cicilan,pengembalian_barang,perbaikan_administrasi,langsung',
            'nilai_tindak_lanjut'     => 'nullable|numeric|min:0',
            'nomor_bukti'             => 'nullable|string|max:255',
            'jumlah_cicilan_rencana'  => 'nullable|integer|min:1',
            'tanggal_mulai_cicilan'   => 'nullable|date',
            'tanggal_jatuh_tempo'     => 'required|date',
            'status_verifikasi'       => 'required|in:menunggu_verifikasi,berjalan,lunas',
            'diverifikasi_oleh'       => 'nullable|integer|exists:users,id',
            'catatan_tl'              => 'nullable|string|max:2000',
            'hambatan'                => 'nullable|string|max:2000',
            'attachments'             => 'nullable|array',
            'attachments.*'           => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ], [
            'recommendation_id.required' => 'Rekomendasi yang Ditindaklanjuti wajib dipilih.',
            'recommendation_id.exists'   => 'Rekomendasi yang dipilih tidak valid atau tidak ditemukan.',
            'jenis_penyelesaian.required'=> 'Metode penyelesaian wajib dipilih.',
            'tanggal_jatuh_tempo.required'=> 'Tanggal jatuh tempo wajib diisi.',
            'status_verifikasi.required' => 'Status verifikasi awal wajib dipilih.',
        ]);

        $rekom = Recommendation::with('temuan.lhp')->findOrFail($validated['recommendation_id']);
        $lhpId = $rekom->temuan?->lhp_id;

        // Normalisasi jenis
        $jenis = $validated['jenis_penyelesaian'];
        if ($jenis === 'langsung') {
            $jenis = 'setor_kas';
        }

        $nilaiTl = (float) ($validated['nilai_tindak_lanjut'] ?? 0);
        if (in_array($jenis, ['pengembalian_barang', 'perbaikan_administrasi'])) {
            $nilaiTl = 0;
        }

        // Susun keterangan / nomor bukti
        $catatan = trim($validated['catatan_tl'] ?? '');
        $nomorBukti = trim($validated['nomor_bukti'] ?? '');
        if ($nomorBukti) {
            $prefix = match($jenis) {
                'pengembalian_barang' => 'No. BAST / Aset: ',
                'perbaikan_administrasi' => 'No. Dokumen / SK: ',
                default => 'No. Bukti / STS: ',
            };
            if (! str_contains($catatan, $prefix)) {
                $catatan = trim($prefix . $nomorBukti . ($catatan ? "\n" . $catatan : ''));
            }
        }

        DB::beginTransaction();
        try {
            $existingTl = TindakLanjut::where('recommendation_id', $rekom->id)->first();

            $tlData = [
                'jenis_penyelesaian'      => $jenis,
                'is_cicilan'              => ($jenis === 'cicilan'),
                'jumlah_cicilan_rencana'  => $validated['jumlah_cicilan_rencana'] ?? null,
                'tanggal_mulai_cicilan'   => $validated['tanggal_mulai_cicilan'] ?? null,
                'tanggal_jatuh_tempo'     => $validated['tanggal_jatuh_tempo'],
                'status_verifikasi'       => $validated['status_verifikasi'],
                'diverifikasi_oleh'       => $validated['diverifikasi_oleh'] ?? auth()->id(),
                'diverifikasi_pada'       => now(),
                'catatan_tl'              => $catatan,
                'hambatan'                => $validated['hambatan'] ?? null,
                'created_by'              => auth()->id(),
                'updated_by'              => auth()->id(),
            ];

            // Hanya tetapkan nilai_tindak_lanjut jika diverifikasi lunas/berjalan atau nilai dimasukkan
            if (in_array($validated['status_verifikasi'], ['lunas', 'berjalan']) || $nilaiTl > 0) {
                $tlData['nilai_tindak_lanjut'] = $nilaiTl;
            } elseif (! $existingTl) {
                $tlData['nilai_tindak_lanjut'] = 0;
            }

            // Gunakan updateOrCreate untuk menjaga 1 TL per Rekomendasi
            $tindakLanjut = TindakLanjut::updateOrCreate(
                ['recommendation_id' => $rekom->id],
                $tlData
            );

            // Jika diverifikasi lunas
            if ($validated['status_verifikasi'] === 'lunas') {
                $tindakLanjut->status_opd = 'dikirim';
                $tindakLanjut->dikirim_pada = $tindakLanjut->dikirim_pada ?? now();
                $tindakLanjut->alasan_tolak_opd = null;

                if ($rekom->isNonUang()) {
                    $tindakLanjut->total_terbayar = 1;
                    $tindakLanjut->sisa_belum_bayar = 0;
                } else {
                    $target = (float) ($nilaiTl > 0 ? $nilaiTl : ($rekom->nilai_rekom ?? 0));
                    $tindakLanjut->total_terbayar = $target;
                    $tindakLanjut->sisa_belum_bayar = 0;
                }
            } elseif ($validated['status_verifikasi'] === 'berjalan') {
                $tindakLanjut->status_opd = 'dikirim';
                $tindakLanjut->dikirim_pada = $tindakLanjut->dikirim_pada ?? now();
                $tindakLanjut->syncCalculations(fromCascade: true);
            } else {
                // status_verifikasi = 'menunggu_verifikasi'
                // Superadmin hanya memberikan/membuka akses agar OPD dapat mengunggah bukti tindak lanjut.
                // Jangan tandai status_opd sebagai 'dikirim', biarkan null/draft agar OPD yang mengirimkan bukti.
                $tindakLanjut->syncCalculations(fromCascade: true);
            }

            // Jika jenis cicilan dan ada nominal awal dicatat
            if ($jenis === 'cicilan' && $nilaiTl > 0 && $tindakLanjut->cicilans()->count() === 0) {
                $tindakLanjut->cicilans()->create([
                    'nilai_bayar'   => $nilaiTl,
                    'tanggal_bayar' => now(),
                    'nomor_bukti'   => $nomorBukti,
                    'keterangan'    => 'Setoran cicilan awal',
                    'jenis_bayar'   => 'setor_kas',
                    'status'        => ($validated['status_verifikasi'] === 'lunas' ? \App\Models\TindakLanjutCicilan::STATUS_DITERIMA : \App\Models\TindakLanjutCicilan::STATUS_MENUNGGU),
                    'created_by'    => auth()->id(),
                ]);
            }

            $tindakLanjut->saveQuietly();

            // Simpan berkas lampiran
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/tindak-lanjut', 'public');
                        $tindakLanjut->attachments()->create([
                            'file_path'   => $path,
                            'file_name'   => $file->getClientOriginalName(),
                            'file_type'   => $file->getMimeType(),
                            'file_size'   => $file->getSize(),
                            'jenis_bukti' => 'tindak_lanjut',
                            'urutan'      => $i,
                            'visibilitas' => 'internal',
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            }

            $this->syncRekomendasi($tindakLanjut);
            $this->updateStatistik($tindakLanjut);

            DB::commit();

            $redirectRoute = $lhpId ? route('tindak-lanjuts.lhp', $lhpId) : route('tindak-lanjuts.index');
            return redirect($redirectRoute)
                ->with('success', 'Tindak lanjut berhasil disimpan dan disinkronkan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('TindakLanjut store error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan tindak lanjut: ' . $e->getMessage());
        }
    }

    public function show(TindakLanjut $tindakLanjut)
    {
        $tindakLanjut->load([
            'recommendation.temuan.lhp.unitDiperiksa',
            'recommendation.temuan.lhp.auditAssignment.ketuaTim',
            'recommendation.temuan.kodeTemuan',
            'recommendation.kodeRekomendasi',
            'verifikator',
            'creator',
            'cicilans.attachments',
            'cicilans.creator',
            'attachments',
            'uploadOpdOleh',
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
        $verifikatorUsers = $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();

        return view('pages.tindak-lanjuts.edit', compact('tindakLanjut', 'recommendations', 'users', 'verifikatorUsers'));
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
            'new_attachments'         => 'nullable|array',
            'new_attachments.*'       => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
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

    public function bukaKunciOpd(TindakLanjut $tindakLanjut): RedirectResponse
    {
        $this->authorize('bukaKunciOpd', $tindakLanjut);

        $tindakLanjut->update([
            'status_opd'   => null,
            'dikirim_pada' => null,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($tindakLanjut)
            ->withProperties(['status_opd' => 'dibuka_kunci'])
            ->log('Admin membuka kunci OPD untuk revisi');

        return redirect()
            ->route('tindak-lanjuts.show', $tindakLanjut)
            ->with('success', 'Kunci OPD berhasil dibuka. OPD dapat mengirim ulang.');
    }

    public function tolakOpd(Request $request, TindakLanjut $tindakLanjut): RedirectResponse
    {
        $this->authorize('tolakOpd', $tindakLanjut);

        $validated = $request->validate([
            'alasan_tolak' => 'required|string|max:2000',
        ]);

        $tindakLanjut->update([
            'status_opd'      => 'draft',
            'dikirim_pada'    => null,
            'alasan_tolak_opd' => $validated['alasan_tolak'],
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($tindakLanjut)
            ->withProperties([
                'status_opd' => 'ditolak',
                'alasan'     => $validated['alasan_tolak'],
            ])
            ->log('Admin menolak bukti tindak lanjut OPD');

        return redirect()
            ->route('tindak-lanjuts.show', $tindakLanjut)
            ->with('success', 'Bukti OPD ditolak. Alasan sudah dicatat dan OPD dapat mengirim ulang.');
    }

    public function verifikasiOpd(Request $request, TindakLanjut $tindakLanjut): RedirectResponse
    {
        $this->authorize('verifikasi', $tindakLanjut);

        $validated = $request->validate([
            'status_verifikasi'  => 'required|in:lunas,berjalan',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $rekom = $tindakLanjut->recommendation;
            $isNonUang = $rekom?->isNonUang() ?? false;
            $newStatus = $validated['status_verifikasi'];

            $tindakLanjut->status_verifikasi  = $newStatus;
            $tindakLanjut->catatan_verifikasi = $validated['catatan_verifikasi'] ?? null;
            $tindakLanjut->diverifikasi_oleh  = auth()->id();
            $tindakLanjut->diverifikasi_pada  = now();

            // SINKRONISASI LENGKAP DENGAN OPD:
            $tindakLanjut->status_opd = 'dikirim';
            if (! $tindakLanjut->dikirim_pada) {
                $tindakLanjut->dikirim_pada = now();
            }
            $tindakLanjut->alasan_tolak_opd = null;

            if ($newStatus === 'lunas') {
                if ($isNonUang) {
                    $tindakLanjut->total_terbayar   = 1;
                    $tindakLanjut->sisa_belum_bayar = 0;
                } else {
                    $target = (float) ($tindakLanjut->nilai_tindak_lanjut > 0
                        ? $tindakLanjut->nilai_tindak_lanjut
                        : ($rekom?->nilai_rekom ?? 0));
                    $tindakLanjut->total_terbayar   = $target;
                    $tindakLanjut->sisa_belum_bayar = 0;

                    // Setujui juga cicilan yang berstatus menunggu jika ada
                    $tindakLanjut->cicilans()->where('status', \App\Models\TindakLanjutCicilan::STATUS_MENUNGGU)->update([
                        'status'             => \App\Models\TindakLanjutCicilan::STATUS_DITERIMA,
                        'diverifikasi_oleh'  => auth()->id(),
                        'diverifikasi_pada'  => now(),
                        'updated_by'         => auth()->id(),
                    ]);
                }
            } elseif ($newStatus === 'berjalan') {
                $tindakLanjut->syncCalculations(fromCascade: true);
            }

            $tindakLanjut->saveQuietly();

            // Sync recommendation & LHP stats
            $this->syncRekomendasi($tindakLanjut);
            $this->updateStatistik($tindakLanjut);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($tindakLanjut)
                ->withProperties(['status_verifikasi' => $newStatus, 'status_opd' => 'dikirim'])
                ->log("Admin memverifikasi tindak lanjut sebagai {$newStatus}");

            DB::commit();

            $label = $newStatus === 'lunas' ? 'Lunas / Selesai' : 'Berjalan (Proses)';
            return redirect()
                ->route('tindak-lanjuts.show', $tindakLanjut)
                ->with('success', "Tindak lanjut berhasil diverifikasi sebagai {$label}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Verifikasi OPD error: ' . $e->getMessage());

            return back()->with('error', 'Gagal memverifikasi tindak lanjut: ' . $e->getMessage());
        }
    }

    private function syncRekomendasi(TindakLanjut $tl): void
    {
        $recommendation = $tl->recommendation;
        if (! $recommendation) {
            $tl->load('recommendation.tindakLanjuts.cicilans');
            $recommendation = $tl->recommendation;
        }

        if (! $recommendation) return;

        $recommendation->refresh();
        $recommendation->load('tindakLanjuts.cicilans');

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