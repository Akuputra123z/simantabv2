@extends('layouts.app')

@section('content')
<style>
    input[type="date"] {
        color-scheme: light;
        -webkit-appearance: auto;
        appearance: auto;
    }
    .dark input[type="date"] { color-scheme: dark; }

    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }

    .audit-card {
        border-radius: 1rem;
        overflow: visible !important;
        position: relative;
    }

    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { 
        background: #e5e7eb; 
        border-radius: 10px; 
    }
    .dark .custom-scroll::-webkit-scrollbar-thumb { background: #374151; }

    .pkpt-wrapper { position: relative; }
    .mp-box { overflow: visible !important; }
</style>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-16 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Penugasan Audit</h2>
            <p class="mt-1 text-sm text-gray-400 font-medium">Perbarui rincian surat tugas dan tim audit</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap bg-gray-50 dark:bg-white/5 p-1.5 rounded-2xl border border-gray-100 dark:border-gray-800">
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm">
                <span class="h-5 w-5 rounded-lg bg-white/20 inline-flex items-center justify-center text-[10px]">1</span>
                Informasi
            </span>
            <span class="text-gray-300 dark:text-gray-600 px-1">›</span>
            <span class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold text-gray-400">2. Jadwal & Tim</span>

        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800/30 dark:bg-red-900/20 shadow-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
            </svg>
            <ul class="list-disc pl-4 space-y-0.5 font-medium">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('audit-assignment.update', $data->id) }}" method="POST"
        enctype="multipart/form-data" class="space-y-6" id="audit-form" novalidate>
        @csrf
        @method('PUT')
        <div id="unit-hidden-inputs"></div>

        {{-- ══ SECTION 1: Informasi Audit ══ --}}
        <div class="audit-card border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/30 px-6 py-4 dark:border-gray-800 dark:bg-white/[0.02] rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Informasi Audit</h3>
                        <p class="text-xs text-gray-400 font-medium">Program, jenis pengawasan, dan nomor surat</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {{-- Program Audit --}}
                    <div class="space-y-1.5">
                        <label for="audit_program_id" class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Program Audit <span class="text-red-400">*</span></label>
                        <select id="audit_program_id" name="audit_program_id" data-no-ts required
                            class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all">
                            <option value="">Pilih program audit</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}" @selected(old('audit_program_id', $data->auditProgramDetail?->audit_program_id) == $p->id)>{{ $p->nama_program }} ({{ $p->tahun }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PKPT + Anggaran Disetujui (grouped) --}}
                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label for="audit_program_detail_id" class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">PKPT / Detail Program <span class="text-red-400">*</span></label>
                            <select id="audit_program_detail_id" name="audit_program_detail_id" data-no-ts required
                                class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all">
                                @if($data->audit_program_detail_id)
                                    <option value="{{ $data->audit_program_detail_id }}" selected>
                                        {{ $data->auditProgramDetail?->nama_detail_program ?? 'Data terpilih' }}
                                    </option>
                                @else
                                    <option value="">Pilih detail setelah memilih program</option>
                                @endif
                            </select>
                            <div id="pkpt-info" class="hidden"></div>
                        </div>
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700/50">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Rp</span>
                                <input type="text" name="anggaran_disetujui" id="anggaran_disetujui" inputmode="numeric"
                                    value="{{ old('anggaran_disetujui', $data->anggaran_disetujui ?? 0) }}"
                                    placeholder="0"
                                    class="h-11 w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- Nomor Surat --}}
                    <div class="space-y-1.5">
                        <label for="nomor_surat" class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nomor Surat Tugas <span class="text-red-400">*</span></label>
                        <input type="text" name="nomor_surat" id="nomor_surat" value="{{ old('nomor_surat', $data->nomor_surat) }}"
                            placeholder="700/001/INSPEKTORAT/2026"
                            class="h-11 w-full rounded-xl border border-gray-200 px-4 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all" required>
                    </div>

                </div>

                {{-- Unit Diperiksa --}}
                <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-6 space-y-5 dark:border-gray-700 dark:bg-white/[0.01]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm shadow-blue-200 dark:shadow-none">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                            </div>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wide">Unit Diperiksa (Auditee)</span>
                        </div>
                        <div id="unit-count-pill" class="hidden rounded-full bg-blue-600 px-3 py-1 text-[11px] font-bold text-white shadow-md transition-all"></div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-gray-400 tracking-tighter">Filter Kategori</label>
                            <select id="filter_kategori" data-no-ts class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="">Semua Kategori</option>
                                @foreach($kategoriOptions as $cat) <option value="{{ $cat }}">{{ $cat }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-gray-400 tracking-tighter">Filter Kecamatan</label>
                            <select id="filter_kecamatan" data-no-ts class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="">Semua Kecamatan</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-gray-400 tracking-tighter">Tampilkan</label>
                            <select id="filter_show" data-no-ts class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="all">Semua Unit</option>
                                <option value="selected">Terpilih Saja</option>
                                <option value="unselected">Belum Dipilih</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-inner">
                        <div class="flex items-center gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700 bg-white dark:bg-gray-900">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" id="unit-search" placeholder="Cari nama unit/desa..." class="flex-1 bg-transparent py-1 text-sm font-medium outline-none dark:text-white">
                        </div>

                        <div id="select-all-row" class="flex cursor-pointer items-center gap-3 border-b border-gray-100 bg-gray-50/50 px-5 py-3 hover:bg-blue-50 transition-colors select-none dark:bg-gray-800/50">
                            <div id="cb-all" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 border-gray-300 bg-white dark:bg-gray-900"></div>
                            <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-widest">Pilih Semua yang Tampil</span>
                        </div>

                        <div id="unit-option-list" class="custom-scroll max-h-[420px] overflow-y-auto p-4 bg-white dark:bg-gray-950/20 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ SECTION 2: Jadwal & Personel ══ --}}
        <div class="audit-card border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
            <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/30 px-6 py-4 dark:border-gray-800 dark:bg-white/[0.02] rounded-t-2xl">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Jadwal & Personel</h3>
                    <p class="text-xs text-gray-400 font-medium">Periode audit dan susunan tim pemeriksa</p>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="space-y-1.5">
                        <label for="tanggal_mulai" class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Mulai <span class="text-red-400">*</span></label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                            value="{{ old('tanggal_mulai', $data->tanggal_mulai?->format('Y-m-d')) }}"
                            class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                    <div class="space-y-1.5">
                        <label for="tanggal_selesai" class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Selesai <span class="text-red-400">*</span></label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" required
                            value="{{ old('tanggal_selesai', $data->tanggal_selesai?->format('Y-m-d')) }}"
                            class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                    <div class="space-y-1.5">
                        <label for="status" class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</label>
                        <select name="status" id="status" class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm dark:bg-gray-900 dark:border-gray-700">
                            @foreach(['draft' => 'Draft', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('status', $data->status) == $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="durasi-info" class="{{ $data->tanggal_mulai && $data->tanggal_selesai ? 'flex' : 'hidden' }} items-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-xs font-bold text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="durasi-txt">
                        @if($data->tanggal_mulai && $data->tanggal_selesai)
                            Estimasi Durasi: {{ round((strtotime($data->tanggal_selesai) - strtotime($data->tanggal_mulai)) / 86400) + 1 }} Hari Kalender
                        @endif
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @php $ketuaTimUsers = $ketuaTim->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values(); @endphp
                    <div class="space-y-1.5"
                         x-data='ketuaSelect(@json($ketuaTimUsers), @json(old("ketua_tim_id", $data->ketua_tim_id)))'>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ketua Tim <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="ketua_tim_id" x-model="selectedId">
                            <button type="button" @click="open = !open" @keydown.escape="open = false"
                                class="flex h-11 w-full items-center rounded-xl border border-gray-200 bg-white px-4 text-sm dark:bg-gray-900 dark:border-gray-700"
                                :class="open ? 'border-blue-500 ring-4 ring-blue-500/10' : ''">
                                <span class="flex-1 text-left truncate"
                                      :class="selectedId ? 'text-gray-900 dark:text-white' : 'text-gray-400'"
                                      x-text="selectedId ? (users.find(u => u.id == selectedId)?.name ?? 'Pilih ketua tim') : 'Pilih ketua tim'">
                                </span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform shrink-0" :class="{'rotate-180': open}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 style="max-height: 15rem; overflow-y: auto;"
                                 class="absolute left-0 right-0 top-full mt-1 z-50 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                <div class="sticky top-0 bg-white dark:bg-gray-900 p-2 border-b border-gray-100 dark:border-gray-700">
                                    <input type="text" x-model="search" @input="open = true"
                                           placeholder="Cari ketua tim..."
                                           class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                                </div>
                                <ul class="py-1">
                                    <template x-for="user in filteredUsers" :key="user.id">
                                        <li>
                                            <button type="button" @click="select(user.id)"
                                                class="flex w-full items-center px-4 py-2.5 text-sm transition-colors"
                                                :class="selectedId == user.id
                                                    ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/20 dark:text-blue-300'
                                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'">
                                                <svg x-show="selectedId == user.id" class="mr-2 h-4 w-4 shrink-0 text-blue-600"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span x-text="user.name"></span>
                                            </button>
                                        </li>
                                    </template>
                                    <li x-show="filteredUsers.length === 0">
                                        <p class="px-4 py-3 text-xs text-gray-400 text-center">Tidak ada ketua tim ditemukan</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Pengendali Teknis --}}
                    <div class="space-y-1.5">
                        <label for="pengendali_teknis" class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pengendali Teknis (Dalnis)</label>
                        <input type="text" name="pengendali_teknis" id="pengendali_teknis" value="{{ old('pengendali_teknis', $data->pengendali_teknis) }}"
                            placeholder="Nama pengendali teknis..."
                            class="h-11 w-full rounded-xl border border-gray-200 px-4 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:bg-gray-900 dark:border-gray-700 dark:text-white transition-all">
                        @error('pengendali_teknis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Member Picker --}}
                @include('pages.audit-assignment.partials._member_picker', [
                    'members' => $members,
                    'preselected' => old('members', $data->members->pluck('id')->toArray())
                ])
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between border-t border-gray-100 pt-8 dark:border-gray-800">
            <a href="{{ route('audit-assignment.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <button type="submit" id="submit-btn" class="inline-flex items-center gap-3 rounded-2xl bg-blue-600 px-10 py-4 text-sm font-bold text-white hover:bg-blue-700 transition-all active:scale-95 shadow-xl shadow-blue-500/20">
                <svg id="submit-icon" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                <span id="submit-text">Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('click', function() {
            this.showPicker();
        });
    });

    const $mulai   = document.getElementById('tanggal_mulai');
    const $selesai = document.getElementById('tanggal_selesai');
    const $info    = document.getElementById('durasi-info');
    const $txt     = document.getElementById('durasi-txt');

    function hitungDurasi() {
        if (!$mulai.value || !$selesai.value) { $info.classList.add('hidden'); return; }
        const diff = Math.round((new Date($selesai.value) - new Date($mulai.value)) / 86400000) + 1;
        if (diff <= 0) { $info.classList.add('hidden'); return; }
        $txt.textContent = `Estimasi Durasi: ${diff} Hari Kalender`;
        $info.classList.replace('hidden', 'flex');
    }
    $mulai?.addEventListener('change', hitungDurasi);
    $selesai?.addEventListener('change', hitungDurasi);

    const $form = document.getElementById('audit-form');
    const $btn  = document.getElementById('submit-btn');
    const $bTxt = document.getElementById('submit-text');
    const $bIco = document.getElementById('submit-icon');

    if ($form && $btn) {
        $form.addEventListener('submit', (e) => {
            if (!$form.checkValidity()) return;
            if ($btn.getAttribute('data-submitting') === 'true') { e.preventDefault(); return; }

            $btn.setAttribute('data-submitting', 'true');
            $btn.disabled = true;
            $btn.classList.add('opacity-70', 'cursor-not-allowed');
            $bTxt.textContent = 'Menyimpan...';
            $bIco.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
        });
    }
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ketuaSelect', (users, oldId) => ({
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
</script>
@endsection

@push('scripts')
@php
    $currentProgId    = old('audit_program_id', $data->auditProgramDetail?->audit_program_id ?? '');
    $currentDetId     = old('audit_program_detail_id', $data->audit_program_detail_id ?? '');
    $oldUnitIds       = old('unit_diperiksa_ids', []);
    $preselectedUnits = !empty($oldUnitIds)
        ? \App\Models\UnitDiperiksa::whereIn('id', (array)$oldUnitIds)->get()
        : $data->unitDiperiksas;
@endphp
@include('pages.audit-assignment.partials._audit_scripts')
@endpush
