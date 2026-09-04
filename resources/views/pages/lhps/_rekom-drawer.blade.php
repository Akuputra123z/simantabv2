{{-- resources/views/pages/lhps/_rekom-drawer.blade.php --}}
{{-- Slide-over Drawer untuk Tambah / Edit Rekomendasi --}}
<div
    x-show="drawer.open"
    x-cloak
    class="fixed inset-0 z-[9999] flex"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDrawer()" onclick="window.lhpPageInstance && window.lhpPageInstance.closeDrawer()"></div>

    {{-- Panel --}}
    <div
        class="relative ml-auto flex h-full w-full max-w-lg flex-col bg-white shadow-2xl dark:bg-gray-900"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white"
                    x-text="drawer.mode === 'edit' ? 'Edit Rekomendasi' : 'Tambah Rekomendasi'"></h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="drawer.temuanLabel"></p>
            </div>
            <button @click="closeDrawer()" onclick="window.lhpPageInstance && window.lhpPageInstance.closeDrawer()"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Error Alert --}}
        <div x-show="drawer.error" x-transition
            class="mx-6 mt-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-400">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span x-text="drawer.error"></span>
        </div>

        {{-- Form Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">
            <div class="space-y-5">

                {{-- Kode Rekomendasi --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kode Rekomendasi <span class="text-red-500">*</span>
                    </label>
                    <select x-model="drawer.form.kode_rekomendasi_id" data-no-ts
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        :class="drawer.fieldErrors.kode_rekomendasi_id ? 'border-red-400' : ''">
                        <option value="">-- Pilih Kode Rekomendasi --</option>
                        @foreach(\App\Models\KodeRekomendasi::active()->orderBy('kode')->get() as $kr)
                        <option value="{{ $kr->id }}">{{ $kr->kode }} — {{ $kr->deskripsi }}</option>
                        @endforeach
                    </select>
                    <p x-show="drawer.fieldErrors.kode_rekomendasi_id" class="mt-1 text-xs text-red-500" x-text="drawer.fieldErrors.kode_rekomendasi_id"></p>
                </div>

                {{-- Uraian Rekomendasi --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Uraian Rekomendasi <span class="text-red-500">*</span>
                    </label>
                    <textarea x-model="drawer.form.uraian_rekom" rows="6"
                        placeholder="Tuliskan uraian rekomendasi secara lengkap dan jelas..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 resize-y"
                        :class="drawer.fieldErrors.uraian_rekom ? 'border-red-400' : ''"></textarea>
                    <p x-show="drawer.fieldErrors.uraian_rekom" class="mt-1 text-xs text-red-500" x-text="drawer.fieldErrors.uraian_rekom"></p>
                </div>

                {{-- Jenis Rekomendasi --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jenis Rekomendasi <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <template x-for="jenis in [{val:'uang',label:'Uang',icon:'💰'},{val:'barang',label:'Barang',icon:'📦'},{val:'administrasi',label:'Administrasi',icon:'📋'}]" :key="jenis.val">
                            <label
                                class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-xl border-2 px-2 py-2.5 text-xs font-semibold transition-all"
                                :class="drawer.form.jenis_rekomendasi === jenis.val
                                    ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-400 dark:bg-blue-900/30 dark:text-blue-300'
                                    : 'border-gray-200 bg-white text-gray-500 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400'">
                                <input type="radio" class="sr-only" :value="jenis.val" x-model="drawer.form.jenis_rekomendasi">
                                <span x-text="jenis.icon"></span>
                                <span x-text="jenis.label"></span>
                            </label>
                        </template>
                    </div>
                    <p x-show="drawer.fieldErrors.jenis_rekomendasi" class="mt-1 text-xs text-red-500" x-text="drawer.fieldErrors.jenis_rekomendasi"></p>
                </div>

                {{-- Nilai Rekomendasi --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nilai Rekomendasi (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-400 pointer-events-none">Rp</span>
                        <input type="text" inputmode="numeric"
                            x-model="drawer.nilaiDisplay"
                            @input="onNilaiInput($event); if (drawer.form.nilai_rekom > 0) drawer.form.jenis_rekomendasi = 'uang'"
                            placeholder="0"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            :class="drawer.fieldErrors.nilai_rekom ? 'border-red-400' : ''">
                    </div>
                    <p x-show="drawer.fieldErrors.nilai_rekom" class="mt-1 text-xs text-red-500" x-text="drawer.fieldErrors.nilai_rekom"></p>
                </div>

                {{-- Batas Waktu --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Batas Waktu Tindak Lanjut <span class="text-red-500">*</span>
                    </label>
                    <input type="date" x-model="drawer.form.batas_waktu"
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        :class="drawer.fieldErrors.batas_waktu ? 'border-red-400' : ''">
                    <p x-show="drawer.fieldErrors.batas_waktu" class="mt-1 text-xs text-red-500" x-text="drawer.fieldErrors.batas_waktu"></p>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800">
            <button type="button" @click="closeDrawer()" onclick="window.lhpPageInstance && window.lhpPageInstance.closeDrawer()"
                class="flex-1 rounded-xl border border-gray-200 bg-white py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:bg-gray-800">
                Batal
            </button>
            <button type="button" @click="submitRekom()" onclick="window.lhpPageInstance && window.lhpPageInstance.submitRekom()" :disabled="drawer.submitting"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">
                <svg x-show="drawer.submitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="drawer.submitting ? 'Menyimpan...' : (drawer.mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Rekomendasi')"></span>
            </button>
        </div>
    </div>
</div>
