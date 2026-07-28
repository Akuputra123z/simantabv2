@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">

    {{-- ROW 1: METRIC CARDS --}}
    <div class="col-span-12">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-5">

            {{-- Belum Upload --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                    {{ $opdStats->belum_upload ?? 0 }}
                </h4>
                <div class="mt-4 flex items-end justify-between sm:mt-5">
                    <div>
                        <p class="text-theme-sm text-gray-700 dark:text-gray-400">Belum Upload</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400">
                            {{ $opdStats->belum_upload ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Draft --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h4 class="text-title-sm font-bold text-amber-600 dark:text-amber-400">
                    {{ $opdStats->draft ?? 0 }}
                </h4>
                <div class="mt-4 flex items-end justify-between sm:mt-5">
                    <div>
                        <p class="text-theme-sm text-gray-700 dark:text-gray-400">Draft</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-theme-xs font-medium text-amber-600 dark:bg-amber-500/15 dark:text-amber-500">
                            Draft
                        </span>
                    </div>
                </div>
            </div>

            {{-- Terkirim --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h4 class="text-title-sm font-bold text-success-600 dark:text-success-500">
                    {{ $opdStats->dikirim ?? 0 }}
                </h4>
                <div class="mt-4 flex items-end justify-between sm:mt-5">
                    <div>
                        <p class="text-theme-sm text-gray-700 dark:text-gray-400">Terkirim</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                            +{{ $opdStats->dikirim ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Ditolak --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h4 class="text-title-sm font-bold text-error-600 dark:text-error-500">
                    {{ $opdStats->ditolak ?? 0 }}
                </h4>
                <div class="mt-4 flex items-end justify-between sm:mt-5">
                    <div>
                        <p class="text-theme-sm text-gray-700 dark:text-gray-400">Ditolak</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                            {{ $opdStats->ditolak ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Lunas --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h4 class="text-title-sm font-bold text-brand-600 dark:text-brand-500">
                    {{ $verifikasiStats->lunas ?? 0 }}
                </h4>
                <div class="mt-4 flex items-end justify-between sm:mt-5">
                    <div>
                        <p class="text-theme-sm text-gray-700 dark:text-gray-400">Verifikasi Lunas</p>
                    </div>
                    @php $tlTotal = ($verifikasiStats->lunas ?? 0) + ($verifikasiStats->berjalan ?? 0) + ($verifikasiStats->menunggu ?? 0); @endphp
                    @php $pct = $tlTotal > 0 ? min(100, round(($verifikasiStats->lunas ?? 0) / $tlTotal * 100)) : 0; @endphp
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-500">
                            {{ $pct }}%
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ROW 2: STATISTICS + VERIFIKASI --}}
    <div class="col-span-12 xl:col-span-8">
        {{-- Statistik Rekapitulasi --}}
        <div class="rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex flex-col gap-5 mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Rekapitulasi Rekomendasi</h3>
                    <p class="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
                        Progress penyelesaian rekomendasi unit Anda
                    </p>
                </div>
            </div>

            <div class="flex gap-4 sm:gap-9">
                <div class="flex items-start gap-2">
                    <div>
                        <h4 class="mb-0.5 text-base font-bold text-gray-800 dark:text-white/90 sm:text-theme-xl">
                            {{ $rekapitulasi->total_rekom ?? 0 }}
                        </h4>
                        <span class="text-gray-500 text-theme-xs dark:text-gray-400">
                            Total Rekomendasi
                        </span>
                    </div>
                    @php $rekomPct = ($rekapitulasi && $rekapitulasi->total_rekom > 0) ? min(100, round(($rekapitulasi->rekom_selesai / $rekapitulasi->total_rekom) * 100)) : 0; @endphp
                    <span class="mt-1.5 flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        {{ $rekomPct }}%
                    </span>
                </div>

                <div class="flex items-start gap-2">
                    <div>
                        <h4 class="mb-0.5 text-base font-bold text-gray-800 dark:text-white/90 sm:text-theme-xl">
                            Rp {{ number_format($rekapitulasi->total_kerugian ?? 0, 0, ',', '.') }}
                        </h4>
                        <span class="text-gray-500 text-theme-xs dark:text-gray-400">
                            Total Kerugian
                        </span>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="mt-6 space-y-3">
                    @if($rekapitulasi)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-500 dark:text-gray-400">Rekomendasi Selesai</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $rekapitulasi->rekom_selesai ?? 0 }} / {{ $rekapitulasi->total_rekom ?? 0 }}</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-2 rounded-full bg-gradient-to-r from-brand-500 to-success-500 transition-all"
                                 style="width: {{ $rekomPct }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-500 dark:text-gray-400">Nilai TL Selesai</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Rp {{ number_format($rekapitulasi->total_tl_selesai ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-white/10">
                            @php $tlPct = $rekapitulasi->total_kerugian > 0 ? min(100, round(($rekapitulasi->total_tl_selesai / $rekapitulasi->total_kerugian) * 100)) : 0; @endphp
                            <div class="h-2 rounded-full bg-brand-500 transition-all"
                                 style="width: {{ $tlPct }}%"></div>
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada data rekomendasi.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-4">
        {{-- Verifikasi Progress --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Status Verifikasi</h3>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                        Progress verifikasi tindak lanjut
                    </p>
                </div>
            </div>

            @php $tlTotal = ($verifikasiStats->lunas ?? 0) + ($verifikasiStats->berjalan ?? 0) + ($verifikasiStats->menunggu ?? 0); @endphp
            <div class="flex items-center justify-center my-6">
                <div class="text-center">
                    <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90">
                        {{ $tlTotal }}
                    </h4>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Total TL</p>
                </div>
            </div>

            <div class="border-gray-200 space-y-5 dark:border-gray-800">
                <div>
                    <p class="mb-2 text-theme-sm text-gray-500 dark:text-gray-400">Lunas</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $verifikasiStats->lunas ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex w-full max-w-[140px] items-center gap-3">
                            <div class="relative block h-2 w-full max-w-[100px] rounded-sm bg-gray-200 dark:bg-gray-800">
                                <div class="absolute left-0 top-0 flex h-full items-center justify-center rounded-sm bg-success-500" style="width: {{ $tlTotal > 0 ? round(($verifikasiStats->lunas ?? 0) / $tlTotal * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ $tlTotal > 0 ? round(($verifikasiStats->lunas ?? 0) / $tlTotal * 100) : 0 }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-theme-sm text-gray-500 dark:text-gray-400">Berjalan</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $verifikasiStats->berjalan ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex w-full max-w-[140px] items-center gap-3">
                            <div class="relative block h-2 w-full max-w-[100px] rounded-sm bg-gray-200 dark:bg-gray-800">
                                <div class="absolute left-0 top-0 flex h-full items-center justify-center rounded-sm bg-amber-500" style="width: {{ $tlTotal > 0 ? round(($verifikasiStats->berjalan ?? 0) / $tlTotal * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ $tlTotal > 0 ? round(($verifikasiStats->berjalan ?? 0) / $tlTotal * 100) : 0 }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-theme-sm text-gray-500 dark:text-gray-400">Menunggu Verifikasi</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $verifikasiStats->menunggu ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex w-full max-w-[140px] items-center gap-3">
                            <div class="relative block h-2 w-full max-w-[100px] rounded-sm bg-gray-200 dark:bg-gray-800">
                                <div class="absolute left-0 top-0 flex h-full items-center justify-center rounded-sm bg-brand-500" style="width: {{ $tlTotal > 0 ? round(($verifikasiStats->menunggu ?? 0) / $tlTotal * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ $tlTotal > 0 ? round(($verifikasiStats->menunggu ?? 0) / $tlTotal * 100) : 0 }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 3: GRAFIK + JATUH TEMPO --}}
    <div class="col-span-12 xl:col-span-6">
        {{-- TL by Status --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Tindak Lanjut by Status</h3>
            </div>
            <div id="chartTlBar" style="min-height: 250px;"></div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-6">
        {{-- Jatuh Tempo --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Jatuh Tempo (7 Hari)</h3>
                <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $overdue->count() }} data</span>
            </div>

            @if($overdue->isEmpty())
                <p class="text-theme-sm text-gray-400 dark:text-gray-500 italic">Tidak ada tindak lanjut yang mendekati jatuh tempo.</p>
            @else
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <div class="min-w-[500px]">
                        <div class="flex flex-col gap-2">
                            @foreach($overdue as $tl)
                            <a href="{{ route('opd.tindak-lanjut.show', $tl) }}"
                               class="flex cursor-pointer items-center gap-9 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-white/[0.03]
                                   {{ $tl->tanggal_jatuh_tempo->isPast() ? 'bg-red-50 dark:bg-red-900/10' : 'bg-amber-50 dark:bg-amber-900/10' }}">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] {{ $tl->tanggal_jatuh_tempo->isPast() ? 'border-red-400 bg-red-100 dark:border-red-600 dark:bg-red-900/30' : 'border-amber-400 bg-amber-100 dark:border-amber-600 dark:bg-amber-900/30' }}">
                                        <svg class="text-white" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="currentColor" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="mb-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
                                            {{ $tl->tanggal_jatuh_tempo->format('D, d M') }}
                                        </span>
                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            @if($tl->tanggal_jatuh_tempo->isPast())
                                                <span class="text-error-600 dark:text-error-400">Terlambat</span>
                                            @else
                                                {{ $tl->tanggal_jatuh_tempo->diffForHumans() }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span class="mb-1 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                        {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }}
                                    </span>
                                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                        {{ $tl->recommendation?->temuan?->lhp?->unitDiperiksa?->nama_unit ?? '-' }}
                                        &middot; {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 50) }}
                                    </span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ROW 4: TABLE KEGIATAN TERBARU --}}
    <div class="col-span-12">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-5 px-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Kegiatan Terbaru</h3>
                </div>
                <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $recent->count() }} data</span>
            </div>

            <div class="custom-scrollbar max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead class="border-y border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 whitespace-nowrap text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Kode</th>
                            <th class="px-6 py-3 whitespace-nowrap text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Unit</th>
                            <th class="px-6 py-3 whitespace-nowrap text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Uraian</th>
                            <th class="px-6 py-3 whitespace-nowrap text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 whitespace-nowrap text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recent as $tl)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-3 whitespace-nowrap">
                                <a href="{{ route('opd.tindak-lanjut.show', $tl) }}" class="text-theme-sm font-medium text-brand-500 hover:text-brand-600">
                                    {{ $tl->recommendation?->kodeRekomendasi?->kode ?? '-' }}
                                </a>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                                    {{ $tl->recommendation?->temuan?->lhp?->unitDiperiksa?->nama_unit ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-theme-sm text-gray-700 dark:text-gray-400 block max-w-[250px] truncate">
                                    {{ Str::limit(strip_tags($tl->recommendation?->uraian_rekom ?? '-'), 60) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                @if($tl->alasan_tolak_opd)
                                    <span class="bg-error-50 text-theme-xs text-error-600 dark:bg-error-500/15 dark:text-error-500 rounded-full px-2 py-0.5 font-medium">Ditolak</span>
                                @elseif($tl->status_opd === 'dikirim')
                                    <span class="bg-success-50 text-theme-xs text-success-600 dark:bg-success-500/15 dark:text-success-500 rounded-full px-2 py-0.5 font-medium">Terkirim</span>
                                @elseif($tl->status_opd === 'draft')
                                    <span class="bg-warning-50 text-theme-xs text-warning-600 dark:bg-warning-500/15 dark:text-warning-400 rounded-full px-2 py-0.5 font-medium">Draft</span>
                                @else
                                    <span class="bg-gray-100 text-theme-xs text-gray-600 dark:bg-white/10 dark:text-gray-400 rounded-full px-2 py-0.5 font-medium">Belum Upload</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">
                                    {{ $tl->updated_at->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-theme-sm text-gray-400 dark:text-gray-500 italic">
                                Belum ada kegiatan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const draft = {{ $opdStats->draft ?? 0 }};
        const dikirim = {{ $opdStats->dikirim ?? 0 }};
        const ditolak = {{ $opdStats->ditolak ?? 0 }};

        if (document.getElementById('chartTlBar') && typeof ApexCharts !== 'undefined') {
            const options = {
                series: [{ name: 'Jumlah', data: [draft, dikirim, ditolak] }],
                chart: {
                    type: 'bar',
                    height: 250,
                    fontFamily: 'Outfit, sans-serif',
                    toolbar: { show: false }
                },
                colors: ['#465fff', '#12b76a', '#f04438'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '55%',
                        distributed: true
                    }
                },
                xaxis: {
                    categories: ['Draft', 'Terkirim', 'Ditolak'],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { fontFamily: 'Outfit', colors: '#667085' } }
                },
                yaxis: {
                    labels: { style: { fontFamily: 'Outfit', colors: '#667085' } }
                },
                dataLabels: { enabled: false },
                grid: { borderColor: '#e4e7ec', strokeDashArray: 3 },
                legend: { show: false }
            };
            new ApexCharts(document.getElementById('chartTlBar'), options).render();
        }
    });
</script>
@endpush
@endsection
