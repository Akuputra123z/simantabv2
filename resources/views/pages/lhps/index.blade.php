@extends('layouts.app')

@section('content')

{{-- Notifikasi Sukses Otomatis Hilang 5 Detik --}}
@if(session('success'))
<div id="alert-success" class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] transition-all duration-500 ease-in-out opacity-100 scale-100">
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Notifikasi</h3>
        <button onclick="dismissAlert()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="p-4 sm:p-6">
        <div class="flex items-center gap-3 w-full sm:max-w-[400px] rounded-md border-b-4 border-green-500 bg-white p-3 shadow-theme-sm dark:bg-[#1E2634]">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg text-green-600 bg-green-50 dark:bg-green-500/15">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-800 dark:text-white">Berhasil!</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ session('success') }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Form Filter --}}
    <form id="filter-form" action="{{ route('lhps.index') }}" method="GET"></form>

    {{-- Form Bulk Delete --}}
    <form id="main-form" action="{{ route('lhps.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')

        {{-- Header & Toolbar --}}
        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Daftar LHP</h3>
                <p class="text-xs text-gray-500">Manajemen Laporan Hasil Pemeriksaan</p>
                <button type="button" id="btn-bulk-delete" class="hidden mt-2 text-xs font-bold text-red-600 hover:text-red-700 transition-all uppercase">
                    🗑️ Hapus Terpilih (<span id="count-selected">0</span>)
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex gap-2">
                    <select name="tahun" form="filter-form" onchange="this.form.submit()"
                        class="h-10 rounded-lg border border-gray-200 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-gray-400">
                        <option value="">Semua Tahun</option>
                        @foreach(range(date('Y'), date('Y')-3) as $y)
                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" form="filter-form" value="{{ request('search') }}"
                        placeholder="Cari nomor LHP..."
                        class="h-10 rounded-lg border border-gray-200 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
                </div>
                <a href="{{ route('lhps.create') }}"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-all">
                    Buat LHP Baru
                </a>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-6 py-4 w-10 text-center">
                            <input type="checkbox" id="check-all"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                        </th>
                        <th class="px-4 py-4">Nomor LHP</th>
                        <th class="px-4 py-4">Program Audit</th>
                        <th class="px-4 py-4">Tanggal</th>
                        <th class="px-4 py-4 text-center">Progress TL</th>
                        <th class="px-4 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    @forelse($lhps as $lhp)

                    {{-- 
                        Ambil persen dari statistik langsung (sudah eager-loaded di controller).
                        Ini lebih aman daripada accessor $lhp->persen_selesai karena
                        tidak tergantung pada kondisi relationLoaded.
                    --}}
                    @php
                        $persen      = (float) ($lhp->statistik?->persen_selesai_gabungan ?? 0);
                        $persenLabel = number_format($persen, 0);

                        $barColor = match(true) {
                            $persen >= 100 => 'bg-green-500',
                            $persen >= 50  => 'bg-amber-400',
                            $persen > 0    => 'bg-blue-500',
                            default        => 'bg-gray-300',
                        };
                    @endphp

                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $lhp->id }}"
                                class="check-item h-4 w-4 rounded border-gray-300 cursor-pointer">
                        </td>
                        <td class="px-4 py-4">
                            <span class="font-bold text-gray-800 dark:text-white">{{ $lhp->nomor_lhp }}</span>
                            <div class="text-[10px] text-gray-400 uppercase tracking-tight">Irban: {{ $lhp->irban }}</div>
                        </td>
                        <td class="px-4 py-4 text-gray-600 dark:text-gray-400 max-w-[200px] truncate">
                            {{ $lhp->auditAssignment?->auditProgram?->nama_program ?? '-' }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-gray-500">
                            {{ $lhp->tanggal_lhp->format('d/m/Y') }}
                        </td>

                        {{-- Kolom Progress — baca dari statistik, bukan accessor --}}
                        <td class="px-4 py-4">
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-24 bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500"
                                        style="width: {{ min($persen, 100) }}%"></div>
                                </div>
                                <span class="text-[10px] font-semibold
                                    {{ $persen >= 100 ? 'text-green-600' : ($persen >= 50 ? 'text-amber-500' : 'text-gray-500') }}">
                                    {{ $persenLabel }}%
                                </span>

                                {{-- Info jumlah rekomendasi jika statistik tersedia --}}
                                @if($lhp->statistik)
                                    <span class="text-[9px] text-gray-400">
                                        {{ $lhp->statistik->rekom_selesai }}/{{ $lhp->statistik->total_rekomendasi }} rekom
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-3 text-gray-400">
                                <a href="{{ route('lhps.show', $lhp->id) }}" class="hover:text-blue-500" title="Lihat">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a href="{{ route('lhps.edit', $lhp->id) }}" class="hover:text-amber-500" title="Edit">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>

                                {{-- Tombol refresh statistik manual --}}
                                <form action="{{ route('lhps.refresh', $lhp->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="hover:text-green-500 transition-colors" title="Refresh statistik">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polyline points="23 4 23 10 17 10"></polyline>
                                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                        </svg>
                                    </button>
                                </form>

                                <button type="button" onclick="openDeleteModal('single', '{{ $lhp->id }}')"
                                    class="hover:text-red-500 transition-colors" title="Hapus">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center text-gray-500 italic">
                            Belum ada data LHP.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
            {{ $lhps->links() }}
        </div>
    </form>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="delete-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 transition-all duration-300 ease-out opacity-0">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
    <div id="modal-content" class="relative w-full max-w-md transform rounded-3xl bg-white p-8 shadow-2xl transition-all duration-300 ease-out scale-95 opacity-0 dark:bg-gray-900 border border-white/10">
        <div class="flex flex-col items-center text-center">
            <div class="relative mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/20">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v2m3 3h7M3 7h18"/>
                </svg>
            </div>
            <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400" id="modal-message"></p>
            <div class="mt-8 flex w-full gap-3">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 rounded-2xl border border-gray-200 bg-white py-3 text-sm font-semibold text-gray-600 dark:bg-transparent dark:text-gray-400">
                    Batal
                </button>
                <button type="button" id="confirm-delete-btn"
                    class="flex-1 rounded-2xl bg-red-600 py-3 text-sm font-semibold text-white hover:bg-red-700 active:scale-95 transition-all">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Form Hapus Satuan --}}
