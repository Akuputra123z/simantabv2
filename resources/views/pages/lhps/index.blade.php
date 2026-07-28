@extends('layouts.app')

@section('content')

{{-- ✅ Alert Berhasil --}}
@if(session('success'))
<div id="alert-success" class="mb-6 rounded-xl border border-green-500 bg-green-50 p-4 dark:border-green-500/30 dark:bg-green-500/15 transition-all duration-500">
    <div class="flex items-start gap-3">
        <div class="text-green-500">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        <div class="flex-1 text-sm font-medium text-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
        <button onclick="dismissAlert()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
@endif

{{-- Header --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Daftar Laporan Hasil Pemeriksaan</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Total: {{ $lhps->total() }} LHP ditemukan</p>
    </div>
    <a href="{{ route('lhps.create') }}"
       class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm shadow-blue-500/10 active:scale-[0.98] transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat LHP Baru
    </a>
</div>

{{-- Filter --}}
<form method="GET" action="{{ url()->current() }}" class="mb-6 flex flex-col gap-3 md:flex-row md:items-center">
    <div class="flex-1 min-w-0">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 dark:text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nomor LHP..."
                   class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500">
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
        <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
            <select name="tahun" data-auto-submit>
                <option value="">Semua Tahun</option>
                @foreach(range(date('Y'), date('Y') - 3) as $y)
                    <option value="{{ $y }}" @selected(request('tahun') == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
            <select name="kategori" data-auto-submit>
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $k)
                    <option value="{{ $k }}" @selected(request('kategori') == $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg bg-gray-950 text-sm font-medium text-white hover:bg-gray-850 focus:outline-none focus:ring-2 focus:ring-gray-950/20 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-500/20 transition-colors whitespace-nowrap">
            Filter
        </button>
        @if (request()->hasAny(['search', 'tahun', 'kategori']))
        <a href="{{ route('lhps.index') }}"
           class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
            Reset
        </a>
        @endif
    </div>
</form>

{{-- Table --}}
<form id="main-form" action="{{ route('lhps.bulkDelete') }}" method="POST">
    @csrf
    @method('DELETE')

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50/70 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3.5 w-[4%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-center">
                        <input type="checkbox" id="check-all"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                    </th>
                    <th class="px-5 py-3.5 w-[24%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Nomor LHP &amp; Program</th>
                    <th class="px-5 py-3.5 w-[18%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Penugasan Audit</th>
                    <th class="px-5 py-3.5 w-[12%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Unit</th>
                    <th class="px-5 py-3.5 w-[9%] text-[10px] font-bold uppercase tracking-wide text-gray-400">Tanggal</th>
                    <th class="px-5 py-3.5 w-[12%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-center">Progress TL</th>
                    <th class="px-5 py-3.5 w-[8%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-center">Kategori</th>
                    <th class="px-5 py-3.5 w-[13%] text-[10px] font-bold uppercase tracking-wide text-gray-400 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($lhps as $lhp)
                @php
                    $persen      = (float) ($lhp->statistik?->persen_selesai_gabungan ?? 0);
                    $persenLabel = number_format($persen, 0);
                    $barColor = match(true) {
                        $persen >= 100 => 'bg-green-500',
                        $persen >= 50  => 'bg-amber-400',
                        $persen > 0    => 'bg-blue-500',
                        default        => 'bg-gray-300',
                    };
                    $k = $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->kategori;
                    $kategoriBadge = match($k) {
                        'PKPT' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                        'BPK'  => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                        'BPKP' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
                        'ITPROV' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                        'ITDA'   => 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400',
                        default  => 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                    {{-- Checkbox --}}
                    <td class="px-5 py-4 text-center">
                        <input type="checkbox" name="ids[]" value="{{ $lhp->id }}"
                            class="check-item h-4 w-4 rounded border-gray-300 cursor-pointer">
                    </td>

                    {{-- Nomor LHP & Program --}}
                    <td class="px-5 py-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $lhp->nomor_lhp }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->nama_program ?? '-' }}
                        </div>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @if($lhp->auditAssignment?->auditProgramDetail?->tim)
                            <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20 text-[9px] font-semibold text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                Irban: {{ $lhp->auditAssignment->auditProgramDetail->tim }}
                            </span>
                            @endif
                            @if($lhp->status)
                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[9px] font-semibold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                {{ ucfirst($lhp->status) }}
                            </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px] font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                {{ $lhp->statistik?->total_temuan ?? 0 }} temuan
                            </span>
                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px] font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                {{ $lhp->statistik?->total_rekomendasi ?? 0 }} rekom
                            </span>
                        </div>
                    </td>

                    {{-- Penugasan Audit --}}
                    <td class="px-5 py-4">
                        <div class="text-[11px] leading-snug text-gray-600 dark:text-gray-400">
                            @if($lhp->auditAssignment)
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $lhp->auditAssignment->nomor_surat }}</div>
                                @if($lhp->auditAssignment->nama_tim)
                                    <div class="text-[10px] text-gray-400 mt-0.5">Tim: {{ $lhp->auditAssignment->nama_tim }}</div>
                                @endif
                            @else
                                <span class="italic text-gray-300">-</span>
                            @endif
                        </div>
                    </td>

                    {{-- Unit --}}
                    <td class="px-5 py-4">
                        <div class="text-[11px] leading-snug text-gray-600 dark:text-gray-400 break-words max-w-[120px]">
                            {{ $lhp->unitDiperiksa?->label ?? $lhp->unitDiperiksa?->nama_unit ?? '-' }}
                        </div>
                    </td>

                    {{-- Tanggal --}}
                    <td class="px-5 py-4 whitespace-nowrap">
                        <div class="text-[11px] text-gray-600 dark:text-gray-400">
                            {{ $lhp->tanggal_lhp->translatedFormat('d M Y') }}
                        </div>
                    </td>

                    {{-- Progress TL --}}
                    <td class="px-5 py-4">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full max-w-[100px] bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500"
                                    style="width: {{ min($persen, 100) }}%"></div>
                            </div>
                            <span class="text-[10px] font-semibold
                                {{ $persen >= 100 ? 'text-green-600' : ($persen >= 50 ? 'text-amber-500' : 'text-gray-500') }}">
                                {{ $persenLabel }}%
                            </span>
                            @if($lhp->statistik)
                            <span class="text-[9px] text-gray-400">
                                {{ $lhp->statistik->rekom_selesai }}/{{ $lhp->statistik->total_rekomendasi }} rekom
                            </span>
                            @endif
                        </div>
                    </td>

                    {{-- Kategori --}}
                    <td class="px-5 py-4 text-center">
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $kategoriBadge }}">
                            {{ $k ?? '-' }}
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-5 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('lhps.show', $lhp->id) }}" class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors" title="Lihat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                            </a>
                            <a href="{{ route('lhps.edit', $lhp->id) }}" class="p-1.5 text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 17H9v-2.828l9.414-9.586z" stroke-width="2"/></svg>
                            </a>
                            <a href="{{ route('laporan.preview-pdf-per-lhp', $lhp->id) }}" target="_blank"
                               class="p-1.5 text-gray-400 hover:text-green-600 transition-colors" title="Unduh PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-width="2"/></svg>
                            </a>
                            <button type="button" onclick="openDeleteModal('single', '{{ $lhp->id }}')"
                                    class="p-1.5 text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-full text-gray-300">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="1.5"/></svg>
                            </div>
                            <p class="text-sm text-gray-400 font-medium italic">Tidak ada data LHP.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5 dark:border-gray-800">
        <button type="button" id="btn-bulk-delete" class="hidden text-xs font-bold text-red-600 hover:text-red-700 transition-all uppercase">
            Hapus Terpilih (<span id="count-selected">0</span>)
        </button>
        <div class="flex-1 flex justify-center">
            @if($lhps->hasPages())
            {{ $lhps->links() }}
            @endif
        </div>
    </div>
</div>
</form>

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