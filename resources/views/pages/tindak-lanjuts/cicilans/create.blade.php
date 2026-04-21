@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center">
    <div class="w-full max-w-3xl">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-indigo-600 transition-colors text-xs uppercase tracking-widest font-bold">Tindak Lanjut</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}" class="hover:text-indigo-600 transition-colors text-xs uppercase tracking-widest font-bold">Cicilan</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 dark:text-white text-xs uppercase tracking-widest font-bold">Tambah</span>
        </nav>

        {{-- Info Rekomendasi (Header Context) --}}
        @php 
            $rekom = $tindakLanjut->recommendation; 
            $isUang = $rekom?->isUang(); 
        @endphp
        
        <div class="mb-6 p-5 bg-white dark:bg-white/[0.02] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm">
            <div class="flex flex-wrap items-center gap-x-8 gap-y-3">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Nomor LHP</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $rekom?->temuan?->lhp?->nomor_lhp ?? '-' }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Jenis</span>
                    <span class="text-sm font-bold text-indigo-600 uppercase">{{ $rekom?->jenis_rekomendasi ?? '-' }}</span>
                </div>
                @if($isUang)
                <div class="flex flex-col border-l border-gray-100 dark:border-gray-800 pl-8">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Sisa Target</span>
                    <span class="text-sm font-black text-red-600">Rp {{ number_format($rekom?->nilai_sisa ?? 0, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="ml-auto">
                    <div class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg border border-indigo-100 dark:border-indigo-800 flex items-center gap-2">
                        <span class="text-[10px] font-bold text-indigo-600 uppercase">Cicilan</span>
                        <span class="text-sm font-black text-indigo-700 dark:text-indigo-400">#{{ $nextKe }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] shadow-xl overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Input Pembayaran</h3>
                <p class="text-xs text-gray-400 mt-1">Lengkapi detail bukti bayar di bawah ini.</p>
            </div>

            <div class="p-8">
                @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800">
                    <ul class="list-disc list-inside space-y-1 font-semibold">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('tindak-lanjuts.cicilans.store', $tindakLanjut) }}" method="POST" id="form-cicilan">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Tanggal Bayar --}}
                        <div class="space-y-1.5">
                            <label class="label-field">Tanggal Bayar <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_bayar"
                                   value="{{ old('tanggal_bayar', date('Y-m-d')) }}"
                                   onclick="this.showPicker()" required
                                   class="input-field {{ $errors->has('tanggal_bayar') ? 'border-red-500' : '' }}">
                        </div>

                        {{-- Nomor Bukti --}}
                        <div class="space-y-1.5">
                            <label class="label-field">Nomor Bukti</label>
                            <input type="text" name="nomor_bukti"
                                   value="{{ old('nomor_bukti') }}"
                                   placeholder="Contoh: BKT/2026/001"
                                   class="input-field uppercase placeholder:normal-case">
                        </div>

                        {{-- Nilai Bayar (Hanya tampil untuk uang) --}}
                        @if($isUang)
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="label-field">Nilai Bayar (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-sm font-bold group-focus-within:text-indigo-600 transition-colors pointer-events-none">Rp</span>
                                <input type="number" name="nilai_bayar"
                                       value="{{ old('nilai_bayar', 0) }}"
                                       step="any" min="0" required
                                       class="input-field pl-12 font-bold text-indigo-600 text-lg {{ $errors->has('nilai_bayar') ? 'border-red-500' : '' }}">
                            </div>
                        </div>

                        {{-- Breakdown Nilai --}}
                        <div class="md:col-span-2">
                            <button type="button" id="toggle-breakdown"
                                    class="text-[10px] font-black text-indigo-600 hover:text-indigo-700 uppercase tracking-widest flex items-center gap-2 mb-4 transition-all">
                                <svg id="chevron-breakdown" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                                </svg>
                                Rincian Distribusi (Opsional)
                            </button>
                            <div id="section-breakdown" class="hidden grid grid-cols-2 gap-4 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                @foreach(['negara' => 'Negara', 'daerah' => 'Daerah', 'desa' => 'Desa', 'bos_blud' => 'BOS/BLUD'] as $key => $label)
                                <div class="space-y-1">
                                    <label class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">{{ $label }} (Rp)</label>
                                    <input type="number" name="nilai_bayar_{{ $key }}"
                                           value="{{ old('nilai_bayar_'.$key, 0) }}"
                                           step="any" min="0"
                                           class="input-field text-xs h-9" id="bd-{{ $key }}">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <input type="hidden" name="nilai_bayar" value="1">
                        @endif

                        {{-- Metadata --}}
                        <div class="space-y-1.5">
                            <label class="label-field">Jenis / Metode Bayar</label>
                            <input type="text" name="jenis_bayar"
                                   value="{{ old('jenis_bayar') }}"
                                   placeholder="Contoh: Transfer, Tunai"
                                   class="input-field">
                        </div>

                        <div class="space-y-1.5">
                            <label class="label-field">Status <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="status" required class="input-field appearance-none pr-10">
                                    <option value="menunggu_verifikasi" {{ old('status') === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                    <option value="diterima" {{ old('status') === 'diterima' ? 'selected' : '' }}>Diterima (Lunas)</option>
                                    <option value="ditolak" {{ old('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="label-field">Keterangan</label>
                            <textarea name="keterangan" rows="3"
                                      placeholder="Catatan tambahan pembayaran..."
                                      class="input-field h-auto py-3 resize-none">{{ old('keterangan') }}</textarea>
                        </div>

                    </div>

                    {{-- Actions --}}
                    <div class="mt-10 flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800">
                        <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}"
                           class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-widest hover:text-gray-700 transition-colors">
                            Batal
                        </a>
                        <button type="submit" id="btn-submit"
                                class="px-8 py-3 text-xs font-black text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 dark:shadow-none flex items-center gap-2">
                            <span id="btn-label">Simpan Cicilan #{{ $nextKe }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.label-field { display: block; font-size: 10px; font-weight: 800; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem; }
.input-field { 
    display: block; width: 100%; height: 3rem; padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 600;
    border: 1px solid #f3f4f6; border-radius: 1rem; background: #f9fafb; transition: all 0.2s; 
    outline: none;
}
.dark .input-field { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.05); color: #f3f4f6; }
.input-field:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 4px rgba(99,102,241,0.05); }
.dark .input-field:focus { background: rgba(255,255,255,0.05); }
.input-field.border-red-500 { border-color: #ef4444; background: #fef2f2; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggle-breakdown');
    const section   = document.getElementById('section-breakdown');
    const chevron   = document.getElementById('chevron-breakdown');

    // Toggle Breakdown
    toggleBtn?.addEventListener('click', () => {
        const isHidden = section.classList.contains('hidden');
        section.classList.toggle('hidden');
        chevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    // Form submission - VERSI FIX
    const form = document.getElementById('form-cicilan');
    const btn = document.getElementById('btn-submit');
    const label = document.getElementById('btn-label');

    form.addEventListener('submit', (e) => {
        // Cek apakah form valid secara HTML5 (required, type, dll)
        if (form.checkValidity()) {
            // Hanya disable JIKA form valid dan akan benar-benar dikirim ke server
            setTimeout(() => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                label.innerText = 'Menyimpan...';
            }, 10);
        }
    });

    // Auto-fill breakdown (Opsional tapi keren)
    // Jika user isi nilai_bayar, masukkan ke 'negara' sebagai default
    const inputUtama = document.querySelector('input[name="nilai_bayar"]');
    const inputNegara = document.getElementById('bd-negara');
    
    inputUtama?.addEventListener('input', (e) => {
        if(inputNegara && inputNegara.value == 0) {
            inputNegara.value = e.target.value;
        }
    });

    // Cegah input negatif
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', () => {
            if (input.value < 0) input.value = 0;
        });
    });
});
</script>
@endsection