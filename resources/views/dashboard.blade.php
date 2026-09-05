@extends('layouts.app')

@section('content')
<main class="min-h-screen bg-slate-50/50 dark:bg-[#121824]">
    <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10 space-y-6">

        {{-- HEADER DASHBOARD --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Dashboard E-AUDIT SIMANTAB
                </h1>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                    Ringkasan real-time aktivitas audit, temuan, rekomendasi, dan pemantauan tindak lanjut
                </p>
            </div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-bold">
                    👋 Hello, {{ $user->name ?? 'User' }}
                </span>
            </nav>
        </div>

        {{-- 1. GLOBAL DATE FILTER & PROGRAM CATEGORY FILTER --}}
        <x-dashboard.date-filter
            :preset="$preset"
            :startDate="$startDate"
            :endDate="$endDate"
            :startDateFormatted="$startDateFormatted"
            :endDateFormatted="$endDateFormatted"
            :kategoriProgram="$kategoriProgram"
            :listKategori="$listKategori"
            :actionUrl="route('dashboard')"
        />

        {{-- 2. KPI CARDS (4 CARDS UTAMA) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-5">

            {{-- Card 1: Total Audit (Blue 🔵) --}}
            <div class="flex flex-col justify-between rounded-2xl border border-blue-100 bg-white p-5 shadow-sm hover:shadow-md transition-all dark:border-gray-800 dark:bg-gray-900/60">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        @if($lhpTrendPct >= 0)
                            &uarr; {{ $lhpTrendPct }}%
                        @else
                            &darr; {{ abs($lhpTrendPct) }}%
                        @endif
                    </span>
                </div>
                <div class="mt-4">
                    <h4 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $totalLhp }}</h4>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Audit (LHP)</p>
                        <span class="text-[10px] text-slate-400">vs periode sebelumnya</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Total Temuan (Orange 🟠) --}}
            <div class="flex flex-col justify-between rounded-2xl border border-orange-100 bg-white p-5 shadow-sm hover:shadow-md transition-all dark:border-gray-800 dark:bg-gray-900/60">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-900/30 dark:text-orange-400">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-1 text-[11px] font-bold text-orange-600 dark:bg-orange-900/40 dark:text-orange-300">
                        @if($temuanTrendPct >= 0)
                            &uarr; {{ $temuanTrendPct }}%
                        @else
                            &darr; {{ abs($temuanTrendPct) }}%
                        @endif
                    </span>
                </div>
                <div class="mt-4">
                    <h4 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $totalTemuan }}</h4>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Temuan</p>
                        <span class="text-[10px] text-slate-400">vs periode sebelumnya</span>
                    </div>
                </div>
            </div>

            {{-- Card 3: Rekomendasi (Amber 🟡) --}}
            <div class="flex flex-col justify-between rounded-2xl border border-amber-100 bg-white p-5 shadow-sm hover:shadow-md transition-all dark:border-gray-800 dark:bg-gray-900/60">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        {{ $rekomPct }}% ditindaklanjuti
                    </span>
                </div>
                <div class="mt-4">
                    <h4 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $totalRekom }}</h4>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Total Rekomendasi</p>
                </div>
            </div>

            {{-- Card 4: Tindak Lanjut (Green 🟢) --}}
            <div class="flex flex-col justify-between rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm hover:shadow-md transition-all dark:border-gray-800 dark:bg-gray-900/60">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        {{ $tlSelesai }} / {{ $totalRekom }} Selesai
                    </span>
                </div>
                <div class="mt-4">
                    <h4 class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $tlPct }}%</h4>
                    <div class="mt-1 flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Status Tindak Lanjut</p>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">SELESAI</span>
                    </div>
                    <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ min(100, $tlPct) }}%"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- 3. CHARTS ROW (TREND 65% + DONUT STATUS 35%) --}}
        <div class="grid grid-cols-12 gap-6">

            {{-- Trend Audit & Temuan (Line / Area Chart ~65%) --}}
            <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Trend Audit & Temuan</h3>
                        <p class="text-xs text-slate-500">Grafik perkembangan volume audit, temuan, dan rekomendasi per bulan</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold">
                        <span class="flex items-center gap-1 text-blue-600"><span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span> Audit</span>
                        <span class="flex items-center gap-1 text-orange-500"><span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span> Temuan</span>
                        <span class="flex items-center gap-1 text-emerald-500"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Rekomendasi</span>
                    </div>
                </div>
                <div id="chartTrendAudit" class="min-h-[320px]"></div>
            </div>

            {{-- Status Tindak Lanjut (Donut Chart ~35%) --}}
            <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Status Tindak Lanjut</h3>
                    <p class="text-xs text-slate-500 mb-4">Proporsi status penyelesaian seluruh rekomendasi</p>
                    <div id="chartDonutStatus" class="flex justify-center my-2"></div>
                </div>
                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-gray-800">
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Selesai
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $rekomSelesai }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Dalam Proses
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $rekomProses }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Belum Ditindaklanjuti
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $rekomBelum }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- 4. BAR CHART PERBANDINGAN KERUGIAN & PENYELAMATAN ASET + PANEL PENJELASAN --}}
        <div class="grid grid-cols-12 gap-6">

            {{-- Left Side: Bar Chart (~65% / 8 cols) --}}
            <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md flex flex-col justify-between">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Perbandingan Kerugian & Penyelamatan Aset</h3>
                        <p class="text-xs text-slate-500">Grafik perbandingan total kerugian finansial vs realisasi penyelamatan aset per bulan</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold shrink-0">
                        <span class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Total Kerugian</span>
                        <span class="flex items-center gap-1.5 text-teal-600 dark:text-teal-400"><span class="h-2.5 w-2.5 rounded-full bg-teal-500"></span> Penyelamatan Aset</span>
                    </div>
                </div>
                <div id="chartKerugianPenyelamatanBar" class="min-h-[320px]"></div>
            </div>

            {{-- Right Side: Panel Penjelasan & Analisis Finansial (~35% / 4 cols) --}}
            <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Analisis Penyelamatan Aset</h3>
                    <p class="text-xs text-slate-500 mb-4">Ringkasan status pengembalian & pemulihan keuangan daerah</p>

                    @php
                        $sisaKerugian = max(0, ($totalKerugian ?? 0) - ($totalPenyelamatan ?? 0));
                        $recoveryRate = ($totalKerugian ?? 0) > 0 ? round((($totalPenyelamatan ?? 0) / $totalKerugian) * 100, 1) : 0;
                    @endphp

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
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                <span>Sisa Kerugian Belum Disetor</span>
                                <span>⏳</span>
                            </div>
                            <h5 class="text-lg font-bold text-slate-800 dark:text-slate-200 truncate" title="Rp {{ number_format($sisaKerugian, 0, ',', '.') }}">
                                Rp {{ number_format($sisaKerugian, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>

                {{-- Recovery Rate Progress Bar & Penjelasan --}}
                <div class="pt-4 border-t border-slate-100 dark:border-gray-800 mt-4">
                    <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                        <span class="text-slate-700 dark:text-slate-300">Tingkat Pemulihan (Recovery Rate)</span>
                        <span class="text-teal-600 dark:text-teal-400">{{ $recoveryRate }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden mb-3">
                        <div class="h-full rounded-full bg-teal-500 transition-all duration-500" style="width: {{ min(100, $recoveryRate) }}%"></div>
                    </div>
                    <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                        💡 <strong class="text-slate-700 dark:text-slate-300">Catatan:</strong> Penyelamatan aset dihitung dari akumulasi setoran lunas & cicilan yang telah diverifikasi oleh tim inspektorat.
                    </p>
                </div>
            </div>

        </div>

        {{-- 5. SPLIT ROW: PRIORITAS PERHATIAN + 10 UNIT PERIKSA TEMUAN TERBANYAK --}}
        <div class="grid grid-cols-12 gap-6">

            {{-- ⚠️ Prioritas Perhatian --}}
            <div class="col-span-12 lg:col-span-6">
                <x-dashboard.priority-attention
                    :overdueCount="$overdueCount ?? 0"
                    :temuanBelumTlCount="$temuanBelumTlCount ?? 0"
                    :unitLowProgressCount="$unitLowProgressCount ?? 0"
                    :overdueItems="$overdueItems ?? []"
                    :temuanBelumTlItems="$temuanBelumTlItems ?? []"
                    :unitLowProgressItems="$unitLowProgressItems ?? []"
                />
            </div>

            {{-- 📊 10 Unit Periksa Temuan Terbanyak --}}
            <div class="col-span-12 lg:col-span-6">
                <x-dashboard.unit-progress :unitKinerja="$unitKinerja" />
            </div>

        </div>

        {{-- 6. MATRIKS KODE TEMUAN & NILAI / REKOMENDASI TERBARU --}}
        <x-dashboard.matriks-kode-temuan
            :matriks="$matriksKodeTemuan ?? []"
            :rekomendasiTerbaru="$rekomendasiTerbaru ?? []"
        />

    </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');

    // 1. Line/Area Chart Trend Audit & Temuan
    if (document.querySelector("#chartTrendAudit") && typeof ApexCharts !== 'undefined') {
        new ApexCharts(document.querySelector("#chartTrendAudit"), {
            series: [
                { name: "Audit (LHP)", data: @json($chartAudit ?? []) },
                { name: "Temuan", data: @json($chartTemuan ?? []) },
                { name: "Rekomendasi", data: @json($chartRekom ?? []) }
            ],
            chart: {
                type: "area",
                height: 320,
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif"
            },
            colors: ["#2563eb", "#f97316", "#10b981"],
            fill: {
                type: "gradient",
                gradient: { opacityFrom: 0.25, opacityTo: 0.05 }
            },
            stroke: { curve: "smooth", width: 2.5 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($chartMonths ?? []),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            yaxis: {
                labels: { style: { colors: isDark ? "#94a3b8" : "#64748b" } }
            },
            grid: {
                borderColor: isDark ? "#1e293b" : "#f1f5f9",
                strokeDashArray: 4
            },
            legend: { show: false }
        }).render();
    }

    // 2. Donut Chart Status Tindak Lanjut
    if (document.querySelector("#chartDonutStatus") && typeof ApexCharts !== 'undefined') {
        new ApexCharts(document.querySelector("#chartDonutStatus"), {
            series: [{{ $rekomSelesai }}, {{ $rekomProses }}, {{ $rekomBelum }}],
            chart: {
                type: "donut",
                height: 220,
                fontFamily: "Outfit, sans-serif"
            },
            colors: ["#10b981", "#f59e0b", "#f43f5e"],
            labels: ["Selesai", "Proses", "Belum Ditindaklanjuti"],
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: "70%",
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: "SELESAI",
                                formatter: () => "{{ $tlPct }}%"
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            stroke: { colors: [isDark ? "#121824" : "#ffffff"] }
        }).render();
    }

    // 3. Bar Chart Perbandingan Kerugian & Penyelamatan Aset (TailAdmin Bar Chart 3)
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
});
</script>
@endpush
@endsection