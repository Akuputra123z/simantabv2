@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .ts-assignment-tall .ts-control,
    .ts-wrapper .ts-control {
        height: auto !important;
        min-height: 42px;
        padding: 6px 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .ts-wrapper .ts-control .item {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: unset !important;
        max-width: 100% !important;
        word-break: break-word !important;
        line-height: 1.4 !important;
    }
</style>

@php
    $programData = ($allPrograms ?? collect())->map(function ($p) {
        $kat = trim($p->kategori ?? '');
        return [
            'id'                => $p->id,
            'kategori'          => !empty($kat) ? $kat : 'PKPT',
            'nama'              => $p->nama_program,
            'tahun'             => $p->tahun,
            'details_count'     => (int)($p->details_count ?? 0),
            'assignments_count' => (int)($p->assignments_count ?? 0),
        ];
    });

    $kategoriList = $kategoris ?? \App\Models\AuditProgram::KATEGORI;

    $assignmentData = $assignments->map(function ($a) {
        return [
            'id'                      => $a->id,
            'kategori'                => $a->auditProgramDetail?->auditProgram?->kategori ?? 'PKPT',
            'audit_program_detail_id' => $a->audit_program_detail_id,
            'program_id'              => $a->auditProgramDetail?->audit_program_id,
            'program_nama'            => $a->auditProgramDetail?->auditProgram?->nama_program ?? '-',
            'detail_nama'             => $a->auditProgramDetail?->nama_detail_program ?? '-',
            'nomor_surat'             => $a->nomor_surat,
            'units'                   => $a->unitDiperiksas->map(fn($u) => [
                'id'   => $u->id,
                'nama' => $u->nama_unit,
            ])->values(),
        ];
    });

    $kodeTemuanData = $kodeTemuans->map(function ($k) {
        $rawAlt = $k->alternatif_rekom;
        if ($rawAlt instanceof \Illuminate\Support\Collection) {
            $rawAlt = $rawAlt->toArray();
        } elseif (!is_array($rawAlt)) {
            $rawAlt = [];
        }
        return [
            'id'               => $k->id,
            'kode'             => $k->kode,
            'nama'             => $k->pernyataan ?? $k->nama ?? '',
            'deskripsi'        => $k->deskripsi ?? null,
            'alternatif_rekom' => array_map('strval', $rawAlt),
        ];
    });

    $kodeRekomData = $kodeRekoms->map(fn($r) => [
        'id'           => $r->id,
        'kode'         => $r->kode,
        'kode_numerik' => (string)($r->kode_numerik ?? $r->kode ?? ''),
        'deskripsi'    => $r->deskripsi ?? '',
    ]);

    $csrfToken = csrf_token();
@endphp

<script>
const ALL_ASSIGNMENTS = @json($assignmentData);
const ALL_PROGRAMS    = @json($programData);
const KATEGORI_LIST   = @json($kategoriList);
const KODE_TEMUANS    = @json($kodeTemuanData);
const KODE_REKOMS     = @json($kodeRekomData);
const USED_UNIT_MAP   = @json($usedUnitMap);
const EXISTING_LHPS   = @json($existingLhpsData ?? []);
const UNSTARTED_ASS   = @json($unstartedAssignments ?? []);
const CSRF_TOKEN      = '{{ $csrfToken }}';

function lhpCreateWizard() {
    return {
        step: 1,

        // Step 1 Form Data & Kategori Filter
        selectedKategori: '',
        kategoriWarningMessage: '',
        kategoriWarningUrl: '',
        kategoriWarningBtn: '',
        lhpInputMode: '{{ old("lhp_input_mode", "new") }}', // 'new' | 'existing'
        selectedExistingLhpKey: '{{ old("existing_lhp_key", "") }}',
        selectedExistingLhpNo: '',
        selectedExistingDoc: null,
        manualNomorLhp: '{{ old("nomor_lhp", "") }}',
        manualTanggalLhp: '{{ old("tanggal_lhp", date("Y-m-d")) }}',
        programId: '',
        assignmentId: '{{ old("audit_assignment_id", request("assignment_id", request("audit_assignment_id", ""))) }}',
        unitId: '{{ old("unit_diperiksa_id", "") }}',
        unitIds: {!! json_encode(array_values(array_map('strval', (array)old('unit_diperiksa_ids', old('unit_diperiksa_id') ? [old('unit_diperiksa_id')] : [])))) !!},
        availableUnits: [],
        availableExistingUnits: [],
        currentKategori: '',
        isExternalAudit: false,
        nomorLhp: @json(old("nomor_lhp", "")),
        tanggalLhp: @json(old("tanggal_lhp", date("Y-m-d"))),
        catatanUmum: @json(old("catatan_umum", "")),
        isNihil: {{ old("is_nihil", 0) ? 'true' : 'false' }},

        get selectedProgramObj() {
            return ALL_PROGRAMS.find(p => String(p.id) === String(this.programId)) || null;
        },

        get selectedAssignmentObj() {
            return ALL_ASSIGNMENTS.find(a => String(a.id) === String(this.assignmentId)) || null;
        },

        get selectedUnitObj() {
            const list = this.lhpInputMode === 'existing' ? this.availableExistingUnits : this.availableUnits;
            return list.find(u => String(u.id) === String(this.unitId)) || null;
        },

        get selectedUnitName() {
            const u = this.selectedUnitObj;
            return u ? u.nama : '';
        },

        getProgramCountByKategori(kat) {
            if (!kat) return ALL_PROGRAMS.length;
            return ALL_PROGRAMS.filter(p => (p.kategori || '').toUpperCase() === kat.toUpperCase()).length;
        },

        getExistingLhpCountByKategori(kat) {
            if (!kat) return (EXISTING_LHPS || []).length;
            return (EXISTING_LHPS || []).filter(item => (item.kategori || '').toUpperCase() === kat.toUpperCase()).length;
        },

        get activeFilterKategori() {
            if (this.selectedKategori) return this.selectedKategori;
            if (this.programId) {
                const prog = ALL_PROGRAMS.find(p => String(p.id) === String(this.programId));
                if (prog && prog.kategori) return prog.kategori;
            }
            return '';
        },

        get filteredExistingLhps() {
            const kat = this.selectedKategori;
            let list = EXISTING_LHPS;
            if (kat) {
                list = list.filter(item => (item.kategori || '').toUpperCase() === kat.toUpperCase());
            }
            return list.slice().sort((a, b) => {
                if (!kat && (a.kategori || '') !== (b.kategori || '')) {
                    return (a.kategori || '').localeCompare(b.kategori || '');
                }
                if ((b.tanggal_lhp || '') !== (a.tanggal_lhp || '')) {
                    return (b.tanggal_lhp || '').localeCompare(a.tanggal_lhp || '');
                }
                return (a.nomor_lhp || '').localeCompare(b.nomor_lhp || '');
            });
        },

        get formatRecommendation() {
            const kat = (this.currentKategori || this.selectedKategori || 'PKPT').toUpperCase();
            const year = new Date().getFullYear();
            if (kat === 'BPK') {
                return {
                    pattern: 'XX/LHP/XIX.PLK/05/' + year,
                    label: 'Format BPK',
                    sample: '12/LHP/XIX.PLK/05/' + year
                };
            } else if (kat === 'BPKP') {
                return {
                    pattern: 'PE.XX.XX/LHP-XXX/PW.XX/' + year,
                    label: 'Format BPKP',
                    sample: 'PE.01.03/LHP-045/PW.14/' + year
                };
            } else if (kat === 'ITPROV') {
                return {
                    pattern: '700.1.2.1/XXX/ITPROV/' + year,
                    label: 'Format ITPROV',
                    sample: '700.1.2.1/021/ITPROV/' + year
                };
            } else {
                return {
                    pattern: '700.1.2.1/XXX/ITDA/' + year,
                    label: 'Format Standar ITDA / IRBAN',
                    sample: '700.1.2.1/045/ITDA/' + year
                };
            }
        },

        applyFormatSample(sample) {
            this.nomorLhp = sample;
            this.manualNomorLhp = sample;
        },

        filterKategori(kat) {
            this.selectedKategori = kat;
            
            if (this.lhpInputMode === 'new') {
                if (this.programId) {
                    const prog = ALL_PROGRAMS.find(p => String(p.id) === String(this.programId));
                    if (prog && kat && (prog.kategori || '').toUpperCase() !== kat.toUpperCase()) {
                        this.programId = '';
                        this.assignmentId = '';
                        this.unitId = '';
                        this.unitIds = [];
                        if (window.tsProgramInstance) window.tsProgramInstance.setValue('', true);
                        if (window.tsAssignmentInstance) {
                            window.tsAssignmentInstance.setValue('', true);
                            window.tsAssignmentInstance.disable();
                        }
                        if (window.tsUnitInstance) {
                            window.tsUnitInstance.setValue('', true);
                            window.tsUnitInstance.disable();
                        }
                    }
                }
                if (window.populateProgramsCascade) {
                    window.populateProgramsCascade(kat);
                }
            } else if (this.lhpInputMode === 'existing') {
                if (window.populateExistingLhpOptions) {
                    window.populateExistingLhpOptions(kat);
                }
            }
        },

        setLhpInputMode(mode, targetAssId = null) {
            this.lhpInputMode = mode;
            if (mode === 'new') {
                this.selectedExistingLhpKey = '';
                this.selectedExistingLhpNo = '';
                this.selectedExistingDoc = null;
                this.nomorLhp = this.manualNomorLhp || '';
                this.tanggalLhp = this.manualTanggalLhp || '{{ date("Y-m-d") }}';
                this.unitId = '';
                this.unitIds = [];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';
                if (window.tsExistingInstance) {
                    window.tsExistingInstance.setValue('', true);
                }
                if (window.tsExistingUnitInstance) {
                    window.tsExistingUnitInstance.setValue('', true);
                }
                if (targetAssId) {
                    this.assignmentId = String(targetAssId);
                    const ass = ALL_ASSIGNMENTS.find(a => String(a.id) === String(targetAssId));
                    if (ass) {
                        this.programId = String(ass.program_id);
                        if (window.tsProgramInstance) window.tsProgramInstance.setValue(this.programId, false);
                        setTimeout(() => {
                            if (window.tsAssignmentInstance) window.tsAssignmentInstance.setValue(this.assignmentId, false);
                        }, 50);
                    }
                }
            } else if (mode === 'existing') {
                if (this.nomorLhp && !this.selectedExistingLhpNo) {
                    this.manualNomorLhp = this.nomorLhp;
                }
                if (this.tanggalLhp) {
                    this.manualTanggalLhp = this.tanggalLhp;
                }
                this.nomorLhp = '';
                this.unitId = '';
                this.unitIds = [];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';
                if (window.populateExistingLhpOptions) {
                    window.populateExistingLhpOptions(this.selectedKategori);
                }
            }
        },

        onSelectExistingLhp(key) {
            this.selectedExistingLhpKey = key ? String(key) : '';
            if (!key) {
                this.selectedExistingDoc = null;
                this.selectedExistingLhpNo = '';
                this.nomorLhp = '';
                this.unitId = '';
                this.unitIds = [];
                this.availableExistingUnits = [];
                if (window.tsExistingUnitInstance) {
                    window.tsExistingUnitInstance.clear();
                    window.tsExistingUnitInstance.clearOptions();
                    window.tsExistingUnitInstance.disable();
                }
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';
                return;
            }

            const found = EXISTING_LHPS.find(l => String(l.audit_assignment_id) === String(key) || String(l.key) === String(key) || l.nomor_lhp === key);
            if (found) {
                this.selectedExistingDoc = found;
                this.selectedExistingLhpNo = found.nomor_lhp;
                this.nomorLhp = found.nomor_lhp;
                this.tanggalLhp = found.tanggal_lhp || '{{ date("Y-m-d") }}';
                if (found.catatan_umum && !this.catatanUmum) {
                    this.catatanUmum = found.catatan_umum;
                }
                this.programId = found.program_id ? String(found.program_id) : '';
                this.assignmentId = found.audit_assignment_id ? String(found.audit_assignment_id) : '';
                this.currentKategori = found.kategori || 'PKPT';

                const hiddenAss = document.getElementById('hidden-assignment-id');
                if (hiddenAss) hiddenAss.value = this.assignmentId;

                this.populateExistingUnits(found);
            }
        },

        populateExistingUnits(found) {
            if (!window.tsExistingUnitInstance) return;
            const tsExistingUnit = window.tsExistingUnitInstance;
            tsExistingUnit.clear();
            tsExistingUnit.clearOptions();
            tsExistingUnit.disable();

            if (!found) {
                this.availableExistingUnits = [];
                this.unitId = '';
                this.unitIds = [];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';
                tsExistingUnit.refreshOptions(false);
                return;
            }

            const available = found.remaining_units || [];
            this.availableExistingUnits = available;

            if (available.length === 0) {
                tsExistingUnit.addOption({ value: '', text: '-- Semua unit pada penugasan ini sudah dibuatkan LHP --' });
                tsExistingUnit.setValue('', false);
                this.unitId = '';
                this.unitIds = [];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';
                tsExistingUnit.refreshOptions(false);
                return;
            }

            available.forEach(u => tsExistingUnit.addOption({ value: String(u.id), text: u.nama }));
            tsExistingUnit.enable();
            tsExistingUnit.refreshOptions(false);

            if (available.length === 1) {
                const singleId = String(available[0].id);
                tsExistingUnit.setValue(singleId, false);
                this.unitId = singleId;
                this.unitIds = [singleId];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = singleId;
            } else {
                tsExistingUnit.setValue('', false);
                this.unitId = '';
                this.unitIds = [];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';
            }
            tsExistingUnit.refreshOptions(false);
        },

        // Step 2 & 3 Dynamic Data Arrays
        temuans: [],
        attachments: [],
        isDragging: false,

        // Modal & AJAX submit state
        showConfirmModal: false,
        showSuccessModal: false,
        showErrorModal: false,
        successMessage: '',
        redirectUrl: '',
        isSubmitting: false,
        formTarget: null,

        // Validation error messages
        stepErrors: [],

        openConfirmModal(event) {
            if (event) event.preventDefault();

            if (!this.validateAllSteps()) {
                this.showErrorModal = true;
                window.scrollTo({ top: 100, behavior: 'smooth' });
                return false;
            }

            const target = event ? event.target : null;
            this.formTarget = (target && target.tagName === 'FORM')
                ? target
                : (target && target.closest ? target.closest('form') : document.getElementById('form-lhp-wizard'));
            this.showConfirmModal = true;
        },

        async confirmAndSave() {
            this.showConfirmModal = false;

            if (this.isSubmitting) return false;
            this.isSubmitting = true;

            const btn = document.getElementById('btn-submit-final');
            const txt = document.getElementById('btn-submit-text');
            if (btn) btn.disabled = true;
            if (txt) txt.textContent = 'Menyimpan & Menerbitkan LHP...';

            const form = (this.formTarget && this.formTarget.tagName === 'FORM')
                ? this.formTarget
                : document.getElementById('form-lhp-wizard');

            const actionUrl = form ? (form.getAttribute('action') || form.action) : '{{ route("lhps.store") }}';
            const formData = form ? new FormData(form) : new FormData();

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });

                let data = {};
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const textContent = await response.text();
                    if (response.status === 419) {
                        data = { message: 'Sesi login Anda telah berakhir. Silakan muat ulang (refresh) halaman lalu coba lagi.' };
                    } else if (response.status === 413) {
                        data = { message: 'Ukuran berkas lampiran terlalu besar untuk dikirim ke server.' };
                    } else {
                        data = { message: `Gagal menyimpan LHP (Status Server ${response.status}). Silakan periksa koneksi atau log server.` };
                    }
                }

                if (response.ok && data.success) {
                    this.successMessage = data.message || 'LHP berhasil dibuat.';
                    this.redirectUrl = data.redirect || '{{ route("lhps.index") }}';
                    this.showSuccessModal = true;
                } else {
                    let errList = [];
                    if (data.errors) {
                        errList = Object.values(data.errors).flat();
                    }
                    if (errList.length === 0 && data.message) {
                        errList = [data.message];
                    }
                    if (errList.length === 0) {
                        errList = ['Gagal menyimpan LHP. Silakan periksa kembali data Anda.'];
                    }

                    this.stepErrors = errList;
                    this.showErrorModal = true;
                    window.scrollTo({ top: 100, behavior: 'smooth' });

                    if (btn) btn.disabled = false;
                    if (txt) txt.textContent = 'Simpan & Terbitkan LHP';
                    this.isSubmitting = false;
                }
            } catch (err) {
                console.error(err);
                this.stepErrors = ['Terjadi kesalahan sistem/koneksi saat menyimpan LHP: ' + (err.message || err)];
                this.showErrorModal = true;
                window.scrollTo({ top: 100, behavior: 'smooth' });
                if (btn) btn.disabled = false;
                if (txt) txt.textContent = 'Simpan & Terbitkan LHP';
                this.isSubmitting = false;
            }
        },

        init() {
            window.lhpWizardInstance = this;

            // Inisialisasi 1 temuan default jika belum ada
            if (this.temuans.length === 0) {
                this.addTemuan();
            }

            this.$nextTick(() => {
                this.initCascadeDropdowns();
                if (window.RupiahInput?.initAll) window.RupiahInput.initAll();
            });
        },

        // Navigasi & Guard Step
        canGoToStep(targetStep) {
            if (targetStep <= 1) return true;

            // Step 1 check
            const hiddenAss = document.getElementById('hidden-assignment-id');
            const hiddenUnit = document.getElementById('hidden-unit-id');
            const assVal = (hiddenAss && hiddenAss.value) || this.assignmentId;
            const hasUnit = (this.unitIds && this.unitIds.length > 0) || Boolean((hiddenUnit && hiddenUnit.value) || this.unitId);
            const isStep1Valid = Boolean(assVal && hasUnit && this.nomorLhp && this.nomorLhp.trim() && this.tanggalLhp);

            if (targetStep === 2) return isStep1Valid && !this.isNihil;
            if (targetStep === 3) return isStep1Valid && !this.isNihil;
            if (targetStep === 4) {
                if (this.isNihil) return isStep1Valid;
                const isStep2Valid = isStep1Valid && this.temuans.length > 0 && this.temuans.every(t => t.kondisi && t.kondisi.trim());
                return isStep2Valid && this.temuans.every(t => 
                    t.recommendations && t.recommendations.length > 0 && t.recommendations.every(r => r.kode_rekomendasi_id && r.uraian_rekom && r.uraian_rekom.trim())
                );
            }

            return false;
        },

        setStep(targetStep) {
            if (targetStep < this.step) {
                this.step = targetStep;
                window.scrollTo({ top: 100, behavior: 'smooth' });
                return;
            }
            if (!this.canGoToStep(targetStep)) {
                this.validateCurrentStep();
                this.showErrorModal = true;
                window.scrollTo({ top: 100, behavior: 'smooth' });
                return;
            }
            this.step = targetStep;
            window.scrollTo({ top: 100, behavior: 'smooth' });
        },

        nextStep() {
            if (this.validateCurrentStep()) {
                this.step = Math.min(4, this.step + 1);
                window.scrollTo({ top: 100, behavior: 'smooth' });
            } else {
                this.showErrorModal = true;
                window.scrollTo({ top: 100, behavior: 'smooth' });
            }
        },

        prevStep() {
            this.step = Math.max(1, this.step - 1);
            window.scrollTo({ top: 100, behavior: 'smooth' });
        },

        // Validasi per Step
        validateCurrentStep() {
            this.stepErrors = [];

            if (this.step === 1) {
                const hiddenAss = document.getElementById('hidden-assignment-id');
                const hiddenUnit = document.getElementById('hidden-unit-id');
                const assVal = (hiddenAss && hiddenAss.value) || this.assignmentId;
                const hasUnit = (this.unitIds && this.unitIds.length > 0) || Boolean((hiddenUnit && hiddenUnit.value) || this.unitId);

                if (this.lhpInputMode === 'existing') {
                    if (!this.selectedExistingDoc) {
                        this.stepErrors.push('Pilih Dokumen LHP Terdaftar pada Langkah 1 terlebih dahulu.');
                    } else if ((this.selectedExistingDoc.remaining_count || 0) === 0) {
                        this.stepErrors.push('Dokumen LHP yang dipilih sudah tidak memiliki unit kerja tersisa (seluruh unit sudah dibuatkan LHP). Silakan pilih nomor LHP lain yang bertanda 🟢 Unit Tersedia atau pilih Jalur A.');
                    }
                }

                if (!assVal) this.stepErrors.push('Pilih Penugasan Audit (Surat Tugas) terlebih dahulu.');
                if (!hasUnit) this.stepErrors.push('Pilih minimal 1 Unit Kerja / Objek Audit terlebih dahulu.');
                if (!this.nomorLhp || !this.nomorLhp.trim()) this.stepErrors.push('Nomor LHP wajib diisi.');
                if (!this.tanggalLhp) this.stepErrors.push('Tanggal LHP wajib diisi.');
            }

            if (!this.isNihil && this.step === 2) {
                if (this.temuans.length === 0) {
                    this.stepErrors.push('Tambahkan minimal 1 Temuan Pemeriksaan.');
                } else {
                    this.temuans.forEach((t, i) => {
                        if (!t.kondisi || !t.kondisi.trim()) {
                            this.stepErrors.push(`Uraian kondisi pada Temuan #${i + 1} belum diisi.`);
                        }
                    });
                }
            }

            if (!this.isNihil && this.step === 3) {
                this.temuans.forEach((t, i) => {
                    if (!t.recommendations || t.recommendations.length === 0) {
                        this.stepErrors.push(`Tambahkan minimal 1 Rekomendasi pada Temuan #${i + 1}.`);
                    } else {
                        t.recommendations.forEach((r, j) => {
                            if (!r.kode_rekomendasi_id) {
                                this.stepErrors.push(`Pilih Kode Rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}).`);
                            }
                            if (!r.uraian_rekom || !r.uraian_rekom.trim()) {
                                this.stepErrors.push(`Uraian rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}) belum diisi.`);
                            }
                        });
                    }
                });
            }

            return this.stepErrors.length === 0;
        },

        validateAllSteps() {
            this.stepErrors = [];

            const hiddenAss = document.getElementById('hidden-assignment-id');
            const hiddenUnit = document.getElementById('hidden-unit-id');
            const assVal = (hiddenAss && hiddenAss.value) || this.assignmentId;
            const hasUnit = (this.unitIds && this.unitIds.length > 0) || Boolean((hiddenUnit && hiddenUnit.value) || this.unitId);

            if (!assVal) this.stepErrors.push('Pilih Penugasan Audit (Surat Tugas) terlebih dahulu.');
            if (!hasUnit) this.stepErrors.push('Pilih minimal 1 Unit Kerja / Objek Audit terlebih dahulu.');
            if (!this.nomorLhp || !this.nomorLhp.trim()) this.stepErrors.push('Nomor LHP wajib diisi.');
            if (!this.tanggalLhp) this.stepErrors.push('Tanggal LHP wajib diisi.');

            if (this.isNihil) {
                return this.stepErrors.length === 0;
            }

            if (this.temuans.length === 0) {
                this.stepErrors.push('Tambahkan minimal 1 Temuan Pemeriksaan.');
            } else {
                this.temuans.forEach((t, i) => {
                    if (!t.kondisi || !t.kondisi.trim()) {
                        this.stepErrors.push(`Uraian kondisi pada Temuan #${i + 1} belum diisi.`);
                    }
                });
            }

            this.temuans.forEach((t, i) => {
                if (!t.recommendations || t.recommendations.length === 0) {
                    this.stepErrors.push(`Tambahkan minimal 1 Rekomendasi pada Temuan #${i + 1}.`);
                } else {
                    t.recommendations.forEach((r, j) => {
                        if (!r.kode_rekomendasi_id) {
                            this.stepErrors.push(`Pilih Kode Rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}).`);
                        }
                        if (!r.uraian_rekom || !r.uraian_rekom.trim()) {
                            this.stepErrors.push(`Uraian rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}) belum diisi.`);
                        }
                    });
                }
            });

            return this.stepErrors.length === 0;
        },

        // Cascade Dropdown Logika Step 1
        initCascadeDropdowns() {
            const selProgram    = document.getElementById('select-program');
            const selAssignment = document.getElementById('select-assignment');
            const selUnit       = document.getElementById('select-unit');

            if (!selProgram || !selAssignment || !selUnit) return;

            // Helper untuk mengecek sisa unit pada penugasan
            const getRemainingUnitsCount = (ass) => {
                const usedIds = (USED_UNIT_MAP[ass.id] || []).map(String);
                if (!ass.units || ass.units.length === 0) return 0;
                return ass.units.filter(u => !usedIds.includes(String(u.id))).length;
            };

            const tsProgram = new TomSelect('#select-program', {
                create: false,
                placeholder: '-- Pilih Program Kerja / Pengawasan --',
                controlInput: '<input>',
                maxOptions: null
            });
            window.tsProgramInstance = tsProgram;

            const tsAssignment = new TomSelect('#select-assignment', {
                create: false,
                placeholder: '-- Pilih Penugasan Audit / Surat Tugas --',
                controlInput: '<input>',
                maxOptions: null
            });
            window.tsAssignmentInstance = tsAssignment;
            tsAssignment.disable();

            const tsUnit = new TomSelect('#select-unit', {
                create: false,
                placeholder: '-- Pilih 1 Unit Kerja / Objek Audit --',
                controlInput: '<input>',
                maxOptions: null
            });
            window.tsUnitInstance = tsUnit;
            tsUnit.disable();

            const tsExisting = new TomSelect('#select-existing-lhp', {
                create: false,
                placeholder: '-- Cari Berdasarkan Surat Tugas, Nomor LHP, atau Nama Unit --',
                controlInput: '<input>',
                maxOptions: null,
                searchField: ['text', 'nomor_lhp', 'nomor_surat', 'program_nama', 'unit_names']
            });
            window.tsExistingInstance = tsExisting;

            let tsExistingUnit = null;
            if (document.getElementById('select-existing-unit')) {
                tsExistingUnit = new TomSelect('#select-existing-unit', {
                    create: false,
                    placeholder: '-- Pilih 1 Unit Kerja yang Tersisa --',
                    controlInput: '<input>',
                    maxOptions: null
                });
                window.tsExistingUnitInstance = tsExistingUnit;
                tsExistingUnit.disable();

                tsExistingUnit.on('change', (val) => {
                    if (isInit) return;
                    this.unitId = val ? String(val) : '';
                    this.unitIds = this.unitId ? [this.unitId] : [];
                    const hiddenUnit = document.getElementById('hidden-unit-id');
                    if (hiddenUnit) hiddenUnit.value = this.unitId;
                });
            }

            const populateExistingLhpOptions = (filterKat = '') => {
                tsExisting.clear();
                tsExisting.clearOptions();

                let list = EXISTING_LHPS;
                if (filterKat) {
                    list = list.filter(item => (item.kategori || '').toUpperCase() === filterKat.toUpperCase());
                }

                list = list.slice().sort((a, b) => {
                    const aHas = (a.remaining_count || 0) > 0 ? 1 : 0;
                    const bHas = (b.remaining_count || 0) > 0 ? 1 : 0;
                    if (bHas !== aHas) return bHas - aHas;
                    if (!filterKat && (a.kategori || '') !== (b.kategori || '')) {
                        return (a.kategori || '').localeCompare(b.kategori || '');
                    }
                    if ((b.tanggal_lhp || '') !== (a.tanggal_lhp || '')) {
                        return (b.tanggal_lhp || '').localeCompare(a.tanggal_lhp || '');
                    }
                    return (a.nomor_surat || '').localeCompare(b.nomor_surat || '');
                });

                if (list.length === 0) {
                    tsExisting.addOption({
                        value: '',
                        text: filterKat ? `-- Tidak ada nomor LHP kategori ${filterKat} yang memiliki sisa unit --` : '-- Tidak ada dokumen LHP induk yang memiliki sisa unit kerja --'
                    });
                    tsExisting.setValue('', false);
                    this.onSelectExistingLhp('');
                    tsExisting.refreshOptions(false);
                    return;
                }

                list.forEach(item => {
                    const rem = item.remaining_count || 1;
                    const tot = item.total_units_in_ass || rem;
                    tsExisting.addOption({
                        value: String(item.audit_assignment_id),
                        text: `[${item.kategori}] Surat Tugas: ${item.nomor_surat} — No. LHP: ${item.nomor_lhp} (${rem} dari ${tot} unit tersisa)`,
                        nomor_lhp: item.nomor_lhp,
                        nomor_surat: item.nomor_surat,
                        program_nama: item.program_nama,
                        unit_names: [
                            ...(item.remaining_units || []).map(u => u.nama),
                            ...(item.completed_units || []).map(u => u.nama),
                        ].join(' ')
                    });
                });

                tsExisting.refreshOptions(false);

                const currentKey = String(this.selectedExistingLhpKey || this.assignmentId || '');
                if (currentKey && list.some(i => String(i.audit_assignment_id) === currentKey)) {
                    tsExisting.setValue(currentKey, false);
                    this.onSelectExistingLhp(currentKey);
                } else if (this.selectedExistingLhpNo && list.some(i => i.nomor_lhp === this.selectedExistingLhpNo)) {
                    const match = list.find(i => i.nomor_lhp === this.selectedExistingLhpNo);
                    if (match) {
                        tsExisting.setValue(String(match.audit_assignment_id), false);
                        this.onSelectExistingLhp(String(match.audit_assignment_id));
                    }
                } else {
                    tsExisting.setValue('', false);
                    this.onSelectExistingLhp('');
                }
                tsExisting.refreshOptions(false);
            };
            window.populateExistingLhpOptions = populateExistingLhpOptions;

            let isInit = true;

            const populateProgramsCascade = (filterKat = '') => {
                tsProgram.clear();
                tsProgram.clearOptions();
                tsAssignment.clear();
                tsAssignment.clearOptions();
                tsAssignment.disable();
                tsUnit.clear();
                tsUnit.clearOptions();
                tsUnit.disable();

                let filteredPrograms = ALL_PROGRAMS;
                if (filterKat) {
                    filteredPrograms = ALL_PROGRAMS.filter(p => (p.kategori || '').toUpperCase() === filterKat.toUpperCase());
                }

                // Check warning status
                if (filterKat) {
                    if (filteredPrograms.length === 0) {
                        this.kategoriWarningMessage = `Belum ada data Program Kerja dengan kategori "${filterKat}".`;
                        this.kategoriWarningUrl = '{{ route("audit-program.create") }}';
                        this.kategoriWarningBtn = `+ Buat Program Kerja ${filterKat}`;
                    } else {
                        const hasAssignments = filteredPrograms.some(p => p.assignments_count > 0);
                        if (!hasAssignments) {
                            this.kategoriWarningMessage = `Program Kerja kategori "${filterKat}" ditemukan (${filteredPrograms.map(p => p.nama).join(', ')}), namun belum memiliki Surat Tugas / Penugasan Audit. LHP dibuat berdasarkan Surat Tugas.`;
                            this.kategoriWarningUrl = '{{ route("audit-assignment.create") }}';
                            this.kategoriWarningBtn = `+ Buat Surat Tugas / Penugasan`;
                        } else {
                            this.kategoriWarningMessage = '';
                            this.kategoriWarningUrl = '';
                            this.kategoriWarningBtn = '';
                        }
                    }
                } else {
                    this.kategoriWarningMessage = '';
                    this.kategoriWarningUrl = '';
                    this.kategoriWarningBtn = '';
                }

                filteredPrograms.forEach(p => {
                    const progAss = ALL_ASSIGNMENTS.filter(a => String(a.program_id) === String(p.id));
                    let totalRemainingUnits = 0;
                    progAss.forEach(a => {
                        totalRemainingUnits += getRemainingUnitsCount(a);
                    });

                    let label = `[${p.kategori}] ${p.nama} (${p.tahun})`;
                    if (p.assignments_count === 0) {
                        label += ` — (Belum ada Surat Tugas)`;
                    } else if (totalRemainingUnits === 0 && (!this.assignmentId || !progAss.some(a => String(a.id) === String(this.assignmentId)))) {
                        label += ` — (Semua unit selesai LHP)`;
                    } else {
                        label += ` — (${totalRemainingUnits} unit siap LHP)`;
                    }

                    tsProgram.addOption({
                        value: String(p.id),
                        text: label
                    });
                });

                tsProgram.refreshOptions(false);
            };

            window.populateProgramsCascade = populateProgramsCascade;

            const populateAssignments = (progId, selectedAssId = null) => {
                tsAssignment.clear(); tsAssignment.clearOptions(); tsAssignment.disable();
                tsUnit.clear(); tsUnit.clearOptions(); tsUnit.disable();

                if (!progId) return;

                const filtered = ALL_ASSIGNMENTS.filter(a => String(a.program_id) === String(progId));

                if (filtered.length === 0) {
                    tsAssignment.addOption({ value: '', text: '-- Belum ada Surat Tugas pada program ini --' });
                    tsAssignment.disable();
                    return;
                }

                filtered.forEach(a => {
                    const remaining = getRemainingUnitsCount(a);
                    const isSelected = selectedAssId && String(a.id) === String(selectedAssId);
                    let label = `[${a.kategori}] ${a.detail_nama}` + (a.nomor_surat ? ' — ' + a.nomor_surat : '');
                    if (remaining > 0) {
                        label += ` (${remaining} unit belum LHP)`;
                    } else if (isSelected) {
                        label += ` (LHP Saat Ini)`;
                    } else {
                        label += ` (Semua unit selesai LHP)`;
                    }
                    tsAssignment.addOption({ value: a.id, text: label });
                });

                tsAssignment.enable();
                if (selectedAssId) {
                    tsAssignment.setValue(String(selectedAssId), true);
                    this.assignmentId = String(selectedAssId);
                    const hiddenAss = document.getElementById('hidden-assignment-id');
                    if (hiddenAss) hiddenAss.value = String(selectedAssId);
                } else if (filtered.length === 1 && (getRemainingUnitsCount(filtered[0]) > 0)) {
                    tsAssignment.setValue(String(filtered[0].id), false);
                }
            };

            const populateUnits = (assId, selectedUnitIds = null) => {
                tsUnit.clear(); tsUnit.clearOptions(); tsUnit.disable();
                this.availableUnits = [];
                if (!assId) return;

                const assignment = ALL_ASSIGNMENTS.find(a => String(a.id) === String(assId));
                const usedIds    = (USED_UNIT_MAP[assId] || []).map(String);

                this.currentKategori = assignment?.kategori || 'PKPT';
                this.isExternalAudit = ['BPK', 'BPKP', 'ITPROV', 'ITDA'].includes(this.currentKategori);

                if (assignment && assignment.units && assignment.units.length > 0) {
                    let selArray = [];
                    if (selectedUnitIds) {
                        selArray = Array.isArray(selectedUnitIds) ? selectedUnitIds.map(String) : [String(selectedUnitIds)];
                    }

                    const available = assignment.units.filter(u =>
                        selArray.includes(String(u.id)) || !usedIds.includes(String(u.id))
                    );
                    this.availableUnits = available;

                    if (available.length === 0) {
                        tsUnit.addOption({ value: '', text: '-- Semua unit sudah dibuatkan LHP --' });
                        tsUnit.enable();
                        return;
                    }

                    available.forEach(u => tsUnit.addOption({ value: String(u.id), text: u.nama }));
                    tsUnit.enable();

                    if (selArray.length > 0 && available.some(u => String(u.id) === String(selArray[0]))) {
                        tsUnit.setValue(String(selArray[0]), true);
                        this.unitId = String(selArray[0]);
                        this.unitIds = [this.unitId];
                    } else if (available.length === 1) {
                        tsUnit.setValue(String(available[0].id), true);
                        this.unitId = String(available[0].id);
                        this.unitIds = [this.unitId];
                    } else {
                        this.unitIds = [];
                        this.unitId = '';
                    }
                } else {
                    tsUnit.addOption({ value: '', text: '-- Tidak ada unit pada penugasan ini --' });
                }
            };

            tsProgram.on('change', (progId) => {
                if (isInit) return;
                this.programId = progId;
                this.assignmentId = '';
                this.unitId = '';
                this.unitIds = [];
                const hiddenAss = document.getElementById('hidden-assignment-id');
                if (hiddenAss) hiddenAss.value = '';
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';

                if (this.lhpInputMode === 'existing') {
                    this.selectedExistingLhpNo = '';
                    this.nomorLhp = '';
                    tsExisting.setValue('', true);
                }

                // If selected program has no assignments, show alert
                const prog = ALL_PROGRAMS.find(p => String(p.id) === String(progId));
                if (prog && prog.assignments_count === 0) {
                    this.kategoriWarningMessage = `Program "${prog.nama}" belum memiliki Surat Tugas / Penugasan Audit. LHP dibuat berdasarkan Surat Tugas.`;
                    this.kategoriWarningUrl = '{{ route("audit-assignment.create") }}';
                    this.kategoriWarningBtn = `+ Buat Surat Tugas / Penugasan`;
                } else if (!this.selectedKategori) {
                    this.kategoriWarningMessage = '';
                    this.kategoriWarningUrl = '';
                    this.kategoriWarningBtn = '';
                }

                populateAssignments(progId);
            });

            tsAssignment.on('change', (assId) => {
                if (isInit) return;
                this.assignmentId = assId;
                const hiddenAss = document.getElementById('hidden-assignment-id');
                if (hiddenAss) hiddenAss.value = assId;

                this.unitId = '';
                this.unitIds = [];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';

                populateUnits(assId);
            });

            tsUnit.on('change', (val) => {
                if (isInit) return;
                this.unitId = val ? String(val) : '';
                this.unitIds = this.unitId ? [this.unitId] : [];
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = this.unitId;
            });

            tsExisting.on('change', (val) => {
                if (isInit) return;
                this.onSelectExistingLhp(val);
            });

            // Initial population
            populateProgramsCascade(this.selectedKategori);
            populateExistingLhpOptions(this.selectedKategori);

            if (!this.programId && this.assignmentId) {
                const found = ALL_ASSIGNMENTS.find(a => String(a.id) === String(this.assignmentId));
                if (found) {
                    this.programId = found.program_id;
                    const prog = ALL_PROGRAMS.find(p => String(p.id) === String(found.program_id));
                    if (prog && prog.kategori) {
                        this.selectedKategori = prog.kategori;
                    }
                }
            }

            if (this.programId) {
                tsProgram.setValue(String(this.programId), true);
                populateAssignments(this.programId, this.assignmentId);
                if (this.assignmentId) {
                    populateUnits(this.assignmentId, (this.unitIds && this.unitIds.length > 0) ? this.unitIds : this.unitId);
                }
            }

            isInit = false;

            if (this.lhpInputMode === 'existing') {
                const targetKey = String(this.selectedExistingLhpKey || this.assignmentId || '');
                if (targetKey && EXISTING_LHPS.some(i => String(i.audit_assignment_id) === targetKey)) {
                    tsExisting.setValue(targetKey, false);
                    this.onSelectExistingLhp(targetKey);
                }
            }
        },

        selectAllUnits() {},
        clearAllUnits() {},

        // Logika Temuan (Step 2)
        addTemuan() {
            const newTemuan = {
                id: Date.now() + Math.random(),
                kode_temuan_id: '',
                kondisi: '',
                sebab: '',
                akibat: '',
                nilai_kerugian_negara: 0,
                nilai_kerugian_daerah: 0,
                nilai_kerugian_desa: 0,
                nilai_kerugian_bos_blud: 0,
                nilai_temuan: 0,
                recommendations: []
            };
            this.temuans.push(newTemuan);

            // Tambahkan 1 rekomendasi default untuk temuan baru ini
            this.addRekomendasi(this.temuans.length - 1);
        },

        removeTemuan(index) {
            if (this.temuans.length > 1) {
                this.temuans.splice(index, 1);
            } else {
                alert('Minimal harus ada 1 temuan.');
            }
        },

        recalcTemuanTotal(t) {
            const prevTotal = parseFloat(t.nilai_temuan || 0);
            const negara = parseFloat(t.nilai_kerugian_negara || 0);
            const daerah = parseFloat(t.nilai_kerugian_daerah || 0);
            const desa   = parseFloat(t.nilai_kerugian_desa || 0);
            const bos    = parseFloat(t.nilai_kerugian_bos_blud || 0);
            const newTotal = negara + daerah + desa + bos;
            t.nilai_temuan = newTotal;

            // Auto-update plafon nilai rekomendasi pertama jika nilainya belum diubah manual oleh pengguna
            if (t.recommendations && t.recommendations.length > 0) {
                const r0 = t.recommendations[0];
                const currentRekomVal = parseFloat(r0.nilai_rekom || 0);
                if (!r0._is_manual_nilai || currentRekomVal === 0 || currentRekomVal === prevTotal) {
                    r0.nilai_rekom = newTotal;
                    if (newTotal > 0) {
                        r0.jenis_rekomendasi = 'uang';
                    }
                }
            }
        },

        getKodeTemuanLabel(kodeId) {
            if (!kodeId) return '';
            const found = KODE_TEMUANS.find(k => String(k.id) === String(kodeId));
            if (!found) return '';
            const desc = found.deskripsi || found.nama || '';
            return desc ? `[${found.kode}] — ${desc}` : `[${found.kode}]`;
        },

        getKodeRekomLabel(kodeId) {
            const found = KODE_REKOMS.find(r => String(r.id) === String(kodeId));
            return found ? `[${found.kode}] — ${found.deskripsi}` : '';
        },

        getFilteredKodeRekoms(kodeTemuanId) {
            if (!kodeTemuanId) return KODE_REKOMS;
            const temuanKode = KODE_TEMUANS.find(k => String(k.id) === String(kodeTemuanId));
            if (temuanKode && Array.isArray(temuanKode.alternatif_rekom) && temuanKode.alternatif_rekom.length > 0) {
                const altList = temuanKode.alternatif_rekom.map(String);
                const filtered = KODE_REKOMS.filter(r => 
                    altList.includes(String(r.id)) || 
                    altList.includes(String(r.kode_numerik))
                );
                return filtered.length > 0 ? filtered : KODE_REKOMS;
            }
            return KODE_REKOMS;
        },

        onKodeTemuanChange(t) {
            const availableKodes = this.getFilteredKodeRekoms(t.kode_temuan_id);
            if (t.recommendations && t.recommendations.length > 0) {
                t.recommendations.forEach(r => {
                    const isValid = availableKodes.some(k => String(k.id) === String(r.kode_rekomendasi_id));
                    if (!isValid && availableKodes.length > 0) {
                        r.kode_rekomendasi_id = availableKodes[0].id;
                    }
                });
            }
        },

        // Logika Rekomendasi (Step 3)
        addRekomendasi(tIndex) {
            const temuan = this.temuans[tIndex];
            if (!temuan) return;

            const defaultBatas = new Date();
            defaultBatas.setDate(defaultBatas.getDate() + 60);

            const isFirst = temuan.recommendations.length === 0;
            const newRekom = {
                id: Date.now() + Math.random(),
                kode_rekomendasi_id: '',
                jenis_rekomendasi: (isFirst && temuan.nilai_temuan > 0) ? 'uang' : 'administrasi',
                nilai_rekom: isFirst ? temuan.nilai_temuan : 0,
                uraian_rekom: '',
                batas_waktu: defaultBatas.toISOString().split('T')[0],
                _is_manual_nilai: false
            };

            // Prefill kode rekomendasi pertama dari opsi yang cocok jika ada
            const availableKodes = this.getFilteredKodeRekoms(temuan.kode_temuan_id);
            if (availableKodes.length > 0) {
                newRekom.kode_rekomendasi_id = availableKodes[0].id;
            }

            temuan.recommendations.push(newRekom);
        },

        removeRekomendasi(tIndex, rIndex) {
            this.temuans[tIndex].recommendations.splice(rIndex, 1);
        },

        // Logika Lampiran Berkas LHP (Step 1)
        cleanFileName(name) {
            if (!name) return '';
            const parts = name.split('.');
            if (parts.length > 1) parts.pop();
            return parts.join('.');
        },

        formatBytes(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        getFileExt(name) {
            if (!name) return 'FILE';
            const parts = name.split('.');
            return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE';
        },

        addAttachment(initialFile = null) {
            const id = Date.now() + Math.random().toString(36).substring(2, 7);
            const newAtt = {
                id: id,
                file_name: initialFile ? this.cleanFileName(initialFile.name) : '',
                raw_name: initialFile ? initialFile.name : '',
                file_size: initialFile ? this.formatBytes(initialFile.size) : '',
                file_ext: initialFile ? this.getFileExt(initialFile.name) : '',
                keterangan: '',
                has_file: !!initialFile
            };
            this.attachments.push(newAtt);

            if (initialFile) {
                this.$nextTick(() => {
                    const el = document.getElementById('att-file-' + id);
                    if (el) {
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(initialFile);
                            el.files = dt.files;
                        } catch (e) {
                            console.warn('DataTransfer not fully supported for programmatic file assignment', e);
                        }
                    }
                });
            } else {
                this.$nextTick(() => {
                    document.getElementById('att-file-' + id)?.click();
                });
            }
        },

        removeAttachment(index) {
            this.attachments.splice(index, 1);
        },

        triggerSingleFilePicker(attId) {
            document.getElementById('att-file-' + attId)?.click();
        },

        processBatchFiles(fileList) {
            const allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            const maxBytes = 10 * 1024 * 1024; // 10 MB

            for (let i = 0; i < fileList.length; i++) {
                const file = fileList[i];
                const ext = this.getFileExt(file.name).toLowerCase();
                
                if (!allowed.includes(ext)) {
                    this.stepErrors = [`Format berkas "${file.name}" tidak diizinkan. Harap unggah format PDF, JPG, atau PNG.`];
                    this.showErrorModal = true;
                    continue;
                }

                if (file.size > maxBytes) {
                    this.stepErrors = [`Ukuran berkas "${file.name}" (${this.formatBytes(file.size)}) melebihi batas 10 MB.`];
                    this.showErrorModal = true;
                    continue;
                }

                this.addAttachment(file);
            }
        },

        handleMasterFileInput(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                this.processBatchFiles(files);
            }
            event.target.value = '';
        },

        handleMasterDrop(event) {
            event.preventDefault();
            this.isDragging = false;
            const files = event.dataTransfer ? event.dataTransfer.files : null;
            if (files && files.length > 0) {
                this.processBatchFiles(files);
            }
        },

        onSingleFileSelected(event, att) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;

            const allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            const ext = this.getFileExt(file.name).toLowerCase();
            const maxBytes = 10 * 1024 * 1024;

            if (!allowed.includes(ext)) {
                this.stepErrors = [`Format berkas "${file.name}" tidak diizinkan. Harap gunakan PDF, JPG, atau PNG.`];
                this.showErrorModal = true;
                event.target.value = '';
                return;
            }

            if (file.size > maxBytes) {
                this.stepErrors = [`Ukuran berkas "${file.name}" (${this.formatBytes(file.size)}) melebihi batas 10 MB.`];
                this.showErrorModal = true;
                event.target.value = '';
                return;
            }

            att.raw_name = file.name;
            att.file_size = this.formatBytes(file.size);
            att.file_ext = ext.toUpperCase();
            att.has_file = true;

            if (!att.file_name || att.file_name.trim() === '') {
                att.file_name = this.cleanFileName(file.name);
            }
        },

        // Format Rupiah Display Helper
        fmtRupiah(num) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);
        },

        // Total Summaries untuk Step 4 Pratinjau
        get totalKerugianSum() {
            return this.temuans.reduce((sum, t) => sum + (parseFloat(t.nilai_temuan) || 0), 0);
        },

        get totalRekomCount() {
            return this.temuans.reduce((sum, t) => sum + t.recommendations.length, 0);
        },

        get totalNilaiRekomSum() {
            return this.temuans.reduce((sum, t) => {
                return sum + t.recommendations.reduce((rSum, r) => rSum + (parseFloat(r.nilai_rekom) || 0), 0);
            }, 0);
        }
    };
}
</script>

<div class="w-full space-y-6" x-data="lhpCreateWizard()" x-cloak>

    {{-- ⚠️ Pop-Up Alert Error Modal Ultra-Smooth (Muncul jika ada kesalahan/data belum lengkap) --}}
    <div x-show="showErrorModal"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        
        <!-- Backdrop overlay -->
        <div x-show="showErrorModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showErrorModal = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

        <!-- Modal Card -->
        <div x-show="showErrorModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-400 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4 blur-sm"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave="transition cubic-bezier(0.7, 0, 0.84, 0) duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2 blur-sm"
             class="relative w-full max-w-[520px] rounded-[32px] bg-white p-6 sm:p-8 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:bg-gray-900 border border-slate-100 dark:border-gray-800 text-center overflow-hidden">
            
            <!-- Glowing background aura -->
            <div class="absolute -top-24 -left-24 h-48 w-48 rounded-full bg-rose-500/10 blur-3xl pointer-events-none"></div>

            <!-- Icon Error Badge -->
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-rose-50 text-rose-500 dark:bg-rose-900/30 dark:text-rose-400 ring-8 ring-rose-50/50 dark:ring-rose-900/20">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Gagal Menyimpan LHP
            </h3>

            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                Mohon perbaiki atau lengkapi data berikut sebelum menyimpan LHP:
            </p>

            <!-- Error List Box -->
            <div class="mb-6 max-h-52 overflow-y-auto rounded-2xl bg-rose-50/80 p-4 border border-rose-100 text-left dark:bg-rose-950/30 dark:border-rose-900/50">
                <ul class="space-y-2 text-xs font-semibold text-rose-700 dark:text-rose-300">
                    <template x-for="(err, idx) in stepErrors" :key="idx">
                        <li class="flex items-start gap-2">
                            <span class="text-rose-500 shrink-0 font-bold">•</span>
                            <span x-text="err" class="leading-tight"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Action Button -->
            <div class="flex items-center justify-center">
                <button type="button"
                        @click="showErrorModal = false"
                        class="w-full rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-rose-600/25 transition-all hover:from-rose-500 hover:to-red-500 active:scale-95 cursor-pointer">
                    Mengerti &amp; Perbaiki Data
                </button>
            </div>
        </div>
    </div>

    {{-- ❓ Pop-Up Confirmation Modal (Tanyakan Sebelum Simpan LHP) --}}
    <div x-show="showConfirmModal"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        
        <!-- Backdrop overlay -->
        <div x-show="showConfirmModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showConfirmModal = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

        <!-- Modal Card -->
        <div x-show="showConfirmModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-400 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4 blur-sm"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave="transition cubic-bezier(0.7, 0, 0.84, 0) duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2 blur-sm"
             class="relative w-full max-w-[480px] rounded-[28px] bg-white p-6 sm:p-8 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:bg-gray-900 border border-slate-100 dark:border-gray-800 text-center overflow-hidden">
            
            <!-- Icon Question Badge -->
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-amber-50 text-amber-500 dark:bg-amber-900/30 dark:text-amber-400 ring-8 ring-amber-50/50 dark:ring-amber-900/20">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Konfirmasi Simpan LHP
            </h3>

            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
                Apakah Anda yakin ingin menyimpan dan menerbitkan data LHP ini? Pastikan seluruh data temuan dan rekomendasi sudah sesuai.
            </p>

            <!-- Actions -->
            <div class="flex items-center gap-3 justify-center">
                <button type="button"
                        @click="showConfirmModal = false"
                        class="w-1/2 rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/80 cursor-pointer">
                    Batal
                </button>
                <button type="button"
                        @click="confirmAndSave()"
                        class="w-1/2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 transition-all hover:from-emerald-700 hover:to-teal-700 hover:shadow-emerald-600/35 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                    Ya, Simpan LHP
                </button>
            </div>
        </div>
    </div>

    {{-- ✅ Pop-Up Alert Success Modal Ultra-Smooth (Muncul Langsung Setelah Simpan) --}}
    <div x-show="showSuccessModal"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        
        <!-- Backdrop overlay -->
        <div x-show="showSuccessModal"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

        <!-- Modal Card -->
        <div x-show="showSuccessModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-6 blur-md"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave="transition cubic-bezier(0.7, 0, 0.84, 0) duration-300 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4 blur-sm"
             class="relative w-full max-w-[520px] rounded-[32px] bg-white p-8 sm:p-10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:bg-gray-900 border border-slate-100 dark:border-gray-800/80 text-center overflow-hidden">
            
            <!-- Glowing background aura -->
            <div class="absolute -top-24 -left-24 h-48 w-48 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

            <!-- Animated Success SVG Badge -->
            <div class="mx-auto mb-6 flex items-center justify-center">
                <img src="{{ asset('images/success.svg') }}" alt="Success" class="h-20 w-20 sm:h-24 sm:w-24 transition-transform duration-300 hover:scale-105">
            </div>

            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white uppercase">
                SUCCESS !
            </h2>

            <p class="mt-3 text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-md mx-auto" x-text="successMessage"></p>

            <!-- Action Button -->
            <div class="mt-8 flex items-center justify-center">
                <button type="button"
                        @click="window.location.href = redirectUrl"
                        class="group relative inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition-all duration-300 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                    <span>Tutup &amp; Ke Halaman Utama</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

            {{-- <!-- Footer -->
            <p class="mt-7 text-center text-xs font-medium text-slate-400 dark:text-slate-500">
                © <span>{{ date('Y') }}</span> SIAPTUNTAS • TailAdmin
            </p> --}}
        </div>
    </div>

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="hover:text-blue-600 transition-colors">LHP</a>
                <span>/</span>
                <span class="text-gray-700 dark:text-gray-300">Wizard Tambah LHP Baru</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah LHP &amp; Rekomendasi Baru</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Lengkapi formulir 4 langkah untuk menerbitkan Laporan Hasil Pemeriksaan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('lhps.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- ── 🌟 SPLIT SCREEN FULL LAYOUT CONTAINER ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- ── LEFT PANEL (COL 1-4): VERTICAL STEPPER & LIVE SUMMARY ── --}}
        <div class="lg:col-span-4 xl:col-span-3 space-y-5 lg:sticky lg:top-6">
            
            {{-- Stepper Nav Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahapan Pengisian</h2>
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500" x-text="`Langkah ${step} dari 4`"></span>
                </div>
                
                <nav class="space-y-2.5">
                    {{-- Step 1 Button --}}
                    <button type="button" @click="setStep(1)"
                            class="w-full flex items-center gap-3.5 rounded-xl border p-3 text-left transition-colors"
                            :class="step === 1
                                ? 'border-blue-600 bg-blue-50/70 text-blue-900 dark:border-blue-500 dark:bg-blue-950/40 dark:text-blue-200 font-semibold'
                                : 'border-gray-100 bg-gray-50/50 hover:bg-gray-100/70 dark:border-gray-800/80 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300'">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition-colors"
                             :class="step === 1 ? 'bg-blue-600 text-white' : (canGoToStep(2) ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300')">
                            <span x-text="canGoToStep(2) &amp;&amp; step !== 1 ? '✓' : '1'"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold">1. Informasi LHP</p>
                            <p class="mt-0.5 truncate text-[11px] opacity-75">PKPT, ST &amp; Unit Kerja</p>
                        </div>
                    </button>

                    {{-- Step 2 Button --}}
                    <button type="button" @click="setStep(2)" :disabled="!canGoToStep(2)"
                            class="w-full flex items-center gap-3.5 rounded-xl border p-3 text-left transition-colors"
                            :class="step === 2
                                ? 'border-blue-600 bg-blue-50/70 text-blue-900 dark:border-blue-500 dark:bg-blue-950/40 dark:text-blue-200 font-semibold'
                                : (canGoToStep(2) ? 'border-gray-100 bg-gray-50/50 hover:bg-gray-100/70 dark:border-gray-800/80 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300' : 'border-gray-100 bg-gray-100/40 text-gray-400 dark:border-gray-800/40 dark:bg-gray-800/20 cursor-not-allowed opacity-60')">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition-colors"
                             :class="step === 2 ? 'bg-blue-600 text-white' : (canGoToStep(3) ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400')">
                            <span x-text="canGoToStep(3) &amp;&amp; step !== 2 ? '✓' : (canGoToStep(2) ? '2' : '2')"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold">2. Temuan Audit</p>
                            <p class="mt-0.5 truncate text-[11px] opacity-75" x-text="`${temuans.length} Temuan`"></p>
                        </div>
                    </button>

                    {{-- Step 3 Button --}}
                    <button type="button" @click="setStep(3)" :disabled="!canGoToStep(3)"
                            class="w-full flex items-center gap-3.5 rounded-xl border p-3 text-left transition-colors"
                            :class="step === 3
                                ? 'border-blue-600 bg-blue-50/70 text-blue-900 dark:border-blue-500 dark:bg-blue-950/40 dark:text-blue-200 font-semibold'
                                : (canGoToStep(3) ? 'border-gray-100 bg-gray-50/50 hover:bg-gray-100/70 dark:border-gray-800/80 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300' : 'border-gray-100 bg-gray-100/40 text-gray-400 dark:border-gray-800/40 dark:bg-gray-800/20 cursor-not-allowed opacity-60')">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition-colors"
                             :class="step === 3 ? 'bg-blue-600 text-white' : (canGoToStep(4) ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400')">
                            <span x-text="canGoToStep(4) &amp;&amp; step !== 3 ? '✓' : (canGoToStep(3) ? '3' : '3')"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold">3. Rekomendasi</p>
                            <p class="mt-0.5 truncate text-[11px] opacity-75" x-text="`${totalRekomCount} Rekomendasi`"></p>
                        </div>
                    </button>

                    {{-- Step 4 Button --}}
                    <button type="button" @click="setStep(4)" :disabled="!canGoToStep(4)"
                            class="w-full flex items-center gap-3.5 rounded-xl border p-3 text-left transition-colors"
                            :class="step === 4
                                ? 'border-blue-600 bg-blue-50/70 text-blue-900 dark:border-blue-500 dark:bg-blue-950/40 dark:text-blue-200 font-semibold'
                                : (canGoToStep(4) ? 'border-gray-100 bg-gray-50/50 hover:bg-gray-100/70 dark:border-gray-800/80 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300' : 'border-gray-100 bg-gray-100/40 text-gray-400 dark:border-gray-800/40 dark:bg-gray-800/20 cursor-not-allowed opacity-60')">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition-colors"
                             :class="step === 4 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                            <span>4</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold">4. Finalisasi LHP</p>
                            <p class="mt-0.5 truncate text-[11px] opacity-75">Review &amp; Submit</p>
                        </div>
                    </button>
                </nav>
            </div>

            {{-- Ringkasan Live Stats Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Ringkasan LHP</h3>

                <div class="space-y-3 text-xs">
                    <div class="rounded-xl bg-rose-50/80 p-3.5 border border-rose-100 dark:bg-rose-900/20 dark:border-rose-900/40">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500">Plafon Kerugian Temuan</span>
                        <p class="mt-1 text-lg font-extrabold text-rose-700 dark:text-rose-300" x-text="fmtRupiah(totalKerugianSum)"></p>
                    </div>

                    <div class="rounded-xl bg-blue-50/80 p-3.5 border border-blue-100 dark:bg-blue-900/20 dark:border-blue-900/40">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-500">Nilai Rekomendasi</span>
                        <p class="mt-1 text-lg font-extrabold text-blue-700 dark:text-blue-300" x-text="fmtRupiah(totalNilaiRekomSum)"></p>
                    </div>

                    <div class="pt-2 border-t border-gray-100 dark:border-gray-800 space-y-2">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Total Temuan:</span>
                            <strong class="text-slate-900 dark:text-white" x-text="`${temuans.length} Temuan`"></strong>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Total Rekomendasi:</span>
                            <strong class="text-slate-900 dark:text-white" x-text="`${totalRekomCount} Rekomendasi`"></strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── RIGHT PANEL (COL 5-12): ACTIVE FORM STEP CONTENT ── --}}
        <div class="lg:col-span-8 xl:col-span-9 min-w-0 space-y-6">

            {{-- Error Warning Banner --}}
            <div x-show="stepErrors.length > 0" x-transition class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300 shadow-sm">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 shrink-0 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-sm">Lengkapi data berikut sebelum melanjutkannya:</p>
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            <template x-for="err in stepErrors" :key="err">
                                <li x-text="err"></li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

            <form action="{{ route('lhps.store') }}" method="POST" enctype="multipart/form-data" id="form-lhp-wizard" novalidate @submit="openConfirmModal($event)">
                @csrf

                {{-- Hidden Form Values --}}
                <input type="hidden" name="audit_assignment_id" id="hidden-assignment-id" :value="assignmentId">
                <template x-for="uid in unitIds" :key="uid">
                    <input type="hidden" name="unit_diperiksa_ids[]" :value="uid">
                </template>
                <input type="hidden" name="unit_diperiksa_id" id="hidden-unit-id" :value="unitIds[0] || unitId">
                <input type="hidden" name="nomor_lhp" :value="nomorLhp">
                <input type="hidden" name="tanggal_lhp" :value="tanggalLhp">
                <input type="hidden" name="lhp_input_mode" :value="lhpInputMode">

        {{-- ═════════════════════════════════════════════════════════════ --}}
        {{-- ── STEP 1: SKEMA PENOMORAN & INFORMASI UTAMA LHP ── --}}
        {{-- ═════════════════════════════════════════════════════════════ --}}
        <div x-show="step === 1" x-cloak>
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Langkah 1: Skema Penomoran &amp; Administrasi LHP</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pilih skema pembuatan dokumen: Terbitkan nomor LHP baru atau lanjutkan dokumen LHP kolektif yang sudah terdaftar.</p>
                </div>

                <div class="p-6 space-y-6">
                    
                    {{-- ── PINTU UTAMA: PILIH SKEMA PEMBUATAN LHP (DUAL-TRACK) ── --}}
                    <div>
                        <label class="mb-2.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Pilih Skema Pembuatan LHP <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                            {{-- Jalur A: Terbitkan LHP Baru --}}
                            <div @click="setLhpInputMode('new')"
                                 :class="lhpInputMode === 'new' 
                                    ? 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-500/20 dark:border-blue-500 dark:bg-blue-950/40 shadow-sm' 
                                    : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-900/40 dark:hover:border-gray-600'"
                                 class="group relative flex cursor-pointer items-start gap-3.5 rounded-2xl border p-4 transition-all">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors"
                                     :class="lhpInputMode === 'new' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 group-hover:bg-gray-200'">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Jalur A: Terbitkan Nomor LHP Baru</span>
                                        <span x-show="lhpInputMode === 'new'" class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[9px] font-bold text-white shadow-xs">Aktif</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        Pemeriksaan reguler/mandiri baru dengan menerbitkan nomor LHP baru.
                                    </p>
                                </div>
                            </div>

                            {{-- Jalur B: Lanjutkan LHP Terdaftar --}}
                            <div @click="setLhpInputMode('existing')"
                                 :class="lhpInputMode === 'existing' 
                                    ? 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-500/20 dark:border-blue-500 dark:bg-blue-950/40 shadow-sm' 
                                    : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-900/40 dark:hover:border-gray-600'"
                                 class="group relative flex cursor-pointer items-start gap-3.5 rounded-2xl border p-4 transition-all">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors"
                                     :class="lhpInputMode === 'existing' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 group-hover:bg-gray-200'">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Jalur B: Lanjutkan LHP Terdaftar</span>
                                        <span class="rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 text-[10px] font-bold" x-text="`${EXISTING_LHPS.length} Dokumen`"></span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        Khusus audit kolektif (BPK / BPKP / ITPROV) untuk menambahkan unit lain ke dokumen yang sudah ada.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═════════════════════════════════════════════════════ --}}
                    {{-- ── FORM JALUR A: NOMOR LHP BARU ── --}}
                    {{-- ═════════════════════════════════════════════════════ --}}
                    <div x-show="lhpInputMode === 'new'" x-cloak class="space-y-6 pt-2">
                        
                        {{-- Bagian 1: Identitas Dokumen Baru --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-gray-700 dark:bg-gray-900/50 space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold">1</span>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                    Identitas Dokumen LHP Baru
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {{-- Nomor LHP --}}
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                                        Nomor LHP <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="nomorLhp"
                                           @input="manualNomorLhp = nomorLhp"
                                           placeholder="Contoh: 700.1.2.1/045/ITDA/2026"
                                           class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 font-mono focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    
                                    {{-- Format Rekomendasi Chip --}}
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                                        <span>Rekomendasi (<span x-text="formatRecommendation.label"></span>):</span>
                                        <button type="button" @click="applyFormatSample(formatRecommendation.sample)"
                                                class="font-mono font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition inline-flex items-center gap-1 cursor-pointer">
                                            <span x-text="formatRecommendation.sample"></span>
                                            <span class="text-[9px] uppercase tracking-wider text-blue-500 font-sans font-bold">Gunakan</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Tanggal LHP --}}
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                                        Tanggal LHP <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" x-model="tanggalLhp" onclick="this.showPicker()"
                                           @input="manualTanggalLhp = tanggalLhp"
                                           class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>

                        {{-- Bagian 2: Parameter & Objek Pemeriksaan --}}
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/70 space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold">2</span>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                    Parameter &amp; Objek Audit
                                </h3>
                            </div>

                            {{-- Filter Kategori --}}
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Filter Kategori Pengawasan
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            @click="filterKategori('')"
                                            :class="selectedKategori === '' ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-500/20 font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-medium'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                                        <span>Semua</span>
                                        <span class="rounded-full px-1.5 py-0.2 text-[10px]"
                                              :class="selectedKategori === '' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200'"
                                              x-text="ALL_PROGRAMS.length"></span>
                                    </button>
                                    <template x-for="kat in KATEGORI_LIST" :key="kat">
                                        <button type="button"
                                                @click="filterKategori(kat)"
                                                :class="selectedKategori === kat ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-500/20 font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-medium'"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                                            <span x-text="kat"></span>
                                            <span class="rounded-full px-1.5 py-0.2 text-[10px]"
                                                  :class="selectedKategori === kat ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200'"
                                                  x-text="getProgramCountByKategori(kat)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Alert jika kategori belum punya program --}}
                            <div x-show="kategoriWarningMessage" x-cloak class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 dark:bg-amber-900/25 dark:border-amber-800 dark:text-amber-200 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <p class="font-semibold" x-text="kategoriWarningMessage"></p>
                                        <p class="text-amber-700 dark:text-amber-300 text-[11px] mt-0.5">Penugasan audit diperlukan untuk menentukan ruang lingkup tim dan unit kerja yang diperiksa sebelum penerbitan LHP.</p>
                                    </div>
                                </div>
                                <template x-if="kategoriWarningUrl">
                                    <a :href="kategoriWarningUrl" target="_blank" class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow transition">
                                        <span x-text="kategoriWarningBtn"></span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </template>
                            </div>

                            {{-- Program Kerja --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Program Kerja / Pengawasan <span class="text-red-500">*</span>
                                </label>
                                <select id="select-program" data-no-ts class="w-full">
                                    <option value="">-- Pilih Program Kerja / Pengawasan --</option>
                                </select>
                            </div>

                            {{-- Penugasan Audit --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Penugasan Audit / Surat Tugas <span class="text-red-500">*</span>
                                </label>
                                <select id="select-assignment" data-no-ts disabled class="ts-assignment-tall w-full">
                                    <option value="">-- Pilih Program Terlebih Dahulu --</option>
                                </select>
                            </div>

                            {{-- Unit Kerja --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                                    Unit Kerja / Objek Audit <span class="text-red-500">*</span>
                                    <span class="ml-1 text-[11px] font-normal text-gray-500 dark:text-gray-400 normal-case">(1 LHP = 1 Unit Kerja)</span>
                                </label>
                                <select id="select-unit" data-no-ts disabled class="w-full">
                                    <option value="">-- Pilih Penugasan Terlebih Dahulu --</option>
                                </select>
                            </div>
                        </div>

                        {{-- Live Preview Card Dokumen Baru --}}
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50/40 dark:from-gray-900 dark:to-blue-950/20 dark:border-gray-800 text-xs">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-1.5 font-bold text-gray-800 dark:text-gray-200">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Preview Identitas LHP Baru</span>
                                </div>
                                <span class="rounded bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 px-2 py-0.5 text-[10px] font-semibold">Dokumen Mandiri</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-gray-600 dark:text-gray-300 font-mono text-xs">
                                <div><span class="text-gray-400 font-sans">Nomor:</span> <strong class="text-gray-900 dark:text-white" x-text="nomorLhp || '(Belum diisi)'"></strong></div>
                                <div><span class="text-gray-400 font-sans">Tanggal:</span> <span class="text-gray-800 dark:text-gray-200" x-text="tanggalLhp || '-'"></span></div>
                                <div><span class="text-gray-400 font-sans">Kategori:</span> <span class="text-gray-800 dark:text-gray-200" x-text="currentKategori || selectedKategori || 'PKPT'"></span></div>
                                <div><span class="text-gray-400 font-sans">Objek:</span> <span class="text-gray-800 dark:text-gray-200" x-text="selectedUnitName || '-'"></span></div>
                            </div>
                        </div>

                    </div>

                    {{-- ═════════════════════════════════════════════════════ --}}
                    {{-- ── FORM JALUR B: LANJUTKAN DOKUMEN TERDAFTAR ── --}}
                    {{-- ═════════════════════════════════════════════════════ --}}
                    <div x-show="lhpInputMode === 'existing'" x-cloak class="space-y-6 pt-2">
                        
                        {{-- Langkah 1 Jalur B: Pilih Dokumen LHP Induk --}}
                        <div class="rounded-2xl border border-blue-200 bg-blue-50/30 p-5 dark:border-blue-900/40 dark:bg-blue-950/20 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold">1</span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                        Pilih Dokumen LHP Induk yang Sudah Terdaftar
                                    </h3>
                                </div>
                                <span class="rounded bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 text-[10px] font-bold" x-text="`${filteredExistingLhps.length} Dokumen Tersedia`"></span>
                            </div>

                            {{-- Filter Kategori Cepat --}}
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold text-gray-600 dark:text-gray-400">
                                    Filter Berdasarkan Kategori Dokumen:
                                </label>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" @click="filterKategori('')"
                                            :class="selectedKategori === '' ? 'bg-blue-600 text-white font-bold shadow-sm ring-2 ring-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 font-medium'"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                        <span>Semua</span>
                                        <span class="rounded-full px-1.5 py-0.2 text-[10px]"
                                              :class="selectedKategori === '' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                              x-text="getExistingLhpCountByKategori('')"></span>
                                    </button>
                                    <template x-for="kat in KATEGORI_LIST" :key="kat">
                                        <button type="button" @click="filterKategori(kat)"
                                                :class="selectedKategori === kat ? 'bg-blue-600 text-white font-bold shadow-sm ring-2 ring-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 font-medium'"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                            <span x-text="kat"></span>
                                            <span class="rounded-full px-1.5 py-0.2 text-[10px]"
                                                  :class="selectedKategori === kat ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                                  x-text="getExistingLhpCountByKategori(kat)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Dropdown Pilih LHP Induk --}}
                            <div>
                                <select id="select-existing-lhp" data-no-ts class="w-full">
                                    <option value="">-- Cari Berdasarkan Surat Tugas, Nomor LHP, atau Nama Unit --</option>
                                </select>
                            </div>

                            {{-- Info Pembantu: Surat Tugas Baru yang Belum Pernah Menerbitkan LHP --}}
                            <template x-if="UNSTARTED_ASS && UNSTARTED_ASS.length > 0 && !selectedExistingDoc">
                                <div class="rounded-xl border border-blue-100 bg-white/80 p-3.5 shadow-xs dark:border-blue-900/40 dark:bg-gray-800/80 text-xs space-y-2">
                                    <div class="flex items-start gap-2.5">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 font-bold text-[11px]">💡</span>
                                        <div class="space-y-1">
                                            <span class="font-bold text-gray-900 dark:text-white">Mencari Surat Tugas Baru dari Penugasan Audit?</span>
                                            <p class="text-gray-500 dark:text-gray-400 text-[11px] leading-relaxed">
                                                Jalur B khusus untuk melanjutkan LHP pada Surat Tugas yang <strong>sudah memiliki minimal 1 LHP</strong>. Untuk Surat Tugas yang belum pernah dibuatkan LHP sama sekali, klik untuk menerbitkan LHP pertamanya di Jalur A:
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 pt-0.5">
                                        <template x-for="unAss in UNSTARTED_ASS" :key="unAss.id">
                                            <button type="button" @click="setLhpInputMode('new', unAss.id)"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50/60 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300 transition cursor-pointer">
                                                <span>+ Mulai LHP:</span>
                                                <strong x-text="unAss.nomor_surat"></strong>
                                                <span class="rounded bg-blue-200/70 dark:bg-blue-900/70 px-1 text-[10px]" x-text="`${unAss.units_count} unit`"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Kartu Info Dokumen Induk Terpilih (Terkunci & Singkron dengan Audit Assignment) --}}
                            <div x-show="selectedExistingDoc" x-cloak
                                 class="rounded-xl border border-blue-200 bg-white p-4.5 shadow-sm dark:border-blue-900/60 dark:bg-gray-900 space-y-3.5">
                                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white text-[10px] font-bold">✓</span>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Dokumen Induk &amp; Penugasan Terhubung</span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 text-[10px] font-bold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Parameter Singkron Otomatis
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-xs">
                                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2.5">
                                        <span class="text-gray-400 block text-[11px] mb-0.5">Nomor Dokumen LHP Induk:</span>
                                        <p class="font-mono font-bold text-blue-700 dark:text-blue-300 text-sm" x-text="selectedExistingDoc?.nomor_lhp"></p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2.5">
                                        <span class="text-gray-400 block text-[11px] mb-0.5">Surat Tugas (Penugasan Audit):</span>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedExistingDoc?.nomor_surat ? `${selectedExistingDoc.nomor_surat}` : 'Penugasan ID #' + selectedExistingDoc?.audit_assignment_id"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Program Kerja:</span>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedExistingDoc?.program_nama || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Tanggal Terbit LHP Induk:</span>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedExistingDoc?.tanggal_lhp || '-'"></p>
                                    </div>
                                </div>

                                {{-- Singkronisasi Unit Diperiksa (Audit Assignment Units) --}}
                                <div class="pt-2.5 border-t border-gray-100 dark:border-gray-800 space-y-2">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-gray-600 dark:text-gray-400">Status Objek Audit pada Surat Tugas:</span>
                                        <span class="font-bold text-gray-900 dark:text-white" x-text="`${selectedExistingDoc?.total_units_created || 0} dari ${selectedExistingDoc?.total_units_in_ass || 0} Unit Selesai`"></span>
                                    </div>

                                    {{-- Pill List: Unit Selesai vs Tersisa --}}
                                    <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                        <template x-for="cu in (selectedExistingDoc?.completed_units || [])" :key="cu.id">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 px-2 py-0.5 line-through">
                                                <span class="text-[9px]">✓</span> <span x-text="cu.nama"></span>
                                            </span>
                                        </template>
                                        <template x-for="ru in (selectedExistingDoc?.remaining_units || [])" :key="ru.id">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 px-2 py-0.5 font-semibold">
                                                <span class="text-[9px]">⏳</span> <span x-text="ru.nama"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Langkah 2 Jalur B: Pilih Unit Kerja yang Tersisa --}}
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/70 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold">2</span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                        Pilih Unit Kerja / Objek Audit yang Belum Dibuatkan LHP <span class="text-red-500">*</span>
                                    </h3>
                                </div>
                                <template x-if="selectedExistingDoc && (selectedExistingDoc?.remaining_count || 0) > 0">
                                    <span class="rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 px-2 py-0.5 text-[10px] font-bold"
                                          x-text="`${selectedExistingDoc.remaining_count} Unit Tersedia`"></span>
                                </template>
                            </div>

                            {{-- Kondisi 1: Belum memilih LHP terdaftar di atas --}}
                            <div x-show="!selectedExistingDoc" class="rounded-xl border border-dashed border-gray-200 bg-gray-50/70 p-4 text-center dark:border-gray-700 dark:bg-gray-800/40">
                                <div class="flex flex-col items-center justify-center gap-1.5 text-gray-500 dark:text-gray-400">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Pilih Dokumen LHP Induk pada Langkah 1 Terlebih Dahulu</span>
                                    <span class="text-[11px] text-gray-400">Daftar unit kerja yang belum dibuatkan LHP akan otomatis ditampilkan di sini setelah nomor LHP dipilih.</span>
                                </div>
                            </div>

                            {{-- Kondisi 2: Sudah memilih LHP tapi seluruh unit kerja sudah dibuatkan LHP (0 sisa) --}}
                            <div x-show="selectedExistingDoc && (selectedExistingDoc?.remaining_count || 0) === 0" x-cloak
                                 class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/50 dark:bg-amber-950/30">
                                <div class="flex items-start gap-3">
                                    <span class="text-amber-600 dark:text-amber-400 text-lg leading-none mt-0.5">⚠️</span>
                                    <div class="text-xs space-y-1">
                                        <h4 class="font-bold text-amber-900 dark:text-amber-200">Seluruh Unit Kerja Sudah Dibuatkan LHP (0 Unit Tersisa)</h4>
                                        <p class="text-amber-700 dark:text-amber-300">
                                            Seluruh unit kerja pada Surat Tugas penugasan ini sudah dibuatkan lembar LHP masing-masing. Tidak ada unit kerja yang tersisa untuk dibuatkan LHP baru dengan nomor ini.
                                        </p>
                                        <div class="pt-1.5 text-amber-800 dark:text-amber-200 flex flex-wrap items-center gap-2">
                                            <span>Solusi:</span>
                                            <span class="font-semibold text-emerald-700 dark:text-emerald-300">Pilih nomor LHP lain yang bertanda 🟢 Unit Tersedia di atas</span>
                                            <span>atau</span>
                                            <button type="button" @click="setLhpInputMode('new')"
                                                    class="inline-flex items-center gap-1 rounded bg-blue-600 text-white px-2 py-0.5 font-bold hover:bg-blue-700 transition cursor-pointer">
                                                Beralih ke Jalur A (Terbitkan LHP Baru)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kondisi 3: Sudah memilih LHP dan masih ada unit kerja yang tersisa --}}
                            <div x-show="selectedExistingDoc && (selectedExistingDoc?.remaining_count || 0) > 0" x-cloak class="space-y-2">
                                <select id="select-existing-unit" data-no-ts disabled class="w-full">
                                    <option value="">-- Pilih 1 Unit Kerja yang Tersisa --</option>
                                </select>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                                    <span>Hanya menampilkan unit kerja yang <strong>belum</strong> memiliki lembar LHP pada penugasan ini.</span>
                                    <template x-if="unitId">
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">✓ 1 Unit Terpilih</span>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>

                        {{-- Status Temuan LHP (Ada Temuan / Nihil) --}}
                        <div class="md:col-span-2 rounded-2xl border p-4 transition-all duration-300"
                             :class="isNihil ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40' : 'bg-gray-50/70 border-gray-200 dark:bg-gray-900/50 dark:border-gray-800'">
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Hasil Pemeriksaan (Status Temuan) <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- Opsi 1: Memiliki Temuan --}}
                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                       :class="!isNihil ? 'bg-white border-blue-500 ring-2 ring-blue-500/20 dark:bg-gray-800 dark:border-blue-400' : 'bg-transparent border-gray-300 dark:border-gray-700 hover:bg-white/60'">
                                    <input type="radio" name="is_nihil" value="0" :checked="!isNihil" @change="isNihil = false" class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span class="block text-xs font-bold text-gray-900 dark:text-white"> Memiliki Temuan Pemeriksaan</span>
                                        <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">LHP berisi daftar temuan &amp; rekomendasi yang wajib ditindaklanjuti.</span>
                                    </div>
                                </label>

                                {{-- Opsi 2: Bebas Temuan (Nihil) --}}
                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                       :class="isNihil ? 'bg-white border-emerald-500 ring-2 ring-emerald-500/20 dark:bg-gray-800 dark:border-emerald-400' : 'bg-transparent border-gray-300 dark:border-gray-700 hover:bg-white/60'">
                                    <input type="radio" name="is_nihil" value="1" :checked="isNihil" @change="isNihil = true" class="mt-0.5 h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="block text-xs font-bold text-emerald-700 dark:text-emerald-400"> Bebas Temuan / Nihil (Tanpa Temuan)</span>
                                        <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Pemeriksaan selesai tanpa temuan. Progress LHP otomatis 100% Selesai.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Catatan Umum --}}
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Catatan Umum / Ringkasan LHP</label>
                                <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500" x-text="`${(catatanUmum || '').length} karakter`"></span>
                            </div>
                            <textarea name="catatan_umum" x-model="catatanUmum" rows="4"
                                      placeholder="Tuliskan catatan ringkas mengenai LHP ini..."
                                      class="w-full resize-y min-h-[90px] rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white transition"></textarea>
                        </div>
                  

                    {{-- Dynamic Berkas Lampiran LHP (Modern Dropzone & Card Layout) --}}
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-800 space-y-4">
                        {{-- Header Section --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 text-[10px] font-bold">📎</span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                        Berkas Lampiran LHP
                                    </h3>
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        Opsional
                                    </span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                    <span>Format: PDF, JPG, PNG</span>
                                    <span>•</span>
                                    <span>Maksimal 10 MB per berkas</span>
                                    <template x-if="attachments.length > 0">
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" x-text="`• ${attachments.length} berkas disiapkan`"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="addAttachment()"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50/70 px-3.5 py-2 text-xs font-semibold text-blue-700 shadow-xs hover:bg-blue-100 active:scale-95 dark:border-blue-800/80 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition cursor-pointer">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Berkas
                                </button>
                            </div>
                        </div>

                        {{-- Dropzone Area Interaktif --}}
                        <div @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop="handleMasterDrop($event)"
                             :class="isDragging ? 'border-blue-500 bg-blue-50/60 ring-2 ring-blue-500/20 dark:bg-blue-950/40' : 'border-gray-300 bg-gray-50/50 hover:border-blue-400 hover:bg-blue-50/20 dark:border-gray-700 dark:bg-gray-900/30 dark:hover:border-blue-500'"
                             class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition-all cursor-pointer"
                             @click="$refs.masterFileInput.click()">
                            
                            <input type="file" x-ref="masterFileInput" class="sr-only" multiple accept=".pdf,.jpg,.jpeg,.png"
                                   @change="handleMasterFileInput($event)">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 group-hover:scale-105 group-hover:shadow transition-all dark:bg-gray-800 dark:ring-white/10">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>

                            <div class="mt-2.5">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                                    Tarik &amp; letakkan berkas ke sini, atau <span class="text-blue-600 underline dark:text-blue-400 font-bold">pilih dari perangkat</span>
                                </p>
                                <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                                    Bisa memilih lebih dari 1 file sekaligus (PDF, Gambar JPG/PNG)
                                </p>
                            </div>
                        </div>

                        {{-- Daftar Kartu Berkas Terpilih --}}
                        <div class="space-y-3" x-show="attachments.length > 0">
                            <template x-for="(att, aIdx) in attachments" :key="att.id">
                                <div class="group relative rounded-2xl border border-gray-200 bg-white p-4 shadow-xs transition-all hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/80 dark:hover:border-gray-600 space-y-3.5">
                                    
                                    {{-- File Input Tersembunyi (Dikendalikan oleh tombol Ganti / File Picker) --}}
                                    <input type="file" :name="`attachments[${aIdx}][file_path]`" :id="`att-file-${att.id}`"
                                           accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                                           @change="onSingleFileSelected($event, att)">

                                    {{-- Baris 1: Header Item & Preview Info --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/60">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            {{-- Format Icon Badge --}}
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-bold text-[10px] uppercase shadow-xs transition-colors"
                                                 :class="att.file_ext === 'PDF' 
                                                    ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-800' 
                                                    : 'bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800'">
                                                <span x-text="att.file_ext || 'FILE'"></span>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-gray-900 dark:text-white truncate max-w-xs sm:max-w-md"
                                                          x-text="att.raw_name || 'Berkas belum dipilih'"></span>
                                                    <span class="rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 px-2 py-0.2 text-[9px] font-bold shrink-0"
                                                          x-show="att.has_file">Siap Diunggah</span>
                                                </div>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5"
                                                   x-text="att.file_size ? `Ukuran: ${att.file_size}` : 'Klik tombol di samping untuk memilih berkas'"></p>
                                            </div>
                                        </div>

                                        {{-- Actions: Ganti & Hapus --}}
                                        <div class="flex items-center gap-1.5 self-end sm:self-auto shrink-0">
                                            <button type="button" @click="triggerSingleFilePicker(att.id)"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-[11px] font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer">
                                                <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                <span x-text="att.has_file ? 'Ganti Berkas' : 'Pilih Berkas'"></span>
                                            </button>
                                            <button type="button" @click="removeAttachment(aIdx)"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50/60 px-2.5 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-100 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-900/60 transition cursor-pointer">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Baris 2: Field Judul & Keterangan --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div>
                                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                Nama / Judul Dokumen
                                            </label>
                                            <input type="text" :name="`attachments[${aIdx}][file_name]`" x-model="att.file_name"
                                                   placeholder="cth: LHP Final Ditandatangani, Matriks Temuan..."
                                                   class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                Keterangan Tambahan (Opsional)
                                            </label>
                                            <input type="text" :name="`attachments[${aIdx}][keterangan]`" x-model="att.keterangan"
                                                   placeholder="cth: Dokumen resmi asli berstempel basah..."
                                                   class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Stepper Footer Step 1 --}}
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/30">
                    <div>
                        <template x-if="isNihil">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                🌿 Status LHP: Bebas Temuan (Nihil) — Progress 100%
                            </span>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="isNihil">
                            <button type="button" @click="setStep(4)"
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
                                Lanjut ke Pratinjau &amp; Simpan (Bebas Temuan) →
                            </button>
                        </template>
                        <template x-if="!isNihil">
                            <button type="button" @click="nextStep()"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 active:scale-95 transition-all">
                                Lanjut ke Langkah 2: Input Temuan →
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════ --}}
        {{-- ── STEP 2: INPUT TEMUAN PEMERIKSAAN ── --}}
        {{-- ═════════════════════════════════════════════════════════════ --}}
        <div x-show="step === 2" x-cloak>
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">Langkah 2: Temuan Pemeriksaan &amp; Nilai Kerugian</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tambahkan daftar temuan hasil audit beserta klasifikasi kode dan kerugian keuangan.</p>
                    </div>
                    <button type="button" @click="addTemuan()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 active:scale-95 transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah Temuan Lagi
                    </button>
                </div>

                {{-- Temuan List Container --}}
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="(t, tIdx) in temuans" :key="t.id">
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-400" x-text="tIdx + 1"></span>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white" x-text="`Temuan #${tIdx + 1}`"></h3>
                                </div>
                                <button type="button" @click="removeTemuan(tIdx)" x-show="temuans.length > 1" class="text-xs font-semibold text-red-500 hover:text-red-700">
                                    Hapus Temuan Ini
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {{-- Searchable Dropdown Kode Temuan --}}
                                <div class="md:col-span-2 relative" x-data="{ search: '', open: false }">
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Kode Temuan <span class="text-red-500">*</span>
                                    </label>
                                    
                                    {{-- Hidden Input for form submission --}}
                                    <input type="hidden" :name="`temuans[${tIdx}][kode_temuan_id]`" :value="t.kode_temuan_id">

                                    {{-- Dropdown Trigger Button --}}
                                    <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchTemuanInput.focus())"
                                            class="w-full flex items-center justify-between rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-left text-xs shadow-xs focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white transition hover:border-gray-400">
                                        <span class="truncate font-medium text-gray-800 dark:text-gray-200" x-text="getKodeTemuanLabel(t.kode_temuan_id) || '-- Pilih / Cari Kode Temuan --'"></span>
                                        <svg class="h-4 w-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    {{-- Dropdown Menu Overlay --}}
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-50 mt-1 w-full rounded-2xl border border-gray-200 bg-white p-2.5 shadow-xl dark:border-gray-800 dark:bg-gray-900 space-y-2">
                                        {{-- Search Input Box --}}
                                        <div class="relative">
                                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <input type="text" x-ref="searchTemuanInput" x-model="search" placeholder="Ketik kode atau kata kunci temuan..."
                                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-3.5 py-2 text-xs text-gray-900 outline-none focus:border-blue-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        </div>

                                        {{-- Options List --}}
                                        <div class="max-h-60 overflow-y-auto space-y-1 pr-1">
                                            <template x-for="k in KODE_TEMUANS.filter(item => {
                                                const q = search.toLowerCase().trim();
                                                if (!q) return true;
                                                return `[${item.kode}] ${item.deskripsi || item.nama || ''}`.toLowerCase().includes(q);
                                            })" :key="k.id">
                                                <button type="button" @click="t.kode_temuan_id = k.id; onKodeTemuanChange(t); open = false; search = ''"
                                                        class="w-full text-left rounded-xl px-3 py-2 text-xs transition flex items-start gap-2"
                                                        :class="t.kode_temuan_id == k.id ? 'bg-blue-50 text-blue-700 font-bold dark:bg-blue-900/40 dark:text-blue-300' : 'hover:bg-gray-50 text-gray-700 dark:text-gray-300 dark:hover:bg-gray-800/80'">
                                                    <span class="inline-block rounded-md bg-blue-100/70 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 px-1.5 py-0.5 font-mono text-[10px] font-bold shrink-0" x-text="`[${k.kode}]`"></span>
                                                    <span class="flex-1 text-[11px] leading-tight" x-text="k.deskripsi || k.nama"></span>
                                                </button>
                                            </template>
                                            
                                            {{-- Empty Search Result --}}
                                            <div x-show="KODE_TEMUANS.filter(item => `[${item.kode}] ${item.deskripsi || item.nama || ''}`.toLowerCase().includes(search.toLowerCase().trim())).length === 0"
                                                 class="p-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                                Tidak ada Kode Temuan yang cocok dengan "<span x-text="search"></span>"
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Kondisi --}}
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                            Uraian Kondisi Temuan <span class="text-red-500">*</span>
                                        </label>
                                        <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500" x-text="`${(t.kondisi || '').length} karakter`"></span>
                                    </div>
                                    <textarea :name="`temuans[${tIdx}][kondisi]`" x-model="t.kondisi" rows="6" placeholder="Uraikan kondisi temuan secara mendetail (fakta yang ditemukan, kriteria/aturan yang dilanggar, bukti pemeriksaan, lokasi, dan objek temuan)..."
                                              class="w-full resize-y min-h-[150px] rounded-xl border border-gray-300 bg-white p-3.5 text-sm leading-relaxed text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition placeholder:text-gray-400" required></textarea>
                                </div>

                                {{-- Sebab --}}
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                            Penyebab (Sebab)
                                        </label>
                                        <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500" x-text="`${(t.sebab || '').length} karakter`"></span>
                                    </div>
                                    <textarea :name="`temuans[${tIdx}][sebab]`" x-model="t.sebab" rows="4" placeholder="Uraikan faktor penyebab timbulnya temuan (kelemahan sistem pengendalian internal, kelalaian personal, ketidaktahuan regulasi, atau kurangnya pengawasan)..."
                                              class="w-full resize-y min-h-[110px] rounded-xl border border-gray-300 bg-white p-3.5 text-sm leading-relaxed text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition placeholder:text-gray-400"></textarea>
                                </div>

                                {{-- Akibat --}}
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                            Dampak (Akibat)
                                        </label>
                                        <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500" x-text="`${(t.akibat || '').length} karakter`"></span>
                                    </div>
                                    <textarea :name="`temuans[${tIdx}][akibat]`" x-model="t.akibat" rows="4" placeholder="Uraikan dampak atau akibat langsung maupun tidak langsung (kerugian finansial, keterlambatan pekerjaan, risiko temuan BPK, sanksi administratif)..."
                                              class="w-full resize-y min-h-[110px] rounded-xl border border-gray-300 bg-white p-3.5 text-sm leading-relaxed text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition placeholder:text-gray-400"></textarea>
                                </div>

                                {{-- Kerugian Finance Breakdown --}}
                                <div class="md:col-span-2 pt-2 space-y-2 border-t border-gray-100 dark:border-gray-800">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Rincian Kerugian Keuangan (Opsional)
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Negara (Rp)</label>
                                            <input type="number" :name="`temuans[${tIdx}][nilai_kerugian_negara]`" x-model.number="t.nilai_kerugian_negara" @input="recalcTemuanTotal(t)" placeholder="0"
                                                   class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-900 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Daerah (Rp)</label>
                                            <input type="number" :name="`temuans[${tIdx}][nilai_kerugian_daerah]`" x-model.number="t.nilai_kerugian_daerah" @input="recalcTemuanTotal(t)" placeholder="0"
                                                   class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-900 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Desa (Rp)</label>
                                            <input type="number" :name="`temuans[${tIdx}][nilai_kerugian_desa]`" x-model.number="t.nilai_kerugian_desa" @input="recalcTemuanTotal(t)" placeholder="0"
                                                   class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-900 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian BOS/BLUD (Rp)</label>
                                            <input type="number" :name="`temuans[${tIdx}][nilai_kerugian_bos_blud]`" x-model.number="t.nilai_kerugian_bos_blud" @input="recalcTemuanTotal(t)" placeholder="0"
                                                   class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-900 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                        </div>
                                    </div>
                                </div>

                                {{-- Total Nilai Kerugian --}}
                                <div class="md:col-span-2 rounded-xl bg-blue-50/70 p-3.5 border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800 flex items-center justify-between">
                                    <span class="text-xs font-bold text-blue-900 dark:text-blue-300">Total Plafon Kerugian Temuan:</span>
                                    <span class="text-sm font-bold text-red-600 dark:text-red-400" x-text="fmtRupiah(t.nilai_temuan)"></span>
                                    <input type="hidden" :name="`temuans[${tIdx}][nilai_temuan]`" :value="t.nilai_temuan">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Stepper Footer Step 2 --}}
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/30">
                    <button type="button" @click="prevStep()"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        ← Kembali ke Step 1
                    </button>
                    <button type="button" @click="nextStep()"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 active:scale-95 transition-all">
                        Lanjut ke Langkah 3: Input Rekomendasi →
                    </button>
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════ --}}
        {{-- ── STEP 3: INPUT REKOMENDASI PER TEMUAN ── --}}
        {{-- ═════════════════════════════════════════════════════════════ --}}
        <div x-show="step === 3" x-cloak>
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">Langkah 3: Input Rekomendasi per Temuan</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tambahkan rekomendasi tindak lanjut spesifik untuk masing-masing temuan yang telah dibuat.</p>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="(t, tIdx) in temuans" :key="t.id">
                        <div class="p-6 space-y-4">
                            {{-- Card Temuan Reference & Plafon --}}
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/80 dark:bg-gray-900 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300" x-text="`Temuan #${tIdx + 1}`"></span>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="getKodeTemuanLabel(t.kode_temuan_id)"></span>
                                    </div>
                                    <button type="button" @click="addRekomendasi(tIdx)"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm active:scale-95 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Rekomendasi Lain
                                    </button>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic whitespace-pre-wrap break-words" x-text="t.kondisi ? 'Kondisi: ' + t.kondisi : '(kondisi temuan belum diisi)'"></p>
                                <div class="mt-2 flex items-center gap-4 text-xs">
                                    <span class="font-semibold text-rose-600 dark:text-rose-400">Plafon Kerugian Temuan: <strong x-text="fmtRupiah(t.nilai_temuan)"></strong></span>
                                    <span class="text-slate-400">&bull;</span>
                                    <span class="text-slate-500" x-text="`${t.recommendations.length} Rekomendasi`"></span>
                                </div>
                            </div>

                            {{-- List Form Rekomendasi di bawah temuan ini --}}
                            <div class="space-y-4 pl-3 border-l-2 border-blue-400 dark:border-blue-700">
                                <template x-for="(r, rIdx) in t.recommendations" :key="r.id">
                                    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 dark:border-gray-700 dark:bg-gray-800/90 shadow-sm">
                                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/80 pb-3">
                                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400" x-text="`Rekomendasi #${rIdx + 1}`"></span>
                                            <button type="button" @click="removeRekomendasi(tIdx, rIdx)" class="text-xs font-semibold text-red-500 hover:text-red-700">
                                                Hapus Rekomendasi
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            {{-- Searchable Dropdown Kode Rekomendasi --}}
                                            <div class="md:col-span-2 relative" x-data="{ search: '', open: false }">
                                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    Kode Rekomendasi <span class="text-red-500">*</span>
                                                </label>
                                                
                                                {{-- Hidden Input for form submission --}}
                                                <input type="hidden" :name="`temuans[${tIdx}][recommendations][${rIdx}][kode_rekomendasi_id]`" :value="r.kode_rekomendasi_id">

                                                {{-- Trigger Button --}}
                                                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchRekomInput.focus())"
                                                        class="w-full flex items-center justify-between rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-left text-xs shadow-xs focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white transition hover:border-gray-400">
                                                    <span class="truncate font-medium text-gray-800 dark:text-gray-200" x-text="getKodeRekomLabel(r.kode_rekomendasi_id) || '-- Pilih / Cari Kode Rekomendasi --'"></span>
                                                    <svg class="h-4 w-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                {{-- Dropdown Menu Overlay --}}
                                                <div x-show="open" @click.outside="open = false" x-cloak
                                                     class="absolute z-50 mt-1 w-full rounded-2xl border border-gray-200 bg-white p-2.5 shadow-xl dark:border-gray-800 dark:bg-gray-900 space-y-2">
                                                    {{-- Search Input Box --}}
                                                    <div class="relative">
                                                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                        </svg>
                                                        <input type="text" x-ref="searchRekomInput" x-model="search" placeholder="Ketik kode atau kata kunci rekomendasi..."
                                                               class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-3.5 py-2 text-xs text-gray-900 outline-none focus:border-blue-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                    </div>

                                                    {{-- Options Scroll Area --}}
                                                    <div class="max-h-60 overflow-y-auto space-y-1 pr-1">
                                                        <template x-for="k in getFilteredKodeRekoms(t.kode_temuan_id).filter(item => {
                                                            const q = search.toLowerCase().trim();
                                                            if (!q) return true;
                                                            return `[${item.kode}] ${item.deskripsi || ''}`.toLowerCase().includes(q);
                                                        })" :key="k.id">
                                                            <button type="button" @click="r.kode_rekomendasi_id = k.id; open = false; search = ''"
                                                                    class="w-full text-left rounded-xl px-3 py-2 text-xs transition flex items-start gap-2"
                                                                    :class="r.kode_rekomendasi_id == k.id ? 'bg-blue-50 text-blue-700 font-bold dark:bg-blue-900/40 dark:text-blue-300' : 'hover:bg-gray-50 text-gray-700 dark:text-gray-300 dark:hover:bg-gray-800/80'">
                                                                <span class="inline-block rounded-md bg-emerald-100/70 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 px-1.5 py-0.5 font-mono text-[10px] font-bold shrink-0" x-text="`[${k.kode}]`"></span>
                                                                <span class="flex-1 text-[11px] leading-tight" x-text="k.deskripsi"></span>
                                                            </button>
                                                        </template>
                                                        
                                                        {{-- Empty Search Result --}}
                                                        <div x-show="getFilteredKodeRekoms(t.kode_temuan_id).filter(item => `[${item.kode}] ${item.deskripsi || ''}`.toLowerCase().includes(search.toLowerCase().trim())).length === 0"
                                                             class="p-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                                            Tidak ada Kode Rekomendasi yang cocok dengan "<span x-text="search"></span>"
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Info Box Kode Rekomendasi terpilih --}}
                                                <div x-show="r.kode_rekomendasi_id" x-transition
                                                     class="mt-2 rounded-lg bg-emerald-50 border border-emerald-100 px-3 py-2 text-xs text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                                                    <span class="font-bold">Info Kode:</span>
                                                    <span x-text="getKodeRekomLabel(r.kode_rekomendasi_id)"></span>
                                                </div>
                                            </div>

                                            {{-- Jenis Rekomendasi --}}
                                            <div>
                                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    Jenis Rekomendasi <span class="text-red-500">*</span>
                                                </label>
                                                <select :name="`temuans[${tIdx}][recommendations][${rIdx}][jenis_rekomendasi]`" x-model="r.jenis_rekomendasi" data-no-ts
                                                        class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                                    <option value="uang">Keuangan (Uang)</option>
                                                    <option value="barang">Barang / Aset</option>
                                                    <option value="administrasi">Administratif</option>
                                                </select>
                                            </div>

                                            {{-- Batas Waktu --}}
                                            <div>
                                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    Batas Waktu Tindak Lanjut <span class="text-red-500">*</span>
                                                </label>
                                                <input type="date" :name="`temuans[${tIdx}][recommendations][${rIdx}][batas_waktu]`" x-model="r.batas_waktu" onclick="this.showPicker()"
                                                       class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                            </div>

                                            {{-- Nilai Rekomendasi (Rp) --}}
                                            <div>
                                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    Nilai Rekomendasi (Rp)
                                                </label>
                                                <div class="relative flex items-center">
                                                    <span class="pointer-events-none absolute left-3.5 text-xs font-bold text-gray-400">Rp</span>
                                                    <input type="number" :name="`temuans[${tIdx}][recommendations][${rIdx}][nilai_rekom]`" x-model.number="r.nilai_rekom"
                                                           @input="r._is_manual_nilai = Boolean(r.nilai_rekom && r.nilai_rekom > 0); if (r.nilai_rekom > 0) r.jenis_rekomendasi = 'uang'" placeholder="0"
                                                           class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3.5 py-2.5 text-xs font-bold text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-blue-500 outline-none">
                                                </div>
                                                
                                                <div class="mt-1 flex items-center justify-between text-[11px] px-1" x-show="r.nilai_rekom > 0">
                                                    <span class="text-slate-500">Format: <strong class="text-blue-600 dark:text-blue-400" x-text="fmtRupiah(r.nilai_rekom)"></strong></span>
                                                    <span class="text-slate-500">Plafon: <strong class="text-rose-600" x-text="fmtRupiah(t.nilai_temuan)"></strong></span>
                                                </div>

                                                {{-- Warning jika nilai rekomendasi melebihi plafon temuan --}}
                                                <div x-show="t.nilai_temuan > 0 && r.nilai_rekom > t.nilai_temuan" x-transition
                                                     class="mt-2 flex items-center gap-1.5 rounded-lg bg-amber-50 border border-amber-200 p-2.5 text-xs text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300">
                                                    <svg class="h-4 w-4 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    ⚠️ Nilai rekomendasi melebihi plafon temuan (<span x-text="fmtRupiah(t.nilai_temuan)"></span>). Pastikan ini disengaja.
                                                </div>
                                            </div>

                                            {{-- Uraian Rekomendasi --}}
                                            <div class="md:col-span-2">
                                                <div class="flex items-center justify-between mb-1.5">
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                        Uraian Rekomendasi <span class="text-red-500">*</span>
                                                    </label>
                                                    <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500" x-text="`${(r.uraian_rekom || '').length} karakter`"></span>
                                                </div>
                                                <textarea :name="`temuans[${tIdx}][recommendations][${rIdx}][uraian_rekom]`" x-model="r.uraian_rekom" rows="4" placeholder="Tuliskan petunjuk / instruksi rekomendasi secara jelas, spesifik, dan terukur..."
                                                          class="w-full resize-y min-h-[100px] rounded-xl border border-gray-300 bg-white p-3.5 text-sm leading-relaxed text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="t.recommendations.length === 0" class="text-center py-4 text-xs text-gray-400">
                                    Belum ada rekomendasi untuk temuan ini. Klik "+ Rekomendasi Lain" di atas.
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Stepper Footer Step 3 --}}
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/30">
                    <button type="button" @click="prevStep()"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        ← Kembali ke Step 2
                    </button>
                    <button type="button" @click="nextStep()"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 active:scale-95 transition-all">
                        Lanjut ke Langkah 4: Review &amp; Finalisasi →
                    </button>
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════ --}}
        {{-- ── STEP 4: REVIEW & FINALISASI LHP ── --}}
        {{-- ═════════════════════════════════════════════════════════════ --}}
        <div x-show="step === 4" x-cloak>
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Langkah 4: Pratinjau &amp; Finalisasi LHP</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tinjau seluruh data sebelum disimpan dan diterbitkan ke sistem.</p>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Summary Stat Highlight Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="rounded-xl border border-gray-200/80 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <span class="text-[10px] font-bold uppercase text-gray-400">Nomor &amp; Tanggal LHP</span>
                            <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white" x-text="nomorLhp || '-'"></p>
                            <p class="text-xs text-gray-500" x-text="tanggalLhp || '-'"></p>
                        </div>
                        <div class="rounded-xl border border-gray-200/80 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <span class="text-[10px] font-bold uppercase text-gray-400">Total Temuan</span>
                            <p class="mt-1 text-xl font-bold" :class="isNihil ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-white'" x-text="isNihil ? '0 Temuan (Nihil)' : `${temuans.length} Temuan`"></p>
                            <p class="text-xs font-semibold" :class="isNihil ? 'text-emerald-600' : 'text-red-600'" x-text="isNihil ? 'Bebas Kerugian' : `Nilai Kerugian: ${fmtRupiah(totalKerugianSum)}`"></p>
                        </div>
                        <div class="rounded-xl border border-gray-200/80 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <span class="text-[10px] font-bold uppercase text-gray-400">Status Progress TL</span>
                            <p class="mt-1 text-xl font-bold" :class="isNihil ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400'" x-text="isNihil ? '100% Selesai' : `${totalRekomCount} Rekomendasi`"></p>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="isNihil ? 'Tidak ada tunggakan' : `Nilai Rekomendasi: ${fmtRupiah(totalNilaiRekomSum)}`"></p>
                        </div>
                    </div>

                    {{-- Summary Tree List --}}
                    <div class="rounded-xl border border-gray-200/80 bg-white p-4 space-y-4 dark:border-gray-700 dark:bg-gray-900/30">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Rincian Temuan &amp; Rekomendasi</h3>
                        
                        <template x-if="isNihil">
                            <div class="p-5 rounded-xl bg-emerald-50/80 border border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800/50 text-center space-y-2">
                                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">LHP Ini Dinyatakan Bebas Temuan (Nihil)</h4>
                                <p class="text-xs text-emerald-700 dark:text-emerald-300/80 max-w-lg mx-auto">
                                    Hasil pemeriksaan tidak mencatat adanya temuan ketidakpatuhan, kelemahan SPI, maupun kerugian negara/daerah. Status Tindak Lanjut otomatis 100% Tuntas.
                                </p>
                            </div>
                        </template>

                        <template x-if="!isNihil">
                            <div class="space-y-3">
                                <template x-for="(t, i) in temuans" :key="t.id">
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/70 p-3 text-xs space-y-2 dark:border-gray-800 dark:bg-gray-800/40">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-900 dark:text-white" x-text="`Temuan #${i + 1}: ${getKodeTemuanLabel(t.kode_temuan_id)}`"></span>
                                            <span class="font-semibold text-red-600" x-text="fmtRupiah(t.nilai_temuan)"></span>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap break-words" x-text="t.kondisi"></p>

                                        {{-- Nested Rekom list --}}
                                        <div class="mt-2 space-y-1 pl-3 border-l-2 border-blue-400">
                                            <template x-for="(r, j) in t.recommendations" :key="r.id">
                                                <div class="flex items-start justify-between text-[11px]">
                                                    <span class="text-gray-700 dark:text-gray-300" x-text="`• Rekom #${j + 1}: ${r.uraian_rekom}`"></span>
                                                    <span class="font-semibold text-gray-900 dark:text-white" x-text="r.jenis_rekomendasi === 'uang' ? fmtRupiah(r.nilai_rekom) : r.jenis_rekomendasi"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                         </div>
                    {{-- Lampiran LHP Preview Box --}}
                    <div class="rounded-xl border border-gray-200/80 bg-white p-4 space-y-3 dark:border-gray-700 dark:bg-gray-900/30">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Berkas Lampiran LHP</h3>
                            <span class="text-xs font-semibold text-gray-500" x-text="`${attachments.length} Berkas Disiapkan`"></span>
                        </div>

                        <template x-if="attachments.length === 0">
                            <p class="text-xs text-gray-400 italic">Tidak ada berkas lampiran pendukung yang disertakan (opsional).</p>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" x-show="attachments.length > 0">
                            <template x-for="(att, idx) in attachments" :key="att.id">
                                <div class="flex items-center gap-2.5 p-2.5 rounded-lg border border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/40 text-xs">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg font-bold text-[9px] uppercase"
                                         :class="att.file_ext === 'PDF' ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'">
                                        <span x-text="att.file_ext || 'FILE'"></span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-gray-900 dark:text-white truncate" x-text="att.file_name || att.raw_name || 'Lampiran LHP'"></p>
                                        <p class="text-[10px] text-gray-400 truncate" x-text="att.raw_name ? `${att.raw_name} (${att.file_size || '0 KB'})` : 'Berkas lokal'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Stepper Footer Step 4 --}}
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/30">
                    <button type="button" @click="prevStep()"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <span x-text="isNihil ? '← Kembali ke Step 1' : '← Kembali ke Edit Step 3'"></span>
                    </button>
                    <button type="submit" id="btn-submit-final"
                            class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-xs font-bold text-white shadow-lg transition-all"
                            :class="isNihil ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-green-600 hover:bg-green-700 shadow-green-600/20'">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="btn-submit-text" x-text="isNihil ? 'Simpan &amp; Terbitkan LHP (Bebas Temuan)' : 'Simpan &amp; Terbitkan LHP'"></span>
                    </button>
                </div>
            </div>
        </div>

    </form>
        </div>
    </div>
</div>
@endsection