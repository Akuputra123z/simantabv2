@extends('layouts.app')

@section('content')

<script>
function kodeRekomendasiForm() {
    return {
        showConfirmModal: false,
        showSuccessModal: false,
        successMessage: '',
        redirectUrl: '',
        isSubmitting: false,
        formTarget: null,

        openConfirmModal(event) {
            if (event) event.preventDefault();
            this.formTarget = event ? event.target : document.getElementById('form-kode-rekom');
            this.showConfirmModal = true;
        },

        async confirmAndSave() {
            this.showConfirmModal = false;
            if (this.isSubmitting) return false;
            this.isSubmitting = true;

            const form = this.formTarget || document.getElementById('form-kode-rekom');
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.successMessage = data.message || 'Data berhasil disimpan.';
                    this.redirectUrl = data.redirect || '{{ route("kode-rekomendasi.index") }}';
                    this.showSuccessModal = true;
                } else {
                    let errMsg = data.message || 'Gagal menyimpan data.';
                    if (data.errors) {
                        const messages = Object.values(data.errors).flat();
                        errMsg = 'Terjadi kesalahan pengisian data:\n\n• ' + messages.join('\n• ');
                    }
                    alert(errMsg);
                    this.isSubmitting = false;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan sistem/koneksi saat menyimpan data.');
                this.isSubmitting = false;
            }
        }
    };
}
</script>

<div class="w-full space-y-6" x-data="kodeRekomendasiForm()" x-cloak>

    {{-- ❓ Pop-Up Confirmation Modal --}}
    <div x-show="showConfirmModal"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        
        <div x-show="showConfirmModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showConfirmModal = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

        <div x-show="showConfirmModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-400 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4 blur-sm"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave="transition cubic-bezier(0.7, 0, 0.84, 0) duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2 blur-sm"
             class="relative w-full max-w-[480px] rounded-[28px] bg-white p-6 sm:p-8 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:bg-gray-900 border border-slate-100 dark:border-gray-800 text-center overflow-hidden">
            
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-amber-50 text-amber-500 dark:bg-amber-900/30 dark:text-amber-400 ring-8 ring-amber-50/50 dark:ring-amber-900/20">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Konfirmasi Simpan Data
            </h3>

            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
                Apakah Anda yakin data Kode Rekomendasi yang diisikan sudah benar dan siap untuk disimpan?
            </p>

            <div class="flex items-center gap-3 justify-center">
                <button type="button"
                        @click="showConfirmModal = false"
                        class="w-1/2 rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                    Batal
                </button>
                <button type="button"
                        @click="confirmAndSave()"
                        class="w-1/2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 transition-all hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                    Ya, Simpan Data
                </button>
            </div>
        </div>
    </div>

    {{-- ✅ Pop-Up Alert Success Modal Ultra-Smooth --}}
    <div x-show="showSuccessModal"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        
        <div x-show="showSuccessModal"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

        <div x-show="showSuccessModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-6 blur-md"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave="transition cubic-bezier(0.7, 0, 0.84, 0) duration-300 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0 blur-none"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4 blur-sm"
             class="relative w-full max-w-[520px] rounded-[32px] bg-white p-8 sm:p-10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:bg-gray-900 border border-slate-100 dark:border-gray-800/80 text-center overflow-hidden">
            
            <div class="absolute -top-24 -left-24 h-48 w-48 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

            <div class="mx-auto mb-6 flex items-center justify-center">
                <img src="{{ asset('images/success.svg') }}" alt="Success" class="h-20 w-20 sm:h-24 sm:w-24 transition-transform duration-300 hover:scale-105">
            </div>

            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white uppercase">
                SUCCESS !
            </h2>

            <p class="mt-3 text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-md mx-auto" x-text="successMessage"></p>

            <div class="mt-8 flex items-center justify-center">
                <button type="button"
                        @click="window.location.href = redirectUrl"
                        class="group relative inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition-all duration-300 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                    <span>Tutup &amp; Ke Halaman Utama</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow max-w-3xl dark:bg-gray-900">

        <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Tambah Kode Rekomendasi</h2>

        <form action="{{ route('kode-rekomendasi.store') }}" method="POST" id="form-kode-rekom" @submit="openConfirmModal($event)">
            @csrf
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Konfigurasi Kode Rekomendasi
                    </h3>
                </div>

                <div class="p-5 space-y-6 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Kode
                            </label>
                            <input type="text" name="kode" value="{{ old('kode', $data?->kode ?? '') }}"
                                placeholder="Masukkan kode unik"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Kode Numerik
                            </label>
                            <input type="number" name="kode_numerik" value="{{ old('kode_numerik', $data?->kode_numerik ?? '') }}"
                                placeholder="Contoh: 101"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Kategori
                            </label>
                            <input type="text" name="kategori" value="{{ old('kategori', $data?->kategori ?? '') }}"
                                placeholder="Kategori sistem"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                        </div>

                        <div class="md:col-span-2">
                            @include('components._rich-editor', [
                                'name'       => 'deskripsi',
                                'value'      => old('deskripsi', $data?->deskripsi ?? ''),
                                'required'   => false,
                                'height'     => 250,
                                'placeholder' => 'Tuliskan deskripsi lengkap di sini...',
                            ])
                        </div>

                        <div class="flex items-center md:pt-7">
                            <div x-data="{ switcherToggle: {{ old('is_active', $data?->is_active ?? true) ? 'true' : 'false' }} }">
                                <label for="toggle1" class="flex items-center gap-3 text-sm font-medium text-gray-700 cursor-pointer select-none dark:text-gray-400">
                                    <div class="relative">
                                        <input type="checkbox" id="toggle1" name="is_active" value="1" class="sr-only"
                                            @change="switcherToggle = !switcherToggle"
                                            :checked="switcherToggle" />
                                        
                                        <div class="block h-6 w-11 rounded-full transition-colors duration-300 ease-in-out"
                                            :class="switcherToggle ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/10'">
                                        </div>
                                        
                                        <div class="shadow-theme-sm absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white transition-transform duration-300 ease-linear"
                                            :class="switcherToggle ? 'translate-x-full' : 'translate-x-0'">
                                        </div>
                                    </div>
                                    Status Aktif
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-all focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/20 cursor-pointer">
                            Simpan Data
                        </button>
                        <a href="{{ route('kode-rekomendasi.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:bg-gray-800 transition-all">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection