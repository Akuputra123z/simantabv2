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

                <form action="{{ route('tindak-lanjuts.update', $tindakLanjut->id) }}" method="POST" id="edit-form" enctype="multipart/form-data">
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
<div class="w-full px-2.5 xl:w-1/2" id="field-nilai-tl">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
        Nilai Tindak Lanjut (Rp) <span class="text-red-500">*</span>
    </label>
    <div class="relative group">
        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 text-sm font-bold group-focus-within:text-brand-500 transition-colors pointer-events-none">Rp</span>
        
        {{-- Input Tampilan (Text agar bisa pakai format titik) --}}
        <input type="text" id="display-nilai-tl"
            inputmode="numeric"
            value="{{ number_format((int)old('nilai_tindak_lanjut', $tindakLanjut->nilai_tindak_lanjut), 0, ',', '.') }}"
            placeholder="0"
            autocomplete="off"
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 py-2.5 text-sm font-bold focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
        
        {{-- Input Hidden (Angka asli untuk database) --}}
        <input type="hidden" name="nilai_tindak_lanjut" id="nilai_tindak_lanjut"
            value="{{ old('nilai_tindak_lanjut', $tindakLanjut->nilai_tindak_lanjut) }}">
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

                        {{-- Lampiran --}}
                        <div class="w-full px-2.5">
                            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                                <div class="flex items-center justify-between px-5 py-4 sm:px-6 sm:py-5">
                                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Lampiran</h3>
                                    <button type="button" onclick="addEditFileInput()"
                                        class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-[10px] font-bold text-white hover:bg-blue-700 transition-all">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        TAMBAH FILE
                                    </button>
                                </div>
                                <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                                    @php $existingAtt = $tindakLanjut->attachments ?? collect([]); @endphp

                                    {{-- Tambah File Baru (ditampilkan pertama) --}}
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tambah File Baru</label>
                                        <div id="edit-attachments-container" class="space-y-3">
                                            <div class="flex items-center gap-3">
                                                <input type="file" name="new_attachments[]"
                                                       onchange="this.nextElementSibling.textContent = this.files[0]?.name || ''"
                                                       class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400">
                                                <span class="text-xs text-gray-400 truncate max-w-[140px]"></span>
                                                <button type="button" onclick="this.parentElement.remove()"
                                                        class="shrink-0 text-red-500 hover:text-red-700 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- File Saat Ini (ditampilkan kedua) --}}
                                    @if($existingAtt->isNotEmpty())
                                    <div class="space-y-2">
                                        <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">File Saat Ini</p>
                                        @foreach($existingAtt as $att)
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800/50">
                                            <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                                               class="flex items-center gap-2 text-sm text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 truncate">
                                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span class="truncate max-w-[250px]">{{ $att->file_name }}</span>
                                            </a>
                                            <label class="flex cursor-pointer items-center gap-1 text-xs text-red-600 hover:text-red-700 dark:text-red-400">
                                                <input type="checkbox" name="delete_attachments[]" value="{{ $att->id }}"
                                                       class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                Hapus
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
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

                    </div>
                </form>

                {{-- Action Buttons --}}
                <div class="w-full mt-6 flex items-center justify-between gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <button type="button" onclick="confirmDelete()"
                            class="flex h-11 items-center justify-center rounded-lg border border-red-200 bg-white px-5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-transparent">
                        Hapus
                    </button>
                    <div class="flex gap-3">
                        <a href="{{ route('tindak-lanjuts.index') }}"
                            class="flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-6 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                            Batal
                        </a>
                        <button type="submit" form="edit-form" id="submit-btn"
                            class="flex h-11 items-center justify-center rounded-lg bg-brand-500 px-8 text-sm font-medium text-white transition-all hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                            <span id="btn-text">Simpan Perubahan</span>
                        </button>
                    </div>
                </div>

                {{-- Standalone delete form (hidden) --}}
                <form id="delete-form" action="{{ route('tindak-lanjuts.destroy', $tindakLanjut->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── Helpers ─────────────────────────────────────────── */
    function formatRibuan(val) {
        const num = String(val).replace(/\D/g, '');
        if (!num) return '';
        return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    /* ── Elemen ──────────────────────────────────────────── */
    const form           = document.getElementById('edit-form');
    const submitBtn      = document.getElementById('submit-btn');
    const btnText        = document.getElementById('btn-text');
    const radios         = document.querySelectorAll('input[name="jenis_penyelesaian"]');
    const sectionCicilan = document.getElementById('section-cicilan-edit');
    
    // Elemen untuk dual-input nilai
    const displayNilai   = document.getElementById('display-nilai-tl');
    const hiddenNilai    = document.getElementById('nilai_tindak_lanjut');

    /* ── Logika Format Ribuan (Input) ────────────────────── */
    if (displayNilai && hiddenNilai) {
        displayNilai.addEventListener('input', function () {
            const raw = this.value.replace(/\./g, '').replace(/\D/g, '');
            
            // Simpan posisi kursor agar tidak melompat
            const curPos = this.selectionStart;
            const prevLen = this.value.length;
            
            const formatted = formatRibuan(raw);
            this.value = formatted;

            const diff = formatted.length - prevLen;
            try { this.setSelectionRange(curPos + diff, curPos + diff); } catch(e) {}

            hiddenNilai.value = parseInt(raw || '0', 10);
        });

        // Handle paste agar tetap terformat
        displayNilai.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = pasted.replace(/\D/g, '');
            this.value = formatRibuan(cleaned);
            hiddenNilai.value = parseInt(cleaned || '0', 10);
        });
    }

    /* ── Toggle Section Cicilan ──────────────────────────── */
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (sectionCicilan) {
                sectionCicilan.classList.toggle('hidden', this.value !== 'cicilan');
            }
        });
    });

    /* ── Guard Submit ────────────────────────────────────── */
    form.addEventListener('submit', function (e) {
        // Jika ada logika validasi sisa saldo bisa ditambahkan di sini
        
        submitBtn.disabled = true;
        btnText.innerText  = 'Menyimpan...';
    });

    // Cegah nilai negatif pada input number lainnya
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function () {
            if (this.value < 0) this.value = 0;
        });
    });

    // ── Delete confirmation ──────────────────────────────────
    window.confirmDelete = function () {
        if (confirm('Yakin ingin menghapus tindak lanjut ini?')) {
            document.getElementById('delete-form').submit();
        }
    };

    // ── Lampiran edit: tambah input file baru ───────────────
    window.addEditFileInput = function () {
        const container = document.getElementById('edit-attachments-container');
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-center gap-3 group';
        wrapper.innerHTML = `
            <input type="file" name="new_attachments[]"
                   onchange="this.nextElementSibling.textContent = this.files[0]?.name || ''"
                   class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400">
            <span class="text-xs text-gray-400 truncate max-w-[140px]"></span>
            <button type="button" onclick="this.parentElement.remove()"
                    class="shrink-0 text-red-500 hover:text-red-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;
        container.appendChild(wrapper);
    };
});
</script>
@endsection