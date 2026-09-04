@props([
    'overdueCount' => 0,
    'temuanBelumTlCount' => 0,
    'unitLowProgressCount' => 0
])

<div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
    <div class="flex items-center gap-2 mb-4">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
            ⚠️
        </span>
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">PERLU PERHATIAN</h3>
            <p class="text-xs text-slate-500">Item prioritas pengawasan yang butuh penanganan</p>
        </div>
    </div>

    <div class="space-y-3">
        {{-- Item 1: Overdue --}}
        <a href="{{ route('tindak-lanjuts.index') }}" class="group flex items-center justify-between rounded-xl border border-red-100 bg-red-50/60 p-3 hover:bg-red-100/70 transition-all dark:border-red-900/30 dark:bg-red-900/10 dark:hover:bg-red-900/20">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 rounded-full bg-red-500 animate-pulse"></span>
                <div>
                    <h4 class="text-xs font-bold text-red-900 dark:text-red-300">
                        {{ $overdueCount }} rekomendasi melewati batas waktu
                    </h4>
                    <p class="text-[11px] text-red-600 dark:text-red-400">Segera verifikasi atau berikan teguran OPD</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-red-600 group-hover:translate-x-0.5 transition-transform dark:text-red-400">&rarr;</span>
        </a>

        {{-- Item 2: Temuan Tanpa TL --}}
        <a href="{{ route('lhps.index') }}" class="group flex items-center justify-between rounded-xl border border-orange-100 bg-orange-50/60 p-3 hover:bg-orange-100/70 transition-all dark:border-orange-900/30 dark:bg-orange-900/10 dark:hover:bg-orange-900/20">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 rounded-full bg-orange-500"></span>
                <div>
                    <h4 class="text-xs font-bold text-orange-900 dark:text-orange-300">
                        {{ $temuanBelumTlCount }} temuan belum memiliki tindak lanjut
                    </h4>
                    <p class="text-[11px] text-orange-600 dark:text-orange-400">Memerlukan pemutakhiran matriks temuan</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-orange-600 group-hover:translate-x-0.5 transition-transform dark:text-orange-400">&rarr;</span>
        </a>

        {{-- Item 3: Unit Progres Rendah --}}
        <a href="{{ route('unit-diperiksa.index') }}" class="group flex items-center justify-between rounded-xl border border-amber-100 bg-amber-50/60 p-3 hover:bg-amber-100/70 transition-all dark:border-amber-900/30 dark:bg-amber-900/10 dark:hover:bg-amber-900/20">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 rounded-full bg-amber-500"></span>
                <div>
                    <h4 class="text-xs font-bold text-amber-900 dark:text-amber-300">
                        {{ $unitLowProgressCount }} unit memiliki progres &lt; 50%
                    </h4>
                    <p class="text-[11px] text-amber-600 dark:text-amber-400">Monitoring asistensi unit periksa</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-amber-600 group-hover:translate-x-0.5 transition-transform dark:text-amber-400">&rarr;</span>
        </a>
    </div>
</div>
