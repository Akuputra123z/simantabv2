@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 pb-12">

    {{-- Notifikasi Error --}}
    @if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-500/20 dark:bg-red-500/10">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Gagal Menyimpan</h3>
                <ul class="mt-1 list-disc pl-5 text-xs text-red-700 dark:text-red-300 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('audit-program.index') }}" class="group inline-flex items-center gap-2 text-sm text-gray-400 hover:text-blue-600 transition-colors">
            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        <h1 class="mt-3 text-2xl font-medium tracking-tight text-gray-900 dark:text-white">Edit Program Kerja</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui data program pengawasan tahunan.</p>
    </div>

    {{-- Card --}}
    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('audit-program.update', $program->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-7 p-6 sm:p-8">

                {{-- Nama Program --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black dark:text-gray-500">Nama Program</label>
                    <input type="text" name="nama_program" value="{{ old('nama_program', $program->nama_program) }}"
                           placeholder="Contoh: Audit Operasional BOS"
                           class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                {{-- Grid 2 Kolom --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    {{-- Tahun --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black dark:text-gray-500">Tahun PKPT</label>
                        <select name="tahun"
                                class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach(range(date('Y') + 1, date('Y') - 2) as $y)
                                <option value="{{ $y }}" {{ old('tahun', $program->tahun) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black dark:text-gray-500">Status Program</label>
                        <select name="status"
                                class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="draft" {{ old('status', $program->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="berjalan" {{ old('status', $program->status) == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="selesai" {{ old('status', $program->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/30 px-6 py-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <a href="{{ route('audit-program.index') }}"
                   class="rounded-xl border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-8 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
