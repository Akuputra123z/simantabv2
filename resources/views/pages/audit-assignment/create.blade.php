@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- HEADER --}}
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Audit Assignment</h2>
        <p class="mt-1 text-sm text-gray-500">Isi data penugasan audit dengan lengkap</p>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="rounded-lg bg-red-100 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('audit-assignment.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

{{-- SECTION: Informasi Audit --}}
<div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-8">

    {{-- TITLE --}}
    <div>
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
            Informasi Audit
        </h3>
        <p class="text-sm text-gray-500">
            Isi data utama penugasan audit
        </p>
    </div>

    {{-- GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Program Audit --}}
        <div class="space-y-2">
            <label for="audit_program_id" class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Program Audit
            </label>
            <select name="audit_program_id" id="audit_program_id"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
                <option value="">Pilih Program Audit</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected(old('audit_program_id') == $program->id)>
                        {{ $program->nama_program }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Jenis Pengawasan --}}
        <div class="space-y-2">
            <label for="jenis_pengawasan" class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Jenis Pengawasan
            </label>
            <select name="jenis_pengawasan" id="jenis_pengawasan"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
                <option value="">Pilih Jenis</option>
                @foreach(\App\Models\AuditAssignment::listJenisPengawasan() as $jenis)
                    <option value="{{ $jenis }}" @selected(old('jenis_pengawasan') == $jenis)>
                        {{ ucfirst(str_replace('_', ' ', $jenis)) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Nomor Surat --}}
        <div class="space-y-2 md:col-span-2">
            <label for="nomor_surat" class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Nomor Surat
            </label>
            <input type="text" name="nomor_surat" id="nomor_surat"
                value="{{ old('nomor_surat') }}"
                placeholder="700/001/INSPEKTORAT/2026"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
        </div>

        {{-- Nama Tim --}}
        <div class="space-y-2 md:col-span-2">
            <label for="nama_tim" class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Nama Tim Audit
            </label>
            <input type="text" name="nama_tim" id="nama_tim"
                value="{{ old('nama_tim') }}"
                placeholder="Nama Tim Audit"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 
                dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                focus:ring-2 focus:ring-blue-500 outline-none"
                required>
        </div>

    </div>

    {{-- SUB SECTION: Filter Unit --}}
    <div class="border-t border-gray-200 pt-6 dark:border-gray-700 space-y-6">

        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
            Filter Unit
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Kategori --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    Kategori Unit
                </label>
                <select id="filter_kategori"
                    class="h-11 w-full rounded-lg border border-gray-300 px-4 
                    dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                    focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoriOptions as $k => $v)
                        <option value="{{ $k }}" @selected(old('filter_kategori') == $k)>
                            {{ $v }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kecamatan --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    Kecamatan
                </label>
                <select id="filter_kecamatan"
                    class="h-11 w-full rounded-lg border border-gray-300 px-4 
                    dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 
                    focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Pilih Kecamatan</option>
                </select>
            </div>

            {{-- Unit --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    Nama Unit
                </label>
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
                    placeholder="Pilih Tanggal"
                    class="h-11 w-full cursor-pointer rounded-lg border border-gray-300 px-4 pr-10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:ring-2 focus:ring-blue-500 outline-none"
                    onclick="togglePicker('mulai')">
                <input type="hidden" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
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
                    placeholder="Pilih Tanggal"
                    class="h-11 w-full cursor-pointer rounded-lg border border-gray-300 px-4 pr-10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:ring-2 focus:ring-blue-500 outline-none"
                    onclick="togglePicker('selesai')">
                <input type="hidden" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </span>
            </div>
            <div id="picker-selesai" class="absolute left-0 top-full mt-2 z-50 hidden w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-900"></div>
        </div>

        <div class="space-y-2">
            <label for="status" class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status</label>
            <select name="status" id="status"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="draft" @selected(old('status') == 'draft')>Draft</option>
                <option value="berjalan" @selected(old('status') == 'berjalan')>Berjalan</option>
                <option value="selesai" @selected(old('status') == 'selesai')>Selesai</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mt-4">
        <div class="space-y-2">
            <label for="ketua_tim_id" class="block text-sm font-medium text-gray-600 dark:text-gray-400">Ketua Tim</label>
            <select name="ketua_tim_id" id="ketua_tim_id"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Pilih Ketua Tim</option>
                @foreach($ketuaTim as $user)
                    <option value="{{ $user->id }}" @selected(old('ketua_tim_id') == $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="space-y-2">
    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">
        Anggota Tim
    </label>

    {{-- Tag Container --}}
    <div id="member-picker"
        class="min-h-[44px] w-full rounded-lg border border-gray-300 px-3 py-2 flex flex-wrap gap-2 cursor-text
        dark:border-gray-700 dark:bg-gray-900 focus-within:ring-2 focus-within:ring-blue-500 transition">
        
        {{-- Chips render di sini via JS --}}
        
        <input type="text" id="member-search"
            placeholder="Cari anggota..."
            autocomplete="off"
            class="flex-1 min-w-[140px] bg-transparent outline-none text-sm text-gray-800 dark:text-white placeholder-gray-400">
    </div>

    {{-- Dropdown Suggestions --}}
    <div id="member-dropdown"
        class="hidden absolute z-50 mt-1 w-full max-h-52 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
    </div>

    {{-- Hidden inputs untuk submit --}}
    <div id="member-hidden-inputs"></div>

    <p class="text-xs text-gray-400">Ketik nama untuk mencari dan pilih anggota tim.</p>
</div>
    </div>
</div>

        {{-- SECTION: Dokumen / Lampiran --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-4">
            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Lampiran Surat Tugas</h3>

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
    
    <a href="{{ route('audit-assignment.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 
        hover:bg-gray-50 transition-colors
        dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
        
        Batal
    </a>

    <button type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white 
        hover:bg-blue-700 transition-colors shadow-sm hover:shadow">
        
        Simpan Data
    </button>

</div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Custom Date Picker ──────────────────────────────────────────────
const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const DAYS   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

const pickerState = {
    mulai:   { year: new Date().getFullYear(), month: new Date().getMonth() },
    selesai: { year: new Date().getFullYear(), month: new Date().getMonth() },
};

// ── Member Tag Picker ─────────────────────────────────────────────────
(function () {
    // Data semua member dari Blade (render sebagai JSON)
    const ALL_MEMBERS = @json($members->map(fn($u) => ['id' => $u->id, 'name' => $u->name]));

    // Untuk edit: pre-selected members
    const PRESELECTED = @json(
        isset($data) 
            ? $data->members->map(fn($u) => ['id' => $u->id, 'name' => $u->name]) 
            : []
    );

    let selected = []; // [{id, name}]

    const picker      = document.getElementById('member-picker');
    const searchInput = document.getElementById('member-search');
    const dropdown    = document.getElementById('member-dropdown');
    const hiddenWrap  = document.getElementById('member-hidden-inputs');

    // Init pre-selected (untuk edit page)
    PRESELECTED.forEach(m => addMember(m));

    // Posisi dropdown relatif ke picker
    function positionDropdown() {
        const rect = picker.getBoundingClientRect();
        dropdown.style.width = picker.offsetWidth + 'px';
    }

    function renderChips() {
        // Hapus semua chip lama (bukan input search)
        picker.querySelectorAll('.member-chip').forEach(el => el.remove());

        // Insert chips sebelum search input
        selected.forEach(m => {
            const chip = document.createElement('span');
            chip.className = 'member-chip inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 dark:bg-blue-900/40 dark:text-blue-300';
            chip.innerHTML = `
                ${m.name}
                <button type="button" data-id="${m.id}"
                    class="ml-0.5 rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 p-0.5 transition"
                    onclick="removeMember(${m.id})">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>`;
            picker.insertBefore(chip, searchInput);
        });
    }

    function renderHiddenInputs() {
        hiddenWrap.innerHTML = '';
        selected.forEach(m => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'members[]';
            inp.value = m.id;
            hiddenWrap.appendChild(inp);
        });
    }

    function renderDropdown(query) {
        const q = query.toLowerCase().trim();
        const filtered = ALL_MEMBERS.filter(m =>
            m.name.toLowerCase().includes(q) &&
            !selected.find(s => s.id === m.id)
        );

        if (!filtered.length || !q) {
            dropdown.classList.add('hidden');
            return;
        }

        dropdown.innerHTML = filtered.map(m => `
            <button type="button" onclick="addMemberById(${m.id})"
                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 dark:text-gray-200 dark:hover:bg-blue-900/20 transition text-left">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300 uppercase shrink-0">
                    ${m.name.charAt(0)}
                </span>
                ${m.name}
            </button>`).join('');

        positionDropdown();
        dropdown.classList.remove('hidden');
    }

    function addMember(m) {
        if (selected.find(s => s.id === m.id)) return;
        selected.push(m);
        renderChips();
        renderHiddenInputs();
        searchInput.value = '';
        dropdown.classList.add('hidden');
    }

    // Global functions (dipanggil dari inline onclick)
    window.addMemberById = function(id) {
        const m = ALL_MEMBERS.find(m => m.id === id);
        if (m) addMember(m);
        searchInput.focus();
    };

    window.removeMember = function(id) {
        selected = selected.filter(m => m.id !== id);
        renderChips();
        renderHiddenInputs();
    };

    // Events
    searchInput.addEventListener('input', () => renderDropdown(searchInput.value));
    searchInput.addEventListener('focus', () => {
        if (searchInput.value) renderDropdown(searchInput.value);
    });

    picker.addEventListener('click', () => searchInput.focus());

    // Backspace untuk hapus chip terakhir
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !searchInput.value && selected.length) {
            selected.pop();
            renderChips();
            renderHiddenInputs();
        }
        if (e.key === 'Escape') dropdown.classList.add('hidden');
    });

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', e => {
        if (!picker.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
})();

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
        const val     = formatValue(year, month, d);
        const isToday = val === todayStr;
        const isSel   = val === currentVal;
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
            <button type="button" onclick="shiftMonth('${key}',-1)"
                class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="h-4 w-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
            </button>
            <span class="text-sm font-semibold text-gray-800 dark:text-white">${MONTHS[month]} ${year}</span>
            <button type="button" onclick="shiftMonth('${key}',1)"
                class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-800">
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
    const el       = document.getElementById(`picker-${key}`);
    const otherKey = key === 'mulai' ? 'selesai' : 'mulai';
    document.getElementById(`picker-${otherKey}`).classList.add('hidden');
    if (el.classList.contains('hidden')) {
        renderPicker(key);
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function shiftMonth(key, dir) {
    const s = pickerState[key];
    s.month += dir;
    if (s.month > 11) { s.month = 0;  s.year++; }
    if (s.month < 0)  { s.month = 11; s.year--; }
    renderPicker(key);
}

function selectDate(key, y, m, d) {
    const val = formatValue(y, m, d);

    if (key === 'selesai') {
        const mulai = document.getElementById('tanggal_mulai').value;
        if (mulai && val < mulai) {
            alert('Tanggal selesai tidak boleh sebelum tanggal mulai');
            return;
        }
    }

    if (key === 'mulai') {
        const selesai = document.getElementById('tanggal_selesai').value;
        if (selesai && val > selesai) {
            alert('Tanggal mulai tidak boleh setelah tanggal selesai');
            return;
        }
    }

    const hiddenId  = key === 'mulai' ? 'tanggal_mulai' : 'tanggal_selesai';
    const displayId = key === 'mulai' ? 'tanggal_mulai_display' : 'tanggal_selesai_display';

    document.getElementById(hiddenId).value  = val;
    document.getElementById(displayId).value = formatDisplay(y, m, d);

    document.getElementById(`picker-${key}`).classList.add('hidden');
}

function selectToday(key) {
    const n = new Date();
    selectDate(key, n.getFullYear(), n.getMonth(), n.getDate());
}

// Tutup picker saat klik di luar
document.addEventListener('click', function(e) {
    ['mulai','selesai'].forEach(key => {
        const picker  = document.getElementById(`picker-${key}`);
        const display = document.getElementById(key === 'mulai' ? 'tanggal_mulai_display' : 'tanggal_selesai_display');
        if (!picker.contains(e.target) && e.target !== display) {
            picker.classList.add('hidden');
        }
    });
});

// Pre-fill display jika ada old() value
document.addEventListener('DOMContentLoaded', () => {
    ['mulai','selesai'].forEach(key => {
        const hiddenId  = key === 'mulai' ? 'tanggal_mulai'         : 'tanggal_selesai';
        const displayId = key === 'mulai' ? 'tanggal_mulai_display' : 'tanggal_selesai_display';
        const val = document.getElementById(hiddenId).value;
        if (val) {
            const [y, m, d] = val.split('-').map(Number);
            document.getElementById(displayId).value = formatDisplay(y, m - 1, d);
            pickerState[key] = { year: y, month: m - 1 };
        }
    });
});

// ── Cascade select ──────────────────────────────────────────────────
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

dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    const dt = new DataTransfer();
    [...e.dataTransfer.files].forEach(f => dt.items.add(f));
    input.files = dt.files;
    renderFiles(input.files);
});
</script>
@endpush