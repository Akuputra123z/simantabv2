{{-- resources/views/pages/recommendations/edit.blade.php --}}
@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8" x-data="editRekomForm()" x-init="init()">

    {{-- HEADER --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Rekomendasi</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Perbarui instruksi tindak lanjut untuk temuan ini.</p>
        </div>
        <a href="{{ route('recommendations.show', $recommendation) }}"
           class="text-sm font-medium text-brand-500 hover:text-brand-600 transition-colors dark:text-brand-400">
            &larr; Kembali
        </a>
    </div>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        {{-- COLUMN LEFT: REFERENSI (READ-ONLY) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <h3 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Data Referensi</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 uppercase">Nomor LHP</label>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $recommendation->temuan->lhp->nomor_lhp ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 uppercase">Kondisi Temuan</label>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300 leading-relaxed italic">
                            "{{ $recommendation->temuan->kondisi ?? '-' }}"
                        </p>
                    </div>

                    @if($recommendation->temuan?->nilai_temuan > 0)
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-800">
                        <label class="block text-[11px] font-medium text-gray-500 uppercase">Nilai Temuan</label>
                        <p class="text-lg font-bold text-rose-600 dark:text-rose-400">
                            Rp {{ number_format($recommendation->temuan->nilai_temuan, 0, ',', '.') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- STATUS SEKARANG --}}
            <div class="rounded-2xl border border-gray-200 bg-brand-50/30 p-5 dark:border-gray-800 dark:bg-brand-900/5">
                <h3 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-brand-500">Status Tindak Lanjut</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status:</span>
                        <span class="font-bold text-brand-600">{{ ucfirst($recommendation->status) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Selesai:</span>
                        <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($recommendation->nilai_tl_selesai,0,',','.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-brand-100 pt-2 dark:border-brand-900/20">
                        <span class="text-gray-500">Sisa:</span>
                        <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($recommendation->nilai_sisa,0,',','.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMN RIGHT: FORM EDIT --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Form Perubahan Rekomendasi</h3>
                </div>

                <div class="p-5 sm:p-6">
                    <form action="{{ route('recommendations.update', $recommendation) }}" method="POST" id="form-edit-rekom">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            
                            {{-- KODE REKOMENDASI --}}
                            <div class="sm:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Kode Rekomendasi <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="kode_rekomendasi_id" required
                                        class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <option value="">-- Pilih Kode --</option>
                                        @foreach($kodeRekoms as $k)
                                            <option value="{{ $k->id }}"
                                                {{ old('kode_rekomendasi_id', $recommendation->kode_rekomendasi_id) == $k->id ? 'selected' : '' }}>
                                                {{ $k->kode }} — {{ $k->deskripsi }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                        <svg width="16" height="16" fill="none"><path d="M4.79 7.396 10 12.604l5.21-5.208" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </span>
                                </div>
                            </div>

                            {{-- JENIS --}}
                            <div class="sm:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Jenis Rekomendasi <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="jenis_rekomendasi" x-model="jenis" required
                                        class="shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <option value="uang">Keuangan (Uang)</option>
                                        <option value="barang">Barang / Aset</option>
                                        <option value="administrasi">Administratif</option>
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                        <svg width="16" height="16" fill="none"><path d="M4.79 7.396 10 12.604l5.21-5.208" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </span>
                                </div>
                            </div>

                            {{-- NILAI REKOMENDASI --}}
                            <div class="sm:col-span-2" x-show="jenis === 'uang'" x-transition>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Nilai Rekomendasi (Rp)
                                </label>
                                <div class="rupiah-wrap relative group">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">Rp</span>
                                    <input type="text"
                                           id="display-nilai-rekom"
                                           class="rupiah-field shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 text-sm font-semibold focus:border-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                           data-name="nilai_rekom"
                                           data-value="{{ old('nilai_rekom', $recommendation->nilai_rekom) }}">
                                </div>
                                <div class="mt-2 flex items-center justify-between px-1">
                                    <span class="text-[11px] font-semibold text-brand-500 uppercase tracking-wide">Input dalam Rupiah</span>
                                    <span class="text-[11px] text-gray-500">
                                        Plafon: <strong x-text="formatRupiah(plafon)"></strong>
                                    </span>
                                </div>
                                <div x-show="nilaiMelebihiPlafon" x-transition
                                     class="mt-2 flex items-center gap-1.5 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300">
                                    ⚠️ Nilai melebihi plafon temuan.
                                </div>
                            </div>

                            {{-- BATAS WAKTU --}}
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Batas Waktu <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="batas_waktu" required
                                    value="{{ old('batas_waktu', $recommendation->batas_waktu?->format('Y-m-d')) }}"
                                    onclick="this.showPicker()"
                                    class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>

                            {{-- URAIAN --}}
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Uraian Rekomendasi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="uraian_rekom" rows="5" required
                                    class="shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('uraian_rekom', $recommendation->uraian_rekom) }}</textarea>
                            </div>

                            {{-- ACTIONS --}}
                            <div class="sm:col-span-2 pt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end border-t border-gray-100 dark:border-gray-800">
                                <a href="{{ route('recommendations.show', $recommendation) }}"
                                   class="flex-1 sm:flex-none flex items-center justify-center rounded-lg border border-gray-300 px-8 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                                    Batal
                                </a>
                                <button type="submit" id="btn-submit-rekom"
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-8 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-all active:scale-[0.98]">
                                    <span id="btn-rekom-text">Update Rekomendasi</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editRekomForm() {
    return {
        jenis: '{{ old("jenis_rekomendasi", $recommendation->jenis_rekomendasi) }}',
        plafon: {{ $recommendation->temuan->nilai_temuan ?? 0 }},
        nilaiRupiah: {{ old('nilai_rekom', $recommendation->nilai_rekom ?? 0) }},

        get nilaiMelebihiPlafon() {
            return this.jenis === 'uang' && this.plafon > 0 && this.nilaiRupiah > this.plafon;
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
            }).format(number ?? 0);
        },

        init() {
            this.$nextTick(() => {
                /* Init rupiah input (helper eksternal) */
                if (window.RupiahInput?.initAll) window.RupiahInput.initAll();

                /* Sync Alpine state dengan input rupiah */
                const displayEl = document.getElementById('display-nilai-rekom');
                if (displayEl) {
                    displayEl.addEventListener('input', () => {
                        const hiddenEl = displayEl._hiddenEl 
                                      ?? document.querySelector('input[name="nilai_rekom"]');
                        this.nilaiRupiah = parseInt(hiddenEl?.value || '0', 10);
                    });
                }
            });

            /* Loading state on Submit */
            document.getElementById('form-edit-rekom')?.addEventListener('submit', () => {
                const btn = document.getElementById('btn-submit-rekom');
                const text = document.getElementById('btn-rekom-text');
                if (btn) btn.disabled = true;
                if (text) text.textContent = 'Menyimpan...';
            });
        }
    }
}
</script>
@endsection