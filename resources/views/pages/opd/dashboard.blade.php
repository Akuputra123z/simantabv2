@extends('layouts.app')

@section('content')
<main class="min-h-screen bg-slate-50/50 dark:bg-[#121824]">
    <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10 space-y-6">

        {{-- HEADER DASHBOARD OPD --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Dashboard Tindak Lanjut OPD
                </h1>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                    Pemantauan status pengunggahan, verifikasi, dan penyelesaian rekomendasi unit Anda
                </p>
            </div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-bold">
                    🏛️ {{ auth()->user()->name }}
                </span>
            </nav>
        </div>

        {{-- GLOBAL DATE FILTER --}}
        <x-dashboard.date-filter
            :preset="$preset"
            :startDate="$startDate"
            :endDate="$endDate"
            :startDateFormatted="$startDateFormatted"
            :endDateFormatted="$endDateFormatted"
            :actionUrl="route('opd.dashboard')"
        />

        {{-- ROW 1: METRIC CARDS --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 xl:grid-cols-5">

            {{-- Belum Upload --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum Upload</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-gray-800 dark:text-slate-300">
                        {{ $opdStats->belum_upload ?? 0 }}
                    </span>
                </div>
                <h4 class="mt-4 text-3xl font-extrabold text-slate-900 dark:text-white">
                    {{ $opdStats->belum_upload ?? 0 }}
                </h4>
            </div>

            {{-- Draft --}}
            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Draft</span>
                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        Draft
                    </span>
                </div>
                <h4 class="mt-4 text-3xl font-extrabold text-amber-600 dark:text-amber-400">
                    {{ $opdStats->draft ?? 0 }}
                </h4>
            </div>

            {{-- Terkirim --}}
            <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Terkirim</span>
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        Proses Verifikasi
                    </span>
                </div>
                <h4 class="mt-4 text-3xl font-extrabold text-blue-600 dark:text-blue-400">
                    {{ $opdStats->dikirim ?? 0 }}
                </h4>
            </div>

            {{-- Ditolak --}}
            <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Ditolak</span>
                    <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                        Perlu Perbaikan
                    </span>
                </div>
                <h4 class="mt-4 text-3xl font-extrabold text-rose-600 dark:text-rose-400">
                    {{ $opdStats->ditolak ?? 0 }}
                </h4>
            </div>

            {{-- Verifikasi Lunas --}}
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Verifikasi Lunas</span>
                    @php $tlTotal = ($verifikasiStats->lunas ?? 0) + ($verifikasiStats->berjalan ?? 0) + ($verifikasiStats->menunggu ?? 0); @endphp
                    @php $pct = $tlTotal > 0 ? min(100, round(($verifikasiStats->lunas ?? 0) / $tlTotal * 100)) : 0; @endphp
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                        {{ $pct }}% Lunas
                    </span>
                </div>
                <h4 class="mt-4 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                    {{ $verifikasiStats->lunas ?? 0 }}
                </h4>
            </div>

        </div>

        {{-- ROW 2: REKAPITULASI + VERIFIKASI PROGRESS --}}
        <div class="grid grid-cols-12 gap-6">

            <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Rekapitulasi Rekomendasi Unit</h3>
                    <p class="text-xs text-slate-500">Progres fisik dan finansial penyelesaian rekomendasi</p>
                </div>

                @php $rekomPct = ($rekapitulasi && $rekapitulasi->total_rekom > 0) ? min(100, round(($rekapitulasi->rekom_selesai / $rekapitulasi->total_rekom) * 100)) : 0; @endphp
                <div class="grid grid-cols-2 gap-4 my-4 p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Total Rekomendasi</span>
                        <h4 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $rekapitulasi->total_rekom ?? 0 }}</h4>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Total Kerugian / Nilai</span>
                        <h4 class="text-xl font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($rekapitulasi->total_kerugian ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-600 dark:text-slate-300">Rekomendasi Selesai</span>
                            <span class="text-slate-900 dark:text-white font-bold">{{ $rekapitulasi->rekom_selesai ?? 0 }} / {{ $rekapitulasi->total_rekom ?? 0 }} ({{ $rekomPct }}%)</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $rekomPct }}%"></div>
                        </div>
                    </div>
                    <div>
                        @php $tlPct = ($rekapitulasi && $rekapitulasi->total_kerugian > 0) ? min(100, round(($rekapitulasi->total_tl_selesai / $rekapitulasi->total_kerugian) * 100)) : 0; @endphp
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-600 dark:text-slate-300">Nilai TL Selesai Disetor</span>
                            <span class="text-slate-900 dark:text-white font-bold">Rp {{ number_format($rekapitulasi->total_tl_selesai ?? 0, 0, ',', '.') }} ({{ $tlPct }}%)</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: {{ $tlPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Status Verifikasi</h3>
                <p class="text-xs text-slate-500 mb-4">Distribusi status verifikasi tindak lanjut</p>

                <div id="chartTlBar" class="min-h-[220px]"></div>
            </div>

        </div>

        {{-- ROW 3: JATUH TEMPO & KEGIATAN TERBARU --}}
        <div class="grid grid-cols-12 gap-6">

            {{-- Jatuh Tempo --}}
            <div class="col-span-12 lg:col-span-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Mendekati Jatuh Tempo (7 Hari)</h3>
                    <span class="text-xs text-slate-400">{{ $overdue->count() }} data</span>
                </div>

                <div class="space-y-2">
                    @forelse($overdue as $tl)
                    <a href="{{ route('opd.tindak-lanjut.show', $tl) }}" class="flex items-center justify-between p-3 rounded-xl border border-amber-100 bg-amber-50/50 hover:bg-amber-100/70 transition-all dark:border-amber-900/30 dark:bg-amber-900/10">
                        <div class="min-w-0 pr-3">
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }} &middot; {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 45) }}
                            </h4>
                            <p class="text-[11px] text-slate-500">Jatuh Tempo: {{ $tl->tanggal_jatuh_tempo?->format('d M Y') }}</p>
                        </div>
                        <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">
                            {{ $tl->tanggal_jatuh_tempo?->isPast() ? 'Terlambat' : $tl->tanggal_jatuh_tempo?->diffForHumans() }}
                        </span>
                    </a>
                    @empty
                    <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada rekomendasi mendekati jatuh tempo.</p>
                    @endforelse
                </div>
            </div>

            {{-- Kegiatan Terbaru OPD --}}
            <div class="col-span-12 lg:col-span-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Kegiatan Terbaru Unit</h3>
                    <span class="text-xs text-slate-400">{{ $recent->count() }} data</span>
                </div>

                <div class="space-y-2">
                    @forelse($recent as $tl)
                    <a href="{{ route('opd.tindak-lanjut.show', $tl) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="min-w-0 pr-3">
                            <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 truncate">
                                {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }}
                            </h4>
                            <p class="text-[11px] text-slate-500 truncate">
                                {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 50) }}
                            </p>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 whitespace-nowrap">
                            {{ $tl->updated_at?->diffForHumans() }}
                        </span>
                    </a>
                    @empty
                    <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada aktivitas terbaru.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const draft = {{ $opdStats->draft ?? 0 }};
    const dikirim = {{ $opdStats->dikirim ?? 0 }};
    const ditolak = {{ $opdStats->ditolak ?? 0 }};

    if (document.getElementById('chartTlBar') && typeof ApexCharts !== 'undefined') {
        const isDark = document.documentElement.classList.contains('dark');
        new ApexCharts(document.getElementById('chartTlBar'), {
            series: [{ name: 'Jumlah', data: [draft, dikirim, ditolak] }],
            chart: {
                type: 'bar',
                height: 220,
                fontFamily: 'Outfit, sans-serif',
                toolbar: { show: false }
            },
            colors: ['#f59e0b', '#2563eb', '#f43f5e'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '50%',
                    distributed: true
                }
            },
            xaxis: {
                categories: ['Draft', 'Terkirim', 'Ditolak'],
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
@endsection
