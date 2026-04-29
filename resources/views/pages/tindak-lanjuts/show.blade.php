@extends('layouts.app')

@section('content')

@if(!isset($tindakLanjut) || !$tindakLanjut->id)
<div class="flex h-[70vh] flex-col items-center justify-center text-center">
    <div class="mb-4 rounded-full bg-gray-100 p-6">
        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    <h2 class="text-2xl font-bold text-gray-900">Data Tidak Ditemukan</h2>
    <p class="mt-2 text-gray-500">Mohon maaf, data tindak lanjut yang Anda cari tidak tersedia.</p>
    <a href="{{ route('tindak-lanjuts.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gray-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-gray-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Daftar
    </a>
</div>
@php return; @endphp
@endif

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    
    {{-- TOP NAVIGATION & ACTIONS --}}
    <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
        <div class="space-y-1">
            <nav class="flex items-center gap-2 text-sm font-medium text-gray-400">
                <a href="{{ route('tindak-lanjuts.index') }}" class="transition hover:text-gray-900">Tindak Lanjut</a>
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg>
                <span class="text-gray-900">ID #{{ $tindakLanjut->id }}</span>
            </nav>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Detail Tindak Lanjut</h1>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('tindak-lanjuts.edit', $tindakLanjut->id) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit Data
            </a>
            <a href="{{ route('tindak-lanjuts.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-gray-800">
                Tutup
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        
     {{-- LEFT COLUMN: SLIM & EFFICIENT --}}
<div class="space-y-5 lg:col-span-8">
    
    {{-- MAIN CONTENT CARD (COMPACT) --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-50 bg-gray-50/30 px-5 py-3 flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Uraian Rekomendasi</span>
            @php
                $statusColor = [
                    'disetujui' => 'bg-green-50 text-green-700 ring-green-600/10',
                    'ditolak' => 'bg-red-50 text-red-700 ring-red-600/10',
                    'menunggu' => 'bg-amber-50 text-amber-700 ring-amber-600/10'
                ][$tindakLanjut->status_verifikasi] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
            @endphp
            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset {{ $statusColor }} uppercase tracking-wider">
                {{ str_replace('_',' ',$tindakLanjut->status_verifikasi) }}
            </span>
        </div>
        <div class="p-5">
            <p class="text-base font-medium leading-relaxed text-gray-800">
                {{ $tindakLanjut->recommendation->uraian_rekom ?? 'Tidak ada uraian rekomendasi.' }}
            </p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3 border-t border-gray-50 pt-5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Metode</p>
                    <p class="mt-0.5 text-sm font-bold text-gray-900">{{ ucfirst($tindakLanjut->jenis_penyelesaian) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Batas Waktu</p>
                    <p class="mt-0.5 text-sm font-bold text-gray-900">
                        {{ $tindakLanjut->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tindakLanjut->tanggal_jatuh_tempo)->format('d M Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Verifikator</p>
                    <p class="mt-0.5 text-sm font-bold text-gray-900 truncate" title="{{ $tindakLanjut->verifikator->name ?? '-' }}">
                        {{ $tindakLanjut->verifikator->name ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- CICILAN / RIWAYAT (SLIM TABLE) --}}
    @if($tindakLanjut->jenis_penyelesaian === 'cicilan')
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-900">Riwayat Pembayaran</h3>
            <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut->id) }}" class="text-[10px] font-bold text-indigo-600 hover:underline uppercase">
                Lihat Semua
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-50">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50/50 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Ke</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2 text-right">Nominal</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($tindakLanjut->cicilans()->latest()->take(3)->get() as $cicilan)
                    <tr class="hover:bg-gray-50/30 transition-colors">
                        <td class="px-4 py-2 font-bold text-gray-900">#{{ $cicilan->ke }}</td>
                        <td class="px-4 py-2 text-gray-500">
                            {{ $cicilan->tanggal_bayar ? $cicilan->tanggal_bayar->format('d/m/y') : '-' }}
                        </td>
                        <td class="px-4 py-2 text-right font-bold text-gray-900">
                            Rp{{ number_format($cicilan->nilai_bayar, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="rounded px-1.5 py-0.5 text-[9px] font-bold {{ $cicilan->status == 'diterima' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ strtoupper($cicilan->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- CATATAN SECTION (REDUCED) --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center gap-2">
            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-900">Catatan</h3>
        </div>
        <div class="rounded-xl bg-gray-50/50 p-4 border border-gray-50">
            <p class="text-xs leading-relaxed text-gray-600 italic">
                {!! $tindakLanjut->catatan_tl ? nl2br(e($tindakLanjut->catatan_tl)) : 'Tidak ada catatan.' !!}
            </p>
        </div>
    </div>
</div>

        {{-- RIGHT COLUMN: SLIM METRICS & INFO --}}
<div class="space-y-5 lg:col-span-4">
    
    {{-- FINANCIAL CARD (SLIM VERSION) --}}
    @if($tindakLanjut->recommendation->jenis_rekomendasi === 'uang')
    <div class="rounded-2xl bg-gray-900 p-6 text-white shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Realisasi</span>
            <span class="text-xl font-black text-green-400">
                {{ round(($tindakLanjut->total_terbayar / max($tindakLanjut->nilai_tindak_lanjut, 1)) * 100) }}%
            </span>
        </div>
        
        <div class="space-y-3">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-bold tracking-tight">Target Nilai</p>
                    <p class="text-base font-bold">Rp{{ number_format($tindakLanjut->nilai_tindak_lanjut, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] text-gray-400 uppercase font-bold tracking-tight">Sisa</p>
                    <p class="text-sm font-bold text-rose-400">Rp{{ number_format($tindakLanjut->nilai_tindak_lanjut - $tindakLanjut->total_terbayar, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-800">
                <div class="h-full bg-green-500 transition-all duration-700"
                     style="width: {{ ($tindakLanjut->total_terbayar / max($tindakLanjut->nilai_tindak_lanjut,1))*100 }}%">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- METADATA INFO (CLEAN LIST) --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h4 class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-900 border-b border-gray-50 pb-2">Metadata</h4>

        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-bold uppercase text-gray-400">No. LHP</p>
                    <p class="text-xs font-bold text-gray-900 truncate">{{ $tindakLanjut->recommendation->temuan->lhp->nomor_lhp ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-bold uppercase text-gray-400">Kode Temuan</p>
                    <p class="text-xs font-bold text-gray-900">{{ $tindakLanjut->recommendation->temuan->kodeTemuan->kode ?? '-' }}</p>
                </div>
            </div>

            <div class="pt-4 mt-2 border-t border-gray-50">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-600">
                        {{ strtoupper(substr($tindakLanjut->creator->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] font-bold uppercase text-gray-400">Oleh</p>
                        <p class="text-xs font-bold text-gray-900 truncate">{{ $tindakLanjut->creator->name ?? 'Sistem' }}</p>
                        <p class="text-[9px] text-gray-400 uppercase tracking-tighter">{{ $tindakLanjut->created_at->format('d/m/y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MINIMAL HELP --}}
    <div class="rounded-2xl border border-dashed border-gray-200 p-5">
        <p class="text-[11px] font-bold text-gray-900 uppercase mb-1">Butuh Bantuan?</p>
        <p class="text-[11px] leading-relaxed text-gray-500">Hubungi admin jika terdapat inkonsistensi data finansial atau status verifikasi.</p>
    </div>

</div>
    </div>
</div>

<style>
    /* Custom smoothing for font rendering */
    body {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        background-color: #fcfcfd;
    }
</style>
@endsection