@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $totalRekom = ($rekomBelum + $rekomProses + $rekomSelesai) ?: 1;
@endphp

<main class="min-h-screen bg-gray-50 dark:bg-[#1a222c]">
    <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Analytics Dashboard</h2>
            <nav>
                <ol class="flex items-center gap-2 text-sm font-medium text-slate-500">
                    <li>Dashboard /</li>
                    <li class="text-blue-600 font-semibold">{{ now()->translatedFormat('d M, Y') }}</li>
                </ol>
            </nav>
        </div>

        {{-- ================= STAT CARDS ================= --}}
        {{-- Selalu 2 kolom di sm, selalu 5 kolom di xl — tidak ada 3 kolom --}}
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-5 md:gap-5">

            {{-- Card 1: Total LHP --}}
            <div class="col-span-1 flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                        <svg class="text-blue-600" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                        {{ $lhpFinal }} Final
                    </span>
                </div>
                <div class="mt-auto pt-4">
                    <h4 class="text-3xl font-bold text-slate-800 dark:text-white">{{ $totalLhp }}</h4>
                    <p class="mt-0.5 text-sm font-medium text-slate-500">Total LHP</p>
                </div>
            </div>

            {{-- Card 2: Total Temuan --}}
            <div class="col-span-1 flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900/20">
                        <svg class="text-orange-500" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <span class="rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-500 dark:bg-orange-900/20 dark:text-orange-400">
                        {{ $temuanProses }} Proses
                    </span>
                </div>
                <div class="mt-auto pt-4">
                    <h4 class="text-3xl font-bold text-slate-800 dark:text-white">{{ $totalTemuan }}</h4>
                    <p class="mt-0.5 text-sm font-medium text-slate-500">Total Temuan</p>
                </div>
            </div>

            {{-- Card 3: Selesai TL --}}
            <div class="col-span-1 flex flex-col rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-800/50 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/20">
                        <svg class="text-emerald-600" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-emerald-500">
                        {{ round($rekomSelesai / $totalRekom * 100) }}%
                    </span>
                </div>
                <div class="mt-auto pt-4">
                    <h4 class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $rekomSelesai }}</h4>
                    <p class="mt-0.5 text-sm font-medium text-slate-500">Selesai TL</p>
                </div>
                <div class="mt-3 h-1 w-full rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="h-full rounded-full bg-emerald-500"
                         style="width: {{ min(100, round($rekomSelesai / $totalRekom * 100)) }}%"></div>
                </div>
            </div>

            {{-- Card 4: Total Kerugian --}}
            <div class="col-span-1 flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                        <svg class="text-red-500" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Kerugian</span>
                </div>
                <div class="mt-auto pt-4">
                    <h4 class="text-lg font-bold text-red-600 dark:text-red-400 break-all leading-tight">
                        Rp {{ number_format($totalKerugian, 0, ',', '.') }}
                    </h4>
                    <p class="mt-0.5 text-sm font-medium text-slate-500">Total Kerugian</p>
                </div>
            </div>

            {{-- Card 5: Overall Progress --}}
            <div class="col-span-2 xl:col-span-1 flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                        <svg class="text-blue-600" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-blue-500">Rate</span>
                </div>
                <div class="mt-auto pt-4">
                    <h4 class="text-3xl font-bold text-slate-800 dark:text-white">
                        {{ number_format($avgProgress, 0) }}<span class="text-lg font-medium text-slate-400">%</span>
                    </h4>
                    <p class="mt-0.5 text-sm font-medium text-slate-500">Overall Progress</p>
                </div>
                <div class="mt-3 h-1 w-full rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="h-full rounded-full bg-blue-500"
                         style="width: {{ min(100, $avgProgress) }}%"></div>
                </div>
            </div>

        </div>

        {{-- ================= CHARTS GRID ================= --}}
        <div class="mt-4 grid grid-cols-12 gap-4 md:mt-6 md:gap-6 2xl:mt-7.5 2xl:gap-7.5">

            {{-- Bar Chart --}}
            <div class="col-span-12 rounded-2xl border border-gray-200 bg-white px-5 pt-7.5 pb-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:px-7.5">
                <h4 class="mb-4 text-xl font-bold text-slate-800 dark:text-white">LHP per Bulan</h4>
                <div class="overflow-x-auto">
                    <div id="chartBarLHP" class="min-w-[600px]"></div>
                </div>
            </div>

            {{-- Line Chart --}}
            <div class="col-span-12 xl:col-span-8 rounded-2xl border border-gray-200 bg-white px-5 pt-7.5 pb-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:px-7.5">
                <h4 class="mb-4 text-xl font-bold text-slate-800 dark:text-white">Tren Penyelesaian (%)</h4>
                <div id="chartLineProgress"></div>
            </div>

            {{-- Donut Chart --}}
            <div class="col-span-12 xl:col-span-4 rounded-2xl border border-gray-200 bg-white px-5 pt-7.5 pb-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:px-7.5">
                <h4 class="mb-4 text-xl font-bold text-slate-800 dark:text-white">Status Rekomendasi</h4>
                <div id="chartDonutRekom" class="flex justify-center"></div>
                <div class="flex flex-col gap-3 pt-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="block h-3 w-3 rounded-full bg-[#ef4444]"></span>
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Belum TL</span>
                        </div>
                        <span class="text-sm font-bold dark:text-white">{{ $rekomBelum }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="block h-3 w-3 rounded-full bg-[#f59e0b]"></span>
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Proses</span>
                        </div>
                        <span class="text-sm font-bold dark:text-white">{{ $rekomProses }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="block h-3 w-3 rounded-full bg-[#10b981]"></span>
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Selesai</span>
                        </div>
                        <span class="text-sm font-bold dark:text-white">{{ $rekomSelesai }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');

    new ApexCharts(document.querySelector("#chartBarLHP"), {
        series: [{ name: "Jumlah LHP", data: @json($bulanJumlah ?? []) }],
        chart: { type: "bar", height: 335, toolbar: { show: false } },
        colors: ["#3b82f6"],
        plotOptions: { bar: { borderRadius: 4, columnWidth: "35%" } },
        dataLabels: { enabled: false },
        xaxis: { categories: @json($bulanLabels ?? []), axisBorder: { show: false }, axisTicks: { show: false } },
        grid: { strokeDashArray: 5, borderColor: isDark ? "#333d4a" : "#e2e8f0" }
    }).render();

    new ApexCharts(document.querySelector("#chartDonutRekom"), {
        series: [{{ $rekomBelum ?? 0 }}, {{ $rekomProses ?? 0 }}, {{ $rekomSelesai ?? 0 }}],
        chart: { type: "donut", width: 320 },
        colors: ["#ef4444", "#f59e0b", "#10b981"],
        labels: ["Belum TL", "Proses", "Selesai"],
        legend: { show: false },
        plotOptions: { pie: { donut: { size: "65%" } } },
        dataLabels: { enabled: false },
        stroke: { colors: [isDark ? "transparent" : "#fff"] }
    }).render();

    new ApexCharts(document.querySelector("#chartLineProgress"), {
        series: [{ name: "Progress", data: @json($bulanPersen ?? []) }],
        chart: { type: "area", height: 350, width: "100%", toolbar: { show: false } },
        colors: ["#3b82f6"],
        dataLabels: { enabled: false },
        stroke: { curve: "smooth", width: 3 },
        fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [20, 100] } },
        xaxis: { categories: @json($bulanLabels ?? ["Jan", "Feb", "Mar"]), tooltip: { enabled: false } },
        grid: { strokeDashArray: 5, borderColor: isDark ? "#333d4a" : "#e2e8f0" },
        yaxis: { labels: { formatter: (v) => v + "%" } }
    }).render();
});
</script>
@endsection