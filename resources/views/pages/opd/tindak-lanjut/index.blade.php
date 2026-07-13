@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Tindak Lanjut Saya
            </h1>
            <p class="text-sm text-gray-500">
                Daftar tindak lanjut untuk unit/instansi Anda.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-5 py-4 text-sm font-semibold text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
        <div class="rounded-2xl border border-green-100 bg-green-50 p-5">
            <p class="text-2xl font-black text-green-700">{{ $stats->total_lunas ?? 0 }}</p>
            <p class="text-xs font-bold uppercase tracking-wider text-green-600">Lunas</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
            <p class="text-2xl font-black text-amber-700">{{ $stats->total_berjalan ?? 0 }}</p>
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Berjalan</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <p class="text-2xl font-black text-blue-700">{{ $stats->total_menunggu ?? 0 }}</p>
            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Menunggu</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
            <p class="text-2xl font-black text-gray-700">{{ $opdStats->total_belum_upload ?? 0 }}</p>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-600">Belum Upload</p>
        </div>
        <div class="rounded-2xl border border-yellow-100 bg-yellow-50 p-5">
            <p class="text-2xl font-black text-yellow-700">{{ $opdStats->total_draft ?? 0 }}</p>
            <p class="text-xs font-bold uppercase tracking-wider text-yellow-600">Draft</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-2xl font-black text-emerald-700">{{ $opdStats->total_dikirim ?? 0 }}</p>
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Terkirim</p>
        </div>
    </div>

    <form action="{{ route('opd.tindak-lanjut.index') }}" method="GET" class="mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <select name="status_opd"
                    class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 outline-none w-full sm:w-48">
                <option value="">Semua Status OPD</option>
                <option value="belum_upload" {{ request('status_opd') == 'belum_upload' ? 'selected' : '' }}>Belum Upload</option>
                <option value="draft" {{ request('status_opd') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="dikirim" {{ request('status_opd') == 'dikirim' ? 'selected' : '' }}>Terkirim</option>
            </select>
            <button type="submit"
                    class="h-10 rounded-lg bg-gray-900 px-5 text-sm font-medium text-white hover:bg-gray-700 transition-colors">
                Filter
            </button>
            @if(request()->filled('status_opd'))
                <a href="{{ route('opd.tindak-lanjut.index') }}"
                   class="inline-flex h-10 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50/50 text-xs font-bold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-4">No. LHP</th>
                    <th class="px-5 py-4">Unit</th>
                    <th class="px-5 py-4">Uraian</th>
                    <th class="px-5 py-4">Status TL</th>
                    <th class="px-5 py-4">Status OPD</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($tindakLanjuts as $tl)
                <tr class="hover:bg-gray-50/30 transition-colors">
                        <td class="px-5 py-4 align-top">
                            <!-- Uraian Rekomendasi (Utama) -->
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 line-clamp-2 leading-snug mb-1">
                                {{ $tl->recommendation->uraian_rekom ?? '-' }}
                            </p>
                            
                            <!-- Nomor LHP (Sekunder/Metadata) -->
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 block">
                                LHP: {{ $tl->recommendation?->temuan?->lhp?->nomor_lhp ?? '-' }}
                            </span>
                        </td>
                    <td class="px-5 py-4 text-gray-600">
                        {{ $tl->recommendation?->temuan?->lhp?->unitDiperiksa?->nama_unit ?? '-' }}
                    </td>
                    <td class="px-5 py-4 max-w-xs truncate text-gray-700">
                        {{ Str::limit($tl->recommendation?->uraian_rekom ?? '-', 60) }}
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $colors = [
                                'lunas' => 'bg-green-50 text-green-700 ring-green-600/10',
                                'berjalan' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                                'menunggu_verifikasi' => 'bg-blue-50 text-blue-700 ring-blue-600/10',
                            ];
                            $color = $colors[$tl->status_verifikasi] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
                        @endphp
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold ring-1 ring-inset {{ $color }}">
                            {{ str_replace('_', ' ', $tl->status_verifikasi) }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $opdColors = [
                                'dikirim' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                                'draft' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/10',
                            ];
                            $opdLabel = $tl->status_opd === 'dikirim' ? 'Terkirim' : ($tl->status_opd === 'draft' ? 'Draft' : 'Belum Upload');
                            $opdColor = $opdColors[$tl->status_opd] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
                        @endphp
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold ring-1 ring-inset {{ $opdColor }}">
                            {{ $opdLabel }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('opd.tindak-lanjut.show', $tl) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </a>
                            @if($tl->status_opd === 'draft')
                                <form action="{{ route('opd.tindak-lanjut.kirim', $tl) }}" method="POST"
                                      onsubmit="return confirm('Kirim tindak lanjut ini? Setelah dikirim tidak bisa diubah.')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        Kirim
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                        Belum ada tindak lanjut untuk unit Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($tindakLanjuts->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $tindakLanjuts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
