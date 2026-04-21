@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen flex justify-center dark:bg-gray-950">
    <div class="w-full max-w-4xl">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex text-sm text-gray-500 gap-2 font-medium">
            <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-brand-500 transition-colors">Tindak Lanjut</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">Edit Data</span>
        </nav>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 border-b border-gray-100 sm:px-6 sm:py-5 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Edit Tindak Lanjut</h3>
                <p class="text-sm text-gray-500 mt-1">Perbarui informasi penyelesaian temuan LHP.</p>
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

                {{-- Info rekomendasi terkait (readonly) --}}
                <div class="mb-5 rounded-lg border border-blue-100 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
                    <p class="text-xs font-semibold text-blue-600 uppercase mb-1">Rekomendasi Terkait</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <strong>[{{ $tindakLanjut->recommendation->temuan->lhp->nomor_lhp ?? '-' }}]</strong>
                        {{ Str::limit($tindakLanjut->recommendation->uraian_rekom ?? '-', 120) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Nilai rekom: <strong>Rp {{ number_format($tindakLanjut->recommendation->nilai_rekom ?? 0, 0, ',', '.') }}</strong>
                        &nbsp;|&nbsp;
                        Jenis: <strong>{{ ucfirst($tindakLanjut->recommendation->jenis_rekomendasi ?? '-') }}</strong>
                    </p>
                </div>

                <form action="{{ route('tindak-lanjuts.update', $tindakLanjut->id) }}" method="POST" id="edit-form">
                    @csrf
                    @method('PUT')

                    <div class="-mx-2.5 flex flex-wrap gap-y-5">

                        {{-- Rekomendasi (hidden — tidak boleh diubah dari sini, tapi tetap dikirim) --}}
                        <input type="hidden" name="recommendation_id" value="{{ $tindakLanjut->recommendation_id }}">

                        {{-- Jenis Penyelesaian --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Jenis Penyelesaian <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-4">
                                <label class="flex-1 flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/20 dark:border-gray-700">
                                    <input type="radio" name="jenis_penyelesaian" value="langsung"
                                        class="text-brand-500 focus:ring-brand-500"
                                        {{ old('jenis_penyelesaian', $tindakLanjut->jenis_penyelesaian) == 'langsung' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Langsung</span>
                                </label>
                                <label class="flex-1 flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/20 dark:border-gray-700">
                                    <input type="radio" name="jenis_penyelesaian" value="cicilan"
                                        class="text-brand-500 focus:ring-brand-500"
                                        {{ old('jenis_penyelesaian', $tindakLanjut->jenis_penyelesaian) == 'cicilan' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cicilan</span>
                                </label>
                            </div>
                        </div>

                        {{-- Nilai Tindak Lanjut --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Nilai Tindak Lanjut (Rp)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 text-sm">Rp</span>
                                <input type="number" name="nilai_tindak_lanjut" step="1" min="0"
                                    value="{{ old('nilai_tindak_lanjut', $tindakLanjut->nilai_tindak_lanjut) }}"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            </div>
                        </div>

                        {{-- Section Cicilan --}}
                        <div id="section-cicilan-edit"
                            class="w-full px-2.5 {{ old('jenis_penyelesaian', $tindakLanjut->jenis_penyelesaian) === 'cicilan' ? '' : 'hidden' }}">
                            <div class="p-4 bg-gray-50 rounded-xl border border-dashed border-gray-300 grid grid-cols-1 md:grid-cols-2 gap-4 dark:bg-gray-900 dark:border-gray-700">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-gray-500 uppercase">Jumlah Cicilan Rencana</label>
                                    <input type="number" name="jumlah_cicilan_rencana" min="1"
                                        value="{{ old('jumlah_cicilan_rencana', $tindakLanjut->jumlah_cicilan_rencana) }}"
                                        placeholder="Contoh: 12"
                                        class="h-10 w-full rounded-md border border-gray-300 px-3 text-sm focus:border-brand-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-gray-500 uppercase">Tanggal Mulai Cicilan</label>
                                    <input type="date" name="tanggal_mulai_cicilan"
                                        onclick="this.showPicker()"
                                        value="{{ old('tanggal_mulai_cicilan', $tindakLanjut->tanggal_mulai_cicilan?->format('Y-m-d')) }}"
                                        class="h-10 w-full rounded-md border border-gray-300 px-3 text-sm focus:border-brand-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>

                        {{-- Tanggal Jatuh Tempo --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_jatuh_tempo" required
                                onclick="this.showPicker()"
                                value="{{ old('tanggal_jatuh_tempo', $tindakLanjut->tanggal_jatuh_tempo?->format('Y-m-d')) }}"
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
                                    <option value="menunggu_verifikasi" {{ old('status_verifikasi', $tindakLanjut->status_verifikasi) == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                    <option value="berjalan"            {{ old('status_verifikasi', $tindakLanjut->status_verifikasi) == 'berjalan'            ? 'selected' : '' }}>Berjalan</option>
                                    <option value="lunas"               {{ old('status_verifikasi', $tindakLanjut->status_verifikasi) == 'lunas'               ? 'selected' : '' }}>Lunas</option>
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
                                            {{ old('diverifikasi_oleh', $tindakLanjut->diverifikasi_oleh) == $user->id ? 'selected' : '' }}>
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
                                placeholder="Tambahkan keterangan..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">{{ old('catatan_tl', $tindakLanjut->catatan_tl) }}</textarea>
                        </div>

                        {{-- Hambatan --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Hambatan
                            </label>
                            <textarea name="hambatan" rows="3" maxlength="1000"
                                placeholder="Tuliskan hambatan yang dihadapi (opsional)..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">{{ old('hambatan', $tindakLanjut->hambatan) }}</textarea>
                        </div>

                        {{-- Info kalkulasi otomatis --}}
                        <div class="w-full px-2.5">
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Kalkulasi Saat Ini (otomatis)</p>
                                <div class="flex flex-wrap gap-4 text-sm">
                                    <span>Total Terbayar: <strong>Rp {{ number_format($tindakLanjut->total_terbayar, 0, ',', '.') }}</strong></span>
                                    <span>Sisa Belum Bayar: <strong>Rp {{ number_format($tindakLanjut->sisa_belum_bayar, 0, ',', '.') }}</strong></span>
                                    <span>Status: <strong class="{{ $tindakLanjut->status_verifikasi === 'lunas' ? 'text-green-600' : ($tindakLanjut->status_verifikasi === 'berjalan' ? 'text-amber-600' : 'text-gray-600') }}">{{ ucfirst(str_replace('_', ' ', $tindakLanjut->status_verifikasi)) }}</strong></span>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-2">Nilai total_terbayar dan sisa dihitung otomatis setelah simpan.</p>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="w-full px-2.5 mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                            <a href="{{ route('tindak-lanjuts.index') }}"
                                class="flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-6 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                                Batal
                            </a>
                            <button type="submit" id="submit-btn"
                                class="flex h-11 items-center justify-center rounded-lg bg-brand-500 px-8 text-sm font-medium text-white transition-all hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                                <span id="btn-text">Simpan Perubahan</span>
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
    const form           = document.getElementById('edit-form');
    const submitBtn      = document.getElementById('submit-btn');
    const btnText        = document.getElementById('btn-text');
    const radios         = document.querySelectorAll('input[name="jenis_penyelesaian"]');
    const sectionCicilan = document.getElementById('section-cicilan-edit');

    // Tampilkan/sembunyikan section cicilan
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            sectionCicilan.classList.toggle('hidden', this.value !== 'cicilan');
        });
    });

    // Cegah double submit
    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        btnText.innerText  = 'Menyimpan...';
    });

    // Cegah nilai negatif
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function () {
            if (this.value < 0) this.value = 0;
        });
    });
});
</script>
@endsection