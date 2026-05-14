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

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- ── Header & Filter ── --}}
    <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Daftar Penugasan Audit</h3>
            <p class="text-xs text-gray-500">Total: {{ $assignments->total() }} penugasan ditemukan</p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ url()->current() }}" method="GET" class="hidden lg:flex gap-2">
                <select name="tahun" onchange="this.form.submit()" class="h-9 rounded-lg border border-gray-200 bg-transparent px-2 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">Semua Tahun</option>
                    @foreach(range(date('Y'), date('Y') - 5) as $y)
                        <option value="{{ $y }}" @selected(request('tahun') == $y)>{{ $y }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="h-9 rounded-lg border border-gray-200 bg-transparent px-2 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') == 'draft')>Draft</option>
                    <option value="berjalan" @selected(request('status') == 'berjalan')>Berjalan</option>
                    <option value="selesai" @selected(request('status') == 'selesai')>Selesai</option>
                </select>

                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor/unit..." 
                        class="h-9 rounded-lg border border-gray-200 bg-transparent pl-8 pr-3 text-xs dark:border-gray-700 dark:text-white outline-none focus:border-blue-500">
                    <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

            <a href="{{ route('audit-assignment.create') }}" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2.5"/></svg>
                TAMBAH
            </a>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-[10px] uppercase tracking-widest text-gray-400 font-bold border-b border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="px-6 py-4">Ketua Tim</th>
                    <th class="px-4 py-4">Program & No. Surat</th>
                    <th class="px-4 py-4">Unit Diperiksa</th>
                    <th class="px-4 py-4">Jadwal</th>
                    <th class="px-4 py-4 text-center">Jenis</th>
                    <th class="px-4 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($assignments as $item)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                    {{-- Ketua Tim --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-[10px] font-bold text-blue-600">
                                {{ strtoupper(substr($item->ketuaTim->name ?? '?', 0, 2)) }}
                            </div>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $item->ketuaTim->name ?? '-' }}</span>
                        </div>
                    </td>

                    {{-- Program & Surat --}}
                    <td class="px-4 py-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $item->auditProgramDetail->auditProgram->nama_program ?? '-' }}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $item->nomor_surat }}
                        </div>
                        <div class="text-[10px] text-blue-500 font-medium mt-1 uppercase italic">
                            {{ $item->auditProgramDetail->nama_detail_program ?? '-' }}
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
                            {{ $item->jenis_pengawasan }}
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
                    <td class="px-6 py-4 text-right">
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
                    <td colspan="7" class="px-6 py-20 text-center">
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