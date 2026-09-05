@props([
    'unitKinerja' => []
])

@php
    $totalItems = count($unitKinerja);
@endphp

<div x-data="{ page: 1, perPage: 5, total: {{ $totalItems }} }" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
  <div class="px-6 py-5 flex items-center justify-between">
    <div>
      <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
        10 Unit Periksa Temuan Terbanyak
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
        Daftar 10 unit periksa (OPD) dengan jumlah temuan hasil audit terbanyak
      </p>
    </div>
    <a href="{{ route('unit-diperiksa.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors">
      Lihat Semua &rarr;
    </a>
  </div>

  <div class="border-t border-gray-100 dark:border-gray-800 p-5 sm:p-6">
    <div class="space-y-3 min-h-[310px]">
      @forelse($unitKinerja as $index => $unit)
      @php
        $lhpId = $unit['lhp_id'] ?? null;
        $unitId = $unit['id'] ?? null;
        if ($lhpId) {
            $lhpShowUrl = route('lhps.show', $lhpId);
        } elseif ($unitId) {
            $lhpShowUrl = route('lhps.index', ['unit_diperiksa_id' => $unitId]);
        } else {
            $lhpShowUrl = route('lhps.index');
        }
      @endphp
      <a href="{{ $lhpShowUrl }}"
         x-show="({{ $index }} >= (page - 1) * perPage) && ({{ $index }} < page * perPage)"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform translate-y-1"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         title="Masuk ke Detail LHP {{ $unit['nama'] }}"
         class="group flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-xl hover:bg-blue-50/70 hover:border-blue-200 dark:hover:bg-blue-900/20 dark:hover:border-blue-800/40 transition-all gap-2 border border-slate-100 dark:border-gray-800/60 shadow-xs cursor-pointer block">
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : ($index === 1 ? 'bg-slate-200 text-slate-700 dark:bg-gray-700 dark:text-slate-200' : ($index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' : 'bg-slate-100 text-slate-500 dark:bg-gray-800 dark:text-slate-400')) }}">
            {{ $index + 1 }}
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5">
              <h4 class="text-xs font-bold text-slate-800 group-hover:text-blue-600 dark:text-slate-200 dark:group-hover:text-blue-400 transition-colors truncate">
                {{ $unit['nama'] }}
              </h4>
              <svg class="h-3.5 w-3.5 text-blue-500 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
              <span class="font-bold text-blue-600 dark:text-blue-400">{{ $unit['total_temuan'] ?? 0 }} Temuan</span>
              <span>&bull;</span>
              <span>{{ $unit['selesai_rekom'] ?? 0 }}/{{ $unit['total_rekom'] ?? 0 }} Rekom Selesai</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 shrink-0 sm:w-44 justify-between sm:justify-end">
          <div class="flex-1 sm:w-28 bg-slate-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 {{ ($unit['progress'] ?? 0) >= 75 ? 'bg-emerald-500' : (($unit['progress'] ?? 0) >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                 style="width: {{ min(100, max(0, $unit['progress'] ?? 0)) }}%"></div>
          </div>
          <span class="text-xs font-extrabold text-right min-w-[36px] {{ ($unit['progress'] ?? 0) >= 75 ? 'text-emerald-600 dark:text-emerald-400' : (($unit['progress'] ?? 0) >= 50 ? 'text-amber-500' : 'text-rose-500') }}">
            {{ $unit['progress'] ?? 0 }}%
          </span>
        </div>
      </a>
      @empty
      <p class="text-xs text-slate-400 italic py-8 text-center">Belum ada data unit periksa.</p>
      @endforelse
    </div>

    @if($totalItems > 0)
    {{-- Pagination Controls --}}
    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
      <div>
        Menampilkan <span class="font-bold text-gray-700 dark:text-gray-200" x-text="total > 0 ? ((page - 1) * perPage) + 1 : 0"></span> - <span class="font-bold text-gray-700 dark:text-gray-200" x-text="Math.min(page * perPage, total)"></span> dari <span class="font-bold text-gray-700 dark:text-gray-200" x-text="total"></span> unit
      </div>

      <div class="flex items-center gap-1">
        {{-- Previous Button --}}
        <button @click="if (page > 1) page--"
                :disabled="page === 1"
                :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200'"
                class="flex h-7 px-2.5 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-semibold transition-colors">
          &larr; Prev
        </button>

        {{-- Page Numbers --}}
        <template x-for="p in Math.ceil(total / perPage)" :key="p">
          <button @click="page = p"
                  :class="page === p ? 'bg-blue-600 text-white font-bold border-blue-600' : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700'"
                  class="flex h-7 w-7 items-center justify-center rounded-lg border text-xs transition-colors"
                  x-text="p">
          </button>
        </template>

        {{-- Next Button --}}
        <button @click="if (page * perPage < total) page++"
                :disabled="page * perPage >= total"
                :class="page * perPage >= total ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200'"
                class="flex h-7 px-2.5 items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-semibold transition-colors">
          Next &rarr;
        </button>
      </div>
    </div>
    @endif
  </div>
</div>
