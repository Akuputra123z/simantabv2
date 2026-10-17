@if(auth()->check() && ! auth()->user()->isVerified())
<div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
     class="relative overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50 via-amber-50/60 to-orange-50/40 p-4 sm:p-5 shadow-xs dark:border-amber-900/60 dark:from-amber-950/40 dark:via-gray-900 dark:to-gray-900">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm ring-4 ring-amber-100 dark:ring-amber-900/40">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                        Akun Anda Belum Terverifikasi
                    </h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                        Perlu Tindakan
                    </span>
                </div>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300 leading-relaxed max-w-3xl">
                    Alamat email akun Anda saat ini (<strong class="font-mono text-gray-800 dark:text-white">{{ auth()->user()->email }}</strong>) belum diverifikasi. 
                    Anda dapat menggantinya dengan email resmi OPD / dinas pribadi Anda. Verifikasi email diperlukan agar Anda dapat <strong>Masuk dengan Google</strong> dan <strong>Mereset kata sandi</strong> jika lupa.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="mt-2.5 inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                        <svg class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span>Tautan verifikasi baru telah berhasil dikirim ke email Anda! Silakan cek kotak masuk atau folder spam.</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mt-2.5 flex items-start gap-1.5 rounded-lg bg-red-50 p-2.5 text-xs font-medium text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-800/60">
                        <svg class="h-4 w-4 shrink-0 text-red-600 dark:text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 pl-13 sm:pl-0">
            <!-- Form Kirim Ulang Link Verifikasi -->
            <form method="POST" action="{{ route('verification.store') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition-all hover:shadow-md cursor-pointer">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>Kirim Email Verifikasi</span>
                </button>
            </form>

            <!-- Link Ganti Email di Profil -->
            <a href="{{ route('settings.profile.edit') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-750 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 transition-all shadow-2xs">
                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                <span>Ubah Email di Profil</span>
            </a>

            <!-- Tombol Dismiss -->
            <button type="button" @click="show = false"
                    title="Tutup sementara"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endif
