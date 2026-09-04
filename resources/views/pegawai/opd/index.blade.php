@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Pegawai Per Instansi/OPD</h1>
                <p class="text-sm text-gray-500 mt-0.5">Kelola data pegawai dari masing-masing OPD/Instansi.</p>
            </div>
            <a href="{{ route('pegawai.opd.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pegawai OPD
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold">Total Pegawai OPD</p>
                <p class="text-2xl font-bold mt-1 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold">Aktif</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['aktif'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold">Nonaktif</p>
                <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['nonaktif'] }}</p>
            </div>
        </div>

        {{-- Filter Card --}}
        <form method="GET" id="filter-form"
              class="bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl p-4 mb-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                {{-- Search --}}
                <div class="sm:col-span-1 lg:col-span-5 relative">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pencarian</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama, email, NIP..."
                               class="w-full h-10 pl-10 pr-4 text-sm border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
                    </div>
                </div>

                {{-- Unit OPD (searchable) --}}
                <div class="sm:col-span-1 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Unit OPD</label>
                    <select name="unit_opd" id="unit-opd-select" data-no-ts
                            class="h-10 w-full px-3 text-sm border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                        <option value="">Semua Unit</option>
                        @foreach($opdUnitOptions as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_opd') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Per Page --}}
                <div class="lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tampil</label>
                    <select name="per_page" id="per-page-select"
                            class="h-10 w-full px-2 text-sm border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                        <option value="15" {{ (request('per_page', '15') == '15') ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2 lg:col-span-2">
                    <button type="submit"
                            class="h-10 flex-1 lg:flex-none px-5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors shadow-sm">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'unit_opd']))
                        <a href="{{ route('pegawai.opd.index') }}"
                           class="h-10 px-4 text-sm border dark:border-gray-700 rounded-lg flex items-center text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            {{-- Active Filter Badges --}}
            @if(request()->hasAny(['search', 'unit_opd']))
                <div class="flex flex-wrap items-center gap-2 pt-1 border-t dark:border-gray-800">
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
        <div class="bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl overflow-hidden">
            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gray-50 dark:bg-gray-800 border-b dark:border-gray-700">
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-left">Pegawai</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-left">NIP</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-left">Unit OPD</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-left">Status</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y dark:divide-gray-800">
                        @forelse($users as $user)
                        <tr class="{{ ! $user->is_active ? 'opacity-60' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-sm font-bold text-emerald-700 dark:text-emerald-400 shrink-0">
                                        {{ $user->initials ?? '-' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <p class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ $user->nip ?? '-' }}</p>
                            </td>

                            <td class="px-5 py-4">
                                @if($user->opdUnits->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->opdUnits as $unit)
                                            <span class="inline-block px-2 py-1 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded">
                                                {{ $unit->nama_unit }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-block px-2.5 py-1 text-xs font-medium rounded {{ $user->is_active ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @can('user.view')
                                    <a href="{{ route('pegawai.opd.show', $user) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 transition hover:border-blue-200 dark:hover:border-blue-700 hover:text-blue-600 dark:hover:text-blue-400"
                                       title="Detail">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('user.edit')
                                    <a href="{{ route('pegawai.opd.edit', $user) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 transition hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300"
                                       title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('user.delete')
                                    <form action="{{ route('pegawai.opd.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Hapus pegawai {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 transition hover:border-red-200 dark:hover:border-red-700 hover:text-red-600 dark:hover:text-red-400"
                                                title="Hapus">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
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
                <div class="px-5 py-4 border-t dark:border-gray-800">
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

    // Tom Select for unit dropdown (searchable)
    const unitSelect = document.getElementById('unit-opd-select');
    if (unitSelect) {
        try {
            new TomSelect(unitSelect, {
                placeholder: 'Cari unit OPD...',
                maxItems: 1,
                allowEmptyOption: true,
                selectOnTab: true,
                maxOptions: null,
            });
        } catch (e) {
            console.warn('TomSelect failed:', e);
        }
    }
});
</script>
@endsection