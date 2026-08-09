{{--
    Member Tag Picker Partial
    Variables:
      - $members     : Collection semua user yang bisa dipilih
      - $preselected : Collection user yang sudah dipilih (untuk edit mode)
--}}
@php
    $allMembers = $members ?? collect();
    
    // Ambil data dari old input (saat validasi error)
    $oldIds = old('members');
    
    if ($oldIds) {
        // Jika ada input lama, cari objek usernya agar JavaScript bisa merender nama
        $preselectedMembers = $allMembers->whereIn('id', (array)$oldIds);
    } else {
        // Jika tidak ada error validasi, gunakan data preselected atau kosong.
        // Resolve ID ke objek user agar nama tersedia untuk render tag di JS
        $preselectedMembers = isset($preselected)
            ? $allMembers->whereIn('id', collect((array)$preselected)->flatten()->all())->values()
            : collect();
    }
@endphp

<div class="space-y-1.5 relative mp-box md:col-span-1">
    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Anggota tim</label>

    {{-- Tag container --}}
    <div id="member-picker"
        class="mp-input-box min-h-[42px] w-full cursor-text rounded-lg border border-gray-200 bg-white
               px-3 py-2 flex flex-wrap gap-1.5 items-start
               focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20
               dark:border-gray-700 dark:bg-gray-900 transition-all">
        <input type="text" id="member-search"
            placeholder="Cari nama anggota..."
            autocomplete="off"
            class="min-w-[140px] flex-1 bg-transparent text-sm text-gray-800
                   dark:text-white placeholder-gray-400 outline-none py-0.5 my-0.5">
    </div>

    {{-- Dropdown hasil pencarian --}}
    <div id="member-dropdown"
        class="hidden absolute left-0 right-0 z-[200] mt-1 max-h-56 overflow-y-auto
               rounded-xl border border-gray-200 bg-white shadow-xl shadow-black/10
               dark:border-gray-700 dark:bg-gray-900">
        <div id="member-dropdown-inner"></div>
        <p id="member-no-result" class="hidden px-4 py-3 text-xs text-gray-400 text-center italic">
            Tidak ada anggota yang ditemukan
        </p>
    </div>

    {{-- Hidden inputs untuk submit form --}}
    <div id="member-hidden-inputs">
        @foreach($preselectedMembers as $m)
            @php 
                // Safety check: handle jika $m adalah object, array, atau string ID
                $mid = is_object($m) ? $m->id : (is_array($m) ? $m['id'] : $m); 
            @endphp
            <input type="hidden" name="members[]" value="{{ $mid }}">
        @endforeach
    </div>

    <p class="text-[11px] text-gray-400">Ketik untuk mencari · tekan <kbd class="rounded bg-gray-100 px-1 py-0.5 font-mono text-[10px] dark:bg-gray-800">×</kbd> pada tag untuk hapus</p>
</div>

<script>
(function initMemberPicker() {
    // Pastikan mapping JSON aman
    const ALL = @json($allMembers->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values());
    const INIT = @json(
        $preselectedMembers->map(function($m) {
            return [
                'id' => is_object($m) ? (int)$m->id : (is_array($m) ? (int)$m['id'] : (int)$m),
                'name' => is_object($m) ? $m->name : (is_array($m) ? $m['name'] : '')
            ];
        })->values()
    );

    const selected = new Map(INIT.filter(m => m.name).map(m => [m.id, m.name]));

    const $picker   = document.getElementById('member-picker');
    const $search   = document.getElementById('member-search');
    const $dropdown = document.getElementById('member-dropdown');
    const $inner    = document.getElementById('member-dropdown-inner');
    const $noResult = document.getElementById('member-no-result');
    const $hiddenW  = document.getElementById('member-hidden-inputs');

    function renderTags() {
        $picker.querySelectorAll('.mp-tag').forEach(t => t.remove());
        selected.forEach((name, id) => {
            const tag = document.createElement('span');
            tag.className =
                'mp-tag inline-flex items-center gap-1 rounded-full ' +
                'bg-blue-50 border border-blue-200 pl-2.5 pr-1 py-0.5 ' +
                'text-xs font-medium text-blue-700 ' +
                'dark:bg-blue-900/30 dark:border-blue-700/50 dark:text-blue-300 ' +
                'my-0.5 max-w-full';
            tag.innerHTML =
                `<span class="truncate max-w-[160px]" title="${esc(name)}">${esc(name)}</span>` +
                `<button type="button" data-id="${id}"
                    class="flex-shrink-0 flex h-4 w-4 items-center justify-center rounded-full
                           bg-blue-200 hover:bg-blue-300 text-blue-700
                           dark:bg-blue-700/50 dark:hover:bg-blue-600 dark:text-blue-200
                           transition-colors text-[10px] font-bold leading-none ml-0.5">×</button>`;
            tag.querySelector('button').addEventListener('click', e => {
                e.stopPropagation();
                selected.delete(Number(e.currentTarget.dataset.id));
                renderTags(); syncHidden();
            });
            $picker.insertBefore(tag, $search);
        });
    }

    function syncHidden() {
        $hiddenW.innerHTML = '';
        selected.forEach((_, id) => {
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = 'members[]'; i.value = id;
            $hiddenW.appendChild(i);
        });
    }

    function renderDropdown(q) {
        q = (q || '').toLowerCase().trim();
        if (!q) { hideDropdown(); return; }

        const results = ALL.filter(m =>
            m.name.toLowerCase().includes(q) && !selected.has(m.id)
        ).slice(0, 10);

        $inner.innerHTML = '';
        if (!results.length) {
            $noResult.classList.remove('hidden');
        } else {
            $noResult.classList.add('hidden');
            results.forEach(m => {
                const div = document.createElement('div');
                div.className =
                    'flex items-center gap-2.5 px-4 py-2.5 cursor-pointer text-sm ' +
                    'text-gray-700 dark:text-gray-200 ' +
                    'hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors';
                div.dataset.id   = m.id;
                div.dataset.name = m.name;
                const nameHl = esc(m.name).replace(
                    new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'),
                    '<mark class="bg-yellow-100 dark:bg-yellow-800/40 not-italic font-semibold rounded px-0.5">$1</mark>'
                );
                div.innerHTML =
                    `<div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full
                                 bg-gradient-to-br from-blue-400 to-blue-600 text-white text-xs font-bold">
                         ${esc(m.name.trim().charAt(0).toUpperCase())}
                     </div>
                     <span class="truncate">${nameHl}</span>`;
                div.addEventListener('mousedown', e => {
                    e.preventDefault();
                    selected.set(m.id, m.name);
                    $search.value = '';
                    hideDropdown();
                    renderTags(); syncHidden();
                    $search.focus();
                });
                $inner.appendChild(div);
            });
        }
        $dropdown.classList.remove('hidden');
    }

    function hideDropdown() {
        $dropdown.classList.add('hidden');
        $inner.innerHTML = '';
        $noResult.classList.add('hidden');
    }

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    $search.addEventListener('input',   () => renderDropdown($search.value));
    $picker.addEventListener('click',   () => $search.focus());
    $search.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !$search.value && selected.size > 0) {
            selected.delete([...selected.keys()].pop());
            renderTags(); syncHidden();
        }
        if (e.key === 'Escape') hideDropdown();
        if (e.key === 'Enter')  e.preventDefault();
    });
    document.addEventListener('click', e => {
        if (!$picker.contains(e.target) && !$dropdown.contains(e.target)) hideDropdown();
    });

    renderTags();
    syncHidden();
})();
</script>