@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $auditProgram->nama_program }}</h2>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600 dark:bg-blue-500/10">{{ $auditProgram->tahun }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Detail capaian dan daftar penugasan audit.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('audit-program.edit', $auditProgram->id) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Edit Program</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Target</p>
            <p class="mt-2 text-2xl font-black text-gray-800 dark:text-white">{{ $auditProgram->target_assignment }} <span class="text-sm font-normal text-gray-400">Unit</span></p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Realisasi</p>
            <p class="mt-2 text-2xl font-black text-blue-600">{{ $auditProgram->realisasi_assignment }} <span class="text-sm font-normal text-gray-400">ST</span></p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">LHP Selesai</p>
            <p class="mt-2 text-2xl font-black text-green-600">{{ $auditProgram->sudah_lhp }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Sisa Target</p>
            <p class="mt-2 text-2xl font-black text-amber-500">{{ $auditProgram->sisa_target }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 dark:text-white/90">Daftar Penugasan (Assignments)</h3>
            <span class="text-xs text-gray-500">{{ $auditProgram->assignments->count() }} Data ditemukan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/50 text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:bg-white/[0.01]">
                    <tr>
                        <th class="px-6 py-4">Nama Tim / Unit</th>
                        <th class="px-6 py-4">Nomor Surat</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($auditProgram->assignments as $assign)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                        <td class="px-6 py-4">
                            <span class="block font-bold text-gray-800 dark:text-white">{{ $assign->nama_tim }}</span>
                            <span class="text-xs text-gray-500">{{ $assign->unitDiperiksa->nama_unit ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $assign->nomor_surat }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] font-bold uppercase dark:bg-white/5">
                                {{ $assign->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-blue-600 font-medium">
                            <a href="#">Detail ST</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Belum ada penugasan untuk program ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection