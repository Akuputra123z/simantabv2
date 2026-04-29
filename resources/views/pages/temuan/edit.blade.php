@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')
<div class="mx-auto max-w-3xl">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="hover:text-primary-600">LHP</a>
                <span>/</span>
                <a href="{{ route('lhps.show', $temuan->lhp_id) }}" class="hover:text-primary-600">
                    {{ $temuan->lhp->nomor_lhp }}
                </a>
                <span>/</span>
                <span class="text-gray-700 dark:text-white">Edit Temuan</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Temuan #{{ $temuan->id }}</h1>
        </div>
        <a href="{{ route('lhps.show', $temuan->lhp_id) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- Error --}}
    @if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <ul class="list-inside list-disc text-sm text-red-700 dark:text-red-400">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('temuan.update', $temuan) }}" method="POST" id="form-temuan">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Data Temuan</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Kode Temuan --}}
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Temuan</label>
                        <select name="kode_temuan_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">-- Pilih Kode Temuan --</option>
                            @foreach ($kodeTemuans as $k)
                                <option value="{{ $k->id }}" {{ old('kode_temuan_id', $temuan->kode_temuan_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->kode }} - {{ $k->deskripsi }}
                                </option>
                            @endforeach
                        </select>
                        @error('kode_temuan_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kondisi --}}
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Kondisi / Uraian Temuan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="kondisi" rows="4"
                                  placeholder="Uraikan kondisi temuan..."
                                  class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('kondisi') border-red-500 @enderror">{{ old('kondisi', $temuan->kondisi) }}</textarea>
                        @error('kondisi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kerugian Negara --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kerugian Negara (Rp)</label>
                        <div class="rupiah-wrap relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                                   class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                   data-name="nilai_kerugian_negara"
                                   data-value="{{ old('nilai_kerugian_negara', (int)$temuan->nilai_kerugian_negara) }}">
                        </div>
                        @error('nilai_kerugian_negara')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kerugian Daerah --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kerugian Daerah (Rp)</label>
                        <div class="rupiah-wrap relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                                   class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                   data-name="nilai_kerugian_daerah"
                                   data-value="{{ old('nilai_kerugian_daerah', (int)$temuan->nilai_kerugian_daerah) }}">
                        </div>
                        @error('nilai_kerugian_daerah')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kerugian Desa --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kerugian Desa (Rp)</label>
                        <div class="rupiah-wrap relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                                   class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                   data-name="nilai_kerugian_desa"
                                   data-value="{{ old('nilai_kerugian_desa', (int)$temuan->nilai_kerugian_desa) }}">
                        </div>
                        @error('nilai_kerugian_desa')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kerugian BOS/BLUD --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kerugian BOS/BLUD (Rp)</label>
                        <div class="rupiah-wrap relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                                   class="rupiah-field w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                   data-name="nilai_kerugian_bos_blud"
                                   data-value="{{ old('nilai_kerugian_bos_blud', (int)$temuan->nilai_kerugian_bos_blud) }}">
                        </div>
                        @error('nilai_kerugian_bos_blud')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                  {{-- Total Otomatis --}}
<div class="md:col-span-2">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Total Nilai Kerugian (Otomatis)</label>
    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-900">
        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span id="total-display" class="text-sm font-bold text-gray-700 dark:text-gray-200">
            Rp {{ number_format((int)$temuan->nilai_temuan, 0, ',', '.') }}
        </span>
        {{-- Input hidden agar nilai_temuan terupdate di DB --}}
        <input type="hidden" name="nilai_temuan" id="nilai_temuan_hidden" value="{{ (int)$temuan->nilai_temuan }}">
    </div>
</div>
                    {{-- Status TL --}}
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Tindak Lanjut</label>
                        <select name="status_tl"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="belum_ditindaklanjuti" {{ old('status_tl', $temuan->status_tl) === 'belum_ditindaklanjuti' ? 'selected' : '' }}>Belum Ditindaklanjuti</option>
                            <option value="dalam_proses"          {{ old('status_tl', $temuan->status_tl) === 'dalam_proses'          ? 'selected' : '' }}>Dalam Proses</option>
                            <option value="selesai"               {{ old('status_tl', $temuan->status_tl) === 'selesai'               ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex items-center justify-end gap-3 rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <a href="{{ route('lhps.show', $temuan->lhp_id) }}"
               class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                Batal
            </a>
            <button type="submit" id="btn-submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:opacity-60 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span id="btn-text">Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalDisplay = document.getElementById('total-display');
    const totalHidden  = document.getElementById('nilai_temuan_hidden');

    function getVal(name) {
        const hidden = document.querySelector(`input[type="hidden"][name="${name}"]`);
        return parseInt(hidden?.value || '0', 10);
    }

    function recalcTotal() {
        const nNegara = getVal('nilai_kerugian_negara');
        const nDaerah = getVal('nilai_kerugian_daerah');
        const nDesa   = getVal('nilai_kerugian_desa');
        const nBos    = getVal('nilai_kerugian_bos_blud');

        const total = nNegara + nDaerah + nDesa + nBos;

        // Update Hidden Field untuk Form Submit
        if (totalHidden) totalHidden.value = total;

        // Update Tampilan (Format Rupiah)
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
    }

    // Tunggu komponen RupiahInput inisialisasi
    setTimeout(() => {
        // Cari semua hidden input yang dibuat oleh RupiahInput
        const fields = [
            'nilai_kerugian_negara', 
            'nilai_kerugian_daerah', 
            'nilai_kerugian_desa', 
            'nilai_kerugian_bos_blud'
        ];

        fields.forEach(fieldName => {
            const hiddenEl = document.querySelector(`input[type="hidden"][name="${fieldName}"]`);
            if (hiddenEl) {
                // Dengarkan event 'input' dari hidden element (dipicu oleh komponen RupiahInput)
                hiddenEl.addEventListener('input', recalcTotal);
                hiddenEl.addEventListener('change', recalcTotal);
            }
        });

        // Hitung awal
        recalcTotal();
    }, 100);

    // Submit guard
    const form = document.getElementById('form-temuan');
    form.addEventListener('submit', function () {
        const btn = document.getElementById('btn-submit');
        const txt = document.getElementById('btn-text');
        btn.disabled = true;
        txt.textContent = 'Menyimpan...';
    });
});
</script>
@endsection