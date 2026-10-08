@props([
    'preset' => 'this_year',
    'tahun' => date('Y'),
    'listTahun' => [],
    'startDate' => '',
    'endDate' => '',
    'startDateFormatted' => '',
    'endDateFormatted' => '',
    'kategoriProgram' => 'semua',
    'listKategori' => ['PKPT', 'BPK', 'BPKP', 'ITPROV', 'ITDA', 'LAINNYA'],
    'actionUrl' => route('dashboard')
])

@php
    if (empty($listTahun)) {
        $currY = (int) date('Y');
        $listTahun = range($currY + 1, $currY - 4);
    }
    $isCustom = ($preset === 'custom');
@endphp

<div x-data="{
    selectedPreset: '{{ $preset }}',
    selectedTahun: '{{ $tahun }}',
    isCustomDate: {{ $isCustom ? 'true' : 'false' }},
    submitPreset(presetName) {
        this.selectedPreset = presetName;
        document.getElementById('preset_input').value = presetName;
        if (presetName === 'all') {
            this.selectedTahun = 'semua';
            document.getElementById('tahun_input').value = 'semua';
        } else if (presetName === 'this_year') {
            this.selectedTahun = '{{ date('Y') }}';
            document.getElementById('tahun_input').value = '{{ date('Y') }}';
        }
        this.$nextTick(() => {
            document.getElementById('dashboardFilterForm').submit();
        });
    },
    changeTahun(tahunVal) {
        this.selectedTahun = tahunVal;
        document.getElementById('tahun_input').value = tahunVal;
        if (tahunVal === 'semua') {
            this.selectedPreset = 'all';
            document.getElementById('preset_input').value = 'all';
        } else {
            this.selectedPreset = (tahunVal == '{{ date('Y') }}') ? 'this_year' : 'year';
            document.getElementById('preset_input').value = this.selectedPreset;
        }
        this.$nextTick(() => {
            document.getElementById('dashboardFilterForm').submit();
        });
    },
    clearCustomDate() {
        if (window.flatpickrRangeInstance) {
            window.flatpickrRangeInstance.clear();
        }
        document.getElementById('start_date_input').value = '';
        document.getElementById('end_date_input').value = '';
        this.submitPreset('this_year');
    }
}" class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70 backdrop-blur-md">

    <form id="dashboardFilterForm" method="GET" action="{{ $actionUrl }}" class="flex flex-col gap-4">
        {{-- Hidden Fields --}}
        <input type="hidden" name="preset" id="preset_input" :value="selectedPreset">
        <input type="hidden" name="tahun" id="tahun_input" :value="selectedTahun">
        <input type="hidden" name="start_date" id="start_date_input" value="{{ $startDate }}">
        <input type="hidden" name="end_date" id="end_date_input" value="{{ $endDate }}">

        {{-- Baris 1: Informasi Judul, Periode Aktif, & Kontrol Dropdown (Tahun & Kategori) --}}
        <div class="flex flex-col gap-3.5 sm:flex-row sm:items-center sm:justify-between pb-3.5 border-b border-gray-100 dark:border-gray-800/80">
            {{-- Kiri: Icon + Judul + Badge Status Periode --}}
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">Filter Periode & Program</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            @if($preset === 'all')
                                Semua Tahun (Seluruh Periode)
                            @elseif($preset === 'year')
                                Tahun {{ $tahun }}
                            @else
                                {{ $startDateFormatted }} &mdash; {{ $endDateFormatted }}
                            @endif
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Menampilkan ringkasan statistik hasil pengawasan dan progres tindak lanjut
                    </p>
                </div>
            </div>

            {{-- Kanan: Dropdown Tahun & Dropdown Kategori Program --}}
            <div class="flex flex-wrap items-center gap-3 self-start sm:self-auto">
                {{-- Dropdown Pilih Tahun --}}
                <div class="flex items-center gap-2">
                    <label for="filter_tahun" class="text-xs font-semibold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tahun:
                    </label>
                    <div class="relative min-w-[135px]">
                        <select id="filter_tahun" x-model="selectedTahun" @change="changeTahun($event.target.value)" data-no-ts
                                class="h-9.5 w-full appearance-none rounded-lg border border-gray-300 bg-gray-50/50 pl-3 pr-8 text-xs font-bold text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition-colors shadow-2xs">
                            <option value="semua" {{ $tahun === 'semua' || $preset === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                            @foreach($listTahun as $y)
                                <option value="{{ $y }}" {{ (string)$tahun === (string)$y && $preset !== 'all' ? 'selected' : '' }}>
                                    {{ $y }}{{ $y == date('Y') ? ' (Tahun Ini)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- Dropdown Kategori Program --}}
                <div class="flex items-center gap-2">
                    <label for="kategori_program" class="text-xs font-semibold text-gray-600 dark:text-gray-400 shrink-0">
                        Kategori:
                    </label>
                    <div class="relative min-w-[145px]">
                        <select name="kategori_program" id="kategori_program" onchange="this.form.submit()" data-no-ts
                                class="h-9.5 w-full appearance-none rounded-lg border border-gray-300 bg-gray-50/50 pl-3 pr-8 text-xs font-semibold text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition-colors shadow-2xs">
                            <option value="semua" {{ $kategoriProgram === 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                            @foreach($listKategori as $kat)
                                <option value="{{ $kat }}" {{ $kategoriProgram === $kat ? 'selected' : '' }}>
                                    {{ $kat }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Baris 2: Preset Periode Cepat & Kalender Custom Range --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            {{-- Preset Tabs --}}
            <div class="flex items-center overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800/90 shrink-0">
                    <button type="button" @click="submitPreset('all')"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer whitespace-nowrap"
                            :class="selectedPreset === 'all' ? 'bg-white text-blue-600 font-bold shadow-xs dark:bg-gray-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                        Semua
                    </button>
                    <button type="button" @click="submitPreset('this_year')"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer whitespace-nowrap"
                            :class="selectedPreset === 'this_year' ? 'bg-white text-blue-600 font-bold shadow-xs dark:bg-gray-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                        Tahun Ini
                    </button>
                    <button type="button" @click="submitPreset('this_month')"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer whitespace-nowrap"
                            :class="selectedPreset === 'this_month' ? 'bg-white text-blue-600 font-bold shadow-xs dark:bg-gray-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                        Bulan Ini
                    </button>
                    <button type="button" @click="submitPreset('30days')"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer whitespace-nowrap"
                            :class="selectedPreset === '30days' ? 'bg-white text-blue-600 font-bold shadow-xs dark:bg-gray-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                        30 Hari
                    </button>
                    <button type="button" @click="submitPreset('7days')"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer whitespace-nowrap"
                            :class="selectedPreset === '7days' ? 'bg-white text-blue-600 font-bold shadow-xs dark:bg-gray-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                        7 Hari
                    </button>
                    <button type="button" @click="submitPreset('today')"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all cursor-pointer whitespace-nowrap"
                            :class="selectedPreset === 'today' ? 'bg-white text-blue-600 font-bold shadow-xs dark:bg-gray-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                        Hari Ini
                    </button>
                </div>
            </div>

            {{-- Custom Date Range Picker Flatpickr & Tombol Terapkan --}}
            <div class="flex items-center gap-2 w-full lg:w-auto">
                <div class="relative flex-1 sm:w-72">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500 z-10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <input type="text" id="flatpickr_range"
                           placeholder="Pilih Rentang Tanggal..."
                           class="h-9.5 w-full rounded-lg border border-gray-300 bg-gray-50/50 pl-9 pr-9 text-xs font-semibold text-gray-800 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 cursor-pointer transition-colors shadow-2xs"
                           readonly="readonly">

                    {{-- Reset / Clear Button for Date Range --}}
                    <button type="button" @click="clearCustomDate()" x-show="selectedPreset === 'custom'" x-cloak
                            title="Reset Rentang Tanggal"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 cursor-pointer z-10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <span x-show="selectedPreset !== 'custom'" class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>

                <button type="submit"
                        class="h-9.5 inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white shadow-xs hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 active:scale-95 transition-all cursor-pointer whitespace-nowrap">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Terapkan
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Scoped Flatpickr Styling for TailAdmin / Tailwind Dark Mode --}}
<style>
.flatpickr-calendar {
    font-family: inherit !important;
    border-radius: 1rem !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid #e2e8f0 !important;
    z-index: 99999 !important;
}
.dark .flatpickr-calendar {
    background: #1e293b !important;
    border-color: #334155 !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
}
.dark .flatpickr-months, .dark .flatpickr-month {
    background: #1e293b !important;
    color: #f8fafc !important;
    fill: #f8fafc !important;
}
.dark .flatpickr-current-month .numInputWrapper span {
    border-color: #475569 !important;
}
.dark .flatpickr-weekday {
    background: #1e293b !important;
    color: #94a3b8 !important;
}
.dark .flatpickr-day {
    color: #f1f5f9 !important;
}
.dark .flatpickr-day.flatpickr-disabled, .dark .flatpickr-day.prevMonthDay, .dark .flatpickr-day.nextMonthDay {
    color: #475569 !important;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
}
.flatpickr-day.inRange {
    background: #dbeafe !important;
    border-color: transparent !important;
    color: #1e40af !important;
}
.dark .flatpickr-day.inRange {
    background: #1e3a8a !important;
    color: #bfdbfe !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rangeInput = document.getElementById('flatpickr_range');
    if (!rangeInput || typeof flatpickr === 'undefined') return;

    const isCustom = '{{ $preset }}' === 'custom';
    const initStart = isCustom ? '{{ $startDate }}' : null;
    const initEnd = isCustom ? '{{ $endDate }}' : null;

    window.flatpickrRangeInstance = flatpickr(rangeInput, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        altInputClass: 'h-9.5 w-full rounded-lg border border-gray-300 bg-gray-50/50 pl-9 pr-9 text-xs font-semibold text-gray-800 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 cursor-pointer transition-colors shadow-2xs',
        defaultDate: (isCustom && initStart && initEnd) ? [initStart, initEnd] : null,
        showMonths: window.innerWidth >= 1024 ? 2 : 1,
        locale: {
            firstDayOfWeek: 1,
            rangeSeparator: ' s/d '
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 1) {
                const s = instance.formatDate(selectedDates[0], 'Y-m-d');
                document.getElementById('start_date_input').value = s;
                document.getElementById('end_date_input').value = s;
                document.getElementById('preset_input').value = 'custom';
            } else if (selectedDates.length === 2) {
                const s = instance.formatDate(selectedDates[0], 'Y-m-d');
                const e = instance.formatDate(selectedDates[1], 'Y-m-d');
                document.getElementById('start_date_input').value = s;
                document.getElementById('end_date_input').value = e;
                document.getElementById('preset_input').value = 'custom';
            }
        }
    });

    if (!isCustom && window.flatpickrRangeInstance && window.flatpickrRangeInstance.altInput) {
        window.flatpickrRangeInstance.altInput.value = '';
        window.flatpickrRangeInstance.altInput.placeholder = 'Pilih Rentang Tanggal...';
    }
});
</script>
