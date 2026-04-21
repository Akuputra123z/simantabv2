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

    {{--
        ✅ FIX: Stats Cards sekarang pakai $stats dari DB query langsung,
        bukan $tindakLanjuts->where() yang hanya menghitung halaman aktif (paginated collection).
    --}}
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

                        {{--
                            ✅ FIX: tampilkan nilai TL hanya untuk rekomendasi jenis 'uang'.
                            Untuk barang/administrasi, tampilkan label yang informatif
                            daripada "Rp 0" yang menyesatkan.
                        --}}
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
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">Belum ada data tindak lanjut.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $tindakLanjuts->links() }}
        </div>
    </div>
</div>
@endsection