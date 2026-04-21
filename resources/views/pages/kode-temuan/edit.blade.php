@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    <form action="{{ route('kode-temuan.update', $data->id) }}" method="POST" x-data="editTemuanForm()">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">Edit Kode Temuan</h2>
                <p class="text-sm text-gray-500">Sesuaikan rincian referensi audit SIMANTAP.</p>
            </div>
            <a href="{{ route('kode-temuan.index') }}" class="text-sm font-medium text-blue-600 hover:underline">Kembali</a>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                <ul class="list-inside list-disc text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Hidden Inputs --}}
        <input type="hidden" name="kelompok" :value="namaKelompok">
        <input type="hidden" name="sub_kelompok" :value="namaSubKelompok">
        <input type="hidden" name="jenis" :value="nomorJenis">

        <div class="space-y-6">
            {{-- Card Utama --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="p-5 space-y-6 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode Numerik Resmi</label>
                            <input type="text" name="kode_numerik" @input="syncJenis" value="{{ old('kode_numerik', $data->kode_numerik) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500 transition">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode (Alias)</label>
                            <input type="text" name="kode" value="{{ old('kode', $data->kode) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500 transition">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kelompok Utama</label>
                            <select name="kel" x-model="kelompok" @change="updateSubKelOptions" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="1">1 — Ketidakpatuhan Terhadap Peraturan</option>
                                <option value="2">2 — Kelemahan SPI</option>
                                <option value="3">3 — Temuan 3E</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub Kelompok</label>
                            <select name="sub_kel" x-model="subKelTerpilih" @change="updateNamaSubKel" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <template x-for="(label, value) in subKelOptions" :key="value">
                                    <option :value="value" x-text="label" :selected="value == subKelTerpilih"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Deskripsi Jenis Temuan</label>
                            <textarea name="deskripsi" rows="3" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white transition">{{ old('deskripsi', $data->deskripsi) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Alternatif Rekomendasi --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 sm:px-6 sm:py-5 font-medium text-gray-800 dark:text-white/90">
                    Alternatif Rekomendasi
                </div>
                <div class="p-5 sm:p-6">
                    <input type="text" x-model="searchRekom" placeholder="Cari rekomendasi..." class="mb-4 h-10 w-full max-w-xs rounded-lg border border-gray-200 bg-transparent px-4 py-2 text-sm dark:border-gray-700 dark:text-white outline-none focus:border-blue-500">
                    <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.01]">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @php
                                $selectedRekom = is_array($data->alternatif_rekom) ? $data->alternatif_rekom : ($data->alternatif_rekom ? $data->alternatif_rekom->toArray() : []);
                            @endphp
                            @foreach(\App\Models\KodeRekomendasi::orderBy('kode_numerik')->get() as $rekom)
                            <label x-show="matchesSearch('{{ $rekom->kode }} {{ $rekom->deskripsi }}')" class="flex cursor-pointer items-start gap-3 p-2 hover:bg-white dark:hover:bg-white/5 transition">
                                <input type="checkbox" name="alternatif_rekom[]" value="{{ $rekom->kode_numerik }}" 
                                    {{ in_array($rekom->kode_numerik, $selectedRekom) ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600">
                                <div class="text-sm">
                                    <span class="font-bold text-gray-800 dark:text-white/90">{{ $rekom->kode }}</span>
                                    <p class="text-gray-500 dark:text-gray-400 line-clamp-1">{{ $rekom->deskripsi }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-8 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-md transition-all">
                    Simpan Perubahan
                </button>
                <a href="{{ route('kode-temuan.index') }}" class="text-sm font-medium text-gray-500">Batal</a>
            </div>
        </div>
    </form>
</div>

<script>
function editTemuanForm() {
    return {
        // Inisialisasi dengan data dari Database
        kelompok: '{{ old('kel', $data->kel) }}',
        subKelTerpilih: '{{ old('sub_kel', $data->sub_kel) }}',
        namaKelompok: '{{ old('kelompok', $data->kelompok) }}',
        namaSubKelompok: '{{ old('sub_kelompok', $data->sub_kelompok) }}',
        nomorJenis: '{{ old('jenis', $data->jenis) }}',
        searchRekom: '',
        subKelOptions: {},
        allOptions: {
            1: { 1: '01 — Kerugian Negara/Daerah', 2: '02 — Potensi Kerugian Negara/Daerah', 3: '03 — Kekurangan Penerimaan', 4: '04 — Administrasi', 5: '05 — Indikasi Tindak Pidana' },
            2: { 1: '01 — Kelemahan SPI Akuntansi dan Pelaporan', 2: '02 — Kelemahan SPI Pelaksanaan Anggaran', 3: '03 — Kelemahan Struktur Pengendalian Intern' },
            3: { 1: '01 — Ketidakhematan/Pemborosan', 2: '02 — Ketidakefisienan', 3: '03 — Ketidakefektifan' }
        },
        init() {
            // Jalankan saat load untuk mengisi dropdown sub_kel
            this.updateSubKelOptions(false);
        },
        updateSubKelOptions(resetSub = true) {
            this.subKelOptions = this.allOptions[this.kelompok] || {};
            const kelLabels = { 1: 'Temuan Ketidakpatuhan Terhadap Peraturan', 2: 'Temuan Kelemahan SPI', 3: 'Temuan 3E' };
            this.namaKelompok = kelLabels[this.kelompok] || '';
            
            if(resetSub) {
                this.subKelTerpilih = '';
                this.namaSubKelompok = '';
            }
        },
        updateNamaSubKel() {
            this.namaSubKelompok = this.subKelOptions[this.subKelTerpilih] || '';
        },
        syncJenis(e) {
            const parts = e.target.value.split('.');
            if(parts.length === 3) this.nomorJenis = parseInt(parts[2]);
        },
        matchesSearch(text) {
            return !this.searchRekom || text.toLowerCase().includes(this.searchRekom.toLowerCase());
        }
    }
}
</script>
@endsection