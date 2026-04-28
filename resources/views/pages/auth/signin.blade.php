<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login - E-AUDIT INSPEKTORAT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "on-error": "#ffffff",
            "on-secondary-fixed": "#011b3e",
            "error": "#ba1a1a",
            "on-background": "#1a1c1c",
            "on-tertiary-container": "#ffdfd2",
            "inverse-surface": "#2f3131",
            "secondary-fixed-dim": "#b2c7f3",
            "secondary": "#4a5f85",
            "tertiary-container": "#ae4600",
            "surface-variant": "#e2e2e2",
            "primary": "#0049ab",
            "surface-container": "#eeeeee",
            "on-secondary-fixed-variant": "#32476c",
            "on-error-container": "#93000a",
            "on-primary-fixed": "#001945",
            "on-tertiary": "#ffffff",
            "primary-container": "#1d61d1",
            "primary-fixed-dim": "#b0c6ff",
            "surface": "#f9f9f9",
            "error-container": "#ffdad6",
            "secondary-container": "#bdd2fe",
            "tertiary-fixed-dim": "#ffb694",
            "on-tertiary-fixed": "#351000",
            "on-tertiary-fixed-variant": "#7b2f00",
            "on-primary-fixed-variant": "#00429b",
            "on-secondary-container": "#455a80",
            "outline": "#737785",
            "inverse-on-surface": "#f0f1f1",
            "surface-dim": "#dadada",
            "tertiary": "#873500",
            "surface-container-high": "#e8e8e8",
            "secondary-fixed": "#d7e3ff",
            "tertiary-fixed": "#ffdbcc",
            "on-surface": "#1a1c1c",
            "on-primary-container": "#dee5ff",
            "on-primary": "#ffffff",
            "surface-container-low": "#f3f3f4",
            "surface-container-lowest": "#ffffff",
            "on-secondary": "#ffffff",
            "surface-bright": "#f9f9f9",
            "background": "#f9f9f9",
            "inverse-primary": "#b0c6ff",
            "surface-tint": "#0858c8",
            "surface-container-highest": "#e2e2e2",
            "on-surface-variant": "#424653",
            "primary-fixed": "#d9e2ff",
            "outline-variant": "#c2c6d6"
          },
          borderRadius: {
            DEFAULT: "0.25rem",
            lg: "0.5rem",
            xl: "0.75rem",
            "2xl": "1rem",
            full: "9999px"
          },
          fontFamily: {
            "body-md": ["Inter"],
            "caption": ["Inter"],
            "label-sm": ["Inter"],
            h1: ["Inter"],
            h2: ["Inter"]
          },
          fontSize: {
            "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
            caption: ["12px", { lineHeight: "16px", fontWeight: "400" }],
            "label-sm": ["14px", { lineHeight: "20px", fontWeight: "500" }],
            h1: ["30px", { lineHeight: "38px", letterSpacing: "-0.02em", fontWeight: "700" }],
            h2: ["24px", { lineHeight: "32px", letterSpacing: "-0.01em", fontWeight: "600" }]
          }
        }
      }
    }
