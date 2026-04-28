@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- HEADER --}}
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Audit Assignment</h2>
        <p class="mt-1 text-sm text-gray-500">Perbarui data penugasan audit</p>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
            </svg>
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('audit-assignment.update', $data->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')
        <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-8">

    <div>
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
            Informasi Audit
        </h3>
        <p class="text-sm text-gray-500">
            Perbarui data utama penugasan audit
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Program Audit --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Program Audit</label>
            <select name="audit_program_id"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
                <option value="">Pilih Program Audit</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" 
                        @selected(old('audit_program_id', $data->audit_program_id) == $program->id)>
                        {{ $program->nama_program }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Jenis Pengawasan --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Jenis Pengawasan</label>
            <select name="jenis_pengawasan"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
                <option value="">Pilih Jenis</option>
                @foreach(\App\Models\AuditAssignment::listJenisPengawasan() as $jenis)
                    <option value="{{ $jenis }}"
                        @selected(old('jenis_pengawasan', $data->jenis_pengawasan) == $jenis)>
                        {{ ucfirst(str_replace('_', ' ', $jenis)) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Nomor Surat --}}
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nomor Surat</label>
            <input type="text" name="nomor_surat"
                value="{{ old('nomor_surat', $data->nomor_surat) }}"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
        </div>

        {{-- Nama Tim --}}
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nama Tim Audit</label>
            <input type="text" name="nama_tim"
                value="{{ old('nama_tim', $data->nama_tim) }}"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
        </div>

    </div>

    {{-- FILTER UNIT (SAMA PERSIS CREATE) --}}
    <div class="border-t border-gray-200 pt-6 dark:border-gray-700 space-y-6">

        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
            Filter Unit
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <select id="filter_kategori"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Pilih Kategori</option>
                @foreach($kategoriOptions as $k => $v)
                    <option value="{{ $k }}" @selected($currentKategori === strtolower($k))>
                        {{ $v }}
                    </option>
                @endforeach
            </select>

            <select id="filter_kecamatan"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Pilih Kecamatan</option>
            </select>

            <select name="unit_diperiksa_id" id="unit"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
                <option value="">Pilih Unit</option>
            </select>

        </div>
    </div>

</div>


{{-- SECTION: Jadwal & Personel --}}
<div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-6 mt-8">
    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Jadwal & Personel</h3>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        {{-- Tanggal Mulai --}}
        <div class="space-y-2 relative">
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Mulai</label>
            <div class="relative">
                <input type="text" id="tanggal_mulai_display" readonly
                    placeholder="Pilih Tanggal Mulai"
                    class="h-11 w-full cursor-pointer rounded-lg border border-gray-300 px-4 pr-10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                    onclick="togglePicker('mulai')">
                <input type="hidden" name="tanggal_mulai" id="tanggal_mulai"
                    value="{{ old('tanggal_mulai', $data->tanggal_mulai?->format('Y-m-d')) }}" required>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </span>
            </div>
            <div id="picker-mulai" class="absolute left-0 top-full mt-2 z-50 hidden w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-900"></div>
        </div>

        {{-- Tanggal Selesai --}}
        <div class="space-y-2 relative">
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Selesai</label>
            <div class="relative">
                <input type="text" id="tanggal_selesai_display" readonly
                    placeholder="Pilih Tanggal Selesai"
                    class="h-11 w-full cursor-pointer rounded-lg border border-gray-300 px-4 pr-10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                    onclick="togglePicker('selesai')">
                <input type="hidden" name="tanggal_selesai" id="tanggal_selesai"
                    value="{{ old('tanggal_selesai', $data->tanggal_selesai?->format('Y-m-d')) }}" required>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </span>
            </div>
            <div id="picker-selesai" class="absolute left-0 top-full mt-2 z-50 hidden w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-900"></div>
        </div>

        <div class="space-y-2">
            <label for="status" class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status Audit</label>
            <select name="status" id="status"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                <option value="draft"    {{ old('status', $data->status) === 'draft'    ? 'selected' : '' }}>Draft</option>
                <option value="berjalan" {{ old('status', $data->status) === 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                <option value="selesai"  {{ old('status', $data->status) === 'selesai'  ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mt-6">
        <div class="space-y-2">
            <label for="ketua_tim_id" class="block text-sm font-medium text-gray-600 dark:text-gray-400">Ketua Tim</label>
            <select name="ketua_tim_id" id="ketua_tim_id"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                <option value="">Pilih Ketua Tim</option>
                @foreach($ketuaTim as $user)
                    <option value="{{ $user->id }}"
                        {{ old('ketua_tim_id', $data->ketua_tim_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="space-y-2">
            <label for="members" class="block text-sm font-medium text-gray-600 dark:text-gray-400">Anggota Tim</label>
            <select name="members[]" id="members" multiple
                class="min-h-[120px] w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                @foreach($members as $user)
                    <option value="{{ $user->id }}"
                        {{ $data->members->contains($user->id) ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400">Tahan Ctrl / ⌘ untuk memilih lebih dari satu anggota.</p>
        </div>
    </div>
</div>

        {{-- SECTION: Lampiran Existing --}}
        @if($data->attachments->count())
        <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-4">
            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Lampiran Saat Ini</h3>
            <ul class="space-y-2">
                @foreach($data->attachments as $att)
                <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                        </svg>
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                            class="truncate text-sm text-blue-600 hover:underline dark:text-blue-400">
                            {{ $att->file_name }}
                        </a>
                        <span class="shrink-0 text-xs text-gray-400">
                            {{ number_format($att->file_size / 1024, 1) }} KB
                        </span>
                    </div>
                    <label class="ml-4 flex shrink-0 cursor-pointer items-center gap-1.5 text-xs text-red-500 hover:text-red-700">
                        <input type="checkbox" name="delete_attachments[]" value="{{ $att->id }}" class="rounded">
                        Hapus
                    </label>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- SECTION: Upload Lampiran Baru --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-4">
            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Tambah Lampiran Baru</h3>

            <input type="file" name="attachments[]" id="attachments" multiple
                accept=".jpg,.jpeg,.png,.pdf,.docx"
                style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">

            <div id="drop-zone"
                class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-10 cursor-pointer transition hover:border-blue-400 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:border-blue-500">
                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Klik untuk upload atau drag & drop</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF, DOCX — maks. 2MB per file</p>
                </div>
            </div>
            <ul id="file-list" class="space-y-2 text-sm"></ul>
        </div>
        

        {{-- BUTTON --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">

    <a href="{{ route('audit-assignment.show', $data->id) }}"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 
        hover:bg-gray-50 transition-colors
        dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
        Batal
    </a>

    <button type="submit"
        onclick="this.disabled=true; this.innerText='Menyimpan...'; this.form.submit();"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white 
        hover:bg-blue-700 transition-colors shadow-sm hover:shadow">
        Simpan Perubahan
    </button>

</div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Preload cascade untuk edit ──────────────────────────────────────
const CURRENT_KATEGORI  = "{{ $currentKategori }}";
const CURRENT_UNIT_ID   = "{{ old('unit_diperiksa_id', $data->unit_diperiksa_id) }}";
const CURRENT_KECAMATAN = "{{ old('', $data->unitDiperiksa?->nama_kecamatan) }}";

// ── Custom Date Picker ──────────────────────────────────────────────
const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const DAYS   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

const pickerState = {
    mulai:   { year: new Date().getFullYear(), month: new Date().getMonth() },
    selesai: { year: new Date().getFullYear(), month: new Date().getMonth() },
};

function pad(n) { return String(n).padStart(2, '0'); }
function formatDisplay(y, m, d) { return `${pad(d)}-${pad(m+1)}-${y}`; }
function formatValue(y, m, d)   { return `${y}-${pad(m+1)}-${pad(d)}`; }

function renderPicker(key) {
    const el    = document.getElementById(`picker-${key}`);
    const { year, month } = pickerState[key];
    const firstDay  = new Date(year, month, 1).getDay();
    const daysCount = new Date(year, month + 1, 0).getDate();
    const currentVal = document.getElementById(key === 'mulai' ? 'tanggal_mulai' : 'tanggal_selesai').value;
    const todayStr   = new Date().toISOString().slice(0, 10);

    let cells = '';
    for (let i = 0; i < firstDay; i++) cells += `<div></div>`;
    for (let d = 1; d <= daysCount; d++) {
        const val   = formatValue(year, month, d);
        const isSel = val === currentVal;
        const isToday = val === todayStr;
        cells += `
            <button type="button" onclick="selectDate('${key}',${year},${month},${d})"
                class="flex h-8 w-8 items-center justify-center rounded-full text-sm transition
                    ${isSel   ? 'bg-blue-600 text-white font-semibold' :
                      isToday ? 'border border-blue-400 text-blue-600 dark:text-blue-400' :
                                'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'}">
                ${d}
            </button>`;
    }

    el.innerHTML = `
        <div class="mb-3 flex items-center justify-between">
            <button type="button" onclick="shiftMonth('${key}',-1)" class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="h-4 w-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
            </button>
            <span class="text-sm font-semibold text-gray-800 dark:text-white">${MONTHS[month]} ${year}</span>
            <button type="button" onclick="shiftMonth('${key}',1)" class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="h-4 w-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </button>
        </div>
        <div class="mb-1 grid grid-cols-7">
            ${DAYS.map(d => `<div class="flex h-8 items-center justify-center text-xs font-medium text-gray-400">${d}</div>`).join('')}
        </div>
        <div class="grid grid-cols-7">${cells}</div>
        <div class="mt-3 border-t border-gray-100 pt-2 dark:border-gray-700">
            <button type="button" onclick="selectToday('${key}')"
                class="w-full rounded-lg py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
                Hari Ini
            </button>
        </div>`;
}

function togglePicker(key) {
    const el = document.getElementById(`picker-${key}`);
    const otherKey = key === 'mulai' ? 'selesai' : 'mulai';
    const otherEl = document.getElementById(`picker-${otherKey}`);
    
    if(otherEl) otherEl.classList.add('hidden');
    
    if (el && el.classList.contains('hidden')) { 
        renderPicker(key); 
        el.classList.remove('hidden'); 
    } else if(el) { 
        el.classList.add('hidden'); 
    }
}

function shiftMonth(key, dir) {
    const s = pickerState[key];
    s.month += dir;
    if (s.month > 11) { s.month = 0; s.year++; }
    else if (s.month < 0)  { s.month = 11; s.year--; }
    renderPicker(key);
}

function selectDate(key, y, m, d) {
    const hiddenId  = key === 'mulai' ? 'tanggal_mulai'         : 'tanggal_selesai';
    const displayId = key === 'mulai' ? 'tanggal_mulai_display' : 'tanggal_selesai_display';
    
    document.getElementById(hiddenId).value  = formatValue(y, m, d);
    document.getElementById(displayId).value = formatDisplay(y, m, d);
    document.getElementById(`picker-${key}`).classList.add('hidden');
}

function selectToday(key) {
    const n = new Date();
    selectDate(key, n.getFullYear(), n.getMonth(), n.getDate());
}

// Global click listener untuk menutup picker
document.addEventListener('click', function(e) {
    ['mulai','selesai'].forEach(key => {
        const picker  = document.getElementById(`picker-${key}`);
        const display = document.getElementById(key === 'mulai' ? 'tanggal_mulai_display' : 'tanggal_selesai_display');
        if (picker && !picker.contains(e.target) && e.target !== display) {
            picker.classList.add('hidden');
        }
    });
});

// ── Perbaikan Utama pada Inisialisasi ───────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    // Pre-fill tanggal display dengan proteksi null/error
    ['mulai','selesai'].forEach(key => {
        const hiddenInput = document.getElementById(key === 'mulai' ? 'tanggal_mulai' : 'tanggal_selesai');
        const displayInput = document.getElementById(key === 'mulai' ? 'tanggal_mulai_display' : 'tanggal_selesai_display');
        
        if (hiddenInput && hiddenInput.value && hiddenInput.value.includes('-')) {
            try {
                const parts = hiddenInput.value.split('-');
                const y = parseInt(parts[0]);
                const m = parseInt(parts[1]);
                const d = parseInt(parts[2]);
                
                if(!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                    displayInput.value = formatDisplay(y, m - 1, d);
                    pickerState[key] = { year: y, month: m - 1 };
                }
            } catch (e) {
                console.warn("Format tanggal tidak valid", e);
            }
        }
    });

    // Pre-load cascade: kecamatan berdasarkan kategori
    if (CURRENT_KATEGORI) {
        try {
            const res  = await fetch(`/get-kecamatan/${encodeURIComponent(CURRENT_KATEGORI)}`);
            const data = await res.json();
            const kecEl = document.getElementById('filter_kecamatan');
            kecEl.innerHTML = '<option value="">Pilih Kecamatan</option>';
            data.forEach(kec => {
                const selected = kec === CURRENT_KECAMATAN ? 'selected' : '';
                kecEl.innerHTML += `<option value="${kec}" ${selected}>${kec}</option>`;
            });

            // Pre-load unit berdasarkan kecamatan
            if (CURRENT_KECAMATAN) {
                const res2  = await fetch(`/get-unit/${encodeURIComponent(CURRENT_KECAMATAN)}`);
                const data2 = await res2.json();
                const unitEl = document.getElementById('unit');
                unitEl.innerHTML = '<option value="">Pilih Unit</option>';
                data2.forEach(unit => {
                    const selected = String(unit.id) === String(CURRENT_UNIT_ID) ? 'selected' : '';
                    unitEl.innerHTML += `<option value="${unit.id}" ${selected}>${unit.label}</option>`;
                });
            }
        } catch(e) {
            console.error('Gagal load cascade:', e);
        }
    }
});

// ── Cascade onChange ────────────────────────────────────────────────
const kategoriEl  = document.getElementById('filter_kategori');
const kecamatanEl = document.getElementById('filter_kecamatan');
const unitEl      = document.getElementById('unit');

kategoriEl.addEventListener('change', async function () {
    kecamatanEl.innerHTML = '<option value="">Memuat...</option>';
    unitEl.innerHTML      = '<option value="">Pilih Unit</option>';
    if (!this.value) { kecamatanEl.innerHTML = '<option value="">Kecamatan</option>'; return; }
    try {
        const res  = await fetch(`/get-kecamatan/${encodeURIComponent(this.value)}`);
        const data = await res.json();
        kecamatanEl.innerHTML = '<option value="">Pilih Kecamatan</option>';
        data.forEach(kec => { kecamatanEl.innerHTML += `<option value="${kec}">${kec}</option>`; });
    } catch { kecamatanEl.innerHTML = '<option value="">Gagal memuat</option>'; }
});

kecamatanEl.addEventListener('change', async function () {
    unitEl.innerHTML = '<option value="">Memuat...</option>';
    if (!this.value) { unitEl.innerHTML = '<option value="">Pilih Unit</option>'; return; }
    try {
        const res  = await fetch(`/get-unit/${encodeURIComponent(this.value)}`);
        const data = await res.json();
        unitEl.innerHTML = '<option value="">Pilih Unit</option>';
        data.forEach(unit => { unitEl.innerHTML += `<option value="${unit.id}">${unit.label}</option>`; });
    } catch { unitEl.innerHTML = '<option value="">Gagal memuat</option>'; }
});

// ── File upload custom ──────────────────────────────────────────────
const input    = document.getElementById('attachments');
const dropZone = document.getElementById('drop-zone');
const fileList = document.getElementById('file-list');

dropZone.addEventListener('click', () => input.click());

function renderFiles(files) {
    fileList.innerHTML = '';
    [...files].forEach(file => {
        const size = (file.size / 1024).toFixed(1) + ' KB';
        fileList.innerHTML += `
            <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 dark:border-gray-700 dark:bg-gray-900">
                <span class="truncate text-gray-700 dark:text-gray-300">${file.name}</span>
                <span class="ml-4 shrink-0 text-xs text-gray-400">${size}</span>
            </li>`;
    });
}

input.addEventListener('change', () => renderFiles(input.files));
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-blue-500','bg-blue-50'); });
dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-blue-500','bg-blue-50'); });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500','bg-blue-50');
    const dt = new DataTransfer();
    [...e.dataTransfer.files].forEach(f => dt.items.add(f));
    input.files = dt.files;
    renderFiles(input.files);
});
</script>
@endpush