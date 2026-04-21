@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">

    {{-- HEADER --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Rekomendasi</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Perbarui data rekomendasi</p>
        </div>
        <a href="{{ route('recommendations.show', $recommendation) }}"
            class="text-sm font-medium text-brand-500 hover:text-brand-600 transition-colors">
            &larr; Kembali
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
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="font-semibold text-gray-800 dark:text-white">Form Edit Rekomendasi</h3>
        </div>

        <div class="p-6">
            <form action="{{ route('recommendations.update', $recommendation) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- LHP (readonly) --}}
                    <div>
                        <label class="mb-1.5 block text-sm text-gray-500">Nomor LHP</label>
                        <input type="text"
                            value="{{ $recommendation->temuan->lhp->nomor_lhp ?? '-' }}"
                            disabled
                            class="h-11 w-full rounded-lg border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                    </div>

                    {{-- Temuan (readonly) --}}
                    <div>
                        <label class="mb-1.5 block text-sm text-gray-500">Temuan</label>
                        <input type="text"
                            value="{{ Str::limit($recommendation->temuan->kondisi ?? '-', 80) }}"
                            disabled
                            class="h-11 w-full rounded-lg border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                    </div>

                    {{-- Kode Rekomendasi --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Kode Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="kode_rekomendasi_id" required
                                class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="">-- Pilih Kode --</option>
                                @foreach($kodeRekoms as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kode_rekomendasi_id', $recommendation->kode_rekomendasi_id) == $k->id ? 'selected' : '' }}>
                                        {{ $k->kode }} - {{ $k->deskripsi }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 pointer-events-none">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        @error('kode_rekomendasi_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Rekomendasi --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Jenis Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_rekomendasi" required
                                class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="uang"         {{ old('jenis_rekomendasi', $recommendation->jenis_rekomendasi) == 'uang'         ? 'selected' : '' }}>Keuangan (Uang)</option>
                                <option value="barang"       {{ old('jenis_rekomendasi', $recommendation->jenis_rekomendasi) == 'barang'       ? 'selected' : '' }}>Barang / Aset</option>
                                <option value="administrasi" {{ old('jenis_rekomendasi', $recommendation->jenis_rekomendasi) == 'administrasi' ? 'selected' : '' }}>Administratif</option>
                            </select>
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 pointer-events-none">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Nilai Rekomendasi --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nilai Rekomendasi (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 text-sm">Rp</span>
                            <input type="number" name="nilai_rekom" step="1" min="0"
                                value="{{ old('nilai_rekom', $recommendation->nilai_rekom) }}"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                        @error('nilai_rekom')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Batas Waktu --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Batas Waktu <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="batas_waktu" required
                            onclick="this.showPicker()"
                            value="{{ old('batas_waktu', $recommendation->batas_waktu?->format('Y-m-d')) }}"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @error('batas_waktu')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Uraian Rekomendasi --}}
                <div class="mt-5">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Uraian Rekomendasi <span class="text-red-500">*</span>
                    </label>
                    <textarea name="uraian_rekom" rows="4" required
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('uraian_rekom', $recommendation->uraian_rekom) }}</textarea>
                    @error('uraian_rekom')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Info statistik saat ini (readonly) --}}
                <div class="mt-5 rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Status Tindak Lanjut Saat Ini</p>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <span>Status: 
                            <strong class="
                                {{ $recommendation->status === 'selesai' ? 'text-green-600' : ($recommendation->status === 'proses' ? 'text-amber-600' : 'text-gray-600') }}">
                                {{ ucfirst(str_replace('_', ' ', $recommendation->status)) }}
                            </strong>
                        </span>
                        <span>Nilai Selesai: <strong>Rp {{ number_format($recommendation->nilai_tl_selesai, 0, ',', '.') }}</strong></span>
                        <span>Sisa: <strong>Rp {{ number_format($recommendation->nilai_sisa, 0, ',', '.') }}</strong></span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">Status dan nilai dihitung otomatis dari tindak lanjut — tidak bisa diubah manual.</p>
                </div>

                {{-- ACTION --}}
                <div class="mt-6 flex gap-3">
                    <button type="submit" id="submit-btn"
                        class="flex-1 rounded-lg bg-brand-500 p-3 text-sm font-medium text-white hover:bg-brand-600 transition-colors active:scale-[0.98] disabled:opacity-50">
                        <span id="btn-text">Update Rekomendasi</span>
                    </button>
                    <a href="{{ route('recommendations.show', $recommendation) }}"
                        class="flex-1 rounded-lg border border-gray-300 p-3 text-center text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const btn  = document.getElementById('submit-btn');
    const text = document.getElementById('btn-text');
    form.addEventListener('submit', function () {
        btn.disabled   = true;
        text.innerText = 'Menyimpan...';
    });
});
</script>
@endsection