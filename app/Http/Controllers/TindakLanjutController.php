<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\AuditProgram;
use App\Models\AuditProgramDetail;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use App\Models\UnitDiperiksa;
use App\Models\User;
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

        $search = $request->input('search');
        $tahun = $request->input('tahun');
        $kategori = $request->input('kategori');
        $status = $request->input('status');
        $statusOpd = $request->input('status_opd');
        $unitId = $request->input('unit_id');

        $query = Lhp::query()
            ->whereHas('temuans.recommendations')
            ->with([
                'unitDiperiksa',
                'auditAssignment.auditProgramDetail.auditProgram',
                'auditAssignment.ketuaTim',
                'statistik',
                'temuans.recommendations.tindakLanjuts',
                'temuans.recommendations.kodeRekomendasi',
            ]);

        // Filter hak akses jika bukan super admin atau kepala inspektorat
        if (! $user->hasRole(['super_admin', 'kepala_inspektorat', 'admin', 'admin_inspektorat'])) {
            if ($user->hasRole(User::ROLE_PENGENDALI_TEKNIS) || $user->isPengendaliTeknis() || $user->isIrban()) {
                $irbanNames = $user->getIrbanTeamNames();
                $query->whereHas('auditAssignment', function ($q) use ($user, $irbanNames) {
                    $q->where('pengendali_teknis_id', $user->id)
                        ->orWhere('pengendali_teknis', $user->name)
                        ->orWhere(function ($sq) use ($irbanNames) {
                            if (! empty($irbanNames)) {
                                $sq->whereHas('auditProgramDetail', fn ($dq) => $dq->whereIn('tim', $irbanNames));
                            }
                        })
                        ->orWhere('ketua_tim_id', $user->id)
                        ->orWhere('created_by', $user->id)
                        ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id));
                })->orWhere('created_by', $user->id);
            } else {
                $query->whereHas('auditAssignment', function ($q) use ($user) {
                    $q->where('ketua_tim_id', $user->id)
                        ->orWhere('created_by', $user->id)
                        ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id));
                })->orWhere('created_by', $user->id);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                    ->orWhere('catatan_umum', 'like', "%{$search}%")
                    ->orWhereHas('unitDiperiksa', fn ($u) => $u->where('nama_unit', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment.auditProgramDetail.auditProgram', fn ($ap) => $ap->where('nama_program', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment.auditProgramDetail', fn ($apd) => $apd->where('nama_detail_program', 'like', "%{$search}%")->orWhere('tim', 'like', "%{$search}%"))
                    ->orWhereHas('auditAssignment.ketuaTim', fn ($kt) => $kt->where('name', 'like', "%{$search}%"));
            });
        }

        if ($tahun) {
            $query->where(function ($q) use ($tahun) {
                $q->whereYear('tanggal_lhp', $tahun)
                    ->orWhereHas('auditAssignment.auditProgramDetail.auditProgram', fn ($ap) => $ap->where('tahun', $tahun));
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
                case 'sub_program':
                    $query->orderBy(
                        AuditProgramDetail::select('nama_detail_program')
                            ->join('audit_assignments', 'audit_assignments.audit_program_detail_id', '=', 'audit_program_details.id')
                            ->whereColumn('audit_assignments.id', 'lhps.audit_assignment_id')
                            ->limit(1),
                        $direction
                    )->orderBy('lhps.nomor_lhp', $direction);
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
                    $latestTlSub = DB::table('tindak_lanjuts')
                        ->join('recommendations', 'recommendations.id', '=', 'tindak_lanjuts.recommendation_id')
                        ->join('temuans', 'temuans.id', '=', 'recommendations.temuan_id')
                        ->whereColumn('temuans.lhp_id', 'lhps.id')
                        ->whereNull('tindak_lanjuts.deleted_at')
                        ->selectRaw('COALESCE(MAX(tindak_lanjuts.updated_at), MAX(tindak_lanjuts.created_at))');

                    $query->orderByDesc($latestTlSub)
                        ->latest('lhps.updated_at')
                        ->latest('lhps.created_at')
                        ->latest('lhps.id');
                    break;
            }
        } else {
            $latestTlSub = DB::table('tindak_lanjuts')
                ->join('recommendations', 'recommendations.id', '=', 'tindak_lanjuts.recommendation_id')
                ->join('temuans', 'temuans.id', '=', 'recommendations.temuan_id')
                ->whereColumn('temuans.lhp_id', 'lhps.id')
                ->whereNull('tindak_lanjuts.deleted_at')
                ->selectRaw('COALESCE(MAX(tindak_lanjuts.updated_at), MAX(tindak_lanjuts.created_at))');

            $query->orderByDesc($latestTlSub)
                ->latest('lhps.updated_at')
                ->latest('lhps.created_at')
                ->latest('lhps.id');
        }

        $lhps = $query->paginate(10)->withQueryString();

        // Hitung statistik kumulatif KESELURUHAN data tindak lanjut secara optimal dalam 1 query
        $statsData = DB::table('recommendations')
            ->join('temuans', 'temuans.id', '=', 'recommendations.temuan_id')
            ->join('lhps', 'lhps.id', '=', 'temuans.lhp_id')
            ->leftJoin('tindak_lanjuts', 'tindak_lanjuts.recommendation_id', '=', 'recommendations.id')
            ->whereNull('lhps.deleted_at')
            ->where('lhps.status', '!=', 'dibatalkan')
            ->whereNull('recommendations.deleted_at')
            ->whereNull('temuans.deleted_at')
            ->selectRaw("
                COUNT(DISTINCT lhps.id) as total_lhp,
                COUNT(DISTINCT recommendations.id) as total_rekomendasi,
                COUNT(CASE WHEN tindak_lanjuts.status_verifikasi = 'lunas' THEN 1 END) as total_lunas,
                COUNT(CASE WHEN tindak_lanjuts.status_verifikasi = 'berjalan' THEN 1 END) as total_berjalan,
                COUNT(CASE WHEN tindak_lanjuts.status_verifikasi = 'menunggu_verifikasi' THEN 1 END) as total_menunggu,
                COALESCE(SUM(CASE WHEN recommendations.jenis_rekomendasi = 'uang' THEN recommendations.nilai_rekom ELSE 0 END), 0) as total_nilai_rekom,
                COALESCE(SUM(CASE WHEN recommendations.jenis_rekomendasi = 'uang' THEN tindak_lanjuts.total_terbayar ELSE 0 END), 0) as total_terbayar
            ")
            ->first();

        $totalNilaiRekomUang = (float) ($statsData->total_nilai_rekom ?? 0);
        $totalTerbayarUang = (float) ($statsData->total_terbayar ?? 0);
        $persenSetor = $totalNilaiRekomUang > 0
            ? min(100, round(($totalTerbayarUang / $totalNilaiRekomUang) * 100, 1))
            : 0;

        $stats = (object) [
            'total_lhp' => (int) ($statsData->total_lhp ?? 0),
            'total_rekomendasi' => (int) ($statsData->total_rekomendasi ?? 0),
            'total_lunas' => (int) ($statsData->total_lunas ?? 0),
            'total_berjalan' => (int) ($statsData->total_berjalan ?? 0),
            'total_menunggu' => (int) ($statsData->total_menunggu ?? 0),
            'total_nilai_rekom' => $totalNilaiRekomUang,
            'total_terbayar' => $totalTerbayarUang,
            'persen_setor' => $persenSetor,
            'sisa_setor' => max(0, $totalNilaiRekomUang - $totalTerbayarUang),
        ];

        $kategoris = AuditProgram::KATEGORI;
        $units = UnitDiperiksa::orderBy('nama_unit')->get();

        $yearExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y', tanggal_lhp) as tahun"
            : 'YEAR(tanggal_lhp) as tahun';

        $tahuns = Lhp::selectRaw($yearExpr)
            ->whereNotNull('tanggal_lhp')
            ->distinct()
            ->pluck('tahun')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->merge(range((int) date('Y') + 1, (int) date('Y') - 4))
            ->unique()
            ->sortDesc()
            ->values();

        return view('pages.tindak-lanjuts.index', compact(
            'lhps',
            'stats',
            'kategoris',
            'units',
            'search',
            'tahun',
            'tahuns',
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
            $assignment = $lhp->auditAssignment;
            $hasAccess = false;
            if ($assignment) {
                if ($user->hasRole(User::ROLE_PENGENDALI_TEKNIS) || $user->isPengendaliTeknis() || $user->isIrban()) {
                    $irbanNames = $user->getIrbanTeamNames();
                    $hasAccess = $assignment->pengendali_teknis_id === $user->id
                        || $assignment->pengendali_teknis === $user->name
                        || ($assignment->auditProgramDetail && in_array($assignment->auditProgramDetail->tim, $irbanNames))
                        || $assignment->ketua_tim_id === $user->id
                        || $assignment->created_by === $user->id
                        || $assignment->members()->where('user_id', $user->id)->exists();
                } else {
                    $hasAccess = $assignment->ketua_tim_id === $user->id
                        || $assignment->created_by === $user->id
                        || $assignment->members()->where('user_id', $user->id)->exists();
                }
            }
            if (! $hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke LHP ini.');
            }
        }

        // Pastikan stub TindakLanjut terbuat untuk semua rekomendasi di LHP ini (bulk insert efisien jika ada yang kurang)
        $missingRekomIds = Recommendation::whereHas('temuan', fn ($q) => $q->where('lhp_id', $lhp->id))
            ->doesntHave('tindakLanjuts')
            ->pluck('id');

        if ($missingRekomIds->isNotEmpty()) {
            $now = now();
            $stubs = $missingRekomIds->map(fn ($rekomId) => [
                'recommendation_id' => $rekomId,
                'status_verifikasi' => 'menunggu_verifikasi',
                'nilai_tindak_lanjut' => 0,
                'total_terbayar' => 0,
                'sisa_belum_bayar' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray();
            TindakLanjut::insert($stubs);
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

        $selectedLhpId = $request->input('lhp_id') ?: old('lhp_id');
        $selectedRekomId = $request->input('recommendation_id') ?: old('recommendation_id');

        // Jika ada recommendation_id tapi belum ada lhp_id, cari LHP id-nya
        if ($selectedRekomId && ! $selectedLhpId) {
            $r = Recommendation::with('temuan')->find($selectedRekomId);
            $selectedLhpId = $r?->temuan?->lhp_id;
        }

        $initialRekomendasis = $selectedLhpId ? $this->getRekomendasisData($selectedLhpId) : collect();

        $lhps = Lhp::query()
            ->whereHas('temuans.recommendations')
            ->select('id', 'nomor_lhp', 'unit_diperiksa_id', 'audit_assignment_id', 'tanggal_lhp')
            ->with([
                'unitDiperiksa:id,nama_unit',
                'auditAssignment:id,audit_program_detail_id',
                'auditAssignment.auditProgramDetail:id,audit_program_id,nama_detail_program',
                'auditAssignment.auditProgramDetail.auditProgram:id,nama_program',
            ])
            ->latest('tanggal_lhp')
            ->get();

        $verifikatorUsers = User::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
            ->values();

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
        if (! is_numeric($lhpId)) {
            $lhpId = Lhp::where('uuid', $lhpId)->value('id') ?? $lhpId;
        }

        return Recommendation::with(['temuan.kodeTemuan', 'kodeRekomendasi', 'tindakLanjuts'])
            ->whereHas('temuan', fn ($q) => $q->where('lhp_id', $lhpId))
            ->orderBy('id')
            ->get()
            ->map(function ($r) {
                $tl = $r->tindakLanjuts->first();
                $target = (float) $r->nilai_rekom;
                $terbayar = (float) ($tl?->total_terbayar ?? 0);
                $sisa = max(0, $target - $terbayar);

                return [
                    'id' => $r->id,
                    'kode' => $r->kodeRekomendasi?->kode_rekomendasi ?? ('Rekom #'.$r->id),
                    'temuan_kode' => $r->temuan?->kodeTemuan?->kode ?? '-',
                    'temuan_kondisi' => \Str::limit(strip_tags($r->temuan?->kondisi ?? ''), 120),
                    'uraian' => strip_tags($r->uraian_rekom),
                    'jenis' => $r->jenis_rekomendasi,
                    'nilai_rekom' => $target,
                    'nilai_sisa' => $sisa,
                    'total_terbayar' => $terbayar,
                    'status' => $r->status,
                    'status_verifikasi' => $tl?->status_verifikasi ?? 'belum',
                    'label' => '['.($r->kodeRekomendasi?->kode_rekomendasi ?? 'REKOM').'] '
                        .\Str::limit(strip_tags($r->uraian_rekom), 120)
                        .($r->jenis_rekomendasi === 'uang' ? ' — (Sisa: Rp'.number_format($sisa, 0, ',', '.').')' : ''),
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
            ->whereHas('temuan.lhp.auditAssignment.auditProgramDetail', fn ($q) => $q->where('audit_program_id', $programId))
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'sisa' => (int) $r->nilai_sisa,
                'rekom' => (int) $r->nilai_rekom,
                'jenis' => $r->jenis_rekomendasi,
                'label' => '['.($r->temuan?->lhp?->nomor_lhp ?? 'LHP').'] '
                    .\Str::limit(strip_tags($r->uraian_rekom), 200)
                    .' — (Sisa: Rp'.number_format($r->nilai_sisa, 0, ',', '.').')',
            ]);

        return response()->json($recommendations);
    }

    /**
     * Membersihkan input rupiah/angka dari karakter format teks (Rp, spasi, titik ribuan)
     */
    private function sanitizeNominal(mixed $value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $cleaned = preg_replace('/[^\d,\.]/', '', $value);
            if (str_contains($cleaned, '.') && str_contains($cleaned, ',')) {
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } elseif (str_contains($cleaned, '.')) {
                $parts = explode('.', $cleaned);
                if (count($parts) > 2 || (count($parts) === 2 && strlen($parts[1]) === 3)) {
                    $cleaned = str_replace('.', '', $cleaned);
                }
            } elseif (str_contains($cleaned, ',')) {
                $cleaned = str_replace(',', '.', $cleaned);
            }

            return is_numeric($cleaned) ? (float) $cleaned : 0.0;
        }

        return 0.0;
    }

    public function store(Request $request)
    {
        // Sanitisasi nilai rupiah jika dikirim dalam format berformat teks
        if ($request->has('nilai_tindak_lanjut')) {
            $request->merge([
                'nilai_tindak_lanjut' => $this->sanitizeNominal($request->input('nilai_tindak_lanjut')),
            ]);
        }

        $validated = $request->validate([
            'recommendation_id' => 'required|exists:recommendations,id',
            'jenis_penyelesaian' => 'required|in:setor_kas,cicilan,pengembalian_barang,perbaikan_administrasi,langsung',
            'nilai_tindak_lanjut' => 'nullable|numeric|min:0',
            'nomor_bukti' => 'nullable|string|max:255',
            'jumlah_cicilan_rencana' => 'nullable|integer|min:1',
            'tanggal_mulai_cicilan' => 'nullable|date',
            'tanggal_jatuh_tempo' => 'required|date',
            'status_verifikasi' => 'required|in:menunggu_verifikasi,berjalan,lunas',
            'diverifikasi_oleh' => 'nullable|integer|exists:users,id',
            'catatan_tl' => 'nullable|string|max:2000',
            'hambatan' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ], [
            'recommendation_id.required' => 'Rekomendasi yang Ditindaklanjuti wajib dipilih.',
            'recommendation_id.exists' => 'Rekomendasi yang dipilih tidak valid atau tidak ditemukan.',
            'jenis_penyelesaian.required' => 'Metode penyelesaian wajib dipilih.',
            'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'status_verifikasi.required' => 'Status verifikasi awal wajib dipilih.',
        ]);

        $rekom = Recommendation::with('temuan.lhp')->findOrFail($validated['recommendation_id']);
        $lhpId = $rekom->temuan?->lhp_id;

        // Normalisasi jenis
        $jenis = $validated['jenis_penyelesaian'];
        if ($jenis === 'langsung') {
            $jenis = 'setor_kas';
        }

        $isUang = $rekom->isUang();
        $nilaiTl = $isUang ? (float) ($validated['nilai_tindak_lanjut'] ?? 0) : 0;

        // Susun keterangan / nomor bukti
        $catatan = trim($validated['catatan_tl'] ?? '');
        $nomorBukti = trim($validated['nomor_bukti'] ?? '');
        if ($nomorBukti) {
            $prefix = match ($jenis) {
                'pengembalian_barang' => 'No. BAST / Aset: ',
                'perbaikan_administrasi' => 'No. Dokumen / SK: ',
                default => 'No. Bukti / STS: ',
            };
            if (! str_contains($catatan, $prefix)) {
                $catatan = trim($prefix.$nomorBukti.($catatan ? "\n".$catatan : ''));
            }
        }

        DB::beginTransaction();
        try {
            $targetRekom = (float) ($rekom->nilai_rekom ?? 0);
            $statusVerifikasi = $validated['status_verifikasi'];

            // Kalkulasi total_terbayar dan sisa_belum_bayar sesuai best practice akuntansi pengawasan
            $totalTerbayar = 0;
            $sisaBelumBayar = 0;

            if ($isUang) {
                if ($jenis === 'cicilan') {
                    // Jenis cicilan: total_terbayar diakumulasikan dari tabel cicilans
                    $totalTerbayar = 0;
                    $sisaBelumBayar = $targetRekom;
                } elseif ($jenis === 'setor_kas') {
                    if ($statusVerifikasi === 'lunas') {
                        // Jika dinyatakan lunas, target dipenuhi secara penuh
                        $totalTerbayar = max($nilaiTl, $targetRekom);
                        $sisaBelumBayar = 0;
                        $nilaiTl = $totalTerbayar;
                    } elseif ($statusVerifikasi === 'berjalan') {
                        // Setoran parsial yang sedang berjalan
                        $totalTerbayar = min($nilaiTl, $targetRekom);
                        $sisaBelumBayar = max(0, $targetRekom - $totalTerbayar);
                        if ($totalTerbayar >= $targetRekom && $targetRekom > 0) {
                            $statusVerifikasi = 'lunas';
                            $sisaBelumBayar = 0;
                        }
                    } else {
                        // Menunggu verifikasi
                        $totalTerbayar = 0;
                        $sisaBelumBayar = $targetRekom;
                    }
                }
            } else {
                // Non-uang (barang / administrasi)
                if ($statusVerifikasi === 'lunas') {
                    $totalTerbayar = 1;
                    $sisaBelumBayar = 0;
                } else {
                    $totalTerbayar = 0;
                    $sisaBelumBayar = 1;
                }
            }

            $tlData = [
                'jenis_penyelesaian' => $jenis,
                'is_cicilan' => ($jenis === 'cicilan'),
                'jumlah_cicilan_rencana' => $validated['jumlah_cicilan_rencana'] ?? null,
                'tanggal_mulai_cicilan' => $validated['tanggal_mulai_cicilan'] ?? null,
                'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                'status_verifikasi' => $statusVerifikasi,
                'nilai_tindak_lanjut' => $nilaiTl,
                'total_terbayar' => $totalTerbayar,
                'sisa_belum_bayar' => $sisaBelumBayar,
                'status_opd' => in_array($statusVerifikasi, ['lunas', 'berjalan']) ? 'dikirim' : null,
                'dikirim_pada' => in_array($statusVerifikasi, ['lunas', 'berjalan']) ? now() : null,
                'diverifikasi_oleh' => $validated['diverifikasi_oleh'] ?? auth()->id(),
                'diverifikasi_pada' => now(),
                'catatan_tl' => $catatan,
                'hambatan' => $validated['hambatan'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            // Gunakan updateOrCreate untuk menjaga 1 TL per Rekomendasi
            $tindakLanjut = TindakLanjut::updateOrCreate(
                ['recommendation_id' => $rekom->id],
                $tlData
            );

            // Jika jenis cicilan dan ada nominal awal dicatat
            if ($jenis === 'cicilan' && $nilaiTl > 0 && $tindakLanjut->cicilans()->count() === 0) {
                $tindakLanjut->cicilans()->create([
                    'nilai_bayar' => $nilaiTl,
                    'tanggal_bayar' => now(),
                    'nomor_bukti' => $nomorBukti,
                    'keterangan' => 'Setoran cicilan awal',
                    'jenis_bayar' => 'setor_kas',
                    'status' => ($statusVerifikasi === 'lunas' ? TindakLanjutCicilan::STATUS_DITERIMA : TindakLanjutCicilan::STATUS_MENUNGGU),
                    'created_by' => auth()->id(),
                ]);
                $tindakLanjut->syncCalculations(fromCascade: true);
                $tindakLanjut->saveQuietly();
            }

            // Simpan berkas lampiran
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $i => $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('attachments/tindak-lanjut', 'public');
                        $tindakLanjut->attachments()->create([
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                            'jenis_bukti' => 'tindak_lanjut',
                            'urutan' => $i,
                            'visibilitas' => 'internal',
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            }

            $this->syncRekomendasi($tindakLanjut);
            $this->updateStatistik($tindakLanjut);
            $rekom->temuan?->lhp?->touch();

            DB::commit();

            $redirectRoute = $lhpId ? route('tindak-lanjuts.lhp', $lhpId) : route('tindak-lanjuts.index');

            return redirect($redirectRoute)
                ->with('success', 'Tindak lanjut berhasil disimpan dan disinkronkan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('TindakLanjut store error: '.$e->getMessage());

            return back()->withInput()->with('error', 'Gagal menyimpan tindak lanjut: '.$e->getMessage());
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
            'cicilans' => fn ($q) => $q->latest(),
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
        $verifikatorUsers = $users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values();

        return view('pages.tindak-lanjuts.edit', compact('tindakLanjut', 'recommendations', 'users', 'verifikatorUsers'));
    }

    public function update(Request $request, TindakLanjut $tindakLanjut)
    {
        if ($request->has('nilai_tindak_lanjut')) {
            $request->merge([
                'nilai_tindak_lanjut' => $this->sanitizeNominal($request->input('nilai_tindak_lanjut')),
            ]);
        }

        $validated = $request->validate([
            'recommendation_id' => 'required|exists:recommendations,id',
            'jenis_penyelesaian' => 'required|in:setor_kas,cicilan,pengembalian_barang,perbaikan_administrasi,langsung',
            'nilai_tindak_lanjut' => 'nullable|numeric|min:0',
            'jumlah_cicilan_rencana' => 'nullable|integer|min:1',
            'tanggal_mulai_cicilan' => 'nullable|date',
            'tanggal_jatuh_tempo' => 'required|date',
            'status_verifikasi' => 'required|in:menunggu_verifikasi,berjalan,lunas',
            'diverifikasi_oleh' => 'nullable|integer|exists:users,id',
            'catatan_tl' => 'nullable|string|max:1000',
            'hambatan' => 'nullable|string|max:1000',
            'new_attachments' => 'nullable|array',
            'new_attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $rekom = Recommendation::findOrFail($validated['recommendation_id']);
        $nilaiTlBaru = (float) ($validated['nilai_tindak_lanjut'] ?? 0);
        $nilaiTlLama = (float) ($tindakLanjut->nilai_tindak_lanjut ?? 0);

        $jenis = $validated['jenis_penyelesaian'];
        if ($jenis === 'langsung') {
            $jenis = match ($rekom->jenis_rekomendasi) {
                'barang' => 'pengembalian_barang',
                'administrasi' => 'perbaikan_administrasi',
                default => 'setor_kas',
            };
        }
        $validated['jenis_penyelesaian'] = $jenis;
        $validated['is_cicilan'] = ($jenis === 'cicilan');
        $validated['updated_by'] = auth()->id();

        if (in_array($jenis, ['pengembalian_barang', 'perbaikan_administrasi'])) {
            $validated['nilai_tindak_lanjut'] = 0;
            $nilaiTlBaru = 0;
        }

        if ($rekom->isUang()) {
            $sisaAvailable = (float) ($rekom->nilai_sisa ?? 0) + $nilaiTlLama;

            if ($sisaAvailable > 0 && $nilaiTlBaru > $sisaAvailable) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nilai_tindak_lanjut' => 'Nilai tindak lanjut melebihi nilai tersedia (Rp '.number_format($sisaAvailable, 0, ',', '.').').',
                    ]);
            }
        }

        if (! empty($validated['diverifikasi_oleh'])) {
            $validated['diverifikasi_pada'] = $tindakLanjut->diverifikasi_pada ?? now();
        } elseif (in_array($validated['status_verifikasi'], ['lunas', 'berjalan'])) {
            $validated['diverifikasi_oleh'] = $tindakLanjut->diverifikasi_oleh ?? auth()->id();
            $validated['diverifikasi_pada'] = $tindakLanjut->diverifikasi_pada ?? now();
        } else {
            $validated['diverifikasi_pada'] = null;
            $validated['diverifikasi_oleh'] = null;
        }

        try {
            DB::beginTransaction();

            $tindakLanjut->update($validated);
            $tindakLanjut->syncCalculations(fromCascade: false)->saveQuietly();

            // Hapus lampiran yang dicentang
            if ($request->has('delete_attachments')) {
                $deletes = $tindakLanjut->attachments()
                    ->whereIn('id', (array) $request->delete_attachments)
                    ->get();
                foreach ($deletes as $att) {
                    if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                        Storage::disk('public')->delete($att->file_path);
                    }
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
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getClientMimeType(),
                            'file_size' => $file->getSize(),
                            'jenis_bukti' => 'tindak_lanjut',
                            'visibilitas' => 'publik',
                            'uploaded_by' => auth()->id(),
                            'urutan' => $existingCount + $i,
                        ]);
                    }
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('TindakLanjut update error: '.$e->getMessage());

            $errMsg = app()->isLocal()
                ? 'Gagal memperbarui data. Error: '.$e->getMessage()
                : 'Gagal memperbarui data.';

            return back()->withInput()->with('error', $errMsg);
        }

        // Sync parent status outside the atomic transaction so a failure here
        // doesn't lose the user's data — log & continue silently.
        try {
            $this->syncRekomendasi($tindakLanjut);
            $this->updateStatistik($tindakLanjut);
            $tindakLanjut->recommendation?->temuan?->lhp?->touch();
        } catch (\Throwable $e) {
            Log::error('TindakLanjut sync error after update: '.$e->getMessage());
        }

        return redirect()
            ->route('tindak-lanjuts.index')
            ->with('success', 'Tindak lanjut diperbarui.');
    }

    public function destroy(TindakLanjut $tindakLanjut)
    {
        if (! auth()->user()->hasRole(['super_admin', 'kepala_inspektorat'])) {
            abort(403, 'Hanya Super Admin atau Kepala Inspektorat yang berhak menghapus data tindak lanjut.');
        }

        $lhpId = $tindakLanjut->recommendation?->temuan?->lhp_id;

        // Hapus file lampiran dari storage & DB
        foreach ($tindakLanjut->attachments as $att) {
            if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                Storage::disk('public')->delete($att->file_path);
            }
            $att->delete();
        }

        // Hapus cicilan jika ada
        $tindakLanjut->cicilans()->delete();

        // Simpan recommendation sebelum TL dihapus untuk sync setelahnya
        $recommendation = $tindakLanjut->recommendation;

        $tindakLanjut->delete();

        // Sync recommendation setelah TL dihapus
        if ($recommendation) {
            $recommendation->refresh();
            $recommendation->load('tindakLanjuts.cicilans');
            $recommendation->syncStatus();
        }

        if ($lhpId) {
            $this->statistikService->updateStatistik($lhpId);
        }

        $redirectUrl = $lhpId ? route('tindak-lanjuts.lhp', $lhpId) : route('tindak-lanjuts.index');

        return redirect($redirectUrl)
            ->with('success', 'Data tindak lanjut berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        if (! auth()->user()->hasRole(['super_admin', 'kepala_inspektorat'])) {
            abort(403, 'Hanya Super Admin atau Kepala Inspektorat yang berhak menghapus data tindak lanjut.');
        }

        $validated = $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'exists:tindak_lanjuts,id',
            'lhp_ids' => 'nullable|array',
            'lhp_ids.*' => 'exists:lhps,id',
        ]);

        $ids = array_filter($validated['ids'] ?? []);
        $lhpIds = array_filter($validated['lhp_ids'] ?? []);

        if (empty($ids) && empty($lhpIds)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
        }

        $tindakLanjuts = collect();

        if (! empty($ids)) {
            $tindakLanjuts = $tindakLanjuts->merge(
                TindakLanjut::whereIn('id', $ids)->get()
            );
        }

        if (! empty($lhpIds)) {
            $tindakLanjuts = $tindakLanjuts->merge(
                TindakLanjut::whereHas('recommendation.temuan', function ($q) use ($lhpIds) {
                    $q->whereIn('lhp_id', $lhpIds);
                })->get()
            );
        }

        $tindakLanjuts = $tindakLanjuts->unique('id');

        $recomIds = [];
        $affectedLhpIds = $lhpIds;
        $deletedCount = $tindakLanjuts->count();

        foreach ($tindakLanjuts as $tl) {
            $lhpId = $tl->recommendation?->temuan?->lhp_id;
            if ($lhpId) {
                $affectedLhpIds[] = $lhpId;
            }

            if ($tl->recommendation_id) {
                $recomIds[] = $tl->recommendation_id;
            }

            // Hapus file lampiran dari storage & DB
            foreach ($tl->attachments as $att) {
                if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                    Storage::disk('public')->delete($att->file_path);
                }
                $att->delete();
            }

            // Hapus cicilan jika ada
            $tl->cicilans()->delete();

            // Hapus record tindak lanjut
            $tl->delete();
        }

        // Sync all affected recommendations
        if (! empty($recomIds)) {
            $recoms = Recommendation::whereIn('id', array_unique($recomIds))->get();
            foreach ($recoms as $rec) {
                $rec->refresh();
                $rec->load('tindakLanjuts.cicilans');
                $rec->syncStatus();
            }
        }

        // Sync all affected LHP statistics
        foreach (array_unique($affectedLhpIds) as $lhpId) {
            $this->statistikService->updateStatistik($lhpId);
        }

        return redirect()
            ->back()
            ->with('success', 'Data tindak lanjut berhasil dihapus.');
    }

    public function bukaKunciOpd(TindakLanjut $tindakLanjut): RedirectResponse
    {
        $this->authorize('bukaKunciOpd', $tindakLanjut);

        $tindakLanjut->update([
            'status_opd' => null,
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
            'status_opd' => 'draft',
            'dikirim_pada' => null,
            'alasan_tolak_opd' => $validated['alasan_tolak'],
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($tindakLanjut)
            ->withProperties([
                'status_opd' => 'ditolak',
                'alasan' => $validated['alasan_tolak'],
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
            'status_verifikasi' => 'required|in:lunas,berjalan',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        $newStatus = $validated['status_verifikasi'];

        // Cegah eksekusi redundan jika status sudah sesuai
        if ($tindakLanjut->status_verifikasi === $newStatus) {
            $label = $newStatus === 'lunas' ? 'Lunas / Selesai' : 'Berjalan (Proses)';

            return redirect()
                ->route('tindak-lanjuts.show', $tindakLanjut)
                ->with('info', "Tindak lanjut sudah berstatus {$label}.");
        }

        DB::beginTransaction();
        try {
            $rekom = $tindakLanjut->recommendation;
            $isNonUang = $rekom?->isNonUang() ?? false;
            $target = (float) ($tindakLanjut->nilai_tindak_lanjut > 0
                ? $tindakLanjut->nilai_tindak_lanjut
                : ($rekom?->nilai_rekom ?? 0));

            $tindakLanjut->status_verifikasi = $newStatus;
            if (! empty($validated['catatan_verifikasi'])) {
                $tindakLanjut->catatan_verifikasi = $validated['catatan_verifikasi'];
            }
            $tindakLanjut->diverifikasi_oleh = auth()->id();
            $tindakLanjut->diverifikasi_pada = now();

            // SINKRONISASI DENGAN OPD
            $tindakLanjut->status_opd = 'dikirim';
            if (! $tindakLanjut->dikirim_pada) {
                $tindakLanjut->dikirim_pada = now();
            }
            $tindakLanjut->alasan_tolak_opd = null;

            if ($newStatus === 'lunas') {
                if ($isNonUang) {
                    $tindakLanjut->total_terbayar = 1;
                    $tindakLanjut->sisa_belum_bayar = 0;
                } else {
                    $tindakLanjut->total_terbayar = $target;
                    $tindakLanjut->sisa_belum_bayar = 0;

                    // Setujui juga cicilan yang berstatus menunggu jika ada
                    $tindakLanjut->cicilans()->where('status', TindakLanjutCicilan::STATUS_MENUNGGU)->update([
                        'status' => TindakLanjutCicilan::STATUS_DITERIMA,
                        'diverifikasi_oleh' => auth()->id(),
                        'diverifikasi_pada' => now(),
                        'updated_by' => auth()->id(),
                    ]);

                    $tindakLanjut->jumlah_cicilan_realisasi = $tindakLanjut->cicilans()
                        ->where('status', TindakLanjutCicilan::STATUS_DITERIMA)
                        ->count();
                }
            } elseif ($newStatus === 'berjalan') {
                if ($isNonUang) {
                    $tindakLanjut->total_terbayar = 0;
                    $tindakLanjut->sisa_belum_bayar = 1;
                } else {
                    if ($tindakLanjut->jenis_penyelesaian === 'cicilan') {
                        $terbayar = (float) $tindakLanjut->cicilanDiterima()->sum('nilai_bayar');
                        $tindakLanjut->total_terbayar = min($terbayar, $target);
                        $tindakLanjut->sisa_belum_bayar = max(0, $target - $tindakLanjut->total_terbayar);
                        $tindakLanjut->jumlah_cicilan_realisasi = $tindakLanjut->cicilanDiterima()->count();
                    } else {
                        // Jika jenis pembayaran bukan cicilan dan diubah ke berjalan:
                        $tindakLanjut->total_terbayar = 0;
                        $tindakLanjut->sisa_belum_bayar = $target;
                        $tindakLanjut->jumlah_cicilan_realisasi = 0;
                    }
                }
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
                ->with('success', "Tindak lanjut berhasil diperbarui menjadi {$label}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Verifikasi OPD error: '.$e->getMessage());

            return back()->with('error', 'Gagal memverifikasi tindak lanjut: '.$e->getMessage());
        }
    }

    private function syncRekomendasi(TindakLanjut $tl): void
    {
        $recommendation = $tl->recommendation;
        if (! $recommendation) {
            $tl->load('recommendation.tindakLanjuts.cicilans');
            $recommendation = $tl->recommendation;
        }

        if (! $recommendation) {
            return;
        }

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
