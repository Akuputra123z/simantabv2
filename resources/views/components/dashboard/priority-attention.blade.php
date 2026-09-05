@props([
    'overdueCount' => 0,
    'temuanBelumTlCount' => 0,
    'unitLowProgressCount' => 0,
    'overdueItems' => [],
    'temuanBelumTlItems' => [],
    'unitLowProgressItems' => []
])

<div x-data="{ openSection: null }" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                ⚠️
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white">PERLU PERHATIAN</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Klik item untuk membuka rincian data prioritas</p>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        {{-- Item 1: Overdue --}}
        <div class="rounded-xl border border-red-100 bg-red-50/60 dark:border-red-900/30 dark:bg-red-900/10 overflow-hidden transition-all">
            <button @click="openSection = openSection === 'overdue' ? null : 'overdue'"
                    type="button"
                    class="w-full flex items-center justify-between p-3.5 text-left hover:bg-red-100/50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="flex h-3 w-3 shrink-0 rounded-full bg-red-500 animate-pulse"></span>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-red-900 dark:text-red-300">
                            {{ $overdueCount }} rekomendasi melewati batas waktu
                        </h4>
                        <p class="text-[11px] text-red-600 dark:text-red-400">Segera verifikasi atau berikan teguran OPD</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                    <span class="text-[11px] font-bold text-red-600 dark:text-red-400 hidden sm:inline" x-text="openSection === 'overdue' ? 'Tutup' : 'Lihat Data'"></span>
                    <svg class="h-4 w-4 text-red-600 dark:text-red-400 transition-transform duration-200" :class="{ 'rotate-180': openSection === 'overdue' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            {{-- Dropdown Sub-Items --}}
            <div x-show="openSection === 'overdue'"
                 x-collapse
                 x-cloak
                 class="border-t border-red-200/60 dark:border-red-900/40 bg-white/90 dark:bg-gray-900/90 p-3 space-y-2">
                @forelse($overdueItems as $item)
                    @php
                        $lhpId = $item->recommendation?->temuan?->lhp_id;
                        $itemUrl = $lhpId ? route('lhps.show', $lhpId) : route('tindak-lanjuts.index');
                    @endphp
                    <a href="{{ $itemUrl }}" class="flex items-center justify-between p-2.5 rounded-lg bg-red-50/50 hover:bg-red-100/70 dark:bg-red-950/30 dark:hover:bg-red-900/40 transition-colors gap-2 border border-red-100/80 dark:border-red-900/30 group">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs font-bold text-red-900 dark:text-red-300 group-hover:text-red-600 transition-colors truncate">
                                    {{ $item->recommendation?->kodeRekomendasi?->kode ?? ('Rekomendasi #' . $item->recommendation_id) }}
                                </span>
                                @if($item->tanggal_jatuh_tempo)
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300">
                                    Jatuh Tempo: {{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                                </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 truncate mt-0.5">
                                {{ $item->recommendation?->temuan?->lhp?->unitDiperiksa?->nama_unit ?? 'Unit tidak diketahui' }}
                            </p>
                        </div>
                        <span class="text-xs font-bold text-red-600 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0">
                            Buka LHP &rarr;
                        </span>
                    </a>
                @empty
                    <p class="text-xs text-red-500/80 italic text-center py-2">Tidak ada rincian rekomendasi terlambat saat ini.</p>
                @endforelse

                <a href="{{ route('tindak-lanjuts.index') }}" class="block text-center text-xs font-bold text-red-700 hover:text-red-800 dark:text-red-400 py-1.5 border-t border-red-100 dark:border-red-900/30">
                    Buka Semua Tindak Lanjut &rarr;
                </a>
            </div>
        </div>

        {{-- Item 2: Temuan Tanpa TL --}}
        <div class="rounded-xl border border-orange-100 bg-orange-50/60 dark:border-orange-900/30 dark:bg-orange-900/10 overflow-hidden transition-all">
            <button @click="openSection = openSection === 'temuan' ? null : 'temuan'"
                    type="button"
                    class="w-full flex items-center justify-between p-3.5 text-left hover:bg-orange-100/50 dark:hover:bg-orange-900/20 transition-colors cursor-pointer">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="flex h-3 w-3 shrink-0 rounded-full bg-orange-500"></span>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-orange-900 dark:text-orange-300">
                            {{ $temuanBelumTlCount }} temuan belum memiliki tindak lanjut
                        </h4>
                        <p class="text-[11px] text-orange-600 dark:text-orange-400">Memerlukan pemutakhiran matriks temuan</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                    <span class="text-[11px] font-bold text-orange-600 dark:text-orange-400 hidden sm:inline" x-text="openSection === 'temuan' ? 'Tutup' : 'Lihat Data'"></span>
                    <svg class="h-4 w-4 text-orange-600 dark:text-orange-400 transition-transform duration-200" :class="{ 'rotate-180': openSection === 'temuan' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            {{-- Dropdown Sub-Items --}}
            <div x-show="openSection === 'temuan'"
                 x-collapse
                 x-cloak
                 class="border-t border-orange-200/60 dark:border-orange-900/40 bg-white/90 dark:bg-gray-900/90 p-3 space-y-2">
                @forelse($temuanBelumTlItems as $t)
                    @php
                        $tUrl = $t->lhp_id ? route('lhps.show', $t->lhp_id) : route('temuan.index');
                    @endphp
                    <a href="{{ $tUrl }}" class="flex items-center justify-between p-2.5 rounded-lg bg-orange-50/50 hover:bg-orange-100/70 dark:bg-orange-950/30 dark:hover:bg-orange-900/40 transition-colors gap-2 border border-orange-100/80 dark:border-orange-900/30 group">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-orange-900 dark:text-orange-300 group-hover:text-orange-600 transition-colors truncate">
                                    {{ $t->kodeTemuan?->kode ?? ('Temuan #' . $t->id) }}
                                </span>
                                <span class="text-[10px] font-semibold text-slate-500 truncate">
                                    &bull; {{ $t->lhp?->unitDiperiksa?->nama_unit ?? 'Unit' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 truncate mt-0.5">
                                {{ Str::limit(strip_tags($t->kondisi ?? 'Belum ada deskripsi'), 55) }}
                            </p>
                        </div>
                        <span class="text-xs font-bold text-orange-600 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0">
                            Buka LHP &rarr;
                        </span>
                    </a>
                @empty
                    <p class="text-xs text-orange-500/80 italic text-center py-2">Semua temuan sudah memiliki tindak lanjut.</p>
                @endforelse

                <a href="{{ route('temuan.index') }}" class="block text-center text-xs font-bold text-orange-700 hover:text-orange-800 dark:text-orange-400 py-1.5 border-t border-orange-100 dark:border-orange-900/30">
                    Buka Daftar Temuan &rarr;
                </a>
            </div>
        </div>

        {{-- Item 3: Unit Progres Rendah --}}
        <div class="rounded-xl border border-amber-100 bg-amber-50/60 dark:border-amber-900/30 dark:bg-amber-900/10 overflow-hidden transition-all">
            <button @click="openSection = openSection === 'unit' ? null : 'unit'"
                    type="button"
                    class="w-full flex items-center justify-between p-3.5 text-left hover:bg-amber-100/50 dark:hover:bg-amber-900/20 transition-colors cursor-pointer">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="flex h-3 w-3 shrink-0 rounded-full bg-amber-500"></span>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-amber-900 dark:text-amber-300">
                            {{ $unitLowProgressCount }} unit memiliki progres &lt; 50%
                        </h4>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400">Monitoring asistensi unit periksa</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hidden sm:inline" x-text="openSection === 'unit' ? 'Tutup' : 'Lihat Data'"></span>
                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400 transition-transform duration-200" :class="{ 'rotate-180': openSection === 'unit' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            {{-- Dropdown Sub-Items --}}
            <div x-show="openSection === 'unit'"
                 x-collapse
                 x-cloak
                 class="border-t border-amber-200/60 dark:border-amber-900/40 bg-white/90 dark:bg-gray-900/90 p-3 space-y-2">
                @forelse($unitLowProgressItems as $u)
                    @php
                        $uLhpId = $u['lhp_id'] ?? null;
                        $uUrl = $uLhpId ? route('lhps.show', $uLhpId) : route('unit-diperiksa.show', $u['id']);
                    @endphp
                    <a href="{{ $uUrl }}" class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50/50 hover:bg-amber-100/70 dark:bg-amber-950/30 dark:hover:bg-amber-900/40 transition-colors gap-2 border border-amber-100/80 dark:border-amber-900/30 group">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-300 group-hover:text-amber-700 transition-colors truncate">
                                    {{ $u['nama'] }}
                                </span>
                                <span class="text-xs font-extrabold text-rose-600 dark:text-rose-400 shrink-0">
                                    {{ $u['progress'] }}% Progres
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 truncate mt-0.5">
                                {{ $u['selesai_rekom'] ?? 0 }} dari {{ $u['total_rekom'] ?? 0 }} Rekomendasi Selesai
                            </p>
                        </div>
                        <span class="text-xs font-bold text-amber-600 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0">
                            Buka LHP &rarr;
                        </span>
                    </a>
                @empty
                    <p class="text-xs text-amber-500/80 italic text-center py-2">Semua unit memiliki progres di atas 50%.</p>
                @endforelse

                <a href="{{ route('unit-diperiksa.index') }}" class="block text-center text-xs font-bold text-amber-700 hover:text-amber-800 dark:text-amber-400 py-1.5 border-t border-amber-100 dark:border-amber-900/30">
                    Buka Unit Periksa &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
