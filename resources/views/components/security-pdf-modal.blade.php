{{-- Modal Verifikasi Keamanan Sandi untuk Membuka Dokumen PDF --}}
<div x-data="pdfSecurityModal()"
     @open-pdf-modal.window="openModal($event.detail)"
     @keydown.escape.window="if (open) closeModal()"
     class="relative z-50"
     x-cloak>

    {{-- Backdrop with blur --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
         @click="if (!loading) closeModal()"></div>

    {{-- Modal Dialog --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">

        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 text-left shadow-2xl transition-all"
             @click.stop>

            {{-- Decorative Header Glow --}}
            <div class="absolute -top-12 -right-12 h-32 w-32 rounded-full bg-rose-500/10 blur-2xl dark:bg-rose-500/20 pointer-events-none"></div>
            <div class="absolute -bottom-12 -left-12 h-32 w-32 rounded-full bg-blue-500/10 blur-2xl dark:bg-blue-500/20 pointer-events-none"></div>

            {{-- Header Icon & Title --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50 shadow-inner">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">
                        Verifikasi Keamanan Dokumen
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Dokumen PDF ini diproteksi. Masukkan password akun Anda untuk membuka.
                    </p>
                </div>
                <button type="button"
                        @click="closeModal()"
                        class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300 p-1 -mr-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="sr-only">Tutup</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Document Info Box --}}
            <div class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-200/80 dark:border-slate-800 flex items-center gap-2.5">
                <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 shadow-2xs text-rose-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Dokumen Target</div>
                    <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate" x-text="documentTitle || 'Dokumen PDF'"></div>
                </div>
            </div>

            {{-- Error Alert --}}
            <div x-show="errorMessage"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 p-3 text-xs text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 flex items-start gap-2"
                 x-cloak>
                <svg class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span x-text="errorMessage" class="flex-1"></span>
            </div>

            {{-- Password Form --}}
            <form @submit.prevent="verifyAndOpen" class="mt-4 space-y-4">
                <div>
                    <label for="pdf-security-password-input" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Password Akun Anda
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'"
                               id="pdf-security-password-input"
                               x-ref="passwordInput"
                               x-model="password"
                               placeholder="Masukkan password akun login..."
                               autocomplete="current-password"
                               required
                               :disabled="loading"
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 disabled:bg-slate-100 dark:disabled:bg-slate-800 transition pr-10">

                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                tabindex="-1">
                            <template x-if="!showPassword">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </template>
                            <template x-if="showPassword">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-5 sm:flex sm:flex-row-reverse sm:gap-2">
                    <button type="submit"
                            :disabled="loading || !password"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md hover:from-rose-500 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-rose-500/40 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto transition-all active:scale-95">
                        <template x-if="loading">
                            <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <template x-if="!loading">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Memverifikasi...' : 'Verifikasi & Buka PDF'"></span>
                    </button>

                    <button type="button"
                            @click="closeModal()"
                            :disabled="loading"
                            class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50 sm:mt-0 sm:w-auto transition">
                        Batal
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    function pdfSecurityModal() {
        return {
            open: false,
            loading: false,
            password: '',
            showPassword: false,
            documentType: '',
            documentId: '',
            documentTitle: '',
            errorMessage: '',
            targetBlankWindow: null,

            openModal(detail) {
                this.documentType = detail.type || '';
                this.documentId = detail.id || '';
                this.documentTitle = detail.title || 'Dokumen PDF';
                this.password = '';
                this.showPassword = false;
                this.errorMessage = '';
                this.loading = false;
                this.open = true;

                this.$nextTick(() => {
                    if (this.$refs.passwordInput) {
                        this.$refs.passwordInput.focus();
                    }
                });
            },

            closeModal() {
                if (this.loading) return;
                this.open = false;
                this.password = '';
                this.errorMessage = '';
            },

            async verifyAndOpen() {
                if (!this.password || this.loading) return;

                this.loading = true;
                this.errorMessage = '';

                // Buka window blank lebih dulu untuk mencegah popup blocker browser
                let newTab = null;
                try {
                    newTab = window.open('about:blank', '_blank');
                    if (newTab) {
                        newTab.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Memverifikasi Akses Dokumen...</title>
                                <style>
                                    body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; display: flex; height: 100vh; margin: 0; align-items: center; justify-content: center; }
                                    .card { text-align: center; background: #1e293b; padding: 2rem 3rem; border-radius: 1rem; border: 1px solid #334155; }
                                    .spinner { width: 36px; height: 36px; border: 3px solid rgba(255,255,255,0.1); border-top-color: #ef4444; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
                                    @keyframes spin { to { transform: rotate(360deg); } }
                                </style>
                            </head>
                            <body>
                                <div class="card">
                                    <div class="spinner"></div>
                                    <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem;">Memverifikasi Keamanan...</h3>
                                    <p style="margin: 0; color: #94a3b8; font-size: 0.85rem;">Dokumen akan segera dimuat di sini.</p>
                                </div>
                            </body>
                            </html>
                        `);
                    }
                } catch (e) {
                    newTab = null;
                }

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch('{{ route("security.verify-pdf-access") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || ''
                        },
                        body: JSON.stringify({
                            password: this.password,
                            document_type: this.documentType,
                            document_id: this.documentId
                        })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        if (newTab && !newTab.closed) {
                            newTab.close();
                        }
                        this.errorMessage = data.message || 'Verifikasi gagal. Pastikan password Anda benar.';
                        this.loading = false;
                        this.$nextTick(() => {
                            if (this.$refs.passwordInput) {
                                this.$refs.passwordInput.select();
                            }
                        });
                        return;
                    }

                    // Berhasil: Alihkan tab baru ke URL yang telah terverifikasi/ditandatangani
                    if (newTab && !newTab.closed) {
                        newTab.location.href = data.url;
                    } else {
                        window.open(data.url, '_blank');
                    }

                    this.loading = false;
                    this.open = false;
                    this.password = '';
                } catch (err) {
                    if (newTab && !newTab.closed) {
                        newTab.close();
                    }
                    this.errorMessage = 'Terjadi kesalahan jaringan saat memverifikasi. Silakan coba lagi.';
                    this.loading = false;
                }
            }
        };
    }

    // Helper global untuk memanggil modal verifikasi dari komponen atau blade manapun
    window.requestSecurePdf = function(type, id, title) {
        window.dispatchEvent(new CustomEvent('open-pdf-modal', {
            detail: { type: type, id: id, title: title }
        }));
    };
</script>
