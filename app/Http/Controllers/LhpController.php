<?php

namespace App\Http\Controllers;

use App\Models\AuditAssignment;
use App\Models\AuditProgram;
use App\Models\KodeRekomendasi;
use App\Models\KodeTemuan;
use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Models\Temuan;
use App\Models\UnitDiperiksa;
use App\Services\LhpStatistikService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LhpController extends Controller
{
    public function __construct(private LhpStatistikService $statistikService) {}

    public function index(Request $request)
    {
        $query = Lhp::with([
            'auditAssignment.auditProgramDetail.auditProgram',
            'statistik',
            'creator',
            'unitDiperiksa',
            'unitDiperiksas',
        ])
            ->forUser(auth()->user());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lhp', 'like', "%{$search}%")
                    ->orWhereHas(
                        'auditAssignment.auditProgramDetail.auditProgram',
                        fn ($sq) => $sq->where('nama_program', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'unitDiperiksas',
                        fn ($sq) => $sq->where('nama_unit', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'unitDiperiksa',
                        fn ($sq) => $sq->where('nama_unit', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('unit_diperiksa_id')) {
            $uId = $request->unit_diperiksa_id;
            $query->where(function ($q) use ($uId) {
                $q->where('unit_diperiksa_id', $uId)
                    ->orWhereHas('unitDiperiksas', fn ($sq) => $sq->where('unit_diperiksas.id', $uId));
            });
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_lhp', $request->tahun);
        }

        if ($request->filled('kategori')) {
            $query->whereHas('auditAssignment.auditProgramDetail.auditProgram', fn ($q) => $q->where('kategori', $request->kategori)
            );
        }

        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sort) {
            switch ($sort) {
                case 'nomor_lhp':
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

                case 'progress':
                    $query->orderBy(
                        LhpStatistik::select('persen_selesai_gabungan')
                            ->whereColumn('lhp_statistik.lhp_id', 'lhps.id')
                            ->limit(1),
                        $direction
                    );
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

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 30, 50, 100])) {
            $perPage = 10;
        }

        $lhps = $query->paginate($perPage)->withQueryString();

        $kategoris = AuditProgram::KATEGORI;

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

        // Peta nomor LHP yang digunakan untuk lebih dari 1 unit (kolektif BPK/BPKP)
        $sharedNomorMap = Lhp::whereIn('nomor_lhp', $lhps->pluck('nomor_lhp'))
            ->where('status', '!=', 'dibatalkan')
            ->whereNull('deleted_at')
            ->select('nomor_lhp', DB::raw('count(*) as total'))
            ->groupBy('nomor_lhp')
            ->having('total', '>', 1)
            ->pluck('total', 'nomor_lhp')
            ->toArray();

        return view('pages.lhps.index', compact('lhps', 'kategoris', 'tahuns', 'sort', 'direction', 'perPage', 'sharedNomorMap'));
    }

    public function create()
    {
        $user = auth()->user();

        $requestedAssignmentId = request('assignment_id') ?? request('audit_assignment_id');

        $assignments = AuditAssignment::query()
            ->with([
                'auditProgramDetail.auditProgram',
                'unitDiperiksas',
            ])
            ->when($requestedAssignmentId, function ($q) use ($user, $requestedAssignmentId) {
                $q->where(function ($sub) use ($user, $requestedAssignmentId) {
                    $sub->where('id', $requestedAssignmentId)
                        ->orWhere(fn ($sq) => $sq->forUser($user));
                });
            }, function ($q) use ($user) {
                $q->forUser($user);
            })
            ->latest()
            ->limit(100)
            ->get();

        // Map (assignment_id → [unit_diperiksa_id]) yang sudah memiliki LHP non-dibatalkan
        $usedUnits = DB::table('lhp_unit_diperiksa')
            ->join('lhps', 'lhps.id', '=', 'lhp_unit_diperiksa.lhp_id')
            ->whereIn('lhps.audit_assignment_id', $assignments->pluck('id'))
            ->where('lhps.status', '!=', 'dibatalkan')
            ->whereNull('lhps.deleted_at')
            ->select('lhps.audit_assignment_id', 'lhp_unit_diperiksa.unit_diperiksa_id')
            ->get();

        $usedUnitsLegacy = Lhp::whereIn('audit_assignment_id', $assignments->pluck('id'))
            ->where('status', '!=', 'dibatalkan')
            ->whereNotNull('unit_diperiksa_id')
            ->select('audit_assignment_id', 'unit_diperiksa_id')
            ->get();

        $usedUnitMap = $usedUnits->concat($usedUnitsLegacy)
            ->groupBy('audit_assignment_id')
            ->map(fn ($items) => $items->pluck('unit_diperiksa_id')->unique()->values()->toArray())
            ->toArray();

        $kodeTemuans = KodeTemuan::orderBy('kode')->get();
        $kodeRekoms = KodeRekomendasi::where('is_active', true)->orderBy('kode')->get();

        $allPrograms = AuditProgram::withCount(['details', 'assignments'])
            ->orderBy('tahun', 'desc')
            ->orderBy('nama_program', 'asc')
            ->get();
        $kategoris = AuditProgram::KATEGORI;

        // Data Nomor LHP aktif yang sudah ada untuk opsi reuse/kolektif BPK/BPKP/ITPROV
        // Disinkronkan langsung dengan Surat Tugas (AuditAssignment) dan unit yang diperiksa
        $assignmentsWithLhp = AuditAssignment::query()
            ->with([
                'auditProgramDetail.auditProgram',
                'unitDiperiksas',
                'lhps' => function ($q) {
                    $q->where('status', '!=', 'dibatalkan')
                      ->whereNull('deleted_at')
                      ->latest('tanggal_lhp')
                      ->latest('id');
                },
                'lhps.unitDiperiksa',
                'lhps.unitDiperiksas',
            ])
            ->when($requestedAssignmentId, function ($q) use ($user, $requestedAssignmentId) {
                $q->where(function ($sub) use ($user, $requestedAssignmentId) {
                    $sub->where('id', $requestedAssignmentId)
                        ->orWhere(fn ($sq) => $sq->forUser($user));
                });
            }, function ($q) use ($user) {
                $q->forUser($user);
            })
            ->whereHas('lhps', function ($q) {
                $q->where('status', '!=', 'dibatalkan')->whereNull('deleted_at');
            })
            ->latest()
            ->get();

        $existingLhpsData = $assignmentsWithLhp->map(function ($assignment) {
            $program = $assignment->auditProgramDetail?->auditProgram;
            $allUnits = $assignment->unitDiperiksas->map(fn ($u) => [
                'id' => $u->id,
                'nama' => $u->nama_unit ?? ('Unit #' . $u->id),
            ])->values();

            // Kumpulkan seluruh unit yang sudah dibuatkan LHP untuk penugasan ini
            $usedUnitsMap = collect();
            foreach ($assignment->lhps as $lhp) {
                if ($lhp->unit_diperiksa_id) {
                    $usedUnitsMap->put($lhp->unit_diperiksa_id, [
                        'id' => $lhp->unit_diperiksa_id,
                        'nama' => $lhp->unitDiperiksa?->nama_unit ?? ('Unit #' . $lhp->unit_diperiksa_id),
                        'nomor_lhp' => $lhp->nomor_lhp,
                        'tanggal_lhp' => $lhp->tanggal_lhp ? $lhp->tanggal_lhp->format('Y-m-d') : '',
                        'lhp_id' => $lhp->id,
                    ]);
                }
                foreach ($lhp->unitDiperiksas as $pu) {
                    $usedUnitsMap->put($pu->id, [
                        'id' => $pu->id,
                        'nama' => $pu->nama_unit ?? ('Unit #' . $pu->id),
                        'nomor_lhp' => $lhp->nomor_lhp,
                        'tanggal_lhp' => $lhp->tanggal_lhp ? $lhp->tanggal_lhp->format('Y-m-d') : '',
                        'lhp_id' => $lhp->id,
                    ]);
                }
            }

            $usedUnitIds = $usedUnitsMap->keys()->toArray();

            // Unit yang masih tersisa pada Surat Tugas (belum dibuatkan LHP)
            $remainingUnits = $allUnits->filter(function ($u) use ($usedUnitIds) {
                return !in_array($u['id'], $usedUnitIds);
            })->values()->toArray();

            $primaryLhp = $assignment->lhps->first();

            return [
                'key' => (string) $assignment->id,
                'audit_assignment_id' => $assignment->id,
                'nomor_lhp' => $primaryLhp?->nomor_lhp ?? '',
                'tanggal_lhp' => $primaryLhp?->tanggal_lhp ? $primaryLhp->tanggal_lhp->format('Y-m-d') : '',
                'catatan_umum' => $primaryLhp?->catatan_umum ?? '',
                'program_id' => $program?->id,
                'kategori' => $program?->kategori ?: 'PKPT',
                'program_nama' => $program?->nama_program ?? ($assignment->nama_tim ?? 'Program Audit'),
                'nomor_surat' => $assignment->nomor_surat ?? ('Penugasan #' . $assignment->id),
                'total_units_created' => $usedUnitsMap->count(),
                'total_units_in_ass' => $allUnits->count(),
                'remaining_count' => count($remainingUnits),
                'remaining_units' => $remainingUnits,
                'completed_units' => $usedUnitsMap->values()->toArray(),
                'all_units' => $allUnits->toArray(),
            ];
        })
        ->filter(fn ($item) => ($item['remaining_count'] ?? 0) > 0)
        ->sortByDesc('remaining_count')
        ->values();

        // Penugasan audit baru yang belum memiliki LHP sama sekali (untuk opsi panduan di Jalur B)
        $unstartedAssignments = $assignments->filter(function ($a) use ($usedUnitMap) {
            $used = $usedUnitMap[$a->id] ?? [];
            return count($used) === 0 && $a->unitDiperiksas->isNotEmpty();
        })->map(function ($a) {
            $prog = $a->auditProgramDetail?->auditProgram;
            return [
                'id' => $a->id,
                'nomor_surat' => $a->nomor_surat,
                'program_id' => $prog?->id,
                'program_nama' => $prog?->nama_program ?? '-',
                'kategori' => $prog?->kategori ?: 'PKPT',
                'units_count' => $a->unitDiperiksas->count(),
            ];
        })->values();

        return view('pages.lhps.create', compact('assignments', 'kodeTemuans', 'kodeRekoms', 'usedUnitMap', 'allPrograms', 'kategoris', 'existingLhpsData', 'unstartedAssignments'));
    }

    // Contoh di Controller yang menghandle API /lhp/{id}/temuans
    public function getTemuans($lhpId)
    {
        return Temuan::with('kodeTemuan')
            ->where('lhp_id', $lhpId)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'kondisi' => $t->kondisi,
                    'nilai_temuan' => $t->nilai_temuan,
                    'alternatif_rekom' => $t->kodeTemuan ? $t->kodeTemuan->alternatif_rekom : [],
                ];
            });
    }

    public function store(Request $request)
    {
        $cleanRupiah = function ($value) {
            if (empty($value) && $value !== '0' && $value !== 0) {
                return 0;
            }
            if (is_numeric($value)) {
                return (float) $value;
            }
            $clean = str_replace('.', '', (string) $value);
            $clean = str_replace(',', '.', $clean);

            return (float) preg_replace('/[^0-9.]/', '', $clean);
        };

        // Normalisasi unit_diperiksa_ids (mendukung multi-unit ataupun single unit)
        $unitDiperiksaIds = $request->input('unit_diperiksa_ids', []);
        if (empty($unitDiperiksaIds) && $request->filled('unit_diperiksa_id')) {
            $unitDiperiksaIds = [$request->unit_diperiksa_id];
        }
        if (is_string($unitDiperiksaIds)) {
            $unitDiperiksaIds = explode(',', $unitDiperiksaIds);
        }
        $unitDiperiksaIds = array_values(array_unique(array_filter(array_map('intval', (array) $unitDiperiksaIds))));
        $primaryUnitId = ! empty($unitDiperiksaIds) ? $unitDiperiksaIds[0] : $request->unit_diperiksa_id;

        $request->merge([
            'unit_diperiksa_ids' => $unitDiperiksaIds,
            'unit_diperiksa_id' => $primaryUnitId,
        ]);

        // Bersihkan data sampah lama (nomor LHP + unit_diperiksa_id) agar tidak bentrok dengan index UNIQUE di MySQL
        if ($request->filled('nomor_lhp') && $primaryUnitId) {
            Lhp::onlyTrashed()
                ->where('nomor_lhp', trim($request->nomor_lhp))
                ->where('unit_diperiksa_id', $primaryUnitId)
                ->forceDelete();
        }
        if ($request->filled('audit_assignment_id') && ! empty($unitDiperiksaIds)) {
            Lhp::onlyTrashed()
                ->where('audit_assignment_id', $request->audit_assignment_id)
                ->whereIn('unit_diperiksa_id', $unitDiperiksaIds)
                ->forceDelete();
        }

        // Cek apakah ada unit yang dipilih sudah digunakan oleh LHP aktif lain pada penugasan yang sama
        if ($request->filled('audit_assignment_id') && ! empty($unitDiperiksaIds)) {
            $alreadyUsedUnits = DB::table('lhp_unit_diperiksa')
                ->join('lhps', 'lhps.id', '=', 'lhp_unit_diperiksa.lhp_id')
                ->where('lhps.audit_assignment_id', $request->audit_assignment_id)
                ->where('lhps.status', '!=', 'dibatalkan')
                ->whereNull('lhps.deleted_at')
                ->whereIn('lhp_unit_diperiksa.unit_diperiksa_id', $unitDiperiksaIds)
                ->join('unit_diperiksas', 'unit_diperiksas.id', '=', 'lhp_unit_diperiksa.unit_diperiksa_id')
                ->pluck('unit_diperiksas.nama_unit')
                ->unique()
                ->toArray();

            if (! empty($alreadyUsedUnits)) {
                throw ValidationException::withMessages([
                    'unit_diperiksa_id' => 'Unit berikut sudah memiliki LHP pada penugasan ini: '.implode(', ', $alreadyUsedUnits),
                ]);
            }
        }

        $validated = $request->validate([
            'audit_assignment_id' => 'required|exists:audit_assignments,id',
            'unit_diperiksa_ids' => 'required|array|min:1',
            'unit_diperiksa_ids.*' => 'exists:unit_diperiksas,id',
            'unit_diperiksa_id' => 'required|exists:unit_diperiksas,id',
            'nomor_lhp' => [
                'required',
                'string',
                Rule::unique('lhps', 'nomor_lhp')
                    ->where('unit_diperiksa_id', $primaryUnitId)
                    ->whereNull('deleted_at'),
            ],
            'tanggal_lhp' => 'required|date',
            'is_nihil' => 'nullable|boolean',
            'catatan_umum' => 'nullable|string',
            'temuans' => 'nullable|array',
            'temuans.*.kode_temuan_id' => 'nullable|exists:kode_temuans,id',
            'temuans.*.kondisi' => 'nullable|string',
            'temuans.*.sebab' => 'nullable|string',
            'temuans.*.akibat' => 'nullable|string',
            'temuans.*.nilai_kerugian_negara' => 'nullable',
            'temuans.*.nilai_kerugian_daerah' => 'nullable',
            'temuans.*.nilai_kerugian_desa' => 'nullable',
            'temuans.*.nilai_kerugian_bos_blud' => 'nullable',
            'temuans.*.recommendations' => 'nullable|array',
            'temuans.*.recommendations.*.kode_rekomendasi_id' => 'nullable|exists:kode_rekomendasis,id',
            'temuans.*.recommendations.*.uraian_rekom' => 'nullable|string',
            'temuans.*.recommendations.*.jenis_rekomendasi' => 'nullable|in:uang,barang,administrasi',
            'temuans.*.recommendations.*.nilai_rekom' => 'nullable',
            'temuans.*.recommendations.*.batas_waktu' => 'nullable|date',
            'attachments' => 'nullable|array',
            'attachments.*.file_path' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:15360',
            'attachments.*.file_name' => 'nullable|string',
            'attachments.*.keterangan' => 'nullable|string',
        ], [
            'unit_diperiksa_ids.required' => 'Pilih minimal 1 unit kerja / objek audit yang diperiksa.',
            'unit_diperiksa_id.required' => 'Pilih minimal 1 unit kerja / objek audit yang diperiksa.',
            'nomor_lhp.unique' => 'Nomor LHP sudah digunakan oleh LHP lain.',
        ]);

        $assignment = AuditAssignment::with('unitDiperiksas')->findOrFail($validated['audit_assignment_id']);

        // 1. Authorization: Non-super admin must be authorized
        $user = auth()->user();
        if (! $user->hasRole(['super_admin', 'kepala_inspektorat', 'staff_inspektorat'])) {
            $irbanNames = ($user->isPengendaliTeknis() || $user->isIrban()) ? $user->getIrbanTeamNames() : [];
            $isAuthorized = $assignment->ketua_tim_id === $user->id
                || $assignment->pengendali_teknis_id === $user->id
                || $assignment->pengendali_teknis === $user->name
                || $assignment->created_by === $user->id
                || $assignment->lhps()->where('created_by', $user->id)->exists()
                || $assignment->members()->where('user_id', $user->id)->exists()
                || (! empty($irbanNames) && in_array($assignment->auditProgramDetail?->tim, $irbanNames));
            if (! $isAuthorized) {
                abort(403, 'Anda tidak memiliki wewenang untuk membuat LHP pada penugasan ini.');
            }
        }

        // 2. Relational Integrity: Unit kerja must belong to this assignment
        $validUnitIds = $assignment->unitDiperiksas->pluck('id')->toArray();
        if (! empty($validUnitIds)) {
            $invalidUnits = array_diff($unitDiperiksaIds, $validUnitIds);
            if (! empty($invalidUnits)) {
                throw ValidationException::withMessages([
                    'unit_diperiksa_id' => 'Unit kerja yang dipilih tidak terdaftar dalam Surat Tugas / Penugasan ini.',
                ]);
            }
        }

        try {
            DB::beginTransaction();

            $isNihil = $request->boolean('is_nihil');

            $lhp = Lhp::create([
                'audit_assignment_id' => $validated['audit_assignment_id'],
                'unit_diperiksa_id' => $primaryUnitId,
                'nomor_lhp' => $validated['nomor_lhp'],
                'tanggal_lhp' => $validated['tanggal_lhp'],
                'is_nihil' => $isNihil,
                'catatan_umum' => $validated['catatan_umum'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Sinkronkan seluruh unit kerja yang dipilih ke pivot lhp_unit_diperiksa
            $lhp->unitDiperiksas()->sync($unitDiperiksaIds);

            if (! $isNihil && ! empty($request->temuans)) {
                foreach ($request->temuans as $temuan) {
                    if (empty($temuan['kode_temuan_id']) && empty($temuan['kondisi'])) {
                        continue;
                    }

                    $negara = $cleanRupiah($temuan['nilai_kerugian_negara'] ?? 0);
                    $daerah = $cleanRupiah($temuan['nilai_kerugian_daerah'] ?? 0);
                    $desa = $cleanRupiah($temuan['nilai_kerugian_desa'] ?? 0);
                    $bosBLud = $cleanRupiah($temuan['nilai_kerugian_bos_blud'] ?? 0);
                    $total = $negara + $daerah + $desa + $bosBLud;

                    $createdTemuan = $lhp->temuans()->create([
                        'kode_temuan_id' => $temuan['kode_temuan_id'] ?? null,
                        'kondisi' => ! empty(trim($temuan['kondisi'] ?? '')) ? trim($temuan['kondisi']) : '-',
                        'sebab' => $temuan['sebab'] ?? null,
                        'akibat' => $temuan['akibat'] ?? null,
                        'nilai_kerugian_negara' => $negara,
                        'nilai_kerugian_daerah' => $daerah,
                        'nilai_kerugian_desa' => $desa,
                        'nilai_kerugian_bos_blud' => $bosBLud,
                        'nilai_temuan' => $total,
                        'status_tl' => 'belum_ditindaklanjuti',
                    ]);

                    if (! empty($temuan['recommendations']) && is_array($temuan['recommendations'])) {
                        foreach ($temuan['recommendations'] as $rekom) {
                            if (empty($rekom['kode_rekomendasi_id']) && empty($rekom['uraian_rekom'])) {
                                continue;
                            }

                            $jenisRekom = $rekom['jenis_rekomendasi'] ?? 'administrasi';
                            $nilaiRekom = ($jenisRekom === 'uang') ? $cleanRupiah($rekom['nilai_rekom'] ?? 0) : 0;

                            $createdTemuan->recommendations()->create([
                                'kode_rekomendasi_id' => $rekom['kode_rekomendasi_id'] ?? null,
                                'uraian_rekom' => ! empty(trim($rekom['uraian_rekom'] ?? '')) ? trim($rekom['uraian_rekom']) : '-',
                                'jenis_rekomendasi' => $jenisRekom,
                                'nilai_rekom' => $nilaiRekom,
                                'nilai_sisa' => $nilaiRekom,
                                'batas_waktu' => ! empty($rekom['batas_waktu']) ? $rekom['batas_waktu'] : now()->addDays(60)->toDateString(),
                                'status' => 'belum_ditindaklanjuti',
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            if (! empty($request->attachments)) {
                foreach ($request->attachments as $item) {
                    if (isset($item['file_path']) && $item['file_path'] instanceof UploadedFile) {
                        $path = $item['file_path']->store('lhp/attachments', 'local');
                        $lhp->attachments()->create([
                            'file_path' => $path,
                            'file_name' => $item['file_name'] ?? $item['file_path']->getClientOriginalName(),
                            'keterangan' => $item['keterangan'] ?? null,
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
                    'success' => true,
                    'message' => "LHP nomor {$lhp->nomor_lhp} beserta temuan dan rekomendasi berhasil dibuat.",
                    'redirect' => route('lhps.index'),
                ]);
            }

            return redirect()->route('lhps.index')
                ->with('success', "LHP nomor {$lhp->nomor_lhp} beserta temuan dan rekomendasi berhasil dibuat.");

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Gagal menyimpan LHP: '.$e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan LHP: '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan LHP: '.$e->getMessage());
        }
    }

    public function show(Lhp $lhp)
    {
        // Enforce canonical UUID in URL
        $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(2);
        if ($rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
            return redirect()->route('lhps.show', $lhp, 301);
        }

        $lhp->load([
            'temuans.kodeTemuan',
            'temuans.recommendations.kodeRekomendasi',
            'temuans.recommendations.tindakLanjuts',
            'auditAssignment.auditProgramDetail.auditProgram',
            'auditAssignment.unitDiperiksas',
            'unitDiperiksa',
            'unitDiperiksas',
            'attachments',
            'statistik',
            'creator',
        ]);

        return view('pages.lhps.show', compact('lhp'));
    }

    public function edit(Lhp $lhp)
    {
        // Enforce canonical UUID in URL
        $rawParam = request()->route()?->originalParameter('lhp') ?? request()->segment(2);
        if ($rawParam && ($rawParam !== $lhp->uuid || is_numeric($rawParam))) {
            return redirect()->route('lhps.edit', $lhp, 301);
        }

        $user = auth()->user();

        $assignments = AuditAssignment::with(['auditProgramDetail.auditProgram', 'unitDiperiksas'])
            ->where(function ($q) use ($user, $lhp) {
                $q->where('id', $lhp->audit_assignment_id)
                    ->orWhere(fn ($sq) => $sq->forUser($user));
            })->get();

        $usedUnits = DB::table('lhp_unit_diperiksa')
            ->join('lhps', 'lhps.id', '=', 'lhp_unit_diperiksa.lhp_id')
            ->where('lhps.id', '!=', $lhp->id)
            ->whereIn('lhps.audit_assignment_id', $assignments->pluck('id'))
            ->where('lhps.status', '!=', 'dibatalkan')
            ->whereNull('lhps.deleted_at')
            ->select('lhps.audit_assignment_id', 'lhp_unit_diperiksa.unit_diperiksa_id')
            ->get();

        $usedUnitsLegacy = Lhp::where('id', '!=', $lhp->id)
            ->whereIn('audit_assignment_id', $assignments->pluck('id'))
            ->where('status', '!=', 'dibatalkan')
            ->whereNotNull('unit_diperiksa_id')
            ->whereNull('deleted_at')
            ->select('audit_assignment_id', 'unit_diperiksa_id')
            ->get();

        $usedUnitMap = $usedUnits->concat($usedUnitsLegacy)
            ->groupBy('audit_assignment_id')
            ->map(fn ($items) => $items->pluck('unit_diperiksa_id')->unique()->values()->toArray())
            ->toArray();

        $kodeTemuans = KodeTemuan::orderBy('kode')->get();
        $kodeRekoms = KodeRekomendasi::where('is_active', true)->orderBy('kode')->get();

        $allPrograms = AuditProgram::withCount(['details', 'assignments'])
            ->orderBy('tahun', 'desc')
            ->orderBy('nama_program', 'asc')
            ->get();
        $kategoris = AuditProgram::KATEGORI;

        $lhp->load([
            'temuans.kodeTemuan',
            'temuans.recommendations.kodeRekomendasi',
            'auditAssignment.auditProgramDetail.auditProgram',
            'unitDiperiksas',
            'attachments',
        ]);

        return view('pages.lhps.edit', compact('lhp', 'assignments', 'kodeTemuans', 'kodeRekoms', 'usedUnitMap', 'allPrograms', 'kategoris'));
    }

    public function update(Request $request, Lhp $lhp)
    {
        $cleanRupiah = function ($value) {
            if (empty($value) && $value !== '0' && $value !== 0) {
                return 0;
            }
            if (is_numeric($value)) {
                return (float) $value;
            }
            $clean = str_replace('.', '', (string) $value);
            $clean = str_replace(',', '.', $clean);

            return (float) preg_replace('/[^0-9.]/', '', $clean);
        };

        // Normalisasi unit_diperiksa_ids
        $unitDiperiksaIds = $request->input('unit_diperiksa_ids', []);
        if (empty($unitDiperiksaIds) && $request->filled('unit_diperiksa_id')) {
            $unitDiperiksaIds = [$request->unit_diperiksa_id];
        }
        if (is_string($unitDiperiksaIds)) {
            $unitDiperiksaIds = explode(',', $unitDiperiksaIds);
        }
        $unitDiperiksaIds = array_values(array_unique(array_filter(array_map('intval', (array) $unitDiperiksaIds))));
        $primaryUnitId = ! empty($unitDiperiksaIds) ? $unitDiperiksaIds[0] : ($request->unit_diperiksa_id ?: $lhp->unit_diperiksa_id);

        $request->merge([
            'unit_diperiksa_ids' => $unitDiperiksaIds,
            'unit_diperiksa_id' => $primaryUnitId,
        ]);

        if ($request->filled('nomor_lhp') && $primaryUnitId) {
            Lhp::onlyTrashed()
                ->where('nomor_lhp', trim($request->nomor_lhp))
                ->where('unit_diperiksa_id', $primaryUnitId)
                ->where('id', '!=', $lhp->id)
                ->forceDelete();
        }
        if ($request->filled('audit_assignment_id') && ! empty($unitDiperiksaIds)) {
            Lhp::onlyTrashed()
                ->where('audit_assignment_id', $request->audit_assignment_id)
                ->whereIn('unit_diperiksa_id', $unitDiperiksaIds)
                ->where('id', '!=', $lhp->id)
                ->forceDelete();
        }

        // Cek apakah ada unit yang dipilih sudah digunakan oleh LHP aktif lain pada penugasan yang sama
        if ($request->filled('audit_assignment_id') && ! empty($unitDiperiksaIds)) {
            $alreadyUsedUnits = DB::table('lhp_unit_diperiksa')
                ->join('lhps', 'lhps.id', '=', 'lhp_unit_diperiksa.lhp_id')
                ->where('lhps.audit_assignment_id', $request->audit_assignment_id)
                ->where('lhps.id', '!=', $lhp->id)
                ->where('lhps.status', '!=', 'dibatalkan')
                ->whereNull('lhps.deleted_at')
                ->whereIn('lhp_unit_diperiksa.unit_diperiksa_id', $unitDiperiksaIds)
                ->join('unit_diperiksas', 'unit_diperiksas.id', '=', 'lhp_unit_diperiksa.unit_diperiksa_id')
                ->pluck('unit_diperiksas.nama_unit')
                ->unique()
                ->toArray();

            $alreadyUsedUnitsLegacy = Lhp::where('audit_assignment_id', $request->audit_assignment_id)
                ->where('id', '!=', $lhp->id)
                ->where('status', '!=', 'dibatalkan')
                ->whereNull('deleted_at')
                ->whereIn('unit_diperiksa_id', $unitDiperiksaIds)
                ->with('unitDiperiksa')
                ->get()
                ->pluck('unitDiperiksa.nama_unit')
                ->filter()
                ->unique()
                ->toArray();

            $alreadyUsedAll = array_unique(array_merge($alreadyUsedUnits, $alreadyUsedUnitsLegacy));

            if (! empty($alreadyUsedAll)) {
                throw ValidationException::withMessages([
                    'unit_diperiksa_id' => 'Unit berikut sudah memiliki LHP pada penugasan ini: '.implode(', ', $alreadyUsedAll),
                ]);
            }
        }

        $validated = $request->validate([
            'audit_assignment_id' => 'required|exists:audit_assignments,id',
            'unit_diperiksa_ids' => 'required|array|min:1',
            'unit_diperiksa_ids.*' => 'exists:unit_diperiksas,id',
            'unit_diperiksa_id' => 'required|exists:unit_diperiksas,id',
            'nomor_lhp' => [
                'required',
                'string',
                Rule::unique('lhps', 'nomor_lhp')
                    ->ignore($lhp->id)
                    ->where('unit_diperiksa_id', $primaryUnitId)
                    ->whereNull('deleted_at'),
            ],
            'tanggal_lhp' => 'required|date',
            'is_nihil' => 'nullable|boolean',
            'catatan_umum' => 'nullable|string',
            'temuans' => 'nullable|array',
            'temuans.*.id' => 'nullable',
            'temuans.*.kode_temuan_id' => 'nullable|exists:kode_temuans,id',
            'temuans.*.kondisi' => 'nullable|string',
            'temuans.*.sebab' => 'nullable|string',
            'temuans.*.akibat' => 'nullable|string',
            'temuans.*.nilai_kerugian_negara' => 'nullable',
            'temuans.*.nilai_kerugian_daerah' => 'nullable',
            'temuans.*.nilai_kerugian_desa' => 'nullable',
            'temuans.*.nilai_kerugian_bos_blud' => 'nullable',
            'temuans.*.recommendations' => 'nullable|array',
            'temuans.*.recommendations.*.id' => 'nullable',
            'temuans.*.recommendations.*.kode_rekomendasi_id' => 'nullable|exists:kode_rekomendasis,id',
            'temuans.*.recommendations.*.uraian_rekom' => 'nullable|string',
            'temuans.*.recommendations.*.jenis_rekomendasi' => 'nullable|in:uang,barang,administrasi',
            'temuans.*.recommendations.*.nilai_rekom' => 'nullable',
            'temuans.*.recommendations.*.batas_waktu' => 'nullable|date',
            'attachments' => 'nullable|array',
            'attachments.*.file_path' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:15360',
            'attachments.*.file_name' => 'nullable|string',
            'attachments.*.keterangan' => 'nullable|string',
        ], [
            'unit_diperiksa_ids.required' => 'Pilih minimal 1 unit kerja / objek audit yang diperiksa.',
            'unit_diperiksa_id.required' => 'Pilih minimal 1 unit kerja / objek audit yang diperiksa.',
            'nomor_lhp.unique' => 'Nomor LHP sudah digunakan oleh LHP lain.',
        ]);

        $assignment = AuditAssignment::with('unitDiperiksas')->findOrFail($validated['audit_assignment_id']);

        // 1. Authorization: Non-super admin must be authorized
        $user = auth()->user();
        if (! $user->hasRole(['super_admin', 'kepala_inspektorat', 'staff_inspektorat'])) {
            $irbanNames = ($user->isPengendaliTeknis() || $user->isIrban()) ? $user->getIrbanTeamNames() : [];
            $isAuthorized = $lhp->created_by === $user->id
                || $assignment->ketua_tim_id === $user->id
                || $assignment->pengendali_teknis_id === $user->id
                || $assignment->pengendali_teknis === $user->name
                || $assignment->created_by === $user->id
                || $assignment->members()->where('user_id', $user->id)->exists()
                || (! empty($irbanNames) && in_array($assignment->auditProgramDetail?->tim, $irbanNames));
            if (! $isAuthorized) {
                abort(403, 'Anda tidak memiliki wewenang untuk mengubah LHP ini.');
            }
        }

        // 2. Relational Integrity: Unit kerja must belong to this assignment
        $validUnitIds = $assignment->unitDiperiksas->pluck('id')->toArray();
        if (! empty($validUnitIds)) {
            $invalidUnits = array_diff($unitDiperiksaIds, $validUnitIds);
            if (! empty($invalidUnits)) {
                throw ValidationException::withMessages([
                    'unit_diperiksa_id' => 'Unit kerja yang dipilih tidak terdaftar dalam Surat Tugas / Penugasan ini.',
                ]);
            }
        }

        try {
            DB::beginTransaction();

            $isNihil = $request->boolean('is_nihil');
            $updateData = collect($validated)->except(['temuans', 'attachments', 'unit_diperiksa_ids'])->toArray();
            $updateData['unit_diperiksa_id'] = $primaryUnitId;
            $updateData['is_nihil'] = $isNihil;

            $lhp->update($updateData);

            // Sinkronkan seluruh unit kerja yang dipilih ke pivot lhp_unit_diperiksa
            $lhp->unitDiperiksas()->sync($unitDiperiksaIds);

            if ($isNihil) {
                // Jika diubah menjadi NIHIL, hapus seluruh temuan & rekomendasi eksisting
                $lhp->temuans()->each(function ($oldTemuan) {
                    $oldTemuan->recommendations()->each(function ($rekom) {
                        $rekom->tindakLanjuts()->delete();
                    });
                    $oldTemuan->delete();
                });
            } elseif ($request->has('temuans')) {
                $existingIds = collect($request->temuans)->pluck('id')->filter()->toArray();

                $lhp->temuans()->whereNotIn('id', $existingIds)->each(function ($oldTemuan) {
                    $oldTemuan->recommendations()->each(function ($rekom) {
                        $rekom->tindakLanjuts()->delete();
                    });
                    $oldTemuan->delete();
                });

                foreach ($request->temuans as $temuan) {
                    $negara = $cleanRupiah($temuan['nilai_kerugian_negara'] ?? 0);
                    $daerah = $cleanRupiah($temuan['nilai_kerugian_daerah'] ?? 0);
                    $desa = $cleanRupiah($temuan['nilai_kerugian_desa'] ?? 0);
                    $bosBLud = $cleanRupiah($temuan['nilai_kerugian_bos_blud'] ?? 0);
                    $totalNilaiBaru = $negara + $daerah + $desa + $bosBLud;

                    $targetTemuan = null;

                    if (! empty($temuan['id'])) {
                        $existing = $lhp->temuans()->find($temuan['id']);
                        if ($existing) {
                            $existing->update([
                                'kode_temuan_id' => $temuan['kode_temuan_id'] ?? null,
                                'kondisi' => ! empty(trim($temuan['kondisi'] ?? '')) ? trim($temuan['kondisi']) : '-',
                                'sebab' => $temuan['sebab'] ?? null,
                                'akibat' => $temuan['akibat'] ?? null,
                                'nilai_kerugian_negara' => $negara,
                                'nilai_kerugian_daerah' => $daerah,
                                'nilai_kerugian_desa' => $desa,
                                'nilai_kerugian_bos_blud' => $bosBLud,
                                'nilai_temuan' => $totalNilaiBaru,
                            ]);
                            $targetTemuan = $existing;
                        }
                    } else {
                        if (empty($temuan['kode_temuan_id']) && empty($temuan['kondisi'])) {
                            continue;
                        }

                        $targetTemuan = $lhp->temuans()->create([
                            'kode_temuan_id' => $temuan['kode_temuan_id'] ?? null,
                            'kondisi' => ! empty(trim($temuan['kondisi'] ?? '')) ? trim($temuan['kondisi']) : '-',
                            'sebab' => $temuan['sebab'] ?? null,
                            'akibat' => $temuan['akibat'] ?? null,
                            'nilai_kerugian_negara' => $negara,
                            'nilai_kerugian_daerah' => $daerah,
                            'nilai_kerugian_desa' => $desa,
                            'nilai_kerugian_bos_blud' => $bosBLud,
                            'nilai_temuan' => $totalNilaiBaru,
                            'status_tl' => 'belum_ditindaklanjuti',
                        ]);
                    }

                    if ($targetTemuan) {
                        $incomingRekomIds = collect($temuan['recommendations'] ?? [])->pluck('id')->filter()->toArray();
                        $targetTemuan->recommendations()->whereNotIn('id', $incomingRekomIds)->each(function ($oldRekom) {
                            $oldRekom->tindakLanjuts()->delete();
                            $oldRekom->delete();
                        });

                        if (! empty($temuan['recommendations']) && is_array($temuan['recommendations'])) {
                            foreach ($temuan['recommendations'] as $rekom) {
                                if (empty($rekom['kode_rekomendasi_id']) && empty($rekom['uraian_rekom'])) {
                                    continue;
                                }

                                $jenisRekom = $rekom['jenis_rekomendasi'] ?? 'administrasi';
                                $nilaiRekom = ($jenisRekom === 'uang') ? $cleanRupiah($rekom['nilai_rekom'] ?? 0) : 0;

                                if (! empty($rekom['id'])) {
                                    $existingRekom = $targetTemuan->recommendations()->find($rekom['id']);
                                    if ($existingRekom) {
                                        $existingRekom->update([
                                            'kode_rekomendasi_id' => $rekom['kode_rekomendasi_id'] ?? null,
                                            'uraian_rekom' => ! empty(trim($rekom['uraian_rekom'] ?? '')) ? trim($rekom['uraian_rekom']) : '-',
                                            'jenis_rekomendasi' => $jenisRekom,
                                            'nilai_rekom' => $nilaiRekom,
                                            'nilai_sisa' => max(0, $nilaiRekom - (float) ($existingRekom->nilai_tl_selesai ?? 0)),
                                            'batas_waktu' => ! empty($rekom['batas_waktu']) ? $rekom['batas_waktu'] : $existingRekom->batas_waktu,
                                        ]);
                                    }
                                } else {
                                    $targetTemuan->recommendations()->create([
                                        'kode_rekomendasi_id' => $rekom['kode_rekomendasi_id'] ?? null,
                                        'uraian_rekom' => ! empty(trim($rekom['uraian_rekom'] ?? '')) ? trim($rekom['uraian_rekom']) : '-',
                                        'jenis_rekomendasi' => $jenisRekom,
                                        'nilai_rekom' => $nilaiRekom,
                                        'nilai_sisa' => $nilaiRekom,
                                        'batas_waktu' => ! empty($rekom['batas_waktu']) ? $rekom['batas_waktu'] : now()->addDays(60)->toDateString(),
                                        'status' => 'belum_ditindaklanjuti',
                                        'created_by' => auth()->id(),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // SIMPAN ATTACHMENT BARU
            if ($request->has('attachments')) {
                foreach ($request->attachments as $item) {
                    if (
                        isset($item['file_path']) &&
                        $item['file_path'] instanceof UploadedFile
                    ) {
                        $path = $item['file_path']->store('lhp/attachments', 'local');
                        $lhp->attachments()->create([
                            'file_path' => $path,
                            'file_name' => $item['file_name'] ?? $item['file_path']->getClientOriginalName(),
                            'keterangan' => $item['keterangan'] ?? null,
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
                    'success' => true,
                    'message' => 'LHP berhasil diperbarui dan statistik disinkronkan.',
                    'redirect' => route('lhps.index'),
                ]);
            }

            return redirect()->route('lhps.index')
                ->with('success', 'LHP berhasil diperbarui dan statistik disinkronkan.');

        } catch (ValidationException $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat kesalahan validasi data.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Gagal update LHP: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'lhp_id' => $lhp->id,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui LHP: '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui LHP: '.$e->getMessage());
        }
    }

    public function destroy(Lhp $lhp)
    {
        $user = auth()->user();
        if (! $user->hasRole('super_admin')) {
            abort(403, 'Hanya Administrator yang memiliki wewenang untuk menghapus data LHP.');
        }

        // Hapus berkas lampiran LHP fisik
        foreach ($lhp->attachments as $file) {
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        // Hapus lampiran temuan & tindak lanjut
        foreach ($lhp->temuans as $temuan) {
            foreach ($temuan->attachments as $file) {
                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
                $file->delete();
            }
            foreach ($temuan->recommendations as $rec) {
                foreach ($rec->tindakLanjuts as $tl) {
                    foreach ($tl->attachments as $file) {
                        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                            Storage::disk('public')->delete($file->file_path);
                        }
                        $file->delete();
                    }
                    $tl->cicilans()->delete();
                }
            }
        }

        // Hapus permanen (forceDelete) agar nomor_lhp tidak mengunci unik dan cascade DB berjalan
        $lhp->forceDelete();

        return redirect()->route('lhps.index')
            ->with('success', 'LHP berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        if (! $user->hasRole('super_admin')) {
            abort(403, 'Hanya Administrator yang memiliki wewenang untuk menghapus data LHP.');
        }

        if (! $request->ids) {
            return back()->with('error', 'Pilih data dulu.');
        }

        Lhp::where(function ($q) use ($request) {
            $q->whereIn('id', $request->ids)
                ->orWhereIn('uuid', $request->ids);
        })
            ->with(['attachments', 'temuans.attachments', 'temuans.recommendations.tindakLanjuts.attachments'])
            ->chunk(50, function ($lhps) {
                foreach ($lhps as $lhp) {
                    foreach ($lhp->attachments as $file) {
                        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                            Storage::disk('public')->delete($file->file_path);
                        }
                        $file->delete();
                    }
                    foreach ($lhp->temuans as $temuan) {
                        foreach ($temuan->attachments as $file) {
                            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                                Storage::disk('public')->delete($file->file_path);
                            }
                            $file->delete();
                        }
                        foreach ($temuan->recommendations as $rec) {
                            foreach ($rec->tindakLanjuts as $tl) {
                                foreach ($tl->attachments as $file) {
                                    if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                                        Storage::disk('public')->delete($file->file_path);
                                    }
                                    $file->delete();
                                }
                                $tl->cicilans()->delete();
                            }
                        }
                    }
                    $lhp->forceDelete();
                }
            });

        return redirect()->route('lhps.index')
            ->with('success', count($request->ids).' data LHP berhasil dihapus.');
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

            if (! $lhp) {
                return redirect()->route('tracking.public')
                    ->with('error', 'Nomor LHP tidak ditemukan dalam sistem kami.')
                    ->withInput();
            }
        }

        return view('pages.tracking', compact('lhp', 'search'));
    }
}
