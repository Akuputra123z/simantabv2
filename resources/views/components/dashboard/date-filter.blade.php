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
}" class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 backdrop-blur-md">
    <form id="dashboardFilterForm" method="GET" action="{{ $actionUrl }}" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- Left: Filter Title & Program Category Select --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Filter Periode & Program</span>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">
                        📅 {{ $startDateFormatted }} &mdash; {{ $endDateFormatted }}
                    </h3>
                </div>
            </div>

            {{-- Dropdown Kategori Program Audit --}}
            <div class="ml-0 lg:ml-4 flex items-center gap-2 border-l border-slate-200 pl-0 lg:pl-4 dark:border-gray-800">
                <label for="kategori_program" class="text-xs font-semibold text-slate-500 dark:text-slate-400">Program:</label>
                <select name="kategori_program" id="kategori_program" onchange="this.form.submit()" data-no-ts
                        class="rounded-xl border border-gray-300 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-colors cursor-pointer">
                    <option value="semua" {{ $kategoriProgram === 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($listKategori as $kat)
                        <option value="{{ $kat }}" {{ $kategoriProgram === $kat ? 'selected' : '' }}>
                            {{ $kat }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Right: Calendar Presets & TailAdmin Flatpickr Date Picker --}}
        <div class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="preset" id="preset_input" :value="selectedPreset">
            <input type="hidden" name="start_date" id="start_date_input" value="{{ $startDate }}">
            <input type="hidden" name="end_date" id="end_date_input" value="{{ $endDate }}">

            {{-- Quick Presets --}}
            <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-gray-800/80">
                <button type="button" @click="submitPreset('today')"
                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-all cursor-pointer"
                        :class="selectedPreset === 'today' ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-700 dark:text-blue-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'">
                    Hari Ini
                </button>
                <button type="button" @click="submitPreset('7days')"
                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-all cursor-pointer"
                        :class="selectedPreset === '7days' ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-700 dark:text-blue-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'">
                    7 Hari
                </button>
                <button type="button" @click="submitPreset('30days')"
                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-all cursor-pointer"
                        :class="selectedPreset === '30days' ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-700 dark:text-blue-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'">
                    30 Hari
                </button>
                <button type="button" @click="submitPreset('this_month')"
                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-all cursor-pointer"
                        :class="selectedPreset === 'this_month' ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-700 dark:text-blue-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'">
                    Bulan Ini
                </button>
                <button type="button" @click="submitPreset('this_year')"
                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-all cursor-pointer"
                        :class="selectedPreset === 'this_year' ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-700 dark:text-blue-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'">
                    Tahun Ini
                </button>
            </div>

            {{-- TailAdmin Flatpickr Component Container --}}
            <div class="relative min-w-[240px]">
                <div class="flatpickr-wrapper w-full">
                    <input type="text" id="flatpickr_range" placeholder="Pilih Rentang Tanggal"
                           class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-10 text-xs font-bold text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input active"
                           readonly="readonly">
                </div>
                <span class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                    <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                    </svg>
                </span>
            </div>

            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-colors shadow-xs cursor-pointer">
                Terapkan
            </button>
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