<form id="delete-single-form" method="POST" class="hidden">@csrf @method('DELETE')</form>

<script>
    let currentDeleteType = '';
    let currentId = null;

    const modal        = document.getElementById('delete-modal');
    const modalContent = document.getElementById('modal-content');
    const btnBulk      = document.getElementById('btn-bulk-delete');
    const checkAll     = document.getElementById('check-all');
    const checkboxes   = document.querySelectorAll('.check-item');
    const countSpan    = document.getElementById('count-selected');

    // Auto-dismiss notifikasi
    function dismissAlert() {
        const alert = document.getElementById('alert-success');
        if (alert) {
            alert.classList.add('opacity-0', 'scale-95');
            setTimeout(() => alert.remove(), 500);
        }
    }
    if (document.getElementById('alert-success')) {
        setTimeout(dismissAlert, 5000);
    }

    // Checkbox bulk
    function toggleBulkUI() {
        const checked = document.querySelectorAll('.check-item:checked');
        btnBulk.classList.toggle('hidden', checked.length === 0);
        countSpan.innerText = checked.length;
    }

    if (checkAll) {
        checkAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            toggleBulkUI();
        });
    }
    checkboxes.forEach(cb => cb.addEventListener('change', toggleBulkUI));

    // Modal hapus
    function openDeleteModal(type, id = null) {
        currentDeleteType = type;
        currentId = id;
        document.getElementById('modal-message').innerText = type === 'bulk'
            ? `Anda akan menghapus ${document.querySelectorAll('.check-item:checked').length} data LHP.`
            : 'Apakah Anda yakin ingin menghapus data LHP ini secara permanen?';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95');
            modalContent.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeDeleteModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    btnBulk.addEventListener('click', () => openDeleteModal('bulk'));

    document.getElementById('confirm-delete-btn').addEventListener('click', function () {
        this.disabled  = true;
        this.innerText = 'Processing...';
        if (currentDeleteType === 'bulk') {
            document.getElementById('main-form').submit();
        } else {
            const form   = document.getElementById('delete-single-form');
            form.action  = `/lhps/${currentId}`;
            form.submit();
        }
    });
</script>
@endsection