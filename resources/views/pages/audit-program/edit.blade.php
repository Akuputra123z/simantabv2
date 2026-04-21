@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('audit-program.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="mt-3 text-2xl font-bold text-gray-800 dark:text-white/90">Edit Program Audit (PKPT)</h2>
        <p class="text-sm text-gray-500">Perbarui data program pengawasan tahunan.</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
        <form action="{{ route('audit-program.update', $program->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Nama Program --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Program</label>
                <input 
                    type="text" 
                    name="nama_program" 
                    value="{{ old('nama_program', $program->nama_program) }}" 
                    placeholder="Contoh: Audit Operasional BOS"
                    class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white">
                
                @error('nama_program') 
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                @enderror
            </div>

            {{-- Tahun & Target --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                {{-- Tahun --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun PKPT</label>
                    <select 
                        name="tahun" 
                        class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm outline-none focus:border-blue-500 dark:border-gray-700 dark:text-white">

                        @foreach(range(date('Y') + 1, date('Y') - 2) as $y)
                            <option 
                                value="{{ $y }}" 
                                {{ old('tahun', $program->tahun) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Target Assignment --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Target Assignment (Unit)</label>
                    <input 
                        type="number" 
                        name="target_assignment" 
                        value="{{ old('target_assignment', $program->target_assignment) }}"
                        class="w-full rounded-xl border border-gray-200 bg-transparent px-4 py-3 text-sm outline-none focus:border-blue-500 dark:border-gray-700 dark:text-white">

                    @error('target_assignment') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

            </div>

            {{-- Action --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('audit-program.index') }}" class="rounded-xl px-6 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5">
                    Batal
                </a>

                <button 
                    type="submit" 
                    class="rounded-xl bg-yellow-500 px-8 py-2.5 text-sm font-medium text-white hover:bg-yellow-600 transition-all shadow-lg shadow-yellow-500/25">
                    Update Program
                </button>
            </div>
        </form>
    </div>
</div>
@endsection