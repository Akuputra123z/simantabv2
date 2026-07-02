<script>
document.addEventListener('DOMContentLoaded', () => {

    // ══════════════════════════════════════════════════════════════════
    // 1. DATA (DIPERBAIKI: Penanganan Array Kosong)
    // ══════════════════════════════════════════════════════════════════
    const ALL_UNITS = @json($units ?? []);
    
    // Pastikan INITIAL_UNITS selalu array agar tidak error .map()
    const rawOldUnits = @json(old('unit_diperiksa_ids', isset($preselectedUnits) ? $preselectedUnits->pluck('id') : []));
    const INITIAL_UNITS = Array.isArray(rawOldUnits) ? rawOldUnits : Object.values(rawOldUnits || {});
    
    let selectedIds = new Set(INITIAL_UNITS.map(id => Number(id)));

    const INIT_PROG_ID  = "{{ $currentProgId ?? '' }}";
    const INIT_DET_ID   = "{{ $currentDetId  ?? '' }}";

    // ══════════════════════════════════════════════════════════════════
    // 2. ELEMENT REFS
    // ══════════════════════════════════════════════════════════════════
    const $progSelect   = document.getElementById('audit_program_id');
    const $detSelect    = document.getElementById('audit_program_detail_id');
    const $list         = document.getElementById('unit-option-list');
    const $unitSearch   = document.getElementById('unit-search');
    const $filterKat    = document.getElementById('filter_kategori');
    const $filterKec    = document.getElementById('filter_kecamatan');
    const $filterShow   = document.getElementById('filter_show');
    const $selectAllRow = document.getElementById('select-all-row');
    const $cbAll        = document.getElementById('cb-all');

    if (!$progSelect || !$detSelect) return;

    // ══════════════════════════════════════════════════════════════════
    // 3. PKPT CUSTOM SEARCHABLE DROPDOWN
    // ══════════════════════════════════════════════════════════════════
    let pkptOptions = [];
    let pkptDetailMap = {};
    let pkptIsOpen  = false;

    const pkptWrapper = document.createElement('div');
    pkptWrapper.className = 'pkpt-wrapper';
    $detSelect.parentNode.insertBefore(pkptWrapper, $detSelect);
    $detSelect.style.display = 'none';
    pkptWrapper.appendChild($detSelect);

    const pkptTrigger = document.createElement('div');
    pkptTrigger.id        = 'pkpt-trigger';
    pkptTrigger.className = 'flex items-center justify-between h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm cursor-pointer select-none transition-all duration-150 hover:border-blue-400 dark:bg-gray-900 dark:border-gray-700';
    pkptTrigger.innerHTML = `
        <span id="pkpt-label" class="truncate text-gray-400 dark:text-gray-500 flex-1 mr-2">Pilih detail setelah memilih program</span>
        <span id="pkpt-anggaran" class="shrink-0 text-[11px] font-semibold text-green-600 dark:text-green-400 mr-2 hidden"></span>
        <svg id="pkpt-arrow" class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>`;
    pkptWrapper.insertBefore(pkptTrigger, $detSelect);

    const pkptPanel = document.createElement('div');
    pkptPanel.id        = 'pkpt-panel';
    pkptPanel.className = 'hidden absolute left-0 right-0 z-[300] mt-1 rounded-xl border border-gray-200 bg-white shadow-2xl shadow-black/10 overflow-hidden dark:border-gray-700 dark:bg-gray-900';
    pkptPanel.innerHTML = `
        <div class="flex items-center gap-2 border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
             <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                 <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
             </svg>
             <input id="pkpt-search" type="text" placeholder="Cari PKPT..." class="flex-1 bg-transparent text-sm outline-none text-gray-700 dark:text-white placeholder-gray-400" autocomplete="off">
        </div>
        <div id="pkpt-list" class="max-h-64 overflow-y-auto"></div>`;
    pkptWrapper.appendChild(pkptPanel);

    const $pkptSearch = pkptPanel.querySelector('#pkpt-search');
    const $pkptList   = pkptPanel.querySelector('#pkpt-list');

    function pkptOpen() {
        pkptPanel.classList.remove('hidden');
        document.getElementById('pkpt-arrow').style.transform = 'rotate(180deg)';
        pkptTrigger.classList.add('border-blue-500', 'ring-2', 'ring-blue-500/20');
        pkptIsOpen = true;
        $pkptSearch.value = '';
        pkptRenderList('');
        setTimeout(() => $pkptSearch.focus(), 40);
    }

    function pkptClose() {
        pkptPanel.classList.add('hidden');
        document.getElementById('pkpt-arrow').style.transform = '';
        pkptTrigger.classList.remove('border-blue-500', 'ring-2', 'ring-blue-500/20');
        pkptIsOpen = false;
    }

    pkptTrigger.addEventListener('click', () => pkptIsOpen ? pkptClose() : pkptOpen());
    $pkptSearch.addEventListener('input', () => pkptRenderList($pkptSearch.value));
    document.addEventListener('click', e => {
        if (pkptIsOpen && !pkptWrapper.contains(e.target)) pkptClose();
    });

    function fmtRupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function pkptUpdateInfo(value) {
        const $info = document.getElementById('pkpt-info');
        if (!$info) return;
        const data = pkptDetailMap[value] || {};
        const jenis = data.jenis_kegiatan || '';
        const tim = data.tim || '';
        if (jenis || tim) {
            $info.innerHTML = `
                <div class="flex flex-wrap gap-3 mt-2 text-xs">
                    ${jenis ? `<span class="inline-flex items-center gap-1 rounded-md bg-purple-50 dark:bg-purple-900/20 px-2 py-1 text-purple-700 dark:text-purple-300 font-medium"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg> Kegiatan: ${esc(jenis)}</span>` : ''}
                    ${tim ? `<span class="inline-flex items-center gap-1 rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-blue-700 dark:text-blue-300 font-medium"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Tim: ${esc(tim)}</span>` : ''}
                </div>`;
            $info.classList.remove('hidden');
        } else {
            $info.classList.add('hidden');
        }
    }

    function pkptSetValue(value, label, anggaran) {
        Array.from($detSelect.options).forEach(o => $detSelect.removeChild(o));
        if (value) {
            $detSelect.add(new Option(label, value, true, true));
        } else {
            $detSelect.add(new Option('Pilih detail setelah memilih program', '', true, true));
        }

        const $label = document.getElementById('pkpt-label');
        const $anggaran = document.getElementById('pkpt-anggaran');
        if (value) {
            $label.textContent = label;
            $label.className   = 'truncate flex-1 mr-2 text-gray-800 dark:text-white';
            if ($anggaran && Number(anggaran) > 0) {
                $anggaran.textContent = fmtRupiah(anggaran);
                $anggaran.classList.remove('hidden');
            } else if ($anggaran) {
                $anggaran.classList.add('hidden');
            }
        } else {
            $label.textContent = 'Pilih detail setelah memilih program';
            $label.className   = 'truncate flex-1 mr-2 text-gray-400 dark:text-gray-500';
            if ($anggaran) $anggaran.classList.add('hidden');
        }

        pkptUpdateInfo(value);
    }

    function pkptRenderList(query) {
        const q = (query || '').toLowerCase().trim();
        const filtered = pkptOptions.filter(o => !o.disabled && (!q || o.label.toLowerCase().includes(q)));

        if (!filtered.length) {
            const empty = pkptOptions.length === 0 ? 'Pilih program audit terlebih dahulu' : 'Tidak ada hasil';
            $pkptList.innerHTML = `<p class="px-4 py-5 text-center text-xs text-gray-400 italic">${empty}</p>`;
            return;
        }

        $pkptList.innerHTML = filtered.map(o => {
            const isSel = o.value === $detSelect.value;
            const labelHtml = q ? esc(o.label).replace(new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'), '<mark class="bg-yellow-100 dark:bg-yellow-800/40 not-italic font-semibold rounded px-0.5">$1</mark>') : esc(o.label);
            const anggaranHtml = Number(o.anggaran) > 0 ? `<span class="shrink-0 text-[11px] font-semibold text-green-600 dark:text-green-400 ml-auto">${fmtRupiah(o.anggaran)}</span>` : '';
            return `<div class="pkpt-opt flex items-center gap-2 px-4 py-3 cursor-pointer text-sm leading-snug transition-colors ${isSel ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/30 dark:text-blue-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/[0.04]'}" data-value="${o.value}" data-label="${esc(o.label)}" data-jenis-kegiatan="${esc(o.jenis_kegiatan)}" data-tim="${esc(o.tim)}" data-anggaran="${o.anggaran || 0}"><span class="truncate">${labelHtml}</span>${anggaranHtml}</div>`;
        }).join('');

        $pkptList.querySelectorAll('.pkpt-opt').forEach(el => {
            el.addEventListener('click', () => {
                pkptSetValue(el.dataset.value, el.dataset.label, el.dataset.anggaran);
                pkptClose();
            });
        });
    }

    function pkptSetOptions(opts) {
        pkptOptions = opts;
        pkptRenderList($pkptSearch?.value || '');
    }

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ══════════════════════════════════════════════════════════════════
    // 4. LOAD PKPT AJAX (DIPERBAIKI: Path Route)
    // ══════════════════════════════════════════════════════════════════
async function loadProgramDetails(programId, selectedDetailId = null) {
    pkptSetValue('', '');
    if (!programId) { pkptSetOptions([]); return; }

    pkptTrigger.classList.add('opacity-50', 'pointer-events-none');
    const $label = document.getElementById('pkpt-label');
    $label.textContent = '⏳ Memuat data...';

    try {
        // Ambil ID assignment jika sedang mode EDIT (untuk exclude agar data lama tidak hilang)
        const currentAssignmentId = "{{ $data->id ?? '' }}";
        
        // Gunakan helper url() agar path selalu benar meskipun di halaman create atau edit
        // Route sesuai route:list: get-program-details/{programId}
        const baseUrl = "{{ url('get-program-details') }}";
        const url = `${baseUrl}/${programId}?exclude_assignment=${currentAssignmentId}`;
        
        const res = await fetch(url);
        
        if (!res.ok) throw new Error('Server merespon ' + res.status);
        
        const details = await res.json();

        const opts = details.map(d => ({
            value: String(d.id),
            label: (d.nama_detail_program || 'Tanpa Nama'),
            jenis_kegiatan: d.jenis_kegiatan || '',
            tim: d.tim || '',
            anggaran: Number(d.anggaran) || 0,
            disabled: false,
        }));
        
        pkptDetailMap = {};
        details.forEach(d => {
            pkptDetailMap[String(d.id)] = {
                jenis_kegiatan: d.jenis_kegiatan || '',
                tim: d.tim || '',
                anggaran: d.anggaran || 0,
            };
        });
        
        pkptSetOptions(opts);
        
        // Jika dalam mode edit, pasang kembali nilai yang sudah terpilih sebelumnya
        if (selectedDetailId) {
            const match = opts.find(o => o.value === String(selectedDetailId));
            if (match) pkptSetValue(match.value, match.label, match.anggaran);
        }
    } catch (err) {
        console.error('PKPT load error:', err);
        $label.textContent = '❌ Gagal memuat data';
    } finally {
        pkptTrigger.classList.remove('opacity-50', 'pointer-events-none');
    }
}

    $progSelect.addEventListener('change', e => loadProgramDetails(e.target.value));
    if (INIT_PROG_ID) loadProgramDetails(INIT_PROG_ID, INIT_DET_ID);

    // ══════════════════════════════════════════════════════════════════
    // 5. UNIT PICKER (MULTIPLE)
    // ══════════════════════════════════════════════════════════════════
    function updateKecOptions() {
        const kat = $filterKat?.value;
        const cur = $filterKec?.value;
        const set = new Set();
        ALL_UNITS.forEach(u => {
            if (!kat || u.kategori === kat) { if (u.kecamatan_nama) set.add(u.kecamatan_nama); }
        });
        if ($filterKec) {
            $filterKec.innerHTML = '<option value="">Semua kecamatan</option>' + [...set].sort().map(k => `<option ${k === cur ? 'selected' : ''}>${esc(k)}</option>`).join('');
        }
    }

    function getFiltered() {
        const q   = ($unitSearch?.value || '').toLowerCase().trim();
        const kat = $filterKat?.value  || '';
        const kec = $filterKec?.value  || '';
        const shw = $filterShow?.value || 'all';
        return ALL_UNITS.filter(u => {
            const sel = selectedIds.has(Number(u.id));
            return (!kat || u.kategori === kat) && (!kec || u.kecamatan_nama === kec) && (!q || u.name.toLowerCase().includes(q)) && (shw === 'selected' ? sel : shw === 'unselected' ? !sel : true);
        });
    }

    function renderUnits() {
        if (!$list) return;
        const filtered = getFiltered();
        $list.innerHTML = filtered.length === 0 ? '<p class="p-6 text-center text-xs text-gray-400 italic">Tidak ada unit yang sesuai</p>' : filtered.map(u => {
            const s = selectedIds.has(Number(u.id));
            return `<div onclick="unitToggle(${u.id})" class="flex cursor-pointer items-center gap-3 px-4 py-2.5 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20 ${s ? 'bg-blue-50/60 dark:bg-blue-900/10' : ''}">
                <div class="h-4 w-4 shrink-0 rounded border flex items-center justify-center transition-all ${s ? 'bg-blue-600 border-blue-600' : 'border-gray-300 bg-white dark:bg-gray-800'}">
                    ${s ? '<svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>' : ''}
                </div>
                <span class="text-sm ${s ? 'font-semibold text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-200'}">${esc(u.name)}</span>
            </div>`;
        }).join('');

        if ($cbAll) {
            const isAll = filtered.length > 0 && filtered.every(u => selectedIds.has(Number(u.id)));
            $cbAll.className = `flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-all ${filtered.length === 0 ? 'border-gray-200 bg-gray-50' : isAll ? 'bg-blue-600 border-blue-600' : 'border-gray-300 bg-white dark:bg-gray-800'}`;
            $cbAll.innerHTML = isAll ? '<svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>' : '';
        }
        syncUnitHidden();
    }

    function syncUnitHidden() {
        const wrap = document.getElementById('unit-hidden-inputs');
        if (!wrap) return;
        wrap.innerHTML = '';
        selectedIds.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'unit_diperiksa_ids[]'; inp.value = id;
            wrap.appendChild(inp);
        });
        const pill = document.getElementById('unit-count-pill');
        if (pill) {
            pill.textContent = `${selectedIds.size} unit terpilih`;
            pill.classList.toggle('hidden', selectedIds.size === 0);
        }
    }

    window.unitToggle = id => {
        const n = Number(id);
        selectedIds.has(n) ? selectedIds.delete(n) : selectedIds.add(n);
        renderUnits();
    };

    $selectAllRow?.addEventListener('click', () => {
        const vis = getFiltered();
        if (!vis.length) return;
        const allSel = vis.every(u => selectedIds.has(Number(u.id)));
        vis.forEach(u => allSel ? selectedIds.delete(Number(u.id)) : selectedIds.add(Number(u.id)));
        renderUnits();
    });

    $filterKat?.addEventListener('change', () => { updateKecOptions(); renderUnits(); });
    $filterKec?.addEventListener('change', renderUnits);
    $unitSearch?.addEventListener('input', renderUnits);
    $filterShow?.addEventListener('change', renderUnits);

    updateKecOptions();
    renderUnits();

    // ══════════════════════════════════════════════════════════════════
    // 6. FORMAT RUPIAH Anggaran Disetujui
    // ══════════════════════════════════════════════════════════════════
    const $anggaran = document.getElementById('anggaran_disetujui');
    if ($anggaran) {
        const raw = v => String(v).replace(/[^0-9]/g, '');
        const fmt = v => {
            const n = parseInt(raw(v), 10) || 0;
            return n ? n.toLocaleString('id-ID') : '';
        };

        $anggaran.value = fmt($anggaran.value);

        $anggaran.addEventListener('input', function () {
            const pos = this.selectionStart;
            const before = this.value.slice(0, pos);
            const dotsBefore = (before.match(/\./g) || []).length;
            const rawVal = raw(this.value);
            this.value = fmt(rawVal);
            const dotsAfter = (this.value.slice(0, pos).match(/\./g) || []).length;
            this.setSelectionRange(pos + (dotsAfter - dotsBefore), pos + (dotsAfter - dotsBefore));
        });

        $anggaran.addEventListener('blur', function () {
            this.value = fmt(raw(this.value) || '0');
        });

        const form = $anggaran.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                $anggaran.value = raw($anggaran.value) || '0';
            });
        }
    }
});
</script>