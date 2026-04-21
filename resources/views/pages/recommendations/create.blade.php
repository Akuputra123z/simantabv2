{{-- resources/views/pages/recommendations/create.blade.php --}}
@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8" x-data="rekomendasiForm()" x-init="init()">

    {{-- HEADER --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Rekomendasi Baru</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pilih LHP dan Temuan untuk menambahkan rekomendasi.</p>
        </div>
        <a href="{{ route('recommendations.index') }}"
           class="text-sm font-medium text-brand-500 hover:text-brand-600 transition-colors dark:text-brand-400">
            &larr; Kembali ke Daftar
        </a>
    </div>

    {{-- FLASH ERROR --}}
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- API ERROR ALERT --}}
    <div x-show="fetchError" x-transition
         class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 flex items-start gap-3">
        <svg class="h-4 w-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="font-semibold">Gagal memuat data temuan</p>
            <p x-text="fetchError" class="mt-0.5 opacity-80"></p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Form Input Rekomendasi</h3>
        </div>

        <div class="p-5 sm:p-6">
            <form action="{{ route('recommendations.store') }}" method="POST" id="form-rekom">
                @csrf
                <div class="-mx-2.5 flex flex-wrap gap-y-5">

                    {{-- ── PILIH LHP ── --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nomor LHP <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="lhp_id"
                                    x-model="lhpId"
                                    @change="onLhpChange()"
                                    required
                                    class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">-- Pilih Nomor LHP --</option>
                                @foreach($lhps as $lhp)
                                    <option value="{{ $lhp->id }}"
                                        {{ old('lhp_id') == $lhp->id ? 'selected' : '' }}>
                                        {{ $lhp->nomor_lhp }} ({{ $lhp->tanggal_lhp->format('Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- ── PILIH TEMUAN ── --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Pilih Temuan <span class="text-red-500">*</span>
                        </label>

                        {{-- Loading skeleton --}}
                        <div x-show="loading" class="h-11 w-full rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 animate-pulse flex items-center px-4">
                            <span class="text-xs text-gray-400">Memuat data temuan...</span>
                        </div>

                        {{-- Select temuan --}}
                        <div x-show="!loading" class="relative">
                            <select name="temuan_id"
                                    x-model="temuanId"
                                    @change="onTemuanChange()"
                                    :disabled="!lhpId || temuans.length === 0"
                                    required
                                    class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 disabled:bg-gray-100 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-white/5">

                                {{-- Placeholder dinamis --}}
                                <option value="">
                                    <template x-if="!lhpId">Pilih LHP terlebih dahulu</template>
                                    <template x-if="lhpId && temuans.length === 0">Tidak ada temuan tersedia</template>
                                    <template x-if="lhpId && temuans.length > 0">-- Pilih Temuan --</template>
                                </option>

                                {{-- Loop temuan dari API --}}
                                <template x-for="t in temuans" :key="t.id">
                                    <option :value="t.id" x-text="t.label"></option>
                                </template>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>

                        {{-- Info: semua temuan sudah selesai --}}
                        <div x-show="lhpId && !loading && temuans.length === 0" x-transition
                             class="mt-2 rounded-lg bg-blue-50 border border-blue-100 px-3 py-2 dark:bg-blue-900/20 dark:border-blue-800">
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                ✓ Semua temuan pada LHP ini sudah memiliki rekomendasi selesai.
                            </p>
                        </div>

                        {{-- Preview kondisi temuan terpilih --}}
                        <div x-show="selectedTemuan && selectedTemuan.kondisi_full" x-transition
                             class="mt-2 rounded-lg bg-gray-50 border border-gray-100 px-3 py-2 dark:bg-gray-800 dark:border-gray-700">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">Kondisi Temuan</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed" x-text="selectedTemuan?.kondisi_full"></p>
                            <div x-show="selectedTemuan?.nilai_temuan > 0" class="mt-1.5 flex items-center gap-1">
                                <span class="text-[10px] font-bold text-rose-500">Nilai Temuan:</span>
                                <span class="text-[10px] text-rose-600 font-semibold" x-text="formatRupiah(selectedTemuan?.nilai_temuan ?? 0)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ── KODE REKOMENDASI ── --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Kode Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="kode_rekomendasi_id"
                                    x-model="kodeRekId"
                                    @change="onKodeRekChange()"
                                    required
                                    class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">-- Pilih Kode --</option>
                                <template x-for="k in filteredKodeRekoms" :key="k.id">
                                    <option :value="k.id" x-text="k.kode + ' — ' + k.deskripsi"></option>
                                </template>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        <div x-show="selectedKodeRek" x-transition
                             class="mt-2 rounded-lg bg-green-50 border border-green-100 px-3 py-2 dark:bg-green-900/20 dark:border-green-800">
                            <p class="text-xs text-green-700 dark:text-green-300"
                               x-text="selectedKodeRek ? selectedKodeRek.kode + ' — ' + selectedKodeRek.deskripsi : ''"></p>
                        </div>
                    </div>

                    {{-- ── JENIS REKOMENDASI ── --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Jenis Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_rekomendasi"
                                    x-model="jenis"
                                    required
                                    class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="uang"         {{ old('jenis_rekomendasi', 'uang') == 'uang'         ? 'selected' : '' }}>Keuangan (Uang)</option>
                                <option value="barang"       {{ old('jenis_rekomendasi', 'uang') == 'barang'       ? 'selected' : '' }}>Barang / Aset</option>
                                <option value="administrasi" {{ old('jenis_rekomendasi', 'uang') == 'administrasi' ? 'selected' : '' }}>Administratif</option>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- ── NILAI REKOMENDASI (hanya jika jenis = uang) ── --}}
                    <div class="w-full px-2.5" x-show="jenis === 'uang'" x-transition>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nilai Rekomendasi (Rp)
                        </label>
                        <div class="rupiah-wrap relative group">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-gray-400 group-focus-within:text-brand-500 transition-colors">Rp</span>
                            <input type="text"
                                   id="display-nilai-rekom"
                                   inputmode="numeric"
                                   autocomplete="off"
                                   placeholder="0"
                                   class="rupiah-field shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 py-2.5 text-sm font-semibold focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                   data-name="nilai_rekom"
                                   data-value="{{ old('nilai_rekom', 0) }}"
                                   data-max="0">
                            <p class="rupiah-error hidden mt-1 text-xs text-red-500"></p>
                        </div>
                        <div class="mt-2 flex items-center justify-between px-1">
                            <span class="text-[11px] font-semibold text-brand-500 uppercase tracking-wide">Input dalam Rupiah</span>
                            <span class="text-[11px] text-gray-500" x-show="plafon > 0">
                                Plafon Temuan: <strong x-text="formatRupiah(plafon)"></strong>
                            </span>
                        </div>
                        <div x-show="nilaiMelebihiPlafon" x-transition
                             class="mt-2 flex items-center gap-1.5 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300">
                            <svg class="h-3.5 w-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Nilai melebihi plafon temuan. Pastikan ini disengaja.
                        </div>
                    </div>

                    {{-- ── URAIAN REKOMENDASI ── --}}
                    <div class="w-full px-2.5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Uraian Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <textarea name="uraian_rekom" rows="4" required
                            placeholder="Tuliskan instruksi rekomendasi secara spesifik dan terukur..."
                            class="shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('uraian_rekom') }}</textarea>
                        @error('uraian_rekom')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ── BATAS WAKTU ── --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Batas Waktu <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="batas_waktu" required
                               value="{{ old('batas_waktu') }}"
                               onclick="this.showPicker()"
                               class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('batas_waktu') border-red-500 @enderror">
                        @error('batas_waktu')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ── TOMBOL AKSI ── --}}
                    <div class="w-full px-2.5 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex gap-3">
                            <button type="submit" id="btn-submit-rekom"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-brand-500 p-3 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span id="btn-rekom-text">Simpan Rekomendasi</span>
                            </button>
                            <a href="{{ route('recommendations.index') }}"
                               class="flex-1 flex items-center justify-center rounded-lg border border-gray-300 p-3 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                Batal
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rekomendasiForm() {
    return {
        /* ── State ── */
        lhpId      : '{{ old("lhp_id", "") }}',
        temuanId   : '{{ old("temuan_id", "") }}',
        jenis      : '{{ old("jenis_rekomendasi", "uang") }}',
        kodeRekId  : '{{ old("kode_rekomendasi_id", "") }}',
        nilaiRupiah: {{ old('nilai_rekom', 0) }},
        plafon     : 0,
        loading    : false,
        fetchError : null,

        /* Master data dari Blade (tidak berubah) */
        masterKodeRekoms : @json($kodeRekoms),

        /* Data dinamis dari API */
        temuans          : [],
        filteredKodeRekoms: @json($kodeRekoms),

        /* ── Computed ── */
        get selectedTemuan() {
            if (!this.temuanId) return null;
            return this.temuans.find(t => String(t.id) === String(this.temuanId)) ?? null;
        },
        get selectedKodeRek() {
            if (!this.kodeRekId) return null;
            return this.masterKodeRekoms.find(k => String(k.id) === String(this.kodeRekId)) ?? null;
        },
        get nilaiMelebihiPlafon() {
            return this.plafon > 0 && this.nilaiRupiah > this.plafon;
        },

        /* ── Methods ── */

        /**
         * Dipanggil saat LHP berubah.
         * Reset state temuan dulu, baru fetch.
         */
        onLhpChange() {
            /* Reset semua state yang bergantung pada LHP */
            this.temuans           = [];
            this.temuanId          = '';
            this.plafon            = 0;
            this.filteredKodeRekoms = this.masterKodeRekoms;
            this.fetchError        = null;

            if (!this.lhpId) return;

            this.fetchTemuans();
        },

        /**
         * Fetch temuan dari route web Laravel.
         * Gunakan route('lhps.temuans', lhpId) yang harus didefinisikan di web.php.
         */
        async fetchTemuans() {
            this.loading    = true;
            this.fetchError = null;

            try {
                const url      = `/lhp/${this.lhpId}/temuans`; // sesuaikan dengan route web.php Anda
                const response = await fetch(url, {
                    method : 'GET',
                    headers: {
                        'Accept'           : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                        'X-CSRF-TOKEN'     : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });

                if (!response.ok) {
                    /* Coba baca pesan error dari server */
                    let errMsg = `HTTP ${response.status}`;
                    try {
                        const errJson = await response.json();
                        errMsg = errJson.message ?? errMsg;
                    } catch (_) {}
                    throw new Error(errMsg);
                }

                const data = await response.json();

                /* Mapping data dari controller RecommendationController@getTemuans */
                this.temuans = data.map(t => ({
                    id          : String(t.id),
                    kondisi_full: t.kondisi ?? '',                        // teks penuh untuk preview
                    nilai_temuan: parseFloat(t.nilai_temuan ?? 0),
                    alternatif_rekom: Array.isArray(t.alternatif_rekom) ? t.alternatif_rekom.map(String) : [],
                    kode_label  : t.kode_label ?? null,
                    /* Label untuk <option> — ikut format yang dikembalikan controller */
                    label       : (t.kode_label ? `[${t.kode_label}] ` : '') +
                                  (t.kondisi    ? t.kondisi.substring(0, 100) + (t.kondisi.length > 100 ? '...' : '') : '(tanpa kondisi)'),
                }));

                /* Jika old('temuan_id') ada & masih valid, pertahankan; jika tidak, reset */
                const oldTemuanId = '{{ old("temuan_id", "") }}';
                if (oldTemuanId && this.temuans.find(t => t.id === oldTemuanId)) {
                    this.temuanId = oldTemuanId;
                } else {
                    this.temuanId = '';
                }

                /* Jalankan onTemuanChange untuk update plafon & filter kode */
                this.onTemuanChange();

            } catch (err) {
                console.error('[fetchTemuans]', err);
                this.fetchError = err.message ?? 'Terjadi kesalahan tidak diketahui.';
                this.temuans    = [];
                this.temuanId   = '';
            } finally {
                this.loading = false;
            }
        },

        /** Dipanggil saat select temuan berubah */
        onTemuanChange() {
            this.updatePlafon();
            this.filterKodeRekoms();
        },

        /**
         * Filter kode rekomendasi berdasarkan alternatif_rekom temuan terpilih.
         * Jika temuan tidak membatasi, tampilkan semua kode.
         */
        filterKodeRekoms() {
            const temuan = this.selectedTemuan;

            if (temuan && temuan.alternatif_rekom.length > 0) {
                this.filteredKodeRekoms = this.masterKodeRekoms.filter(k =>
                    temuan.alternatif_rekom.includes(String(k.kode_numerik))
                );
                /* Reset kodeRekId jika pilihan sebelumnya tidak ada di filter baru */
                if (this.kodeRekId && !this.filteredKodeRekoms.find(k => String(k.id) === String(this.kodeRekId))) {
                    this.kodeRekId = '';
                }
            } else {
                this.filteredKodeRekoms = this.masterKodeRekoms;
            }
        },

        /** Update plafon & auto-isi field nilai dari nilai_temuan */
        updatePlafon() {
            const temuan   = this.selectedTemuan;
            this.plafon    = temuan?.nilai_temuan ?? 0;

            if (this.plafon > 0) {
                const hiddenEl  = document.querySelector('input[name="nilai_rekom"]');
                const displayEl = document.getElementById('display-nilai-rekom');

                /* Auto-fill hanya jika nilai masih 0 / kosong */
                if (hiddenEl && (!hiddenEl.value || parseInt(hiddenEl.value, 10) === 0)) {
                    hiddenEl.value          = this.plafon;
                    this.nilaiRupiah        = this.plafon;
                    if (displayEl && window.RupiahInput?.fmt) {
                        displayEl.value = window.RupiahInput.fmt(this.plafon);
                    }
                }
            }
        },

        onKodeRekChange() {
            /* Computed selectedKodeRek otomatis terupdate — tidak perlu action manual */
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
            }).format(number ?? 0);
        },

        /** Alpine init() — dipanggil via x-init="init()" di element root */
        init() {
            /* Init rupiah input setelah Alpine selesai mount */
            this.$nextTick(() => {
                if (window.RupiahInput?.initAll) window.RupiahInput.initAll();

                /* Sync nilaiRupiah dengan perubahan dari RupiahInput */
                const displayEl = document.getElementById('display-nilai-rekom');
                if (displayEl) {
                    displayEl.addEventListener('input', () => {
                        const hiddenEl   = displayEl._hiddenEl
                                        ?? document.querySelector('input[name="nilai_rekom"]');
                        this.nilaiRupiah = parseInt(hiddenEl?.value || '0', 10);
                    });
                }
            });

            /* Jika ada old('lhp_id') dari validasi gagal, fetch temuan langsung */
            if (this.lhpId) {
                this.fetchTemuans();
            }

            /* Guard submit — disable tombol agar tidak double-submit */
            document.getElementById('form-rekom')?.addEventListener('submit', () => {
                const btn  = document.getElementById('btn-submit-rekom');
                const text = document.getElementById('btn-rekom-text');
                if (btn)  btn.disabled    = true;
                if (text) text.textContent = 'Menyimpan...';
            });
        },
    };
}
</script>
@endsection