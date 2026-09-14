@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Pegawai Per Instansi / OPD</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola data pegawai dan hak akses unit kerja OPD / Instansi.</p>
            </div>
            @can('user.create')
            <a href="{{ route('pegawai.opd.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pegawai OPD
            </a>
            @endcan
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase font-semibold">Total Pegawai OPD</p>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase font-semibold">Aktif</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['aktif'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase font-semibold">Nonaktif</p>
                <p class="text-2xl font-bold text-rose-500 dark:text-rose-400 mt-1">{{ $stats['nonaktif'] }}</p>
            </div>
        </div>

        {{-- Filter Card --}}
        <form method="GET" id="filter-form"
              class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 mb-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 items-end">
                {{-- Search --}}
                <div class="lg:col-span-5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Pencarian</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama, email, NIP..."
                               class="w-full h-10 pl-10 pr-4 text-xs border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                    </div>
                </div>

                {{-- Unit OPD --}}
                <div class="lg:col-span-4">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Unit OPD</label>
                    <select name="unit_opd" id="unit-opd-select" data-no-ts
                            class="h-10 w-full px-3 text-xs border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="">Semua Unit OPD</option>
                        @foreach($opdUnitOptions as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_opd') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Per Page --}}
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Tampil</label>
                    <select name="per_page" id="per-page-select" data-no-ts
                            class="h-10 w-full px-2 text-xs border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="15" {{ (request('per_page', '15') == '15') ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="lg:col-span-2 flex gap-1.5">
                    <button type="submit"
                            class="h-10 w-full inline-flex items-center justify-center rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white active:scale-95 transition-all shadow-sm">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'unit_opd']))
                        <a href="{{ route('pegawai.opd.index') }}"
                           class="h-10 px-3 inline-flex items-center justify-center rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            {{-- Active Filter Badges --}}
            @if(request()->hasAny(['search', 'unit_opd']))
                <div class="flex flex-wrap items-center gap-2 pt-3 mt-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-xs text-gray-400 font-medium">Filter aktif:</span>
                    @if($search = request('search'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-full">
                            Pencarian: "{{ $search }}"
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-indigo-900 dark:hover:text-indigo-300">&times;</a>
                        </span>
                    @endif
                    @if($unitId = request('unit_opd'))
                        @php $unitName = $opdUnitOptions->firstWhere('id', (int) $unitId)?->nama_unit ?? 'Unit #'.$unitId @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full">
                            Unit: {{ $unitName }}
                            <a href="{{ request()->fullUrlWithQuery(['unit_opd' => null]) }}" class="hover:text-emerald-900 dark:hover:text-emerald-300">&times;</a>
                        </span>
                    @endif
                </div>
            @endif
        </form>

        {{-- Info Bar --}}
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $users->firstItem() ?: 0 }}</span>
                –
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $users->lastItem() ?: 0 }}</span>
                dari
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $users->total() }}</span>
                pegawai
            </p>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-sm text-left">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700 backdrop-blur-sm">
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Pegawai</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">NIP / Kontak</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Unit OPD</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($users as $user)
                        <tr class="{{ ! $user->is_active ? 'opacity-60 bg-gray-50/50 dark:bg-gray-950/30' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                            {{-- Column 1: Pegawai --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-xs font-bold text-indigo-700 dark:text-indigo-300 shrink-0 ring-1 ring-indigo-200 dark:ring-indigo-800">
                                        {{ $user->initials ?? '-' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Column 2: NIP / Kontak --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="text-xs font-mono font-medium text-gray-800 dark:text-gray-200">{{ $user->nip ?: '-' }}</p>
                                @if($user->phone)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $user->phone }}</p>
                                @endif
                            </td>

                            {{-- Column 3: Unit OPD --}}
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-xs">
                                    @forelse($user->opdUnits as $unit)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                                            {{ $unit->nama_unit }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">Belum di-assign</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- Column 4: Status --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Column 5: Aksi --}}
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @can('user.view')
                                    <a href="{{ route('pegawai.opd.show', $user) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 transition hover:border-blue-300 dark:hover:border-blue-700 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                       title="Detail">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('user.edit')
                                    <a href="{{ route('pegawai.opd.edit', $user) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 transition hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20"
                                       title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('user.delete')
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('pegawai.opd.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Hapus pegawai OPD {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 transition hover:border-rose-300 dark:hover:border-rose-700 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20"
                                                title="Hapus">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-16">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-gray-400 dark:text-gray-500 font-medium">Belum ada data pegawai OPD</p>
                                <p class="text-gray-400 dark:text-gray-600 text-sm mt-1">Tambahkan pegawai baru untuk mulai.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Per-page auto-submit
    const perPage = document.getElementById('per-page-select');
    if (perPage) {
        perPage.addEventListener('change', function () {
            document.getElementById('filter-form').submit();
        });
    }
});
</script>
@endsection