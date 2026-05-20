@extends('layouts.app')

@section('content')

@if(session('success'))
<div id="alert-success"
     class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 dark:border-green-500/20 dark:bg-green-500/10 transition-all duration-300">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 text-green-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <div class="flex-1">
            <h4 class="text-xs font-bold text-green-700 dark:text-green-300">
                Berhasil
            </h4>

            <p class="mt-0.5 text-xs text-green-600/80 dark:text-green-400">
                {{ session('success') }}
            </p>
        </div>

        <button onclick="dismissAlert()" class="text-green-500 hover:text-green-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- FILTER --}}
    <form method="GET" action="{{ route('audit-program.index') }}">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-4 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Program Kerja Pengawasan Tahunan
                </h3>

                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Monitoring progres audit dan LHP
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">

                {{-- Tahun --}}
                <select name="tahun"
                        onchange="this.form.submit()"
                        class="h-9 rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-700 outline-none transition focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Semua Tahun</option>

                    @foreach(range(date('Y') + 1, 2024) as $y)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>

                {{-- Search --}}
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari program..."
                           class="h-9 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-xs text-gray-700 outline-none transition focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:w-52">

                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                {{-- Button --}}
                <a href="{{ route('audit-program.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah PKPT
                </a>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-100 dark:divide-gray-800">

            <thead class="bg-gray-50/70 dark:bg-gray-900/40">
                <tr>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Program
                    </th>

                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Sub Program
                    </th>

                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Progress
                    </th>

                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Status
                    </th>

                    <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                @forelse($data as $item)

                @php
                    $percent = $item->progress_persen ?? 0;

                    $statusClasses = [
                        'draft' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                        'berjalan' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
                        'selesai' => 'bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                    ];
                @endphp

                <tr class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">

                    {{-- Program --}}
                    <td class="px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $item->nama_program }}
                            </p>

                            <p class="mt-0.5 text-[10px] uppercase tracking-wide text-gray-400">
                                Tahun {{ $item->tahun }}
                            </p>
                        </div>
                    </td>

                    {{-- Sub Program --}}
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">
                            {{ $item->details_count ?? 0 }}
                        </span>
                    </td>

                    {{-- Progress --}}
                    <td class="px-4 py-3 min-w-[220px]">
                        <div class="flex items-center gap-3">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                                     style="width: {{ $percent }}%">
                                </div>
                            </div>

                            <span class="w-9 text-right text-[11px] font-bold text-gray-600 dark:text-gray-300">
                                {{ $percent }}%
                            </span>
                        </div>

                        <p class="mt-1 text-[10px] text-gray-400">
                            {{ $item->sudah_lhp ?? 0 }} selesai / {{ $item->target_assignment ?? 0 }} sub program
                        </p>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3 text-center">
                        @php
                            $displayStatus = $item->status_dinamis;
                        @endphp
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide {{ $statusClasses[$displayStatus] ?? $statusClasses['draft'] }}">
                            {{ $displayStatus }}
                        </span>
                    </td>

                    {{-- Action --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">

                            <a href="{{ route('audit-program.show', $item->id) }}"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:border-blue-200 hover:text-blue-600 dark:border-gray-700">
                               <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                            </a>

                            <a href="{{ route('audit-program.edit', $item->id) }}"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:hover:text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

                            <form action="{{ route('audit-program.destroy', $item->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus data PKPT ini?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:border-red-200 hover:text-red-600 dark:border-gray-700">
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
                    <td colspan="5" class="px-6 py-14 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <svg class="mb-3 h-8 w-8 opacity-30"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 17v-6h13M9 5v6h13M5 5h.01M5 12h.01M5 19h.01"/>
                            </svg>

                            <p class="text-xs font-medium">
                                Data PKPT belum tersedia
                            </p>
                        </div>
                    </td>
                </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($data->hasPages())
    <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
        {{ $data->links() }}
    </div>
    @endif
</div>

<script>
function dismissAlert() {
    const el = document.getElementById('alert-success');

    if (el) {
        el.classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            el.remove();
        }, 300);
    }
}

setTimeout(() => {
    dismissAlert();
}, 4000);
</script>

@endsection