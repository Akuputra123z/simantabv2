@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    {{-- Header Section --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Tindak Lanjut</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola dan pantau progres tindak lanjut hasil pemeriksaan.</p>
        </div>
        <a href="{{ route('tindak-lanjuts.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Tindak Lanjut
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Lunas</span>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats->total_lunas ?? 0 }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sedang Berjalan</span>
            <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats->total_berjalan ?? 0 }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menunggu Verifikasi</span>
            <div class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats->total_menunggu ?? 0 }}</div>
        </div>
    </div>

    {{-- SEARCH & FILTER BAR --}}
    <form action="{{ route('tindak-lanjuts.index') }}" method="GET" class="mb-6 flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari uraian rekomendasi atau nomor LHP..." 
                   class="block w-full pl-10 pr-3 py-2 bg-white border border-gray-200 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
        </div>
        
        <div class="flex gap-3">
            <select name="status" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full md:w-48 p-2 transition-all">
                <option value="">Semua Status</option>
                {{-- FIX: Memastikan status yang dipilih tetap terpilih setelah reload --}}
                <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                <option value="berjalan" {{ request('status') == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
            </select>
            
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-black transition-colors">
                Filter
            </button>
            
            @if(request()->filled('search') || request()->filled('status'))
                <a href="{{ route('tindak-lanjuts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Table Section --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase">Rekomendasi / LHP</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase">Jenis</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase text-right">Nilai TL</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tindakLanjuts as $tl)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $tl->recommendation->uraian_rekom ?? '-' }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $tl->recommendation->temuan->lhp->nomor_lhp ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tl->jenis_penyelesaian === 'cicilan' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ ucfirst($tl->jenis_penyelesaian) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right">
                            @if($tl->recommendation?->isUang())
                                <div class="text-sm font-mono font-medium text-gray-900">
                                    Rp {{ number_format($tl->nilai_tindak_lanjut, 0, ',', '.') }}
                                </div>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                    {{ ucfirst($tl->recommendation?->jenis_rekomendasi ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClass = match($tl->status_verifikasi) {
                                    'lunas'               => 'bg-green-100 text-green-700',
                                    'berjalan'            => 'bg-blue-100 text-blue-700',
                                    'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-700',
                                    default               => 'bg-gray-100 text-gray-600'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold uppercase {{ $statusClass }}">
                                {{ str_replace('_', ' ', $tl->status_verifikasi) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('tindak-lanjuts.show', $tl->id) }}" class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" title="Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('tindak-lanjuts.edit', $tl->id) }}" class="p-1 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L21 3.586a2 2 0 010 2.828l-7 7a2 2 0 01-.883.515l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.515-.883l7-7z"/></svg>
                                </a>
                                <form action="{{ route('tindak-lanjuts.destroy', $tl->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm italic">Data tidak ditemukan untuk kriteria tersebut.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $tindakLanjuts->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection