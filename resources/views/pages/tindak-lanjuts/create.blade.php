@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center">
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

                <form action="{{ route('tindak-lanjuts.store') }}" method="POST" id="main-form" enctype="multipart/form-data"
                      x-data="tlRekomForm()">
                    @csrf
                    <div class="-mx-2.5 flex flex-wrap gap-y-5">

                        {{-- ── PILIH PROGRAM AUDIT ── --}}
                        <div class="w-full px-2.5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Program Audit <span class="text-red-500">*</span>
                            </label>
                            <select name="audit_program_id" id="audit_program_id" data-no-ts
                                    x-model="programId"
                                    @change="onProgramChange()"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="">-- Pilih Program Audit --</option>
                                @foreach($auditPrograms as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_program }} ({{ $p->tahun }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ── PILIH REKOMENDASI ── --}}
                        <div class="w-full px-2.5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Pilih Rekomendasi <span class="text-red-500">*</span>
                            </label>

                            <div x-show="loadingRekom" class="h-11 w-full rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 animate-pulse flex items-center px-4">
                                <span class="text-xs text-gray-400">Memuat data rekomendasi...</span>
                            </div>

                            <div x-show="!loadingRekom">
                                <select name="recommendation_id" id="recommendation_id" data-no-ts required
                                    :disabled="!programId || rekomendasis.length === 0"
                                    class="h-11 w-full rounded-lg border {{ $errors->has('recommendation_id') ? 'border-red-500' : 'border-gray-300' }} bg-transparent px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white disabled:bg-gray-100 disabled:cursor-not-allowed dark:disabled:bg-white/5">
                                    <option value="">
                                        <template x-if="!programId">Pilih Program terlebih dahulu</template>
                                        <template x-if="programId && rekomendasis.length === 0">Tidak ada rekomendasi tersedia</template>
                                        <template x-if="programId && rekomendasis.length > 0">-- Pilih Rekomendasi --</template>
                                    </option>
                                    <template x-for="r in rekomendasis" :key="r.id">
                                        <option :value="r.id"
                                                :data-sisa="r.sisa"
                                                :data-rekom="r.rekom"
                                                :data-jenis="r.jenis"
                                                x-text="r.label"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Info: tidak ada rekomendasi tersedia --}}
                            <div x-show="programId && !loadingRekom && rekomendasis.length === 0" x-transition
                                 class="mt-2 rounded-lg bg-blue-50 border border-blue-100 px-3 py-2 dark:bg-blue-900/20 dark:border-blue-800">
                                <p class="text-xs text-blue-700 dark:text-blue-300">
                                    ✓ Semua rekomendasi pada program ini sudah selesai (tidak ada sisa).
                                </p>
                            </div>

                            @error('recommendation_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Info Rekomendasi (muncul setelah pilih) --}}
                        <div class="w-full px-2.5" id="info-rekom-box" style="display:none;">
                            <div class="flex flex-wrap gap-4 p-4 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl text-sm">
                                <div>
                                    <span class="text-xs font-bold text-blue-500 uppercase block">Nilai Rekomendasi</span>
                                    <span class="font-semibold text-gray-900 dark:text-white" id="info-nilai-rekom">Rp 0</span>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-blue-500 uppercase block">Sisa Belum Lunas</span>
                                    <span class="font-semibold text-red-600" id="info-nilai-sisa">Rp 0</span>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-blue-500 uppercase block">Jenis</span>
                                    <span class="font-semibold text-gray-900 dark:text-white capitalize" id="info-jenis">-</span>
                                </div>
                            </div>
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
                        <div class="w-full px-2.5 xl:w-1/2" id="field-nilai-tl">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Total Nilai yang Akan Di-setorkan (Rp) <span class="text-red-500">*</span>
                            </label>

                            {{--
                                POLA DUAL INPUT:
                                - #display-nilai-tl  → input teks dengan format titik ribuan (yang user lihat & ketik)
                                - #nilai_tindak_lanjut (hidden) → nilai integer sesungguhnya yang dikirim ke server
                            --}}
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 text-sm font-bold group-focus-within:text-brand-500 transition-colors pointer-events-none">Rp</span>
                                <input type="text" id="display-nilai-tl"
                                    inputmode="numeric"
                                    value="{{ old('nilai_tindak_lanjut') ? number_format((int)old('nilai_tindak_lanjut'), 0, ',', '.') : '' }}"
                                    placeholder="0"
                                    autocomplete="off"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 py-2.5 text-sm font-bold focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                    id="display-nilai-tl">
                                {{-- Hidden input inilah yang benar-benar dikirim ke server --}}
                                <input type="hidden" name="nilai_tindak_lanjut" id="nilai_tindak_lanjut"
                                    value="{{ old('nilai_tindak_lanjut', 0) }}">
                            </div>

                            {{-- Pesan error validasi nilai (muncul secara dinamis) --}}
                            <div id="error-nilai-tl" class="hidden mt-1.5 flex items-center gap-1 text-xs text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span id="error-nilai-tl-msg"></span>
                            </div>
                            @error('nilai_tindak_lanjut')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-[10px] text-gray-400 font-medium">* Masukkan total target penyelesaian. Tidak boleh melebihi nilai sisa rekomendasi.</p>
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
                                        <span class="text-xs font-bold text-gray-400 whitespace-nowrap">Bulan</span>
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
                        @php $verifikatorUsers = $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values(); @endphp
                        <div class="w-full px-2.5 xl:w-1/2"
                             x-data='verifikatorSelect(@json($verifikatorUsers), @json(old("diverifikasi_oleh")))'>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Verifikator
                            </label>
                            <div class="relative">
                                <input type="hidden" name="diverifikasi_oleh" x-model="selectedId">
                                <button type="button" @click="open = !open" @keydown.escape="open = false"
                                    class="flex h-11 w-full items-center rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                    :class="open ? 'border-brand-300 ring-3 ring-brand-500/10' : ''">
                                    <span class="flex-1 text-left truncate"
                                          :class="selectedId ? 'text-gray-900 dark:text-white' : 'text-gray-400'"
                                          x-text="selectedId ? (users.find(u => u.id == selectedId)?.name ?? '-- Pilih Verifikator --') : '-- Pilih Verifikator --'">
                                    </span>
                                    <svg class="h-4 w-4 text-gray-500 transition-transform" :class="{'rotate-180': open}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open = false"
                                     style="max-height: 15rem; overflow-y: auto;"
                                     class="absolute left-0 right-0 top-full mt-1 z-50 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                    <div class="sticky top-0 bg-white dark:bg-gray-900 p-2 border-b border-gray-100 dark:border-gray-700">
                                        <input type="text" x-model="search" @input="open = true"
                                               placeholder="Cari verifikator..."
                                               class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm outline-none focus:border-brand-300 focus:ring-1 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                                    </div>
                                    <ul class="py-1">
                                        <template x-for="user in filteredUsers" :key="user.id">
                                            <li>
                                                <button type="button" @click="select(user.id)"
                                                    class="flex w-full items-center px-4 py-2.5 text-sm transition-colors"
                                                    :class="selectedId == user.id
                                                        ? 'bg-brand-50 text-brand-700 font-semibold dark:bg-brand-900/20 dark:text-brand-300'
                                                        : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'">
                                                    <svg x-show="selectedId == user.id" class="mr-2 h-4 w-4 shrink-0 text-brand-600"
                                                         fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span x-text="user.name"></span>
                                                </button>
                                            </li>
                                        </template>
                                        <li x-show="filteredUsers.length === 0">
                                            <p class="px-4 py-3 text-xs text-gray-400 text-center">Tidak ada verifikator ditemukan</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan Tindak Lanjut</label>
                            <textarea name="catatan_tl" rows="3" maxlength="1000"
                                placeholder="Tambahkan keterangan (maks. 1000 karakter)..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">{{ old('catatan_tl') }}</textarea>
                        </div>

                        {{-- Hambatan --}}
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Hambatan</label>
                            <textarea name="hambatan" rows="3" maxlength="1000"
                                placeholder="Tuliskan hambatan yang dihadapi (opsional)..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:ring-brand-500/10 focus:border-brand-300 dark:bg-gray-900 dark:border-gray-700 dark:text-white">{{ old('hambatan') }}</textarea>
                        </div>

                        {{-- Lampiran --}}
                        <div class="w-full px-2.5">
                            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                                <div class="flex items-center justify-between px-5 py-4 sm:px-6 sm:py-5">
                                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Lampiran</h3>
                                    <button type="button" onclick="addFileInput()"
                                        class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-[10px] font-bold text-white hover:bg-blue-700 transition-all">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        TAMBAH FILE
                                    </button>
                                </div>
                                <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                                    <div id="attachments-container" class="space-y-3">
                                        <div class="flex flex-col items-center justify-center py-6 text-center" id="empty-attachments">
                                            <svg class="mb-2 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <p class="text-xs font-medium text-gray-400">Belum ada lampiran</p>
                                        </div>
                                    </div>
                                </div>
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
                                <span id="btn-text">Simpan Data</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tlRekomForm', () => ({
        programId    : '{{ old("audit_program_id", "") }}',
        rekomendasis : [],
        rekomendasiId: '{{ old("recommendation_id", "") }}',
        loadingRekom : false,

        onProgramChange() {
            this.rekomendasis  = [];
            this.rekomendasiId = '';
            if (!this.programId) return;
            this.fetchRekomendasis();
        },

        async fetchRekomendasis() {
            this.loadingRekom = true;
            try {
                const url = `/recommendations-by-program/${this.programId}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                this.rekomendasis = await res.json();

                if (this.rekomendasiId && this.rekomendasis.find(r => String(r.id) === this.rekomendasiId)) {
                    this.$nextTick(() => {
                        const sel = document.getElementById('recommendation_id');
                        if (sel) { sel.value = this.rekomendasiId; sel.dispatchEvent(new Event('change')); }
                    });
                }
            } catch (e) {
                console.error('[tlRekomForm]', e);
                this.rekomendasis = [];
            } finally {
                this.loadingRekom = false;
            }
        },

        init() {
            if (this.programId) this.fetchRekomendasis();
        }
    }));

    Alpine.data('verifikatorSelect', (users, oldId) => ({
        users: users,
        selectedId: oldId || '',
        search: '',
        open: false,

        get filteredUsers() {
            if (!this.search) return this.users;
            const q = this.search.toLowerCase();
            return this.users.filter(u => u.name.toLowerCase().includes(q));
        },

        select(id) {
            this.selectedId = id;
            this.search = '';
            this.open = false;
        }
    }));
});

document.addEventListener('DOMContentLoaded', function () {
    /* ── Helpers ─────────────────────────────────────────── */
    function formatRibuan(val) {
        const num = String(val).replace(/\D/g, '');
        if (!num) return '';
        return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function parseRibuan(val) {
        return parseInt(String(val).replace(/\./g, '') || '0', 10);
    }
    function rupiahLabel(num) {
        return 'Rp ' + formatRibuan(num);
    }

    /* ── Elemen ──────────────────────────────────────────── */
    const selectRekom    = document.getElementById('recommendation_id');
    const displayNilai   = document.getElementById('display-nilai-tl');
    const hiddenNilai    = document.getElementById('nilai_tindak_lanjut');
    const errorBox       = document.getElementById('error-nilai-tl');
    const errorMsg       = document.getElementById('error-nilai-tl-msg');
    const infoBox        = document.getElementById('info-rekom-box');
    const infoRekom      = document.getElementById('info-nilai-rekom');
    const infoSisa       = document.getElementById('info-nilai-sisa');
    const infoJenis      = document.getElementById('info-jenis');
    const radios         = document.querySelectorAll('input[name="jenis_penyelesaian"]');
    const sectionCicilan = document.getElementById('section-cicilan');
    const form           = document.getElementById('main-form');
    const submitBtn      = document.getElementById('submit-btn');
    const btnText        = document.getElementById('btn-text');

    let maxNilai  = 0;  // nilai_sisa dari rekomendasi terpilih
    let isNilaiOK = true;

    /* ── Format rupiah on input ──────────────────────────── */
    displayNilai.addEventListener('input', function () {
        const raw = this.value.replace(/\./g, '').replace(/\D/g, '');
        const curPos = this.selectionStart;
        const prevLen = this.value.length;

        const formatted = formatRibuan(raw);
        this.value = formatted;

        // Pertahankan posisi cursor
        const diff = formatted.length - prevLen;
        try { this.setSelectionRange(curPos + diff, curPos + diff); } catch(e) {}

        const num = parseInt(raw || '0', 10);
        hiddenNilai.value = num;

        validateNilai(num);
    });

    displayNilai.addEventListener('paste', function (e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = pasted.replace(/\D/g, '');
        this.value = formatRibuan(cleaned);
        const num = parseInt(cleaned || '0', 10);
        hiddenNilai.value = num;
        validateNilai(num);
    });

    /* ── Validasi nilai ≤ sisa rekomendasi ───────────────── */
    function validateNilai(num) {
        const opt = selectRekom.options[selectRekom.selectedIndex];
        const jenis = opt?.getAttribute('data-jenis') ?? '';

        // Untuk non-uang, skip validasi
        if (jenis && jenis !== 'uang') {
            showError(null);
            return;
        }

        if (maxNilai > 0 && num > maxNilai) {
            showError('Nilai melebihi sisa rekomendasi (' + rupiahLabel(maxNilai) + '). Tidak dapat disimpan.');
        } else if (num === 0 && jenis === 'uang') {
            showError(null); // izinkan 0, hanya warn saat submit
        } else {
            showError(null);
        }
    }

    function showError(msg) {
        if (msg) {
            errorMsg.textContent = msg;
            errorBox.classList.remove('hidden');
            displayNilai.classList.add('border-red-500');
            displayNilai.classList.remove('border-gray-300');
            isNilaiOK = false;
        } else {
            errorBox.classList.add('hidden');
            displayNilai.classList.remove('border-red-500');
            displayNilai.classList.add('border-gray-300');
            isNilaiOK = true;
        }
    }

    /* ── Saat pilih rekomendasi ──────────────────────────── */
    selectRekom.addEventListener('change', function () {
        const opt    = this.options[this.selectedIndex];
        const sisa   = parseInt(opt.getAttribute('data-sisa') || '0', 10);
        const rekom  = parseInt(opt.getAttribute('data-rekom') || '0', 10);
        const jenis  = opt.getAttribute('data-jenis') || '';

        maxNilai = sisa;

        // Tampilkan info box
        if (this.value) {
            infoBox.style.display = 'block';
            infoRekom.textContent = rupiahLabel(rekom);
            infoSisa.textContent  = rupiahLabel(sisa);
            infoJenis.textContent = jenis;
        } else {
            infoBox.style.display = 'none';
        }

        // Untuk jenis uang: auto-fill nilai = sisa
        if (jenis === 'uang' && sisa > 0) {
            displayNilai.value = formatRibuan(sisa);
            hiddenNilai.value  = sisa;
            document.getElementById('field-nilai-tl').style.display = '';
        } else if (jenis !== 'uang') {
            // Non-uang: sembunyikan field nilai, set 0
            displayNilai.value = '';
            hiddenNilai.value  = 0;
            document.getElementById('field-nilai-tl').style.display = 'none';
        }

        validateNilai(parseInt(hiddenNilai.value || '0', 10));
    });

    // Old value restoration — Alpine async cascade might still be loading
    // This retries until the select has the expected old option
    (function restoreOldValue() {
        const oldId = '{{ old("recommendation_id", "") }}';
        if (!oldId) return;
        (function check() {
            if (selectRekom.options.length > 1) {
                for (let opt of selectRekom.options) {
                    if (opt.value === oldId) {
                        selectRekom.value = oldId;
                        selectRekom.dispatchEvent(new Event('change'));
                        const oldNilai = parseInt('{{ old("nilai_tindak_lanjut", 0) }}', 10);
                        if (oldNilai > 0) {
                            displayNilai.value = String(oldNilai).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            hiddenNilai.value  = oldNilai;
                            validateNilai(oldNilai);
                        }
                        return;
                    }
                }
            }
            setTimeout(check, 100);
        })();
    })();

    /* ── Toggle section cicilan ──────────────────────────── */
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            sectionCicilan.classList.toggle('hidden', this.value !== 'cicilan');
            if (this.value === 'cicilan') {
                document.getElementsByName('jumlah_cicilan_rencana')[0]?.focus();
            }
        });
    });

    /* ── Guard submit ────────────────────────────────────── */
    form.addEventListener('submit', function (e) {
        const opt   = selectRekom.options[selectRekom.selectedIndex];
        const jenis = opt?.getAttribute('data-jenis') ?? '';
        const num   = parseInt(hiddenNilai.value || '0', 10);

        // Blokir jika nilai melebihi sisa
        if (jenis === 'uang' && maxNilai > 0 && num > maxNilai) {
            e.preventDefault();
            showError('Nilai melebihi sisa rekomendasi (' + rupiahLabel(maxNilai) + '). Perbaiki sebelum menyimpan.');
            displayNilai.focus();
            return;
        }

        // Warn jika nilai 0 dan jenis uang
        if (jenis === 'uang' && num === 0) {
            if (!confirm('Nilai Tindak Lanjut adalah 0. Anda yakin ingin melanjutkan?')) {
                e.preventDefault();
                return;
            }
        }

        submitBtn.disabled = true;
        btnText.innerText  = 'Menyimpan...';
    });
    // ── Lampiran: tambah input file ──────────────────────────
    window.addFileInput = function () {
        const container = document.getElementById('attachments-container');
        const empty = document.getElementById('empty-attachments');
        if (empty) empty.remove();

        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-center gap-3 group';
        wrapper.innerHTML = `
            <input type="file" name="attachments[]"
                   onchange="this.nextElementSibling.textContent = this.files[0]?.name || ''"
                   class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400">
            <span class="text-xs text-gray-400 truncate max-w-[160px]"></span>
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