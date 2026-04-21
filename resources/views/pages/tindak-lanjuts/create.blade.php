@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen flex justify-center dark:bg-gray-950">
    <div class="w-full max-w-4xl">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex text-sm text-gray-500 gap-2 font-medium">
            <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-brand-500 transition-colors">Tindak Lanjut</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">Tambah Baru</span>
        </nav>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 border-b border-gray-100 sm:px-6 sm:py-5 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Input Tindak Lanjut</h3>
                <p class="text-sm text-gray-500 mt-1">Pastikan seluruh data bertanda bintang (*) diisi dengan benar.</p>
            </div>

            <div class="p-5 sm:p-6">

                @if ($errors->any())
                    <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">Mohon perbaiki kesalahan berikut:</p>
                                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('tindak-lanjuts.store') }}" method="POST" id="main-form">
    @csrf
    <div class="-mx-2.5 flex flex-wrap gap-y-5">

        {{-- Pilih Rekomendasi --}}
        <div class="w-full px-2.5">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Pilih Rekomendasi <span class="text-red-500">*</span>
            </label>
            {{-- Tambahkan data-sisa untuk dibaca JS --}}
            <select name="recommendation_id" id="recommendation_id" required
                class="h-11 w-full rounded-lg border {{ $errors->has('recommendation_id') ? 'border-red-500' : 'border-gray-300' }} bg-transparent px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                <option value="">-- Pilih Rekomendasi --</option>
                @foreach($recommendations as $rekom)
                    <option value="{{ $rekom->id }}"
                        data-sisa="{{ $rekom->nilai_sisa }}"
                        data-jenis="{{ $rekom->jenis_rekomendasi }}"
                        {{ old('recommendation_id') == $rekom->id ? 'selected' : '' }}>
                        [{{ $rekom->temuan->lhp->nomor_lhp ?? 'LHP' }}]
                        {{ Str::limit($rekom->uraian_rekom, 80) }} 
                        — (Sisa: Rp{{ number_format($rekom->nilai_sisa, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Jenis Penyelesaian --}}
        <div class="w-full px-2.5 xl:w-1/2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Jenis Penyelesaian <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-4">
                <label class="flex-1 flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/20 dark:border-gray-700">
                    <input type="radio" name="jenis_penyelesaian" value="langsung"
                        class="text-brand-500 focus:ring-brand-500"
                        {{ old('jenis_penyelesaian', 'langsung') == 'langsung' ? 'checked' : '' }}>
                    <div class="ml-2">
                        <span class="block text-sm font-bold text-gray-700 dark:text-gray-300">Langsung</span>
                        <span class="block text-[10px] text-gray-400 uppercase">Sekali Bayar</span>
                    </div>
                </label>
                <label class="flex-1 flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/20 dark:border-gray-700">
                    <input type="radio" name="jenis_penyelesaian" value="cicilan"
                        class="text-brand-500 focus:ring-brand-500"
                        {{ old('jenis_penyelesaian') == 'cicilan' ? 'checked' : '' }}>
                    <div class="ml-2">
                        <span class="block text-sm font-bold text-gray-700 dark:text-gray-300">Cicilan</span>
                        <span class="block text-[10px] text-gray-400 uppercase">Bertahap</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Nilai Tindak Lanjut --}}
        <div class="w-full px-2.5 xl:w-1/2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Total Nilai yang Akan Di-setorkan (Rp) <span class="text-red-500">*</span>
            </label>
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 text-sm font-bold group-focus-within:text-brand-500 transition-colors">Rp</span>
                <input type="number" name="nilai_tindak_lanjut" id="nilai_tindak_lanjut"
                    value="{{ old('nilai_tindak_lanjut', 0) }}"
                    min="0" step="any" required
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 py-2.5 text-sm font-bold focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
            </div>
            <p class="mt-1 text-[10px] text-gray-400 font-medium">* Masukkan total target penyelesaian (biasanya sama dengan nilai sisa rekomendasi).</p>
        </div>

        {{-- Section Cicilan --}}
        <div id="section-cicilan"
            class="w-full px-2.5 {{ old('jenis_penyelesaian') === 'cicilan' ? '' : 'hidden' }}">
            <div class="p-6 bg-indigo-50/50 rounded-2xl border border-dashed border-indigo-200 grid grid-cols-1 md:grid-cols-2 gap-6 dark:bg-gray-900 dark:border-gray-700">
                <div class="col-span-full">
                    <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Rencana Jadwal Cicilan</h4>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold text-gray-500 uppercase">Tenor / Jumlah Cicilan</label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="jumlah_cicilan_rencana"
                            value="{{ old('jumlah_cicilan_rencana') }}" min="1"
                            placeholder="12"
                            class="h-10 w-full rounded-md border border-gray-300 px-3 text-sm focus:border-brand-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        <span class="text-xs font-bold text-gray-400">Bulan</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold text-gray-500 uppercase">Tanggal Mulai Cicilan</label>
                    <input type="date" name="tanggal_mulai_cicilan"
                        value="{{ old('tanggal_mulai_cicilan') }}"
                        onclick="this.showPicker()"
                        class="h-10 w-full rounded-md border border-gray-300 px-3 text-sm focus:border-brand-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                </div>
            </div>
        </div>

                        {{-- Tanggal Jatuh Tempo --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_jatuh_tempo"
                                value="{{ old('tanggal_jatuh_tempo') }}"
                                onclick="this.showPicker()" required
                                class="h-11 w-full rounded-lg border {{ $errors->has('tanggal_jatuh_tempo') ? 'border-red-500' : 'border-gray-300' }} bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            @error('tanggal_jatuh_tempo')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status Verifikasi --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Status Verifikasi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="status_verifikasi" required
                                    class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                    <option value="menunggu_verifikasi" {{ old('status_verifikasi', 'menunggu_verifikasi') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                    <option value="berjalan"            {{ old('status_verifikasi') == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                                    <option value="lunas"               {{ old('status_verifikasi') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                </select>
                                <span class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 pointer-events-none">
                                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Verifikator --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Verifikator
                            </label>
                            <div class="relative">
                                <select name="diverifikasi_oleh"
                                    class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                    <option value="">-- Pilih Verifikator --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('diverifikasi_oleh') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
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

                        {{-- Catatan Tindak Lanjut --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Catatan Tindak Lanjut
                            </label>
                            <textarea name="catatan_tl" rows="3" maxlength="1000"
                                placeholder="Tambahkan keterangan (maks. 1000 karakter)..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">{{ old('catatan_tl') }}</textarea>
                        </div>

                        {{-- Hambatan --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Hambatan
                            </label>
                            <textarea name="hambatan" rows="3" maxlength="1000"
                                placeholder="Tuliskan hambatan yang dihadapi (opsional)..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">{{ old('hambatan') }}</textarea>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="w-full px-2.5 mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                            <a href="{{ route('tindak-lanjuts.index') }}"
                                class="flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-6 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                                Batal
                            </a>
                            <button type="submit" id="submit-btn"
                                class="flex h-11 items-center justify-center rounded-lg bg-brand-500 px-8 text-sm font-medium text-white transition-all hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                                <span id="btn-text">Simpan Data</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectRekom    = document.getElementById('recommendation_id');
    const inputNilai     = document.getElementById('nilai_tindak_lanjut');
    const radios         = document.querySelectorAll('input[name="jenis_penyelesaian"]');
    const sectionCicilan = document.getElementById('section-cicilan');
    const form           = document.getElementById('main-form');

    // 1. Logic: Auto-fill nilai saat pilih rekomendasi
    selectRekom.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const sisa = selectedOption.getAttribute('data-sisa');
        
        if (sisa) {
            inputNilai.value = sisa;
        }
    });

    // 2. Logic: Toggle Section Cicilan
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'cicilan') {
                sectionCicilan.classList.remove('hidden');
                // Optional: Beri focus ke tenor
                document.getElementsByName('jumlah_cicilan_rencana')[0].focus();
            } else {
                sectionCicilan.classList.add('hidden');
            }
        });
    });

    // 3. Form Validation sebelum submit
    form.addEventListener('submit', function (e) {
        const submitBtn = document.getElementById('submit-btn');
        const btnText   = document.getElementById('btn-text');
        
        // Peringatan jika nilai tindak lanjut masih 0
        if (parseFloat(inputNilai.value) <= 0) {
            if (!confirm('Nilai Tindak Lanjut adalah 0. Anda yakin ingin melanjutkan?')) {
                e.preventDefault();
                return;
            }
        }

        submitBtn.disabled = true;
        btnText.innerText  = 'Menyimpan...';
    });
});
</script>
@endsection