@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER DASHBOARD OPD --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    Dashboard Tindak Lanjut OPD
                </h1>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Realtime Live Sync
                </span>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">
                Pemantauan status pengunggahan, verifikasi, dan penyelesaian rekomendasi unit Anda secara otomatis & tersinkronisasi
            </p>
        </div>
        <div class="flex items-center gap-2.5 text-xs font-semibold shrink-0">
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-50 px-3 py-1.5 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 font-bold border border-blue-100 dark:border-blue-800/40">
                <span>👋</span>
                <span class="max-w-[140px] truncate sm:max-w-none">{{ auth()->user()->name }}</span>
            </span>
            <button onclick="window.location.reload()" title="Segarkan Data Dashboard" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all cursor-pointer shadow-xs">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- 1. GLOBAL DATE FILTER --}}
    <x-dashboard.date-filter
        :preset="$preset"
        :kategoriProgram="$kategoriProgram"
        :listKategori="$listKategori"
        :startDate="$startDate"
        :endDate="$endDate"
        :startDateFormatted="$startDateFormatted"
        :endDateFormatted="$endDateFormatted"
        :actionUrl="route('opd.dashboard')"
    />

    {{-- 2. INFORMASI KATEGORI PROGRAM AUDIT --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/80">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Informasi Kategori Program Audit</h3>
                <p class="text-xs text-gray-500">Ringkasan LHP, rekomendasi, dan realisasi per kategori audit unit Anda</p>
            </div>
            <a href="{{ route('opd.tindak-lanjut.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 hover:text-blue-800 dark:text-blue-400">
                <span>Lihat Tindak Lanjut OPD</span>
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($kategoriBreakdown as $item)
                @php
                    $isDefault = $item->kategori === 'PKPT';
                @endphp
                <a href="{{ route('opd.tindak-lanjut.index', ['kategori' => $item->kategori]) }}"
                   class="group relative rounded-xl border border-gray-200 bg-slate-50/60 p-4 hover:bg-white hover:border-blue-600 hover:shadow-md transition-all dark:border-gray-800 dark:bg-gray-800/40 dark:hover:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-block rounded-md bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                {{ $item->kategori }}
                            </span>
                            @if($isDefault)
                                <span class="rounded bg-blue-700 px-1.5 py-0.5 text-[10px] font-bold text-white uppercase">
                                    Default Utama
                                </span>
                            @endif
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-500 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                            {{ $item->progres }}% Selesai
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Jumlah LHP</span>
                            <p class="font-mono font-bold text-gray-900 dark:text-white text-sm">{{ $item->total_lhp }} LHP</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Rekomendasi</span>
                            <p class="font-mono font-bold text-gray-900 dark:text-white text-sm">{{ $item->total_rekom }} Items</p>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 border-t border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between text-[11px]">
                        <span class="text-gray-500">Nilai Kerugian:</span>
                        <span class="font-mono font-bold text-gray-900 dark:text-white">Rp {{ number_format($item->total_kerugian, 0, ',', '.') }}</span>
                    </div>

                    <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                        <div class="h-full rounded-full {{ $item->progres >= 100 ? 'bg-emerald-500' : ($item->progres > 0 ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600') }}" style="width: {{ min(100, max(0, $item->progres)) }}%"></div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- 3. PERBANDINGAN KERUGIAN & PENYELAMATAN ASET + PANEL PENJELASAN FINANSIAL --}}
    <div class="grid grid-cols-12 gap-6">

        {{-- Left Side: Bar Chart (~65% / 8 cols) --}}
        <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Perbandingan Kerugian & Penyelamatan Aset Unit</h3>
                    <p class="text-xs text-gray-500">Grafik real-time perbandingan total kerugian finansial vs realisasi setoran penyelamatan aset per bulan</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold shrink-0">
                    <span class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Total Kerugian</span>
                    <span class="flex items-center gap-1.5 text-teal-600 dark:text-teal-400"><span class="h-2.5 w-2.5 rounded-full bg-teal-500"></span> Penyelamatan Aset</span>
                </div>
            </div>
            <div id="chartKerugianPenyelamatanBar" class="min-h-[320px] w-full"></div>
        </div>

        {{-- Right Side: Panel Penjelasan & Analisis Finansial (~35% / 4 cols) --}}
        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Analisis Penyelamatan Aset</h3>
                <p class="text-xs text-gray-500 mb-4">Ringkasan status pengembalian & pemulihan keuangan unit Anda (Realtime Database Sync)</p>

                {{-- Metrics Breakdown Cards --}}
                <div class="space-y-3">
                    {{-- Total Kerugian --}}
                    <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-100 dark:bg-rose-900/20 dark:border-rose-800/40">
                        <div class="flex items-center justify-between text-xs font-semibold text-rose-700 dark:text-rose-300 mb-1">
                            <span>Total Kerugian (Nominal)</span>
                            <span>🔴</span>
                        </div>
                        <h5 class="text-lg font-bold text-rose-700 dark:text-rose-400 truncate" title="Rp {{ number_format($totalKerugian ?? 0, 0, ',', '.') }}">
                            Rp {{ number_format($totalKerugian ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>

                    {{-- Penyelamatan Aset --}}
                    <div class="p-3.5 rounded-xl bg-teal-50/70 border border-teal-100 dark:bg-teal-900/20 dark:border-teal-800/40">
                        <div class="flex items-center justify-between text-xs font-semibold text-teal-700 dark:text-teal-300 mb-1">
                            <span>Penyelamatan Aset (Setoran)</span>
                            <span>🟢</span>
                        </div>
                        <h5 class="text-lg font-bold text-teal-700 dark:text-teal-400 truncate" title="Rp {{ number_format($totalPenyelamatan ?? 0, 0, ',', '.') }}">
                            Rp {{ number_format($totalPenyelamatan ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>

                    {{-- Sisa Kerugian --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 dark:bg-gray-800/60 dark:border-gray-700">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                            <span>Sisa Kerugian Belum Disetor</span>
                            <span>⏳</span>
                        </div>
                        <h5 class="text-lg font-bold text-gray-800 dark:text-gray-200 truncate" title="Rp {{ number_format($sisaKerugian ?? 0, 0, ',', '.') }}">
                            Rp {{ number_format($sisaKerugian ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            {{-- Recovery Rate Progress Bar & Penjelasan --}}
            <div class="pt-4 border-t border-slate-100 dark:border-gray-800 mt-4">
                <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                    <span class="text-gray-700 dark:text-gray-300">Tingkat Pemulihan (Recovery Rate)</span>
                    <span class="text-teal-600 dark:text-teal-400">{{ $recoveryRate ?? 0 }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden mb-3">
                    <div class="h-full rounded-full bg-teal-500 transition-all duration-500" style="width: {{ min(100, $recoveryRate ?? 0) }}%"></div>
                </div>
                <p class="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                    💡 <strong class="text-gray-700 dark:text-gray-300">Catatan:</strong> Penyelamatan aset dihitung secara real-time dari akumulasi setoran lunas & cicilan yang telah diverifikasi oleh tim inspektorat.
                </p>
            </div>
        </div>

    </div>

    {{-- 4. REKAPITULASI + VERIFIKASI PROGRESS --}}
    <div class="grid grid-cols-12 gap-6">

        <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <div class="mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Rekapitulasi Rekomendasi Unit</h3>
                <p class="text-xs text-gray-500">Progres fisik dan finansial penyelesaian rekomendasi</p>
            </div>

            @php $rekomPctCalc = ($rekapitulasi && $rekapitulasi->total_rekom > 0) ? min(100, round(($rekapitulasi->rekom_selesai / $rekapitulasi->total_rekom) * 100)) : 0; @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4 p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                <div>
                    <span class="text-xs font-semibold text-gray-400">Total Rekomendasi</span>
                    <h4 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $rekapitulasi->total_rekom ?? 0 }}</h4>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-400">Total Kerugian / Nilai</span>
                    <h4 class="text-xl font-extrabold text-gray-900 dark:text-white">Rp {{ number_format($rekapitulasi->total_kerugian ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-600 dark:text-gray-300">Rekomendasi Selesai</span>
                        <span class="text-gray-900 dark:text-white font-bold">{{ $rekapitulasi->rekom_selesai ?? 0 }} / {{ $rekapitulasi->total_rekom ?? 0 }} ({{ $rekomPctCalc }}%)</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $rekomPctCalc }}%"></div>
                    </div>
                </div>
                <div>
                    @php $tlPctCalc = ($rekapitulasi && $rekapitulasi->total_kerugian > 0) ? min(100, round(($rekapitulasi->total_tl_selesai / $rekapitulasi->total_kerugian) * 100)) : 0; @endphp
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-600 dark:text-gray-300">Nilai TL Selesai Disetor</span>
                        <span class="text-gray-900 dark:text-white font-bold">Rp {{ number_format($rekapitulasi->total_tl_selesai ?? 0, 0, ',', '.') }} ({{ $tlPctCalc }}%)</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: {{ $tlPctCalc }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Status Pengunggahan OPD</h3>
            <p class="text-xs text-gray-500 mb-4">Distribusi status pengunggahan berkas tindak lanjut</p>

            <div id="chartTlBar" class="min-h-[220px] w-full"></div>
        </div>

    </div>

    {{-- 5. JATUH TEMPO & KEGIATAN TERBARU --}}
    <div class="grid grid-cols-12 gap-6">

        {{-- Jatuh Tempo --}}
        <div class="col-span-12 lg:col-span-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Mendekati Jatuh Tempo (7 Hari)</h3>
                <span class="text-xs text-gray-400">{{ $overdue->count() }} data</span>
            </div>

            <div class="space-y-2">
                @forelse($overdue as $tl)
                <a href="{{ route('opd.tindak-lanjut.show', $tl) }}" class="flex items-center justify-between p-3 rounded-xl border border-amber-100 bg-amber-50/50 hover:bg-amber-100/70 transition-all dark:border-amber-900/30 dark:bg-amber-900/10 min-h-[44px]">
                    <div class="min-w-0 pr-3">
                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">
                            {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }} &middot; {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 45) }}
                        </h4>
                        <p class="text-[11px] text-gray-500">Jatuh Tempo: {{ $tl->tanggal_jatuh_tempo?->format('d M Y') }}</p>
                    </div>
                    <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">
                        {{ $tl->tanggal_jatuh_tempo?->isPast() ? 'Terlambat' : $tl->tanggal_jatuh_tempo?->diffForHumans() }}
                    </span>
                </a>
                @empty
                <p class="text-xs text-gray-400 italic py-4 text-center">Tidak ada rekomendasi mendekati jatuh tempo.</p>
                @endforelse
            </div>
        </div>

        {{-- Kegiatan Terbaru OPD --}}
        <div class="col-span-12 lg:col-span-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Kegiatan Terbaru Unit</h3>
                <span class="text-xs text-gray-400">{{ $recent->count() }} data</span>
            </div>

            <div class="space-y-2">
                @forelse($recent as $tl)
                <a href="{{ route('opd.tindak-lanjut.show', $tl) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors min-h-[44px]">
                    <div class="min-w-0 pr-3">
                        <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 truncate">
                            {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }}
                        </h4>
                        <p class="text-[11px] text-gray-500 truncate">
                            {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 50) }}
                        </p>
                    </div>
                    <span class="text-[10px] font-semibold text-gray-400 whitespace-nowrap">
                        {{ $tl->updated_at?->diffForHumans() }}
                    </span>
                </a>
                @empty
                <p class="text-xs text-gray-400 italic py-4 text-center">Belum ada aktivitas terbaru.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');

    // 1. Bar Chart Perbandingan Kerugian & Penyelamatan Aset Unit (Adaptive RWD)
    if (document.querySelector("#chartKerugianPenyelamatanBar") && typeof ApexCharts !== 'undefined') {
        new ApexCharts(document.querySelector("#chartKerugianPenyelamatanBar"), {
            series: [
                { name: "Total Kerugian", data: @json($chartKerugian ?? []) },
                { name: "Penyelamatan Aset", data: @json($chartPenyelamatan ?? []) }
            ],
            chart: {
                type: "bar",
                height: 320,
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif"
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 260 },
                        plotOptions: { bar: { columnWidth: "65%" } },
                        xaxis: { labels: { style: { fontSize: "10px" } } }
                    }
                }
            ],
            colors: ["#f43f5e", "#0d9488"],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "45%",
                    borderRadius: 5,
                    dataLabels: { position: "top" }
                }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 3, colors: ["transparent"] },
            xaxis: {
                categories: @json($chartMonths ?? []),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        if (val >= 1000000000) return (val / 1000000000).toFixed(1) + " M";
                        if (val >= 1000000) return (val / 1000000).toFixed(0) + " Jt";
                        return val;
                    },
                    style: { colors: isDark ? "#94a3b8" : "#64748b" }
                }
            },
            grid: {
                borderColor: isDark ? "#1e293b" : "#f1f5f9",
                strokeDashArray: 4
            },
            legend: { show: false },
            tooltip: {
                theme: isDark ? "dark" : "light",
                y: {
                    formatter: function (val) {
                        return "Rp " + new Intl.NumberFormat("id-ID").format(val);
                    }
                }
            }
        }).render();
    }

    // 2. Bar Chart Status Pengunggahan OPD
    const belum = {{ $opdStats->belum_upload ?? 0 }};
    const draft = {{ $opdStats->draft ?? 0 }};
    const dikirim = {{ $opdStats->dikirim ?? 0 }};
    const ditolak = {{ $opdStats->ditolak ?? 0 }};

    if (document.getElementById('chartTlBar') && typeof ApexCharts !== 'undefined') {
        new ApexCharts(document.getElementById('chartTlBar'), {
            series: [{ name: 'Jumlah', data: [belum, draft, dikirim, ditolak] }],
            chart: {
                type: 'bar',
                height: 220,
                fontFamily: 'Outfit, sans-serif',
                toolbar: { show: false }
            },
            colors: ['#94a3b8', '#f59e0b', '#2563eb', '#f43f5e'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '50%',
                    distributed: true
                }
            },
            xaxis: {
                categories: ['Belum Upload', 'Draft', 'Terkirim', 'Ditolak'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            yaxis: {
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: isDark ? "#1e293b" : "#f1f5f9", strokeDashArray: 4 },
            legend: { show: false }
        }).render();
    }
});
</script>
@endpush
