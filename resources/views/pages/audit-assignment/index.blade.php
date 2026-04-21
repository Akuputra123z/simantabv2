@extends('layouts.app')

@section('content')

{{-- ✅ Alert Berhasil --}}
@if(session('success'))
<div id="alert-success" 
     class="mb-6 rounded-xl border border-green-500 bg-green-50 p-4 dark:border-green-500/30 dark:bg-green-500/15 transition-all duration-500 ease-in-out opacity-100 scale-100">
    <div class="flex items-start gap-3">
        <div class="text-green-500">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
        <div class="flex-1">
            <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">Berhasil!</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ session('success') }}</p>
        </div>
        <button onclick="dismissAlert()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
@endif

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    
    <form id="filter-form" action="{{ url()->current() }}" method="GET"></form>

    <form id="main-form" action="{{ route('audit-assignment.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4 sm:px-6 sm:py-5 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Program Kerja Pengawasan Tahunan (PKPT)</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Monitoring realisasi dan progress LHP per program</p>
                
                <button type="button" id="btn-bulk-delete" class="hidden mt-2 text-xs font-bold text-red-600 hover:text-red-800 transition-all uppercase tracking-wider">
                    🗑️ Hapus yang dipilih (<span id="count-selected">0</span>)
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex gap-2">
                    <select name="tahun" form="filter-form" onchange="this.form.submit()" 
                        class="h-10 rounded-lg border border-gray-200 bg-transparent px-3 text-sm text-gray-500 outline-none focus:border-blue-300 dark:border-gray-700 dark:bg-gray-900">
                        <option value="">Semua Tahun</option>
                        @foreach(range(date('Y'), date('Y')-3) as $y)
                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" form="filter-form" value="{{ request('search') }}" placeholder="Cari program..." 
                        class="h-10 rounded-lg border border-gray-200 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-blue-300 dark:border-gray-700 dark:text-white">
                </div>
                
                <a href="{{ route('audit-assignment.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-all shadow-sm shadow-blue-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Program Baru
                </a>
            </div>
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50">
                    <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        <th class="px-6 py-3 w-10 text-center">
                            <input type="checkbox" id="check-all" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3">Ketua Tim</th>
                        <th class="px-4 py-3">Audit Program</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($assignments as $assignment)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] text-sm">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $assignment->id }}" class="check-item h-4 w-4 rounded border-gray-300 cursor-pointer">
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-xs uppercase">
                                    {{ substr($assignment->ketuaTim->name ?? '??', 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white">{{ $assignment->ketuaTim->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 text-left">{{ $assignment->ketuaTim->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ $assignment->auditProgram->nama_program ?? '-' }}</td>
                        <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ $assignment->unitDiperiksa->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-4 text-center">
                            @php
                                $status = strtolower($assignment->status);
                                $statusClass = ($status == 'complete' || $status == 'selesai')
                                    ? 'bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-500' 
                                    : 'bg-orange-100 text-orange-600 dark:bg-orange-500/15 dark:text-orange-500';
                            @endphp
                            <span class="{{ $statusClass }} rounded-full px-3 py-1 text-xs font-medium inline-block">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-3 text-gray-400">
                                <a href="{{ route('audit-assignment.show', $assignment->id) }}" class="hover:text-blue-500 transition-colors" title="Detail">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                <a href="{{ route('audit-assignment.edit', $assignment->id) }}" class="hover:text-primary transition-colors" title="Edit">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <button type="button" onclick="confirmDelete('single', '{{ $assignment->id }}')" class="hover:text-red-500 transition-colors" title="Hapus">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-16 text-center text-gray-500 italic">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

{{-- --- Modal Konfirmasi Custom --- --}}
<div id="delete-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 transition-all duration-300 ease-out opacity-0">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
    <div id="modal-content" class="relative w-full max-w-md transform rounded-3xl bg-white p-8 shadow-2xl transition-all duration-300 ease-out scale-95 opacity-0 dark:bg-gray-900 border border-white/10">
        <div class="flex flex-col items-center text-center">
            <div class="relative mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/20">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v2m3 3h7M3 7h18"/></svg>
            </div>
            <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400" id="modal-message"></p>
            <div class="mt-8 flex w-full gap-3">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 rounded-2xl border border-gray-200 bg-white py-3 text-sm font-semibold text-gray-600 dark:border-gray-800 dark:bg-transparent dark:text-gray-400">Batal</button>
                <button type="button" id="confirm-delete-btn" class="flex-1 rounded-2xl bg-red-600 py-3 text-sm font-semibold text-white hover:bg-red-700 transition-all active:scale-95">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<form id="delete-single-form" method="POST" class="hidden">@csrf @method('DELETE')</form>

<script>
    let currentDeleteType = ''; 
    let currentId = null;

    const modal = document.getElementById('delete-modal');
    const modalContent = document.getElementById('modal-content');
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.check-item');
    const btnBulk = document.getElementById('btn-bulk-delete');
    const countSpan = document.getElementById('count-selected');

    function toggleBulkButton() {
        const checkedCount = document.querySelectorAll('.check-item:checked').length;
        btnBulk.classList.toggle('hidden', checkedCount === 0);
        countSpan.innerText = checkedCount;
    }

    if(checkAll) {
        checkAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            toggleBulkButton();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', toggleBulkButton));

    function confirmDelete(type, id = null) {
        currentDeleteType = type;
        currentId = id;
        
        const msg = (type === 'bulk') 
            ? `Anda akan menghapus ${document.querySelectorAll('.check-item:checked').length} data sekaligus.` 
            : "Apakah Anda yakin ingin menghapus data ini secara permanen?";
        
        document.getElementById('modal-message').innerText = msg;
        
        // Membuka modal dengan animasi smooth sesuai class CSS Anda
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95');
            modalContent.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function dismissAlert() {
        const alert = document.getElementById('alert-success');
        if (alert) {
            // Berikan efek fade out dan shrink
            alert.classList.add('opacity-0', 'scale-95', 'blur-sm');
            
            // Hapus elemen dari DOM setelah animasi selesai (500ms sesuai durasi class)
            setTimeout(() => {
                alert.remove();
            }, 500);
        }
    }

    // Jalankan timer otomatis 5 detik (5000ms)
    setTimeout(() => {
        dismissAlert();
    }, 3000);

    function closeDeleteModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('opacity-0', 'scale-95');
        modalContent.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    btnBulk.addEventListener('click', () => confirmDelete('bulk'));

    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        this.disabled = true;
        this.innerText = "Memproses...";
        
        if (currentDeleteType === 'bulk') {
            document.getElementById('main-form').submit();
        } else {
            const form = document.getElementById('delete-single-form');
            form.action = `/audit-assignment/${currentId}`;
            form.submit();
        }
    });
</script>
@endsection