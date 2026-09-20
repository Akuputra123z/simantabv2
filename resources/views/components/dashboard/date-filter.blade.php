@props([
    'preset' => 'this_year',
    'startDate' => '',
    'endDate' => '',
    'startDateFormatted' => '',
    'endDateFormatted' => '',
    'kategoriProgram' => 'semua',
    'listKategori' => ['PKPT', 'BPK', 'BPKP', 'ITPROV', 'ITDA', 'LAINNYA'],
    'actionUrl' => route('dashboard')
])

<div x-data="{
    selectedPreset: '{{ $preset }}',
    submitPreset(presetName) {
        this.selectedPreset = presetName;
        document.getElementById('preset_input').value = presetName;
        this.$nextTick(() => {
            document.getElementById('dashboardFilterForm').submit();
        });
    }
}" class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70 backdrop-blur-md">
    <form id="dashboardFilterForm" method="GET" action="{{ $actionUrl }}" class="flex flex-col gap-4">

        {{-- Baris 1: Informasi Judul, Periode Aktif, & Dropdown Program --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-3.5 border-b border-gray-100 dark:border-gray-800/80">
            {{-- Kiri: Icon + Judul + Periode Aktif --}}
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
                            {{ $startDateFormatted }} &mdash; {{ $endDateFormatted }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Menampilkan ringkasan statistik hasil pengawasan dan progres tindak lanjut
                    </p>
                </div>
            </div>

            {{-- Kanan: Dropdown Kategori Program --}}
            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <label for="kategori_program" class="text-xs font-semibold text-gray-600 dark:text-gray-400 shrink-0">
                    Kategori:
                </label>
                <div class="relative min-w-[160px]">
                    <select name="kategori_program" id="kategori_program" onchange="this.form.submit()" data-no-ts
                            class="h-9.5 w-full appearance-none rounded-lg border border-gray-300 bg-gray-50/50 pl-3.5 pr-8 text-xs font-semibold text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition-colors">
                        <option value="semua" {{ $kategoriProgram === 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach($listKategori as $kat)
                            <option value="{{ $kat }}" {{ $kategoriProgram === $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- Baris 2: Preset Periode Cepat & Kalender Custom Range --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <input type="hidden" name="preset" id="preset_input" :value="selectedPreset">
            <input type="hidden" name="start_date" id="start_date_input" value="{{ $startDate }}">
            <input type="hidden" name="end_date" id="end_date_input" value="{{ $endDate }}">

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
                <div class="relative flex-1 sm:w-64">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="text" id="flatpickr_range" placeholder="Pilih Rentang Tanggal..."
                           class="h-9.5 w-full appearance-none rounded-lg border border-gray-300 bg-gray-50/50 pl-9 pr-8 text-xs font-semibold text-gray-800 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 cursor-pointer flatpickr-input transition-colors"
                           readonly="readonly">
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 dark:text-gray-500">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr !== 'undefined' && document.getElementById('flatpickr_range')) {
        flatpickr('#flatpickr_range', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            defaultDate: ['{{ $startDate }}', '{{ $endDate }}'],
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const startStr = instance.formatDate(selectedDates[0], 'Y-m-d');
                    const endStr   = instance.formatDate(selectedDates[1], 'Y-m-d');
                    document.getElementById('start_date_input').value = startStr;
                    document.getElementById('end_date_input').value   = endStr;
                    document.getElementById('preset_input').value     = 'custom';
                }
            }
        });
    }
});
</script>
