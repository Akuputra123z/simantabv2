@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
@endphp

<main class="min-h-screen bg-gray-50 dark:bg-[#1a222c]">
    <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
        
        {{-- ================= BREADCRUMB / HEADER ================= --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">
                Analytics Dashboard
            </h2>

            <nav>
                <ol class="flex items-center gap-2 text-sm font-medium text-slate-500">
                    <li>Dashboard /</li>
                    <li class="text-blue-600 font-semibold">{{ now()->translatedFormat('d M, Y') }}</li>
                </ol>
            </nav>
        </div>

        {{-- ================= TOP STAT CARDS ================= --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            {{-- Card 1: Total LHP --}}
            <div class="rounded-2xl border border-gray-200 bg-white py-6 px-7.5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-gray-100 dark:bg-white/[0.05]">
                    <svg class="text-blue-600 dark:text-white" width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $totalLhp }}</h4>
                        <span class="text-sm font-medium text-slate-500">Total LHP</span>
                    </div>
                    <span class="flex items-center gap-1 text-sm font-medium text-emerald-500">{{ $lhpFinal }} Final</span>
                </div>
            </div>

            {{-- Card 2: Total Temuan --}}
            <div class="rounded-2xl border border-gray-200 bg-white py-6 px-7.5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-gray-100 dark:bg-white/[0.05]">
                    <svg class="text-blue-600 dark:text-white" width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $totalTemuan }}</h4>
                        <span class="text-sm font-medium text-slate-500">Total Temuan</span>
                    </div>
                    <span class="flex items-center gap-1 text-sm font-medium text-red-500">{{ $temuanProses }} Proses</span>
                </div>
            </div>

            {{-- Card 3: Kerugian --}}
            <div class="rounded-2xl border border-gray-200 bg-white py-6 px-7.5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mt-2">
                    <span class="text-sm font-medium text-slate-500">Total Kerugian</span>
                    <h4 class="text-xl font-bold text-red-600 mt-1">Rp {{ number_format($totalKerugian, 0, ',', '.') }}</h4>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-slate-400">Data akumulasi nilai temuan</div>
            </div>

            {{-- Card 4: Progress --}}
            <div class="rounded-2xl border border-gray-200 bg-white py-6 px-7.5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Progress TL</span>
                        <span class="text-sm font-bold text-blue-600">{{ number_format($avgProgress, 1) }}%</span>
                    </div>
                    <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-white/10">
                        <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: {{ min(100, $avgProgress) }}%"></div>
                    </div>
                </div>
                <p class="mt-4 text-xs text-slate-400">Rata-rata penyelesaian</p>
            </div>
        </div>

        {{-- ================= CHARTS GRID ================= --}}
        <div class="mt-4 grid grid-cols-12 gap-4 md:mt-6 md:gap-6 2xl:mt-7.5 2xl:gap-7.5">
            
            {{-- Bar Chart (TailAdmin Style) --}}
            <div class="col-span-12 rounded-2xl border border-gray-200 bg-white px-5 pt-7.5 pb-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:px-7.5">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3 sm:flex-nowrap">
                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">LHP per Bulan</h4>
                </div>
                <div class="overflow-x-auto">
    <div id="chartBarLHP" class="min-w-[600px]"></div>
</div>
            </div>

            {{-- Line Chart --}}
            <div class="col-span-12 xl:col-span-8 rounded-2xl border border-gray-200 bg-white px-5 pt-7.5 pb-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:px-7.5">
                <h4 class="mb-4 text-xl font-bold text-slate-800 dark:text-white">Tren Penyelesaian (%)</h4>
                <div id="chartLineProgress"></div>
            </div>

            {{-- Doughnut Chart --}}
            <div class="col-span-12 xl:col-span-4 rounded-2xl border border-gray-200 bg-white px-5 pt-7.5 pb-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:px-7.5">
                <h4 class="mb-4 text-xl font-bold text-slate-800 dark:text-white">Status Rekomendasi</h4>
                <div class="mb-2">
                    <div id="chartDonutRekom" class="flex justify-center"></div>
                </div>
                
                <div class="flex flex-col gap-3 pt-2">
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

{{-- Ganti ke ApexCharts agar identik dengan TailAdmin --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');

    // ================= BAR CHART =================
    const optionsBar = {
        series: [{ name: "Jumlah LHP", data: @json($bulanJumlah ?? []) }],
        chart: { type: "bar", height: 335, toolbar: { show: false } },
        colors: ["#3b82f6"],
        plotOptions: { bar: { borderRadius: 4, columnWidth: "35%", } },
        dataLabels: { enabled: false },
        xaxis: {
            categories: @json($bulanLabels ?? []),
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        grid: { strokeDashArray: 5, borderColor: isDark ? "#333d4a" : "#e2e8f0" }
    };
    new ApexCharts(document.querySelector("#chartBarLHP"), optionsBar).render();

    // ================= DONUT CHART =================
    const optionsDonut = {
        series: [{{ $rekomBelum ?? 0 }}, {{ $rekomProses ?? 0 }}, {{ $rekomSelesai ?? 0 }}],
        chart: { type: "donut", width: 320 },
        colors: ["#ef4444", "#f59e0b", "#10b981"],
        labels: ["Belum TL", "Proses", "Selesai"],
        legend: { show: false },
        plotOptions: { pie: { donut: { size: "65%" } } },
        dataLabels: { enabled: false },
        stroke: { colors: [isDark ? "transparent" : "#fff"] }
    };
    new ApexCharts(document.querySelector("#chartDonutRekom"), optionsDonut).render();

    // ================= LINE CHART =================
    const optionsLine = {
    series: [{ name: "Progress", data: @json($bulanPersen ?? []) }],
    chart: { 
        type: "area", 
        height: 350, 
        width: "100%",
        toolbar: { show: false },
        animations: { enabled: true }
    },
    colors: ["#3b82f6"],
    dataLabels: { enabled: false },
    stroke: { curve: "smooth", width: 3 }, // Makes it look like TailAdmin
    fill: { 
        type: "gradient", 
        gradient: { 
            shadeIntensity: 1, 
            opacityFrom: 0.45, 
            opacityTo: 0.05, 
            stops: [20, 100] 
        } 
    },
    xaxis: { 
        categories: @json($bulanLabels ?? ["Jan", "Feb", "Mar"]),
        tooltip: { enabled: false }
    },
    grid: { 
        strokeDashArray: 5, 
        borderColor: isDark ? "#333d4a" : "#e2e8f0" 
    },
    yaxis: { 
        labels: { 
            formatter: (v) => v + "%" 
        } 
    }
};
    new ApexCharts(document.querySelector("#chartLineProgress"), optionsLine).render();
});
</script>
@endsection