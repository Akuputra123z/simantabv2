{{--
    Reusable File Drop-Zone Partial
    Usage: @include('partials._dropzone', ['existing' => $data->attachments ?? collect()])
--}}
<div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Lampiran Surat Tugas</h3>

    {{-- ── Existing Attachments (edit mode) ── --}}
    @if(isset($existing) && $existing->count())
        <div>
            <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Lampiran Terunggah</h4>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($existing as $att)
                <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 p-3
                    dark:border-gray-700 dark:bg-gray-900/50">
                    <div class="flex min-w-0 items-center gap-3">
                        <svg class="h-5 w-5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate text-xs font-medium text-gray-700 dark:text-gray-300">{{ $att->file_name }}</span>
                    </div>
                    <label class="ml-3 flex shrink-0 cursor-pointer items-center gap-1.5 text-[10px] font-bold text-red-500">
                        <input type="checkbox" name="delete_attachments[]" value="{{ $att->id }}"
                            class="h-3.5 w-3.5 rounded text-red-500 accent-red-500">
                        HAPUS
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Drop Zone ── --}}
    <div>
        @if(isset($existing) && $existing->count())
            <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Upload Baru</h4>
        @endif

        <label id="drop-zone"
            class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2
            border-dashed border-gray-300 bg-gray-50 px-6 py-10 transition
            hover:border-blue-400 hover:bg-blue-50/50
            dark:border-gray-700 dark:bg-gray-900/30 dark:hover:border-blue-500 dark:hover:bg-blue-900/10">

            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 7.5m0 0L7.5 12M12 7.5v9"/>
            </svg>

            <div class="text-center">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Klik untuk upload atau drag &amp; drop</p>
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, PDF, DOC, DOCX, XLS, XLSX — maks. 2 MB per file</p>
            </div>

            <input type="file" name="attachments[]" id="attachments"
                class="sr-only" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
        </label>

        <ul id="file-list" class="mt-3 space-y-2 text-sm"></ul>
    </div>
</div>

<script>
(function dropzone() {
    const input    = document.getElementById('attachments');
    const dropZone = document.getElementById('drop-zone');
    const fileList = document.getElementById('file-list');
    const MAX_MB   = 2;

    function renderFiles(files) {
        fileList.innerHTML = '';
        [...files].forEach(file => {
            const ok   = file.size <= MAX_MB * 1024 * 1024;
            const size = (file.size / 1024).toFixed(0) + ' KB';
            fileList.innerHTML += `
                <li class="flex items-center justify-between rounded-lg border px-4 py-2
                    ${ok ? 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900'
                         : 'border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/20'}">
                    <span class="truncate text-xs ${ok ? 'text-gray-700 dark:text-gray-300' : 'text-red-600'}">${file.name}</span>
                    <span class="ml-4 shrink-0 text-xs ${ok ? 'text-gray-400' : 'text-red-500 font-semibold'}">
                        ${ok ? size : '⚠ Terlalu besar'}
                    </span>
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
})();
</script>