</script>
<style>
    * { box-sizing: border-box; }

    .glass-panel {
        background: rgba(255,255,255,0.03);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.08);
        box-shadow: 0 4px 30px rgba(0,0,0,0.1);
    }
    .smooth-gradient {
        background: linear-gradient(135deg, #1d61d1 0%, #0049ab 100%);
    }
    .smooth-gradient-hover:hover {
        background: linear-gradient(135deg, #1d61d1 10%, #00429b 100%);
    }
    .bg-mesh {
        background-color: #051024;
        background-image:
            radial-gradient(at 80% 0%, hsla(217,100%,25%,0.4) 0px, transparent 50%),
            radial-gradient(at 0% 100%, hsla(217,100%,15%,0.6) 0px, transparent 50%);
    }

    /* Floating badges — static on small screens, animated on lg+ */
    .float-badge-right {
        transform: translateY(16px);
        transition: transform 0.5s ease;
    }
    .float-badge-right:hover { transform: translateY(-8px); }

    .float-badge-left {
        transform: translateY(-16px);
        transition: transform 0.5s ease;
    }
    .float-badge-left:hover { transform: translateY(8px); }

    /* Bar chart bars hover */
    .bar { transition: height 0.3s ease; }

    /* Shimmer on info card */
    .shimmer-group:hover .shimmer {
        transform: translateX(100%);
        transition: transform 1s ease-in-out;
    }
    .shimmer {
        transform: translateX(-100%);
        transition: none;
    }

    /* Ensure the page never overflows on small screens */
    html, body { height: 100%; }
</style>
</head>
<body class="bg-slate-50 text-on-background font-body-md antialiased selection:bg-[#1d61d1] selection:text-white min-h-screen flex flex-col">

<main class="flex-grow flex w-full items-start lg:items-center justify-center p-3 sm:p-4 lg:p-8">

    <!-- Outer card -->
    <div class="flex w-full bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_20px_60px_-15px_rgba(0,0,0,0.08)] border border-outline-variant/20 flex-col lg:flex-row max-w-[1300px] mx-auto">

        <!-- ═══════════════════════════════════════
             LEFT — Authentication Zone
        ════════════════════════════════════════ -->
        <div class="w-full lg:w-[42%] xl:w-[38%] bg-white flex flex-col items-center justify-center px-5 py-10 sm:px-8 sm:py-12 lg:px-16 lg:py-16 relative z-10">

            <div class="w-full max-w-[400px] mb-40">
                <div class="w-full max-w-[400px]">
                    <a href="{{ route('home') }}" class="group inline-flex items-center text-[13px] font-medium text-gray-400 hover:text-primary transition-all duration-300">
                        <span class="material-symbols-outlined text-[18px] mr-2 group-hover:-translate-x-1 transition-transform">arrow_back</span>
                        Kembali ke Beranda
                    </a>
                </div>
                

                <!-- Logo & Header -->
        <div class="mb-8 text-center flex flex-col items-center space-y-4 mt-10">

    <!-- Logo -->
    <div class="flex justify-center">
        <img 
            src="{{ asset('images/logo/image.png') }}" 
            alt="Logo Inspektorat" 
            class="h-24 sm:h-28 w-auto object-contain"
        >
    </div>

    <!-- Nama Instansi -->
    <div class="space-y-0.5">
        <h1 class="text-[18px] sm:text-[20px] font-bold text-gray-900 tracking-tight">
            E-AUDIT INSPEKTORAT
        </h1>
        <p class="text-[12px] text-[#1d61d1] font-semibold tracking-[0.15em] uppercase">
            Kabupaten Rembang
        </p>
    </div>

    <!-- Divider -->
    <div class="w-14 h-[2px] bg-gradient-to-r from-[#1d61d1] to-blue-400 rounded-full"></div>

    <!-- Welcome Text -->
    <div class="space-y-1 pt-1">
        <h2 class="text-[18px] sm:text-[20px] font-semibold text-gray-800">
            Selamat Datang
        </h2>
        <p class="text-[13px] text-gray-500 font-light">
            Silakan masuk untuk melanjutkan ke sistem
        </p>
    </div>

</div>
                <!-- Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5" for="email">Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-gray-400 group-focus-within:text-[#1d61d1] transition-colors duration-300 text-[20px]">mail</span>
                            </div>
                            <input
                                class="w-full pl-10 pr-4 py-[11px] rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50/30' : 'border-gray-200 bg-gray-50/50' }} text-gray-900 focus:bg-white focus:ring-4 focus:ring-[#1d61d1]/10 focus:border-[#1d61d1] transition-all duration-300 text-[14px] font-light placeholder:text-gray-400 outline-none"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="Masukkan email Anda"
                            />
                        </div>
                        @error('email')
                            <p class="text-red-500 text-[12px] mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div x-data="{ show: false }">
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5" for="password">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-gray-400 group-focus-within:text-[#1d61d1] transition-colors duration-300 text-[20px]">lock</span>
                            </div>
                            <input
                                :type="show ? 'text' : 'password'"
                                class="w-full pl-10 pr-11 py-[11px] rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50/30' : 'border-gray-200 bg-gray-50/50' }} text-gray-900 focus:bg-white focus:ring-4 focus:ring-[#1d61d1]/10 focus:border-[#1d61d1] transition-all duration-300 text-[14px] font-light placeholder:text-gray-400 outline-none"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                                <button type="button" @click="show = !show" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                                    <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                                </button>
                            </div>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-[12px] mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between pt-1 pb-2">
                        <div class="flex items-center">
                            <input class="h-[16px] w-[16px] text-[#1d61d1] focus:ring-[#1d61d1]/20 border-gray-300 rounded cursor-pointer transition-colors duration-200" id="remember-me" type="checkbox" name="remember"/>
                            <label class="ml-2.5 text-[13px] text-gray-600 cursor-pointer font-light" for="remember-me">Ingat saya</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="text-[13px] text-[#1d61d1] hover:text-[#0049ab] transition-colors font-medium" href="{{ route('password.request') }}">Lupa password?</a>
                        @endif
                    </div>

                    <!-- Global error alert -->
                    @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-2.5 text-red-600 text-[13px]">
                            <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                            Email atau password salah
                        </div>
                    @endif
                    @if ($errors->has('email') || $errors->has('password'))
                        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-2.5 text-red-600 text-[13px]">
                            <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                            Email atau password salah. Silakan coba lagi.
                        </div>
                    @endif

                    <div class="flex justify-center py-2">
    <x-turnstile />
</div>
@error('cf-turnstile-response')
    <p class="text-red-500 text-[12px] mb-4 flex items-center gap-1 justify-center">
        <span class="material-symbols-outlined text-[14px]">error</span>
        Konfirmasi bahwa Anda bukan robot.
    </p>
@enderror

                    <!-- Submit -->
                    <div>
                        <button type="submit" class="w-full flex justify-center items-center py-[13px] px-6 border border-transparent rounded-xl shadow-lg shadow-[#1d61d1]/20 text-[14px] font-semibold text-white smooth-gradient smooth-gradient-hover transition-all duration-300 ease-out focus:outline-none focus:ring-4 focus:ring-[#1d61d1]/30 active:scale-[0.98]">
                            <span class="material-symbols-outlined mr-2 text-[20px]">lock_open</span>
                            Masuk ke Sistem
                        </button>
                    </div>

                  

                    <!-- Security notice -->
                    <div class="pt-2 flex flex-col items-center justify-center text-center opacity-60 hover:opacity-100 transition-opacity duration-300">
                    
                        <p class="text-[11px] text-gray-500 max-w-[280px] leading-relaxed font-light">
                            Sistem ini dilindungi dengan enkripsi AES-256 dan hanya untuk pengguna yang berwenang.
                        </p>
                    </div>

                    

                </form>
            </div>

            <!-- Footer mobile -->
            <div class="mt-8 lg:hidden text-center w-full">
                <p class="text-[11px] text-gray-400 font-light">© 2024 Inspektorat. All rights reserved.</p>
            </div>
        </div>

        <!-- ═══════════════════════════════════════
             RIGHT — Information Zone
        ════════════════════════════════════════ -->
        <div class="hidden lg:flex w-[58%] xl:w-[62%] bg-mesh relative overflow-hidden flex-col justify-between items-center p-12 xl:p-16 rounded-r-2xl">

            <!-- Background dot grid -->
            <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(rgba(255,255,255,0.08) 1px,transparent 1px);background-size:40px 40px;opacity:0.5;"></div>

            <!-- Decorative glows -->
            <div class="absolute top-[-10%] right-[-5%] w-[400px] h-[400px] bg-blue-500/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>

            <!-- Headline -->
            <div class="z-10 text-left w-full max-w-xl mt-4 relative">
                <h2 class="font-h1 text-[38px] xl:text-[46px] leading-[1.15] font-bold text-white mb-6 tracking-tight">
                    Transparansi, <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-indigo-200">Akuntabilitas,</span><br/>
                    Integritas.
                </h2>
                <div class="w-14 h-1.5 bg-gradient-to-r from-[#1d61d1] to-blue-400 rounded-full mb-6 shadow-[0_0_15px_rgba(29,97,209,0.5)]"></div>
               
            </div>

            <!-- Illustration -->
            <div class="z-10 relative flex-grow flex items-center justify-center w-full my-8 xl:my-10">
                <div class="relative w-full max-w-[500px] h-[340px] xl:h-[370px]">

                    <!-- Dashboard card -->
                    <div class="absolute inset-0 glass-panel rounded-2xl overflow-hidden shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] -rotate-2 hover:rotate-0 transition-transform duration-700 ease-out">
                        <!-- Titlebar -->
                        <div class="h-9 border-b border-white/5 flex items-center px-5 bg-white/5">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-400/80 shadow-[0_0_8px_rgba(248,113,113,0.5)]"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400/80 shadow-[0_0_8px_rgba(250,204,21,0.5)]"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400/80 shadow-[0_0_8px_rgba(74,222,128,0.5)]"></div>
                            </div>
                        </div>
                        <!-- Content -->
                        <div class="p-6 flex flex-col gap-5">
                            <!-- Stat cards row -->
                            <div class="flex gap-4">
                                <div class="flex-1 rounded-xl bg-white/[0.02] border border-white/5 p-4 hover:bg-white/[0.05] transition-colors duration-300">
                                    <div class="w-9 h-9 rounded-full bg-blue-500/20 flex items-center justify-center mb-3 shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                                        <span class="material-symbols-outlined text-blue-400 text-[18px]">monitoring</span>
                                    </div>
                                    <div class="w-1/2 h-2 bg-white/20 rounded-full mb-2"></div>
                                    <div class="w-3/4 h-2 bg-white/10 rounded-full"></div>
                                </div>
                                <div class="flex-1 rounded-xl bg-white/[0.02] border border-white/5 p-4 hover:bg-white/[0.05] transition-colors duration-300">
                                    <div class="w-9 h-9 rounded-full bg-green-500/20 flex items-center justify-center mb-3 shadow-[0_0_15px_rgba(34,197,94,0.2)]">
                                        <span class="material-symbols-outlined text-green-400 text-[18px]">task_alt</span>
                                    </div>
                                    <div class="w-1/2 h-2 bg-white/20 rounded-full mb-2"></div>
                                    <div class="w-3/4 h-2 bg-white/10 rounded-full"></div>
                                </div>
                                <div class="flex-1 rounded-xl bg-white/[0.02] border border-white/5 p-4 hover:bg-white/[0.05] transition-colors duration-300">
                                    <div class="w-9 h-9 rounded-full bg-purple-500/20 flex items-center justify-center mb-3 shadow-[0_0_15px_rgba(168,85,247,0.2)]">
                                        <span class="material-symbols-outlined text-purple-400 text-[18px]">description</span>
                                    </div>
                                    <div class="w-1/2 h-2 bg-white/20 rounded-full mb-2"></div>
                                    <div class="w-3/4 h-2 bg-white/10 rounded-full"></div>
                                </div>
                            </div>
                            <!-- Bar chart -->
                            <div class="w-full h-[120px] xl:h-[130px] rounded-xl bg-white/[0.02] border border-white/5 p-4 relative overflow-hidden">
                                <div class="absolute bottom-0 left-0 w-full h-full bg-gradient-to-t from-blue-500/5 to-transparent"></div>
                                <div class="flex items-end gap-2.5 h-full justify-between px-1 pb-1 relative z-10">
                                    <div class="flex-1 bg-gradient-to-t from-blue-500/60 to-blue-400/40 rounded-t-md bar" style="height:33%"></div>
                                    <div class="flex-1 bg-gradient-to-t from-blue-500/70 to-blue-400/50 rounded-t-md bar" style="height:66%"></div>
                                    <div class="flex-1 bg-gradient-to-t from-blue-500/80 to-blue-400/60 rounded-t-md bar" style="height:50%"></div>
                                    <div class="flex-1 bg-gradient-to-t from-blue-500/60 to-blue-400/40 rounded-t-md bar" style="height:75%"></div>
                                    <div class="flex-1 bg-gradient-to-t from-[#1d61d1]/90 to-blue-400/70 rounded-t-md shadow-[0_0_20px_rgba(29,97,209,0.3)]" style="height:100%"></div>
                                    <div class="flex-1 bg-gradient-to-t from-blue-500/80 to-blue-400/60 rounded-t-md bar" style="height:80%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Badge: Data Terenkripsi -->
                    <div class="absolute -right-6 xl:-right-8 top-14 p-4 glass-panel rounded-2xl flex items-center gap-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.4)] float-badge-right z-20 border border-white/10 min-w-[180px]">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#1d61d1]/30 to-blue-500/10 flex items-center justify-center border border-blue-400/20 shadow-[0_0_15px_rgba(29,97,209,0.3)] shrink-0">
                            <span class="material-symbols-outlined text-blue-300 text-[22px]" style="font-variation-settings:'FILL' 1;">security</span>
                        </div>
                        <div>
                            <div class="text-[13px] text-white font-medium tracking-wide">Data Terenkripsi</div>
                            <div class="text-[11px] text-blue-200/60 mt-0.5">AES-256 Bit Security</div>
                        </div>
                    </div>

                    <!-- Badge: Audit Real-time -->
                    <div class="absolute -left-6 xl:-left-8 bottom-16 p-4 glass-panel rounded-2xl flex items-center gap-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.4)] float-badge-left z-20 border border-white/10 min-w-[180px]">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500/20 to-green-400/5 flex items-center justify-center border border-green-400/20 shadow-[0_0_15px_rgba(34,197,94,0.2)] shrink-0">
                            <span class="material-symbols-outlined text-green-400 text-[22px]" style="font-variation-settings:'FILL' 1;">fact_check</span>
                        </div>
                        <div>
                            <div class="text-[13px] text-white font-medium tracking-wide">Audit Real-time</div>
                            <div class="text-[11px] text-blue-200/60 mt-0.5">Sinkronisasi Otomatis</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security card -->
            <div class="z-10 w-full max-w-[500px] glass-panel p-5 rounded-2xl flex items-start gap-4 mb-2 hover:bg-white/[0.05] transition-all duration-500 border border-white/5 shadow-xl relative overflow-hidden shimmer-group">
                <div class="shimmer absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent pointer-events-none"></div>
                <div class="bg-gradient-to-br from-blue-500/20 to-[#1d61d1]/10 p-3 rounded-xl shrink-0 border border-blue-400/20">
                    <span class="material-symbols-outlined text-blue-300 text-[24px]" style="font-variation-settings:'FILL' 1;">verified_user</span>
                </div>
                <div class="pt-0.5">
                    <h4 class="text-[15px] font-semibold text-white mb-1.5 tracking-wide">Keamanan Sistem Terjamin</h4>
                    <p class="text-[13px] leading-[1.65] text-blue-100/70 font-light">Seluruh data dan aktivitas diawasi dan dilindungi sesuai standar keamanan informasi pemerintah.</p>
                </div>
            </div>

            <!-- Footer desktop right panel -->
            <div class="z-10 w-full text-left mt-4">
                <p class="text-[11px] text-blue-200/30 font-light tracking-wide">© 2024 Inspektorat. All rights reserved.</p>
            </div>

        </div><!-- /right panel -->

    </div><!-- /outer card -->

</main>
<x-turnstile.scripts />

</body>
</html>