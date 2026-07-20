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
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Daftar Penugasan Audit</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Total: {{ $assignments->total() }} penugasan ditemukan</p>
    </div>
    <a href="{{ route('audit-assignment.create') }}"
       class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm shadow-blue-500/10 active:scale-[0.98] transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Penugasan
    </a>
</div>

{{-- Filter --}}
<form method="GET" action="{{ url()->current() }}" class="mb-5 flex flex-col gap-3 md:flex-row md:items-center">
    <div class="flex-1 min-w-0 md:max-w-md">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 dark:text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nomor surat atau unit..."
                   class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500">
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
        <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
            <select name="tahun" data-auto-submit>
                <option value="">Semua Tahun</option>
                @foreach(range(date('Y'), date('Y') - 5) as $y)
                    <option value="{{ $y }}" @selected(request('tahun') == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
            <select name="kategori" data-auto-submit>
                <option value="">Semua Kategori</option>
                @foreach($kategoriOptions as $k)
                    <option value="{{ $k }}" @selected(request('kategori') == $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[140px] sm:flex-initial sm:w-44">
            <select name="status" data-auto-submit>
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') == 'draft')>Draft</option>
                <option value="berjalan" @selected(request('status') == 'berjalan')>Berjalan</option>
                <option value="selesai" @selected(request('status') == 'selesai')>Selesai</option>
            </select>
        </div>
        <button type="submit"
                class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg bg-gray-950 text-sm font-medium text-white hover:bg-gray-850 focus:outline-none focus:ring-2 focus:ring-gray-950/20 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-500/20 transition-colors whitespace-nowrap">
            Filter
        </button>
        @if (request()->hasAny(['search','tahun','kategori','status']))
        <a href="{{ route('audit-assignment.index') }}"
           class="h-10 px-4 flex-1 sm:flex-initial inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
            Reset
        </a>
        @endif
    </div>
</form>

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- ── Table ── --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-[10px] uppercase tracking-widest text-gray-400 font-bold border-b border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="px-5 py-4 w-[30%]">Program & No. Surat</th>
                    <th class="px-4 py-4 w-[16%]">Unit Diperiksa</th>
                    <th class="px-4 py-4 w-[16%]">Jadwal</th>
                    <th class="px-4 py-4 w-[9%] text-center">Jenis</th>
                    <th class="px-4 py-4 w-[9%] text-center">Kategori</th>
                    <th class="px-4 py-4 w-[9%] text-center">Status</th>
                    <th class="px-5 py-4 w-[11%] text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($assignments as $item)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                    {{-- Program & Surat --}}
                    <td class="px-5 py-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                             {{ $item->auditProgramDetail->nama_detail_program ?? '-' }}
                            
                        </div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $item->nomor_surat }}
                        </div>
                        <div class="text-[10px] text-blue-500 font-medium mt-1 uppercase italic">
                           {{ $item->auditProgramDetail->auditProgram->nama_program ?? '-' }}
                        </div>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @if($item->auditProgramDetail?->jenis_kegiatan)
                                <span class="px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-900/20 text-[9px] font-semibold text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/30">
                                    {{ $item->auditProgramDetail->jenis_kegiatan }}
                                </span>
                            @endif
                            @if($item->auditProgramDetail?->tim)
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20 text-[9px] font-semibold text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                    Tim: {{ $item->auditProgramDetail->tim }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Unit Diperiksa (Pivot Many-to-Many) --}}
                    <td class="px-4 py-4">
                        <div class="flex flex-wrap gap-1 max-w-[250px]">
                            @foreach($item->unitDiperiksas->take(2) as $unit)
                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px] text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                    {{ $unit->nama_unit }}
                                </span>
                            @endforeach
                            @if($item->unitDiperiksas->count() > 2)
                                <span class="text-[10px] text-gray-400 ml-1">+{{ $item->unitDiperiksas->count() - 2 }} lainnya</span>
                            @endif
                        </div>
                    </td>

                    {{-- Jadwal --}}
                    <td class="px-4 py-4">
                        <div class="text-[11px] text-gray-600 dark:text-gray-400 flex flex-col">
                            <span class="font-medium text-gray-800 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y') }}
                            </span>
                            <span class="text-[10px] opacity-60">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($item->tanggal_selesai)) + 1 }} Hari Kerja
                            </span>
                        </div>
                    </td>

                    {{-- Jenis --}}
                    <td class="px-4 py-4 text-center">
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
                            {{ $item->auditProgramDetail?->jenis_kegiatan ?? '-' }}
                        </span>
                    </td>

                    {{-- Kategori --}}
                    <td class="px-4 py-4 text-center">
                        @php
                            $k = $item->auditProgramDetail?->auditProgram?->kategori;
                            $kategoriBadge = match($k) {
                                'PKPT' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                                'BPK'  => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                                'BPKP' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
                                'ITPROV' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                                'ITDA'   => 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400',
                                default  => 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $kategoriBadge }}">
                            {{ $k ?? '-' }}
                        </span>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-4 text-center">
                        @php
                            $statusMap = [
                                'draft' => 'bg-gray-100 text-gray-500',
                                'berjalan' => 'bg-blue-100 text-blue-600',
                                'selesai' => 'bg-emerald-100 text-emerald-600',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusMap[$item->status] ?? 'bg-gray-100' }}">
                            {{ $item->status }}
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-5 py-4 text-right">
                        <div class="flex justify-end gap-2">
                           <a href="{{ route('audit-assignment.print', $item->id) }}" target="_blank" class="p-1.5 text-gray-400 hover:text-green-600 transition-colors" title="Cetak Surat Tugas">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </a>
                            <a href="{{ route('audit-assignment.show', $item->id) }}" class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                            </a>
                            <a href="{{ route('audit-assignment.edit', $item->id) }}" class="p-1.5 text-gray-400 hover:text-amber-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 17H9v-2.828l9.414-9.586z" stroke-width="2"/></svg>
                            </a>
                            <form action="{{ route('audit-assignment.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus penugasan ini?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-full text-gray-300">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="1.5"/></svg>
                            </div>
                            <p class="text-sm text-gray-400 font-medium italic">Tidak ada data penugasan audit.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    @if($assignments->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
        {{ $assignments->links() }}
    </div>
    @endif
</div>

<script>
    function dismissAlert() {
        const el = document.getElementById('alert-success');
        if (el) { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 500); }
    }
    setTimeout(dismissAlert, 5000);
</script>

@endsection