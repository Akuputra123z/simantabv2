@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                Detail Kode Temuan
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan rincian lengkap referensi kode audit.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('kode-temuan.index') }}" 
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:bg-white/5 transition-all">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <a href="{{ route('kode-temuan.edit', $data->id) }}" 
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-all">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Data
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01] px-6 py-5">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $data->kode }}</h4>
                    <span class="text-sm text-gray-500">Kode Numerik: {{ $data->kode_numerik }}</span>
                </div>
                <div class="ml-auto">
                    @php
                        $labels = [1 => 'Ketidakpatuhan', 2 => 'SPI', 3 => '3E'];
                        $colors = [1 => 'bg-blue-100 text-blue-700', 2 => 'bg-purple-100 text-purple-700', 3 => 'bg-amber-100 text-amber-700'];
                    @endphp
                    <span class="{{ $colors[$data->kel] ?? 'bg-gray-100' }} px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                        {{ $labels[$data->kel] ?? 'Unknown' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Kelompok</label>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $data->kelompok }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Sub Kelompok</label>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $data->sub_kelompok }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Dibuat Pada</label>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $data->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 p-5 dark:bg-white/[0.02] border border-gray-100 dark:border-gray-800">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Deskripsi Lengkap</label>
                    <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                        {{ $data->deskripsi }}
                    </p>
                </div>
            </div>

         {{-- Alternatif Rekomendasi Section --}}
@if($rekomendasiTerkait->isNotEmpty())
<div class="mt-10 pt-8 border-t border-gray-100 dark:border-gray-800">
    <div class="flex items-center gap-2 mb-6">
        <div class="h-1.5 w-6 rounded-full bg-blue-600"></div>
        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
            Alternatif Rekomendasi Terkait
        </label>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($rekomendasiTerkait as $rekom)
        <div class="group relative flex flex-col p-4 rounded-2xl border border-gray-200 bg-white transition-all duration-300 hover:border-blue-400 hover:shadow-lg dark:border-gray-800 dark:bg-white/[0.02]">
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20">
                    REKOM: {{ $rekom->kode }}
                </span>
                <span class="text-[9px] text-gray-400 uppercase font-medium">{{ $rekom->kategori }}</span>
            </div>
            
            {{-- Deskripsi asli dari tabel KodeRekomendasi --}}
            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200">
                {{ $rekom->deskripsi }}
            </p>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="mt-8 py-4 px-6 rounded-xl bg-gray-50 border border-dashed border-gray-200 dark:bg-transparent dark:border-gray-800">
    <p class="text-xs text-gray-500 italic text-center">Tidak ada alternatif rekomendasi yang ditautkan.</p>
</div>
@endif
        </div>
    </div>
</div>
@endsection