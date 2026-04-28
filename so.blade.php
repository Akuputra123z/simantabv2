<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login - E-AUDIT INSPEKTORAT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
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
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "2xl": "1rem",
                      "full": "9999px"
              },
              "spacing": {
                      "md": "24px",
                      "xs": "4px",
                      "xl": "64px",
                      "lg": "40px",
                      "sm": "12px",
                      "base": "8px",
                      "container-padding": "120px"
              },
              "fontFamily": {
                      "body-md": [
                              "Inter"
                      ],
                      "caption": [
                              "Inter"
                      ],
                      "label-sm": [
                              "Inter"
                      ],
                      "h1": [
                              "Inter"
                      ],
                      "h2": [
                              "Inter"
                      ]
              },
              "fontSize": {
                      "body-md": [
                              "16px",
                              {
                                      "lineHeight": "24px",
                                      "fontWeight": "400"
                              }
                      ],
                      "caption": [
                              "12px",
                              {
                                      "lineHeight": "16px",
                                      "fontWeight": "400"
                              }
                      ],
                      "label-sm": [
                              "14px",
                              {
                                      "lineHeight": "20px",
                                      "fontWeight": "500"
                              }
                      ],
                      "h1": [
                              "30px",
                              {
                                      "lineHeight": "38px",
                                      "letterSpacing": "-0.02em",
                                      "fontWeight": "700"
                              }
                      ],
                      "h2": [
                              "24px",
                              {
                                      "lineHeight": "32px",
                                      "letterSpacing": "-0.01em",
                                      "fontWeight": "600"
                              }
                      ]
              }
      },
          },
        }
    </script>
