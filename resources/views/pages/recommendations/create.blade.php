@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8" x-data="rekomendasiForm()">

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

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Form Input Rekomendasi</h3>
        </div>

        <div class="p-5 sm:p-6">
            <form action="{{ route('recommendations.store') }}" method="POST">
                @csrf
                <div class="-mx-2.5 flex flex-wrap gap-y-5">

                    {{-- Pilih LHP --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nomor LHP <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="lhp_id" x-model="lhpId" @change="fetchTemuans()" required
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">-- Pilih Nomor LHP --</option>
                                @foreach($lhps as $lhp)
                                    <option value="{{ $lhp->id }}">
                                        {{ $lhp->nomor_lhp }} ({{ $lhp->tanggal_lhp->format('Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 pointer-events-none">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Pilih Temuan --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Pilih Temuan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="temuan_id" x-model="temuanId"
                                :disabled="!lhpId || loading" required
                                @change="onTemuanChange()"
                                class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-white/5">
                                <option value="" x-text="loading ? 'Memuat...' : (lhpId && temuans.length === 0 ? 'Semua temuan sudah tuntas' : '-- Pilih Temuan --')"></option>
                                <template x-for="t in temuans" :key="t.id">
                                    <option :value="t.id" x-text="t.kondisi"></option>
                                </template>
                            </select>
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 pointer-events-none">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Kode Rekomendasi (DIFILTER BERDASARKAN TEMUAN) --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Kode Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="kode_rekomendasi_id" required
                                class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">-- Pilih Kode --</option>
                                <template x-for="k in filteredKodeRekoms" :key="k.id">
                                    <option :value="k.id" x-text="k.kode + ' - ' + k.deskripsi"></option>
                                </template>
                            </select>
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 pointer-events-none">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Jenis Rekomendasi --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Jenis Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_rekomendasi" x-model="jenis" required
                                class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="uang" {{ old('jenis_rekomendasi') == 'uang' ? 'selected' : '' }}>Keuangan (Uang)</option>
                                <option value="barang" {{ old('jenis_rekomendasi') == 'barang' ? 'selected' : '' }}>Barang / Aset</option>
                                <option value="administrasi" {{ old('jenis_rekomendasi') == 'administrasi' ? 'selected' : '' }}>Administratif</option>
                            </select>
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 pointer-events-none">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Nilai Rekomendasi --}}
                    <div class="w-full px-2.5" x-show="jenis === 'uang'" x-transition>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nilai Rekomendasi (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 text-sm">Rp</span>
                            <input type="number" name="nilai_rekom" step="1" min="0"
                                x-model="nilaiInput"
                                class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div class="mt-1.5 flex justify-between px-1">
                            <span class="text-[11px] font-medium text-brand-500 uppercase">Input Rupiah</span>
                            <span class="text-[11px] text-gray-500" x-show="plafon > 0">
                                Plafon Temuan: <b x-text="formatRupiah(plafon)"></b>
                            </span>
                        </div>
                    </div>

                    {{-- Uraian Rekomendasi --}}
                    <div class="w-full px-2.5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Uraian Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <textarea name="uraian_rekom" rows="4" required
                            placeholder="Tuliskan instruksi rekomendasi..."
                            class="shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('uraian_rekom') }}</textarea>
                        @error('uraian_rekom')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Batas Waktu --}}
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Batas Waktu <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="batas_waktu" required
                            value="{{ old('batas_waktu') }}"
                            onclick="this.showPicker()"
                            class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('batas_waktu')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="w-full px-2.5 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex gap-3">
                            <button type="submit" id="submit-btn"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-brand-500 p-3 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm active:scale-[0.98] disabled:opacity-50">
                                <span id="btn-text">Simpan Rekomendasi</span>
                            </button>
                            <a href="{{ route('recommendations.index') }}"
                                class="flex-1 flex items-center justify-center rounded-lg border border-gray-300 p-3 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
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
        lhpId: '{{ old("lhp_id", "") }}', 
        temuanId: '{{ old("temuan_id", "") }}',
        jenis: '{{ old("jenis_rekomendasi", "uang") }}',
        
        masterKodeRekoms: @json($kodeRekoms),
        temuans: [],
        filteredKodeRekoms: @json($kodeRekoms),
        loading: false,
        plafon: 0,
        nilaiInput: {{ old('nilai_rekom', 0) }},

        async fetchTemuans() {
            if (!this.lhpId) {
                this.resetForm();
                return;
            }
            
            this.loading = true;
            try {
                const response = await fetch(`/api/lhp/${this.lhpId}/temuans`);
                const data = await response.json();
                this.temuans = data;
                
                // Cek apakah temuan yang di-old() masih ada di list
                if (!this.temuans.find(t => String(t.id) === String(this.temuanId))) {
                    this.temuanId = '';
                }
                this.onTemuanChange();
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        onTemuanChange() {
            this.updatePlafon();
            this.filterRekoms();
        },

        filterRekoms() {
            const selected = this.temuans.find(t => String(t.id) === String(this.temuanId));
            
            // Jika temuan dipilih dan punya aturan alternatif_rekom
            if (selected && selected.alternatif_rekom && selected.alternatif_rekom.length > 0) {
                // Ubah semua alternatif_rekom ke string agar aman dibandingkan
                const allowedCodes = selected.alternatif_rekom.map(n => String(n));
                
                this.filteredKodeRekoms = this.masterKodeRekoms.filter(k => 
                    allowedCodes.includes(String(k.kode_numerik))
                );
            } else {
                // Jika temuan tidak punya batasan, tampilkan semua master data
                this.filteredKodeRekoms = this.masterKodeRekoms;
            }
        },

        updatePlafon() {
            const selected = this.temuans.find(t => String(t.id) === String(this.temuanId));
            if (selected) {
                this.plafon = selected.nilai_temuan ?? 0;
                if (this.nilaiInput == 0) this.nilaiInput = this.plafon;
            } else {
                this.plafon = 0;
            }
        },

        resetForm() {
            this.temuans = [];
            this.temuanId = '';
            this.plafon = 0;
            this.filteredKodeRekoms = this.masterKodeRekoms;
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
            }).format(number);
        },

        init() {
            if (this.lhpId) {
                this.fetchTemuans();
            }
        }
    }
}
</script>
@endsection