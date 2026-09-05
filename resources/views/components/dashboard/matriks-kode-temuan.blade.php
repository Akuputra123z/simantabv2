@props([
    'matriks' => [],
    'rekomendasiTerbaru' => []
])

<div x-data="{ activeTab: 'matriks', expandedSub: {}, search: '' }" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
    
    {{-- Card Header & Tabs --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 border-b border-gray-100 dark:border-gray-800 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-bold text-sm">
                    📊
                </span>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Matriks Kode Temuan & Nilai (Rp)
                </h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Rekapitulasi kelompok temuan, jumlah kejadian, persentase, dan nilai rupiah hasil pengawasan
            </p>
        </div>

        <div class="flex items-center gap-2 bg-slate-100 dark:bg-gray-800/80 p-1 rounded-xl shrink-0">
            <button @click="activeTab = 'matriks'"
                    :class="activeTab === 'matriks' ? 'bg-white text-blue-600 dark:bg-gray-700 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all">
                Matriks Kode Temuan
            </button>
            <button @click="activeTab = 'rekomendasi'"
                    :class="activeTab === 'rekomendasi' ? 'bg-white text-blue-600 dark:bg-gray-700 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                    class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all">
                Rekomendasi Terbaru
            </button>
        </div>
    </div>

    {{-- TAB 1: MATRIKS KODE TEMUAN & NILAI --}}
    <div x-show="activeTab === 'matriks'" x-transition:enter="transition ease-out duration-150">
        
        {{-- Total Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 dark:bg-blue-950/20 dark:border-blue-900/30 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Total Kejadian</span>
                    <div class="text-lg font-black text-blue-950 dark:text-blue-200">
                        {{ $matriks['summary']['total_kejadian'] ?? 77 }} Kejadian
                    </div>
                </div>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 font-black text-xs">100%</span>
            </div>
            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100 dark:bg-emerald-950/20 dark:border-emerald-900/30 flex items-center justify-between sm:col-span-2">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total Nilai Temuan (Rp)</span>
                    <div class="text-lg font-black text-emerald-950 dark:text-emerald-200">
                        Rp {{ number_format($matriks['summary']['total_nilai'] ?? 1222989713, 2, ',', '.') }}
                    </div>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                    Kabupaten Rembang
                </span>
            </div>
        </div>

        {{-- Table Search & Filter --}}
        <div class="mb-3 flex items-center justify-between gap-2">
            <div class="relative flex-1 max-w-xs">
                <input type="text" x-model="search" placeholder="Cari desa / unit periksa..."
                       class="w-full h-8 pl-8 pr-3 text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-800 dark:border-gray-700 dark:bg-gray-800 dark:text-slate-200 focus:outline-none focus:border-blue-500">
                <svg class="h-3.5 w-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button @click="expandedSub = Object.keys(expandedSub).length > 0 ? {} : {'1.01.00': true, '1.02.00': true, '1.03.00': true, '1.04.00': true, '2.01.00': true, '2.02.00': true, '2.03.00': true}"
                    class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                <span x-text="Object.keys(expandedSub).length > 0 ? 'Tutup Semua Rincian ↑' : 'Buka Semua Rincian ↓'"></span>
            </button>
        </div>

        {{-- Matriks Table --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-gray-800">
            <table class="w-full text-left text-xs border-collapse min-w-[640px]">
                <thead>
                    <tr class="bg-slate-100 dark:bg-gray-800/90 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-gray-700">
                        <th class="py-2.5 px-3 w-10 text-center border-r border-slate-200/60 dark:border-gray-700">No</th>
                        <th class="py-2.5 px-3 min-w-[260px] border-r border-slate-200/60 dark:border-gray-700">Sub Kelompok Temuan</th>
                        <th class="py-2.5 px-3 w-20 text-center whitespace-nowrap border-r border-slate-200/60 dark:border-gray-700">Kode</th>
                        <th class="py-2.5 px-3 w-28 text-center whitespace-nowrap border-r border-slate-200/60 dark:border-gray-700">Jumlah Kejadian</th>
                        <th class="py-2.5 px-3 w-14 text-center whitespace-nowrap border-r border-slate-200/60 dark:border-gray-700">%</th>
                        <th class="py-2.5 px-3 min-w-[170px] text-right whitespace-nowrap">Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-800/60 text-[11px]">
                    
                    @foreach($matriks['kelompok'] as $kelIndex => $kel)
                    {{-- HEADER KELOMPOK (e.g. 1.00.00) --}}
                    <tr class="bg-slate-200/80 dark:bg-gray-800 font-bold text-slate-900 dark:text-white border-t border-slate-300 dark:border-gray-700">
                        <td class="py-2.5 px-3 text-center font-bold">{{ $kelIndex + 1 }}</td>
                        <td class="py-2.5 px-3 font-extrabold text-xs">
                            {{ $kel['nama'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold font-mono">{{ $kel['kode'] }}</td>
                        <td class="py-2.5 px-3 text-center font-bold font-mono tabular-nums">{{ $kel['jumlah_kejadian'] }}</td>
                        <td class="py-2.5 px-3 text-center font-bold font-mono tabular-nums">{{ $kel['persen'] }}</td>
                        <td class="py-2.5 px-3 text-right font-extrabold whitespace-nowrap font-mono tabular-nums tracking-tight">
                            {{ $kel['nilai'] > 0 ? number_format($kel['nilai'], 2, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- SUB KELOMPOK ITEMS --}}
                    @foreach($kel['sub'] as $subIndex => $sub)
                    <tr class="bg-slate-50 dark:bg-gray-900/40 hover:bg-slate-100/80 dark:hover:bg-gray-800/50 transition-colors font-semibold">
                        <td class="py-2 px-3 text-center text-slate-500 font-mono">{{ $subIndex + 1 }}</td>
                        <td class="py-2 px-3 text-slate-800 dark:text-slate-200">
                            <div class="flex items-center justify-between gap-2">
                                <span>{{ $sub['nama'] }}</span>
                                @if(!empty($sub['units']))
                                <button @click="expandedSub['{{ $sub['kode'] }}'] = !expandedSub['{{ $sub['kode'] }}']"
                                        type="button"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 shrink-0 font-sans">
                                    <span x-text="expandedSub['{{ $sub['kode'] }}'] ? 'Sembunyikan Unit' : 'Lihat ' + {{ count($sub['units']) }} + ' Unit'"></span>
                                    <svg class="h-3 w-3 transition-transform" :class="{ 'rotate-180': expandedSub['{{ $sub['kode'] }}'] }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                        <td class="py-2 px-3 text-center font-bold text-blue-600 dark:text-blue-400 font-mono">{{ $sub['kode'] }}</td>
                        <td class="py-2 px-3 text-center font-bold text-slate-800 dark:text-slate-200 font-mono tabular-nums">{{ $sub['jumlah_kejadian'] }}</td>
                        <td class="py-2 px-3 text-center text-slate-600 dark:text-slate-400 font-mono tabular-nums">{{ $sub['persen'] }}</td>
                        <td class="py-2 px-3 text-right font-bold text-slate-900 dark:text-white whitespace-nowrap font-mono tabular-nums tracking-tight">
                            {{ $sub['nilai'] > 0 ? number_format($sub['nilai'], 2, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- UNIT DETAILS FOR SUB KELOMPOK --}}
                    @if(!empty($sub['units']))
                    @foreach($sub['units'] as $unit)
                    <tr x-show="(expandedSub['{{ $sub['kode'] }}'] || search) && (!search || '{{ strtolower($unit['nama']) }}'.includes(search.toLowerCase()))"
                        x-transition
                        class="bg-white dark:bg-gray-900/90 hover:bg-amber-50/50 dark:hover:bg-gray-800/60 transition-colors border-l-4 border-blue-500">
                        <td class="py-1.5 px-3 text-center text-slate-400 text-[10px]">↳</td>
                        <td class="py-1.5 px-3 pl-6 text-slate-700 dark:text-slate-300 text-[11px]">
                            <a href="{{ route('temuan.index', ['search' => $unit['nama']]) }}"
                               title="Filter data temuan untuk {{ $unit['nama'] }}"
                               class="group inline-flex items-center gap-1.5 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span class="font-bold text-slate-900 dark:text-slate-100 group-hover:underline">{{ $unit['nama'] }}</span>
                                <svg class="h-3 w-3 text-slate-400 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                        <td class="py-1.5 px-3 text-center text-slate-400 text-[10px] font-mono">{{ $sub['kode'] }}</td>
                        <td class="py-1.5 px-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-gray-800 font-bold text-[10px] font-mono tabular-nums">{{ $unit['jumlah'] }}</span>
                        </td>
                        <td class="py-1.5 px-3 text-center text-slate-400 text-[10px]">-</td>
                        <td class="py-1.5 px-3 text-right font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap font-mono tabular-nums tracking-tight">
                            {{ $unit['nilai'] > 0 ? number_format($unit['nilai'], 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    @endforeach

                    {{-- SUBTOTAL KELOMPOK --}}
                    <tr class="bg-slate-100/90 dark:bg-gray-800/70 font-bold border-t border-b border-slate-300 dark:border-gray-700 text-slate-900 dark:text-white">
                        <td class="py-2.5 px-3 text-center" colspan="3">Subtotal {{ $kel['nama'] }}</td>
                        <td class="py-2.5 px-3 text-center font-extrabold text-blue-600 dark:text-blue-400 font-mono tabular-nums">{{ $kel['jumlah_kejadian'] }}</td>
                        <td class="py-2.5 px-3 text-center font-bold font-mono tabular-nums">{{ $kel['persen'] }}</td>
                        <td class="py-2.5 px-3 text-right font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-mono tabular-nums tracking-tight">
                            {{ $kel['nilai'] > 0 ? number_format($kel['nilai'], 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @endforeach

                    {{-- TOTAL AKHIR KESELURUHAN --}}
                    <tr class="bg-blue-900 text-white font-black text-xs uppercase tracking-wider">
                        <td class="py-3 px-3 text-center" colspan="3">JUMLAH KEJADIAN KESELURUHAN</td>
                        <td class="py-3 px-3 text-center font-black text-amber-300 text-sm font-mono tabular-nums">
                            {{ $matriks['summary']['total_kejadian'] ?? 77 }}
                        </td>
                        <td class="py-3 px-3 text-center font-black text-white font-mono">100%</td>
                        <td class="py-3 px-3 text-right font-black text-amber-300 text-sm whitespace-nowrap font-mono tabular-nums tracking-tight">
                            Rp {{ number_format($matriks['summary']['total_nilai'] ?? 1222989713, 2, ',', '.') }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB 2: REKOMENDASI TERBARU --}}
    <div x-show="activeTab === 'rekomendasi'" x-transition:enter="transition ease-out duration-150" x-cloak>
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-500">Daftar rekomendasi audit yang baru diterbitkan</span>
            <a href="{{ route('recommendations.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-gray-800">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 dark:bg-gray-800 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3 px-4">Kode / ID</th>
                        <th class="py-3 px-4">Unit Periksa (OPD)</th>
                        <th class="py-3 px-4">Uraian Rekomendasi</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    @forelse($rekomendasiTerbaru as $rekom)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                            {{ $rekom->kodeRekomendasi?->kode ?? ('R-' . $rekom->id) }}
                        </td>
                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-semibold max-w-[200px] truncate">
                            @if($rekom->temuan?->lhp?->unitDiperiksa)
                                <a href="{{ route('lhps.show', $rekom->temuan->lhp->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors">
                                    {{ $rekom->temuan->lhp->unitDiperiksa->nama_unit }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400 max-w-[300px] truncate">
                            {{ Str::limit(strip_tags($rekom->uraian_rekom ?? '-'), 75) }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                            {{ $rekom->created_at?->translatedFormat('d/m/Y') ?? '-' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($rekom->status === 'selesai')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Selesai
                                </span>
                            @elseif($rekom->status === 'proses')
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                                    Proses
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-bold text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                                    Belum
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('tindak-lanjuts.index') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-slate-200 dark:hover:bg-gray-700 transition-colors shadow-xs">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-xs text-slate-400 italic">Belum ada rekomendasi terbaru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