<style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        .glass-panel-light {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }
        .smooth-gradient {
            background: linear-gradient(135deg, #1d61d1 0%, #0049ab 100%);
        }
        .smooth-gradient-hover:hover {
            background: linear-gradient(135deg, #1d61d1 10%, #00429b 100%);
        }
        .bg-mesh {
            background-color: #051024;
            background-image: radial-gradient(at 80% 0%, hsla(217, 100%, 25%, 0.4) 0px, transparent 50%),
                              radial-gradient(at 0% 100%, hsla(217, 100%, 15%, 0.6) 0px, transparent 50%);
        }
    </style>
</head>
<body class="bg-surface-container-lowest text-on-background font-body-md h-screen flex flex-col antialiased selection:bg-[#1d61d1] selection:text-white">
<main class="flex-grow flex w-full relative min-h-screen bg-slate-50 p-sm lg:p-lg items-center justify-center">
<!-- Main Container -->
<div class="flex w-full bg-surface-container-lowest rounded-[24px] overflow-hidden shadow-[0_20px_60px_-15px_rgba(0,0,0,0.05)] border border-outline-variant/20 flex-col lg:flex-row max-w-[1440px] mx-auto min-h-[850px]">
<!-- Authentication Zone (Left) -->
<div class="w-full lg:w-[45%] xl:w-[40%] bg-white flex flex-col items-center justify-center p-xl lg:p-[80px] relative z-10">
<div class="w-full max-w-[420px]">
<!-- Logo and Header -->
<div class="mb-xl text-center flex flex-col items-center">
<div class="w-16 h-16 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-blue-100">
<span class="material-symbols-outlined text-[#1d61d1] text-[32px]" style="font-variation-settings: 'FILL' 1;">admin_panel_settings</span>
</div>
<h1 class="font-h1 text-[32px] leading-[40px] font-bold text-gray-900 mb-2 tracking-tight">E-AUDIT INSPEKTORAT</h1>
<p class="font-label-sm text-[13px] text-gray-500 mb-8 uppercase tracking-[0.15em] font-semibold">Sistem Audit Internal Terintegrasi</p>
<div class="h-px w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent mb-8"></div>
<h2 class="font-h2 text-[26px] text-gray-800 mb-2 tracking-tight">Selamat Datang</h2>
<p class="font-body-md text-[15px] text-gray-500 font-light">Silakan masuk untuk melanjutkan ke sistem.</p>
</div>
<!-- Form -->
<form class="space-y-6">
<!-- Email Input -->
<div>
<label class="block font-label-sm text-[14px] font-medium text-gray-700 mb-2" for="email">Email</label>
<div class="relative group">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
<span class="material-symbols-outlined text-gray-400 group-focus-within:text-[#1d61d1] transition-colors duration-300 text-[20px]">mail</span>
</div>
<input class="w-full pl-[44px] pr-4 py-[12px] rounded-xl border border-gray-200 bg-gray-50/50 text-gray-900 focus:bg-white focus:ring-4 focus:ring-[#1d61d1]/10 focus:border-[#1d61d1] transition-all duration-300 ease-in-out text-[15px] font-light placeholder:text-gray-400" id="email" placeholder="Masukkan email Anda" type="email"/>
</div>
</div>
<!-- Password Input -->
<div>
<label class="block font-label-sm text-[14px] font-medium text-gray-700 mb-2" for="password">Password</label>
<div class="relative group">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
<span class="material-symbols-outlined text-gray-400 group-focus-within:text-[#1d61d1] transition-colors duration-300 text-[20px]">lock</span>
</div>
<input class="w-full pl-[44px] pr-4 py-[12px] rounded-xl border border-gray-200 bg-gray-50/50 text-gray-900 focus:bg-white focus:ring-4 focus:ring-[#1d61d1]/10 focus:border-[#1d61d1] transition-all duration-300 ease-in-out text-[15px] font-light placeholder:text-gray-400" id="password" placeholder="••••••••" type="password"/>
<div class="absolute inset-y-0 right-0 pr-4 flex items-center">
<button class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none" type="button">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</div>
</div>
<!-- Options -->
<div class="flex items-center justify-between pt-2 pb-4">
<div class="flex items-center">
<input class="h-[18px] w-[18px] text-[#1d61d1] focus:ring-[#1d61d1]/20 border-gray-300 rounded cursor-pointer transition-colors duration-200" id="remember-me" type="checkbox"/>
<label class="ml-3 block font-label-sm text-[14px] text-gray-600 cursor-pointer font-light" for="remember-me">
                                Ingat saya
                            </label>
</div>
<div class="text-[14px]">
<a class="font-label-sm text-[#1d61d1] hover:text-[#0049ab] transition-colors font-medium" href="#">
                                Lupa password?
                            </a>
</div>
</div>
<!-- Primary Button -->
<div>
<button class="w-full flex justify-center items-center py-[14px] px-6 border border-transparent rounded-xl shadow-lg shadow-[#1d61d1]/20 font-label-sm text-[15px] font-semibold text-white smooth-gradient smooth-gradient-hover transition-all duration-300 ease-out focus:outline-none focus:ring-4 focus:ring-[#1d61d1]/30 active:scale-[0.98]" type="submit">
<span class="material-symbols-outlined mr-2 text-[20px]">lock_open</span> Masuk ke Sistem
                        </button>
</div>
<!-- Divider -->
<div class="my-8 flex items-center">
<div class="flex-grow border-t border-gray-200"></div>
<span class="flex-shrink-0 mx-4 font-caption text-[12px] text-gray-400 uppercase tracking-wider font-medium">atau</span>
<div class="flex-grow border-t border-gray-200"></div>
</div>
<!-- Secondary Button -->
<div>
<button class="w-full flex justify-center items-center py-[14px] px-6 border border-gray-200 rounded-xl font-label-sm text-[15px] font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 hover:text-[#1d61d1] transition-all duration-300 ease-out focus:outline-none focus:ring-4 focus:ring-gray-100 active:scale-[0.98]" type="button">
<span class="material-symbols-outlined mr-2 text-[20px] text-inherit transition-colors">shield_person</span> Login dengan SSO
                        </button>
</div>
<!-- Security Notice -->
<div class="mt-10 flex flex-col items-center justify-center text-center opacity-60 hover:opacity-100 transition-opacity duration-300">
<span class="material-symbols-outlined text-gray-400 text-[18px] mb-2">lock</span>
<p class="font-caption text-[12px] text-gray-500 max-w-[280px] leading-relaxed font-light">
                            Sistem ini dilindungi dengan enkripsi AES-256 dan hanya untuk pengguna yang berwenang.
                        </p>
</div>
</form>
</div>
<!-- Mobile Footer Placement -->
<div class="absolute bottom-6 lg:hidden text-center w-full">
<p class="font-caption text-[12px] text-gray-400 font-light">© 2024 Inspektorat. All rights reserved.</p>
</div>
</div>
<!-- Information Zone (Right) -->
<div class="hidden lg:flex w-[55%] xl:w-[60%] bg-mesh relative overflow-hidden flex-col justify-between items-center p-[60px] rounded-r-[24px]">
<!-- Sophisticated Background Pattern -->
<div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 40px 40px; opacity: 0.5;"></div>
<!-- Decorative Glows -->
<div class="absolute top-[-10%] right-[-5%] w-[400px] h-[400px] bg-blue-500/20 rounded-full blur-[100px] pointer-events-none"></div>
<div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>
<div class="z-10 text-left w-full max-w-2xl mt-8 pl-8 relative">
<h2 class="font-h1 text-[48px] leading-[1.15] font-bold text-white mb-8 tracking-tight">Transparansi, <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-indigo-200">Akuntabilitas,</span> <br/>Integritas.</h2>
<div class="w-16 h-1.5 bg-gradient-to-r from-[#1d61d1] to-blue-400 rounded-full mb-8 shadow-[0_0_15px_rgba(29,97,209,0.5)]"></div>
<p class="font-body-md text-[18px] leading-[1.6] text-blue-100/80 font-light max-w-xl">
                    E-Audit Inspektorat merupakan sistem terintegrasi untuk mendukung pengelolaan audit internal secara efektif, terdokumentasi, dan akuntabel.
                </p>
</div>
<!-- Illustration Area -->
<div class="z-10 relative flex-grow flex items-center justify-center w-full mt-12 mb-12 perspective-[1000px]">
<!-- Abstract layered UI representation -->
<div class="relative w-[540px] h-[380px] transform-gpu hover:scale-[1.02] transition-transform duration-700 ease-out">
<!-- Main Dashboard Card -->
<div class="absolute inset-0 glass-panel rounded-2xl overflow-hidden transform -rotate-2 hover:rotate-0 transition-transform duration-700 ease-out shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)]">
<div class="h-10 border-b border-white/5 flex items-center px-5 bg-white/5 backdrop-blur-sm">
<div class="flex gap-2">
<div class="w-3 h-3 rounded-full bg-red-400/80 shadow-[0_0_8px_rgba(248,113,113,0.5)]"></div>
<div class="w-3 h-3 rounded-full bg-yellow-400/80 shadow-[0_0_8px_rgba(250,204,21,0.5)]"></div>
<div class="w-3 h-3 rounded-full bg-green-400/80 shadow-[0_0_8px_rgba(74,222,128,0.5)]"></div>
</div>
</div>
<div class="p-8 flex flex-col gap-6">
<div class="flex gap-5">
<div class="w-1/3 h-28 rounded-xl bg-white/[0.02] border border-white/5 p-4 hover:bg-white/[0.05] transition-colors duration-300">
<div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center mb-3 shadow-[0_0_15px_rgba(59,130,246,0.2)]">
<span class="material-symbols-outlined text-blue-400 text-[20px]">monitoring</span>
</div>
<div class="w-1/2 h-2.5 bg-white/20 rounded-full mb-3"></div>
<div class="w-3/4 h-2.5 bg-white/10 rounded-full"></div>
</div>
<div class="w-1/3 h-28 rounded-xl bg-white/[0.02] border border-white/5 p-4 hover:bg-white/[0.05] transition-colors duration-300">
<div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center mb-3 shadow-[0_0_15px_rgba(34,197,94,0.2)]">
<span class="material-symbols-outlined text-green-400 text-[20px]">task_alt</span>
</div>
<div class="w-1/2 h-2.5 bg-white/20 rounded-full mb-3"></div>
<div class="w-3/4 h-2.5 bg-white/10 rounded-full"></div>
</div>
<div class="w-1/3 h-28 rounded-xl bg-white/[0.02] border border-white/5 p-4 hover:bg-white/[0.05] transition-colors duration-300">
<div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center mb-3 shadow-[0_0_15px_rgba(168,85,247,0.2)]">
<span class="material-symbols-outlined text-purple-400 text-[20px]">description</span>
</div>
<div class="w-1/2 h-2.5 bg-white/20 rounded-full mb-3"></div>
<div class="w-3/4 h-2.5 bg-white/10 rounded-full"></div>
</div>
</div>
<div class="w-full h-36 rounded-xl bg-white/[0.02] border border-white/5 p-5 relative overflow-hidden">
<div class="absolute bottom-0 left-0 w-full h-full bg-gradient-to-t from-blue-500/5 to-transparent"></div>
<div class="flex items-end gap-3 h-full justify-between px-2 pb-2 relative z-10">
<div class="w-10 bg-gradient-to-t from-blue-500/60 to-blue-400/40 rounded-t-md h-1/3 hover:h-2/5 transition-all duration-300"></div>
<div class="w-10 bg-gradient-to-t from-blue-500/70 to-blue-400/50 rounded-t-md h-2/3 hover:h-3/4 transition-all duration-300"></div>
<div class="w-10 bg-gradient-to-t from-blue-500/80 to-blue-400/60 rounded-t-md h-1/2 hover:h-3/5 transition-all duration-300"></div>
<div class="w-10 bg-gradient-to-t from-blue-500/60 to-blue-400/40 rounded-t-md h-3/4 hover:h-4/5 transition-all duration-300"></div>
<div class="w-10 bg-gradient-to-t from-[#1d61d1]/90 to-blue-400/70 rounded-t-md h-full shadow-[0_0_20px_rgba(29,97,209,0.3)]"></div>
<div class="w-10 bg-gradient-to-t from-blue-500/80 to-blue-400/60 rounded-t-md h-4/5 hover:h-[85%] transition-all duration-300"></div>
</div>
</div>
</div>
</div>
<!-- Floating Elements -->
<div class="absolute -right-8 top-16 p-5 glass-panel rounded-2xl flex items-center gap-4 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.4)] transform translate-y-4 hover:-translate-y-2 transition-transform duration-500 z-20 border-white/10 before:absolute before:inset-0 before:rounded-2xl before:border before:border-white/20 before:mix-blend-overlay">
<div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#1d61d1]/30 to-blue-500/10 flex items-center justify-center border border-blue-400/20 shadow-[0_0_15px_rgba(29,97,209,0.3)]">
<span class="material-symbols-outlined text-blue-300 text-[24px]" style="font-variation-settings: 'FILL' 1;">security</span>
</div>
<div>
<div class="font-label-sm text-[14px] text-white font-medium tracking-wide">Data Terenkripsi</div>
<div class="font-caption text-[11px] text-blue-200/60 mt-0.5">AES-256 Bit Security</div>
</div>
</div>
<div class="absolute -left-8 bottom-20 p-5 glass-panel rounded-2xl flex items-center gap-4 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.4)] transform -translate-y-4 hover:translate-y-2 transition-transform duration-500 z-20 border-white/10 before:absolute before:inset-0 before:rounded-2xl before:border before:border-white/20 before:mix-blend-overlay">
<div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500/20 to-green-400/5 flex items-center justify-center border border-green-400/20 shadow-[0_0_15px_rgba(34,197,94,0.2)]">
<span class="material-symbols-outlined text-green-400 text-[24px]" style="font-variation-settings: 'FILL' 1;">fact_check</span>
</div>
<div>
<div class="font-label-sm text-[14px] text-white font-medium tracking-wide">Audit Real-time</div>
<div class="font-caption text-[11px] text-blue-200/60 mt-0.5">Sinkronisasi Otomatis</div>
</div>
</div>
</div>
</div>
<div class="z-10 w-full max-w-[540px] glass-panel p-6 rounded-2xl flex items-start gap-5 mb-4 hover:bg-white/[0.05] transition-all duration-500 border border-white/5 shadow-xl relative overflow-hidden group">
<div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
<div class="bg-gradient-to-br from-blue-500/20 to-[#1d61d1]/10 p-3 rounded-xl shrink-0 border border-blue-400/20">
<span class="material-symbols-outlined text-blue-300 text-[26px]" style="font-variation-settings: 'FILL' 1;">verified_user</span>
</div>
<div class="pt-1">
<h4 class="font-label-sm text-[16px] font-semibold text-white mb-2 tracking-wide">Keamanan Sistem Terjamin</h4>
<p class="font-caption text-[14px] leading-[1.6] text-blue-100/70 font-light">Seluruh data dan aktivitas diawasi dan dilindungi sesuai standar keamanan informasi pemerintah.</p>
</div>
</div>
</div>
<!-- Global Footer -->
<div class="absolute bottom-6 left-0 w-full text-center lg:text-left lg:pl-[80px] lg:w-[40%] z-0">
<p class="font-caption text-[12px] text-gray-400 font-light tracking-wide">© 2024 Inspektorat. All rights reserved.</p>
</div>
</div>
</main>
</body></html>