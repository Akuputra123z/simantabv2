@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="hover:text-primary-600">LHP</a>
                <span>/</span>
                <a href="{{ route('lhps.show', $temuan->lhp_id) }}" class="hover:text-primary-600">
                    {{ $temuan->lhp->nomor_lhp }}
                </a>
                <span>/</span>
                <span class="text-gray-700 dark:text-white">Temuan #{{ $temuan->id }}</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Detail Temuan</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('temuan.edit', $temuan) }}"
               class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Edit
            </a>
            <a href="{{ route('lhps.show', $temuan->lhp_id) }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                Kembali
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Temuan</h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase">Kode Temuan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $temuan->kodeTemuan->kode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase">Nilai Temuan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($temuan->nilai_temuan, 0, ',', '.') }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-medium text-gray-400 uppercase">Kondisi</p>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $temuan->kondisi ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-medium text-gray-400 uppercase">Sebab</p>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $temuan->sebab ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-medium text-gray-400 uppercase">Akibat</p>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $temuan->akibat ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($temuan->recommendations->isNotEmpty())
    <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Rekomendasi ({{ $temuan->recommendations->count() }})</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($temuan->recommendations as $rekom)
            <div class="p-4 px-6">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{!! $rekom->uraian_rekom !!}</p>
                <p class="mt-1 text-xs text-gray-400">Status: {{ $rekom->status ?? '-' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($temuan->attachments->isNotEmpty())
    <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Lampiran ({{ $temuan->attachments->count() }})</h2>
        </div>
        <div class="p-6 space-y-2">
            @foreach($temuan->attachments as $file)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $file->file_name }}</span>
                <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                   class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Download</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection