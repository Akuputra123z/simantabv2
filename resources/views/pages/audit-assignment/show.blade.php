@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
            Detail Audit Assignment
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ $data->nomor_surat }}
        </p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('audit-assignment.edit', $data->id) }}"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white 
            hover:bg-blue-700 transition-colors shadow-sm hover:shadow">
            Edit
        </a>

        <a href="{{ route('audit-assignment.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 
            hover:bg-gray-50 transition-colors
            dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
            Kembali
        </a>
    </div>

</div>

    {{-- STATUS BADGE --}}
    <div>
       @php
    $badge = match($data->status) {
        'berjalan' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'selesai'  => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        default    => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };
@endphp

<div>
    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $badge }}">
        {{ $data->status }}
    </span>
</div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- KOLOM KIRI: Info Utama --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informasi Audit --}}
           <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">

    <div>
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
            Informasi Audit
        </h3>
        <p class="text-sm text-gray-500">
            Detail utama penugasan audit
        </p>
    </div>

    <dl class="grid grid-cols-1 gap-y-6 sm:grid-cols-2">

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Program Audit</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $data->auditProgram?->nama_program ?? '-' }}
            </dd>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Jenis Pengawasan</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ ucfirst(str_replace('_',' ',$data->jenis_pengawasan)) }}
            </dd>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Nomor Surat</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $data->nomor_surat }}
            </dd>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Tim</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $data->nama_tim }}
            </dd>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Unit Diperiksa</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $data->unitDiperiksa?->nama_unit ?? '-' }}
            </dd>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Kecamatan</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $data->unitDiperiksa?->nama_kecamatan ?? '-' }}
            </dd>
        </div>

    </dl>
</div>

            {{-- Jadwal --}}
           <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">

    <div>
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
            Jadwal
        </h3>
    </div>

    <dl class="grid grid-cols-1 gap-y-6 sm:grid-cols-3">

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tanggal Mulai</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $data->tanggal_mulai?->translatedFormat('d F Y') ?? '-' }}
            </dd>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tanggal Selesai</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $data->tanggal_selesai?->translatedFormat('d F Y') ?? '-' }}
            </dd>
        </div>

        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Durasi</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                @if($data->tanggal_mulai && $data->tanggal_selesai)
                    {{ $data->tanggal_mulai->diffInDays($data->tanggal_selesai) + 1 }} hari
                @else
                    -
                @endif
            </dd>
        </div>

    </dl>
</div>

            {{-- Lampiran --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-6 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Lampiran
                    <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        {{ $data->attachments->count() }}
                    </span>
                </h3>
                @if($data->attachments->count())
                    <ul class="space-y-2">
                        @foreach($data->attachments as $att)
                        <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-900">
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                                </svg>
                                <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                                    class="truncate text-sm text-blue-600 hover:underline dark:text-blue-400">
                                    {{ $att->file_name }}
                                </a>
                            </div>
                            <span class="ml-4 shrink-0 text-xs text-gray-400">
                                {{ number_format($att->file_size / 1024, 1) }} KB
                            </span>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-400">Belum ada lampiran.</p>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: Tim --}}
        <div class="space-y-6">

            {{-- Ketua Tim --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Ketua Tim</h3>
                @if($data->ketuaTim)
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        {{ strtoupper(substr($data->ketuaTim->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $data->ketuaTim->name }}</p>
                        <p class="text-xs text-gray-400">{{ $data->ketuaTim->email }}</p>
                    </div>
                </div>
                @else
                    <p class="text-sm text-gray-400">-</p>
                @endif
            </div>

            {{-- Anggota Tim --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Anggota Tim
                    <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        {{ $data->members->count() }}
                    </span>
                </h3>
                @if($data->members->count())
                    <ul class="space-y-3">
                        @foreach($data->members as $member)
                        <li class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white">{{ $member->name }}</p>
                                @if($member->pivot->jabatan_tim)
                                    <p class="text-xs capitalize text-gray-400">{{ $member->pivot->jabatan_tim }}</p>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-400">Belum ada anggota.</p>
                @endif
            </div>

            {{-- Meta --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Info Lainnya</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Dibuat</dt>
                        <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">
                            {{ $data->created_at->translatedFormat('d F Y, H:i') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Diperbarui</dt>
                        <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">
                            {{ $data->updated_at->translatedFormat('d F Y, H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- DELETE --}}
    <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">

    <form action="{{ route('audit-assignment.destroy', $data->id) }}" method="POST"
        onsubmit="return confirm('Yakin ingin menghapus assignment ini?')">
        @csrf
        @method('DELETE')

        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-5 py-2.5 text-sm font-medium text-red-600 
            hover:bg-red-100 transition-colors
            dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
            Hapus Assignment
        </button>

    </form>

</div>

</div>
@endsection