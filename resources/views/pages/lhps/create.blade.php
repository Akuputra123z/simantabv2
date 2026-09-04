@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .ts-assignment-tall .ts-control {
        height: auto;
        min-height: 56px;
        padding: 10px 14px;
        align-items: flex-start;
    }
</style>

@php
    $assignmentData = $assignments->map(function ($a) {
        return [
            'id'                      => $a->id,
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
const KODE_TEMUANS    = @json($kodeTemuanData);
const KODE_REKOMS     = @json($kodeRekomData);
const USED_UNIT_MAP   = @json($usedUnitMap);
const CSRF_TOKEN      = '{{ $csrfToken }}';

function lhpCreateWizard() {
    return {
        step: 1,

        // Step 1 Form Data
        programId: '',
        assignmentId: '',
        unitId: '',
        nomorLhp: '{{ old("nomor_lhp", "") }}',
        tanggalLhp: '{{ old("tanggal_lhp", date("Y-m-d")) }}',
        catatanUmum: '{{ old("catatan_umum", "") }}',

        // Step 2 & 3 Dynamic Data Arrays
        temuans: [],
        attachments: [],

        // Modal & AJAX submit state
        showConfirmModal: false,
        showSuccessModal: false,
        successMessage: '',
        redirectUrl: '',
        isSubmitting: false,
        formTarget: null,

        // Validation error messages
        stepErrors: [],

        openConfirmModal(event) {
            if (event) event.preventDefault();

            if (!this.validateAllSteps()) {
                window.scrollTo({ top: 100, behavior: 'smooth' });
                return false;
            }

            this.formTarget = event ? event.target : document.getElementById('form-lhp-wizard');
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

            const form = this.formTarget || document.getElementById('form-lhp-wizard');
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.successMessage = data.message || 'LHP berhasil dibuat.';
                    this.redirectUrl = data.redirect || '{{ route("lhps.index") }}';
                    this.showSuccessModal = true;
                } else {
                    alert(data.message || 'Gagal menyimpan LHP. Silakan periksa kembali data Anda.');
                    if (btn) btn.disabled = false;
                    if (txt) txt.textContent = 'Simpan & Terbitkan LHP';
                    this.isSubmitting = false;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan sistem/koneksi saat menyimpan LHP.');
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
            const assVal = hiddenAss ? hiddenAss.value : this.assignmentId;
            const unitVal = hiddenUnit ? hiddenUnit.value : this.unitId;
            const isStep1Valid = Boolean(assVal && unitVal && this.nomorLhp && this.nomorLhp.trim() && this.tanggalLhp);

            if (targetStep === 2) return isStep1Valid;

            // Step 2 check
            const isStep2Valid = isStep1Valid && this.temuans.length > 0 && this.temuans.every(t => t.kondisi && t.kondisi.trim());

            if (targetStep === 3) return isStep2Valid;

            // Step 3 check
            const isStep3Valid = isStep2Valid && this.temuans.every(t => 
                t.recommendations.every(r => r.kode_rekomendasi_id && r.uraian_rekom && r.uraian_rekom.trim())
            );

            if (targetStep === 4) return isStep3Valid;

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
                return;
            }
            this.step = targetStep;
            window.scrollTo({ top: 100, behavior: 'smooth' });
        },

        nextStep() {
            if (this.validateCurrentStep()) {
                this.step = Math.min(4, this.step + 1);
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
                const assVal = hiddenAss ? hiddenAss.value : this.assignmentId;
                const unitVal = hiddenUnit ? hiddenUnit.value : this.unitId;

                if (!assVal) this.stepErrors.push('Pilih Penugasan Audit (Surat Tugas) terlebih dahulu.');
                if (!unitVal) this.stepErrors.push('Pilih Unit Kerja / Objek Audit terlebih dahulu.');
                if (!this.nomorLhp.trim()) this.stepErrors.push('Nomor LHP wajib diisi.');
                if (!this.tanggalLhp) this.stepErrors.push('Tanggal LHP wajib diisi.');
            }

            if (this.step === 2) {
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

            if (this.step === 3) {
                this.temuans.forEach((t, i) => {
                    t.recommendations.forEach((r, j) => {
                        if (!r.kode_rekomendasi_id) {
                            this.stepErrors.push(`Pilih Kode Rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}).`);
                        }
                        if (!r.uraian_rekom || !r.uraian_rekom.trim()) {
                            this.stepErrors.push(`Uraian rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}) belum diisi.`);
                        }
                    });
                });
            }

            return this.stepErrors.length === 0;
        },

        validateAllSteps() {
            this.stepErrors = [];

            const hiddenAss = document.getElementById('hidden-assignment-id');
            const hiddenUnit = document.getElementById('hidden-unit-id');
            const assVal = hiddenAss ? hiddenAss.value : this.assignmentId;
            const unitVal = hiddenUnit ? hiddenUnit.value : this.unitId;

            if (!assVal) this.stepErrors.push('Pilih Penugasan Audit (Surat Tugas) terlebih dahulu.');
            if (!unitVal) this.stepErrors.push('Pilih Unit Kerja / Objek Audit terlebih dahulu.');
            if (!this.nomorLhp || !this.nomorLhp.trim()) this.stepErrors.push('Nomor LHP wajib diisi.');
            if (!this.tanggalLhp) this.stepErrors.push('Tanggal LHP wajib diisi.');

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
                t.recommendations.forEach((r, j) => {
                    if (!r.kode_rekomendasi_id) {
                        this.stepErrors.push(`Pilih Kode Rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}).`);
                    }
                    if (!r.uraian_rekom || !r.uraian_rekom.trim()) {
                        this.stepErrors.push(`Uraian rekomendasi pada Rekomendasi #${j + 1} (Temuan #${i + 1}) belum diisi.`);
                    }
                });
            });

            return this.stepErrors.length === 0;
        },

        // Cascade Dropdown Logika Step 1
        initCascadeDropdowns() {
            const selProgram    = document.getElementById('select-program');
            const selAssignment = document.getElementById('select-assignment');
            const selUnit       = document.getElementById('select-unit');

            if (!selProgram || !selAssignment || !selUnit) return;

            const seen = new Set();
            ALL_ASSIGNMENTS.forEach(a => {
                if (!seen.has(a.program_id)) {
                    seen.add(a.program_id);
                    selProgram.add(new Option(a.program_nama, a.program_id));
                }
            });

            const tsProgram = new TomSelect('#select-program', {
                create: false, placeholder: '-- Pilih Program Kerja (PKPT) --', controlInput: '<input>', maxOptions: null
            });
            const tsAssignment = new TomSelect('#select-assignment', {
                create: false, placeholder: '-- Pilih Penugasan Audit --', controlInput: '<input>', maxOptions: null
            });
            tsAssignment.disable();

            const tsUnit = new TomSelect('#select-unit', {
                create: false, placeholder: '-- Pilih Unit Kerja / Objek Audit --', controlInput: '<input>', maxOptions: null
            });
            tsUnit.disable();

            tsProgram.on('change', (programId) => {
                this.programId = programId;
                tsAssignment.clear(); tsAssignment.clearOptions(); tsAssignment.disable();
                tsUnit.clear(); tsUnit.clearOptions(); tsUnit.disable();
                this.assignmentId = ''; this.unitId = '';

                if (!programId) return;
                const filtered = ALL_ASSIGNMENTS.filter(a => String(a.program_id) === String(programId));
                filtered.forEach(a => {
                    const label = a.detail_nama + (a.nomor_surat ? ' — ' + a.nomor_surat : '');
                    tsAssignment.addOption({ value: a.id, text: label });
                });
                if (filtered.length > 0) tsAssignment.enable();
            });

            tsAssignment.on('change', (assignmentId) => {
                this.assignmentId = assignmentId;
                const hiddenAss = document.getElementById('hidden-assignment-id');
                if (hiddenAss) hiddenAss.value = assignmentId;

                tsUnit.clear(); tsUnit.clearOptions(); tsUnit.disable();
                this.unitId = '';
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = '';

                if (!assignmentId) return;
                const assignment = ALL_ASSIGNMENTS.find(a => String(a.id) === String(assignmentId));
                const usedIds    = USED_UNIT_MAP[assignmentId] || [];

                if (assignment && assignment.units.length > 0) {
                    const available = assignment.units.filter(u => !usedIds.includes(u.id));
                    if (available.length === 0) {
                        tsUnit.addOption({ value: '', text: '-- Semua unit sudah dibuatkan LHP --' });
                        return;
                    }
                    available.forEach(u => tsUnit.addOption({ value: u.id, text: u.nama }));
                    tsUnit.enable();
                    if (available.length === 1) {
                        tsUnit.setValue(available[0].id);
                        this.unitId = available[0].id;
                        if (hiddenUnit) hiddenUnit.value = available[0].id;
                    }
                }
            });

            tsUnit.on('change', (unitId) => {
                this.unitId = unitId;
                const hiddenUnit = document.getElementById('hidden-unit-id');
                if (hiddenUnit) hiddenUnit.value = unitId;
            });
        },

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
            const negara = parseFloat(t.nilai_kerugian_negara || 0);
            const daerah = parseFloat(t.nilai_kerugian_daerah || 0);
            const desa   = parseFloat(t.nilai_kerugian_desa || 0);
            const bos    = parseFloat(t.nilai_kerugian_bos_blud || 0);
            t.nilai_temuan = negara + daerah + desa + bos;

            // Auto-update plafon nilai rekomendasi pertama jika nilainya belum diubah manual
            if (t.recommendations.length > 0 && t.nilai_temuan > 0) {
                if (!t.recommendations[0].nilai_rekom || t.recommendations[0].nilai_rekom === 0) {
                    t.recommendations[0].nilai_rekom = t.nilai_temuan;
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

            const newRekom = {
                id: Date.now() + Math.random(),
                kode_rekomendasi_id: '',
                jenis_rekomendasi: temuan.nilai_temuan > 0 ? 'uang' : 'administrasi',
                nilai_rekom: temuan.recommendations.length === 0 ? temuan.nilai_temuan : 0,
                uraian_rekom: '',
                batas_waktu: defaultBatas.toISOString().split('T')[0],
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

        // Logika Lampiran File (Step 1)
        addAttachment() {
            this.attachments.push({ id: Date.now(), file_name: '' });
        },

        removeAttachment(index) {
            this.attachments.splice(index, 1);
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
                © <span>{{ date('Y') }}</span> SIMANTAB • TailAdmin
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
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Tahapan Pengisian</h2>
                
                <nav class="space-y-3">
                    {{-- Step 1 Button --}}
                    <button type="button" @click="setStep(1)"
                            class="w-full group relative flex items-center gap-3.5 rounded-xl border p-3.5 text-left transition-all duration-200"
                            :class="step === 1
                                ? 'border-blue-600 bg-blue-50/80 text-blue-900 shadow-sm dark:border-blue-500 dark:bg-blue-900/30 dark:text-blue-200 ring-2 ring-blue-500/20'
                                : 'border-gray-100 bg-gray-50/60 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300'">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-xs transition-all"
                             :class="step === 1 ? 'bg-blue-600 text-white' : (canGoToStep(2) ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300')">
                            <span x-text="canGoToStep(2) ? '✓' : '1'"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold">1. Informasi LHP</p>
                            <p class="mt-0.5 truncate text-[11px] opacity-75">PKPT, ST &amp; Unit Kerja</p>
                        </div>
                    </button>

                    {{-- Step 2 Button --}}
                    <button type="button" @click="setStep(2)" :disabled="!canGoToStep(2)"
                            class="w-full group relative flex items-center gap-3.5 rounded-xl border p-3.5 text-left transition-all duration-200"
                            :class="step === 2
                                ? 'border-blue-600 bg-blue-50/80 text-blue-900 shadow-sm dark:border-blue-500 dark:bg-blue-900/30 dark:text-blue-200 ring-2 ring-blue-500/20'
                                : (canGoToStep(2) ? 'border-gray-100 bg-gray-50/60 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300' : 'border-gray-100 bg-gray-100/40 text-gray-400 dark:border-gray-800 dark:bg-gray-800/20 cursor-not-allowed opacity-60')">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-xs transition-all"
                             :class="step === 2 ? 'bg-blue-600 text-white' : (canGoToStep(3) ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400')">
                            <span x-text="canGoToStep(3) ? '✓' : (canGoToStep(2) ? '2' : '🔒')"></span>
                        </div>
                        <div class="min-w-0 flex-1 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold">2. Temuan Audit</p>
                                <p class="mt-0.5 truncate text-[11px] opacity-75" x-text="`${temuans.length} Temuan`"></p>
                            </div>
                            <span x-show="!canGoToStep(2)" class="text-[10px] font-bold uppercase text-gray-400">Terkunci</span>
                        </div>
                    </button>

                    {{-- Step 3 Button --}}
                    <button type="button" @click="setStep(3)" :disabled="!canGoToStep(3)"
                            class="w-full group relative flex items-center gap-3.5 rounded-xl border p-3.5 text-left transition-all duration-200"
                            :class="step === 3
                                ? 'border-blue-600 bg-blue-50/80 text-blue-900 shadow-sm dark:border-blue-500 dark:bg-blue-900/30 dark:text-blue-200 ring-2 ring-blue-500/20'
                                : (canGoToStep(3) ? 'border-gray-100 bg-gray-50/60 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300' : 'border-gray-100 bg-gray-100/40 text-gray-400 dark:border-gray-800 dark:bg-gray-800/20 cursor-not-allowed opacity-60')">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-xs transition-all"
                             :class="step === 3 ? 'bg-blue-600 text-white' : (canGoToStep(4) ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400')">
                            <span x-text="canGoToStep(4) ? '✓' : (canGoToStep(3) ? '3' : '🔒')"></span>
                        </div>
                        <div class="min-w-0 flex-1 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold">3. Rekomendasi</p>
                                <p class="mt-0.5 truncate text-[11px] opacity-75" x-text="`${totalRekomCount} Rekomendasi`"></p>
                            </div>
                            <span x-show="!canGoToStep(3)" class="text-[10px] font-bold uppercase text-gray-400">Terkunci</span>
                        </div>
                    </button>

                    {{-- Step 4 Button --}}
                    <button type="button" @click="setStep(4)" :disabled="!canGoToStep(4)"
                            class="w-full group relative flex items-center gap-3.5 rounded-xl border p-3.5 text-left transition-all duration-200"
                            :class="step === 4
                                ? 'border-blue-600 bg-blue-50/80 text-blue-900 shadow-sm dark:border-blue-500 dark:bg-blue-900/30 dark:text-blue-200 ring-2 ring-blue-500/20'
                                : (canGoToStep(4) ? 'border-gray-100 bg-gray-50/60 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300' : 'border-gray-100 bg-gray-100/40 text-gray-400 dark:border-gray-800 dark:bg-gray-800/20 cursor-not-allowed opacity-60')">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-xs transition-all"
                             :class="step === 4 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                            <span x-text="canGoToStep(4) ? '4' : '🔒'"></span>
                        </div>
                        <div class="min-w-0 flex-1 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold">4. Finalisasi LHP</p>
                                <p class="mt-0.5 truncate text-[11px] opacity-75">Review &amp; Submit</p>
                            </div>
                            <span x-show="!canGoToStep(4)" class="text-[10px] font-bold uppercase text-gray-400">Terkunci</span>
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

                {{-- Hidden Cascade Values --}}
                <input type="hidden" name="audit_assignment_id" id="hidden-assignment-id" :value="assignmentId">
                <input type="hidden" name="unit_diperiksa_id" id="hidden-unit-id" :value="unitId">

        {{-- ═════════════════════════════════════════════════════════════ --}}
        {{-- ── STEP 1: INFORMASI UTAMA & LAMPIRAN LHP ── --}}
        {{-- ═════════════════════════════════════════════════════════════ --}}
        <div x-show="step === 1" x-cloak>
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Langkah 1: Informasi Utama &amp; Administrasi LHP</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pilih penugasan audit, unit kerja diperiksa, serta nomor dan tanggal LHP.</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        
                        {{-- 1. Program Kerja PKPT --}}
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Program Kerja (PKPT) <span class="text-red-500">*</span>
                            </label>
                            <select id="select-program" data-no-ts class="w-full">
                                <option value="">-- Pilih Program Kerja --</option>
                            </select>
                        </div>

                        {{-- 2. Penugasan Audit --}}
                        <div class="md:col-span-1">
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Penugasan Audit / Surat Tugas <span class="text-red-500">*</span>
                            </label>
                            <select id="select-assignment" data-no-ts disabled class="ts-assignment-tall w-full">
                                <option value="">-- Pilih Program Terlebih Dahulu --</option>
                            </select>
                        </div>

                        {{-- 3. Unit Kerja / Objek Audit --}}
                        <div class="md:col-span-1">
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Unit Kerja / Objek Audit <span class="text-red-500">*</span>
                            </label>
                            <select id="select-unit" data-no-ts disabled class="w-full">
                                <option value="">-- Pilih Penugasan Terlebih Dahulu --</option>
                            </select>
                        </div>

                        {{-- Nomor LHP --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Nomor LHP <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nomor_lhp" x-model="nomorLhp"
                                   placeholder="Contoh: LHP/001/IRBAN/2024"
                                   class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>

                        {{-- Tanggal LHP --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Tanggal LHP <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_lhp" x-model="tanggalLhp" onclick="this.showPicker()"
                                   class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>

                        {{-- Catatan Umum --}}
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Catatan Umum / Ringkasan LHP</label>
                            <textarea name="catatan_umum" x-model="catatanUmum" rows="3"
                                      placeholder="Tuliskan catatan ringkas mengenai LHP ini..."
                                      class="w-full resize-none rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    {{-- Dynamic Berkas Lampiran LHP --}}
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-white/90">
                                    Berkas Lampiran LHP (Opsional)
                                </h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Unggah bukti pendukung LHP dalam format PDF, JPG, PNG, atau JPEG.</p>
                            </div>
                            <button type="button" @click="addAttachment()"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 transition">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                 Tambah File
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(att, aIdx) in attachments" :key="att.id">
                                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" x-text="`Lampiran #${aIdx + 1}`"></span>
                                        <button type="button" @click="removeAttachment(aIdx)" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus Lampiran
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                                Upload File
                                            </label>
                                            <input type="file" :name="`attachments[${aIdx}][file_path]`" accept=".pdf,.jpg,.jpeg,.png"
                                                   class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                                Nama / Keterangan File
                                            </label>
                                            <input type="text" :name="`attachments[${aIdx}][file_name]`" x-model="att.file_name" placeholder="cth: Laporan Hasil Audit (PDF)..."
                                                   class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-xs text-gray-900 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-400">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="attachments.length === 0" class="rounded-xl border border-dashed border-gray-200 py-6 text-center text-xs text-gray-400 dark:border-gray-800 dark:text-gray-500">
                                Belum ada berkas lampiran ditambahkan. Klik <strong>"+ Tambah File"</strong> di atas.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stepper Footer Step 1 --}}
                <div class="flex items-center justify-end border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/30">
                    <button type="button" @click="nextStep()"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 active:scale-95 transition-all">
                        Lanjut ke Langkah 2: Input Temuan →
                    </button>
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
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Uraian Kondisi Temuan <span class="text-red-500">*</span></label>
                                    <textarea :name="`temuans[${tIdx}][kondisi]`" x-model="t.kondisi" rows="3" placeholder="Uraikan kondisi temuan secara mendetail..."
                                              class="w-full resize-none rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-blue-500 outline-none" required></textarea>
                                </div>

                                {{-- Sebab --}}
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Penyebab (Sebab)</label>
                                    <textarea :name="`temuans[${tIdx}][sebab]`" x-model="t.sebab" rows="2" placeholder="Uraikan penyebab temuan..."
                                              class="w-full resize-none rounded-xl border border-gray-300 bg-white p-2.5 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none"></textarea>
                                </div>

                                {{-- Akibat --}}
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Dampak (Akibat)</label>
                                    <textarea :name="`temuans[${tIdx}][akibat]`" x-model="t.akibat" rows="2" placeholder="Uraikan dampak temuan..."
                                              class="w-full resize-none rounded-xl border border-gray-300 bg-white p-2.5 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none"></textarea>
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
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic" x-text="t.kondisi ? 'Kondisi: ' + t.kondisi : '(kondisi temuan belum diisi)'"></p>
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
                                                           @input="if (r.nilai_rekom > 0) r.jenis_rekomendasi = 'uang'" placeholder="0"
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
                                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                                    Uraian Rekomendasi <span class="text-red-500">*</span>
                                                </label>
                                                <textarea :name="`temuans[${tIdx}][recommendations][${rIdx}][uraian_rekom]`" x-model="r.uraian_rekom" rows="3" placeholder="Tuliskan petunjuk / instruksi rekomendasi secara jelas, spesifik, dan terukur..."
                                                          class="w-full resize-none rounded-xl border border-gray-300 bg-white p-3 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-blue-500 outline-none" required></textarea>
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
                            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white" x-text="`${temuans.length} Temuan`"></p>
                            <p class="text-xs font-semibold text-red-600" x-text="`Nilai Kerugian: ${fmtRupiah(totalKerugianSum)}`"></p>
                        </div>
                        <div class="rounded-xl border border-gray-200/80 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <span class="text-[10px] font-bold uppercase text-gray-400">Total Rekomendasi</span>
                            <p class="mt-1 text-xl font-bold text-blue-600 dark:text-blue-400" x-text="`${totalRekomCount} Rekomendasi`"></p>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="`Nilai Rekomendasi: ${fmtRupiah(totalNilaiRekomSum)}`"></p>
                        </div>
                    </div>

                    {{-- Summary Tree List --}}
                    <div class="rounded-xl border border-gray-200/80 bg-white p-4 space-y-4 dark:border-gray-700 dark:bg-gray-900/30">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Rincian Temuan &amp; Rekomendasi</h3>
                        <div class="space-y-3">
                            <template x-for="(t, i) in temuans" :key="t.id">
                                <div class="rounded-lg border border-gray-100 bg-gray-50/70 p-3 text-xs space-y-2 dark:border-gray-800 dark:bg-gray-800/40">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900 dark:text-white" x-text="`Temuan #${i + 1}: ${getKodeTemuanLabel(t.kode_temuan_id)}`"></span>
                                        <span class="font-semibold text-red-600" x-text="fmtRupiah(t.nilai_temuan)"></span>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400" x-text="t.kondisi"></p>

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
                    </div>
                </div>

                {{-- Stepper Footer Step 4 --}}
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/30">
                    <button type="button" @click="prevStep()"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        ← Kembali ke Edit Step 3
                    </button>
                    <button type="submit" id="btn-submit-final"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-6 py-3 text-xs font-bold text-white shadow-lg shadow-green-600/20 hover:bg-green-700 active:scale-95 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="btn-submit-text">Simpan &amp; Terbitkan LHP</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
        </div>
    </div>
</div>
@endsection