<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="SIMANTAB - Sistem Audit Internal Terintegrasi Inspektorat Kabupaten Rembang" />

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Lacak LHP — SIMANTAB</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary:       "#003366",
            secondary:     "#c5a059",
            accent:        "#2563eb",
            "bg-light":    "#f8fafc",
            "bg-dark":     "#020617",
            "border-light":"#e2e8f0",
            "border-dark": "#1e293b",
          },
          fontFamily: {
            display: ["Sora",   "sans-serif"],
            sans:    ["DM Sans","sans-serif"],
          },
          borderRadius: {
            "4xl": "2rem",
            "5xl": "2.5rem",
          },
        },
      },
    };
  </script>

  <style>
    html { -webkit-font-smoothing: antialiased; }

    /* ── Scroll Progress ── */
    #scroll-progress {
      position:fixed; top:0; left:0; height:3px; width:0%;
      background:linear-gradient(90deg,#003366,#2563eb);
      z-index:9999; transition:width 150ms ease-out;
    }

    /* ── Animations ── */
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(22px); }
      to   { opacity:1; transform:translateY(0);    }
    }
    .animate-fadeup { opacity:0; animation:fadeUp .6s cubic-bezier(.16,1,.3,1) forwards; }
    .delay-1 { animation-delay:.08s; }
    .delay-2 { animation-delay:.18s; }
    .delay-3 { animation-delay:.28s; }
    .delay-4 { animation-delay:.38s; }
    .delay-5 { animation-delay:.48s; }

    @keyframes growBar {
      from { width:0%; }
    }
    .progress-fill { animation:growBar 1.6s cubic-bezier(.25,1,.5,1) forwards; }

    /* ── Card hover ── */
    .card-lift { transition:transform .25s ease, box-shadow .25s ease; }
    .card-lift:hover { transform:translateY(-3px); box-shadow:0 20px 40px -10px rgba(0,51,102,.12); }

    /* ── Timeline ── */
    .timeline-track { position:relative; }
    .timeline-track::before {
      content:''; position:absolute; left:.875rem; top:2.2rem;
      width:2px; height:calc(100% - 2.2rem);
      background:linear-gradient(to bottom,#e2e8f0,transparent);
    }
    .dark .timeline-track::before { background:linear-gradient(to bottom,#1e293b,transparent); }

    /* ── Background blobs ── */
    .blob { position:absolute; border-radius:9999px; pointer-events:none; }
    .blob-tr { width:480px; height:480px; top:-120px; right:-120px;
      background:radial-gradient(circle, rgba(37,99,235,.06) 0%,transparent 70%); }
    .blob-bl { width:380px; height:380px; bottom:-80px; left:-80px;
      background:radial-gradient(circle, rgba(0,51,102,.05) 0%,transparent 70%); }

    /* ── Status badges ── */
    .badge-selesai { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
    .badge-proses  { background:#fef9c3; color:#a16207; border:1px solid #fde68a; }
    .badge-belum   { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; }

    /* ── Table row hover ── */
    .finding-row:hover td { background:rgba(37,99,235,.025); }

    /* ── Truncation & expand ── */
    .line-clamp-2 { overflow:hidden; display:-webkit-box;
      -webkit-line-clamp:2; -webkit-box-orient:vertical; }
  </style>
</head>

<body class="font-sans bg-bg-light dark:bg-bg-dark text-slate-900 dark:text-slate-100 transition-colors duration-500 antialiased">

  <div id="scroll-progress" aria-hidden="true"></div>

  {{-- ═══════════════════════ NAVBAR ═══════════════════════ --}}
 <nav class="fixed top-0 inset-x-0 z-50 bg-bg-light/80 dark:bg-bg-dark/80 backdrop-blur-md border-b border-border-light dark:border-border-dark transition-colors duration-300" role="navigation" aria-label="Main navigation">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">

      <!-- Logo -->
      <a href="#" class="flex items-center gap-3" aria-label="SIMANTAB — Beranda">
        <div class="px-2 h-8 bg-primary dark:bg-white rounded-lg flex items-center justify-center" aria-hidden="true">
          <span class="text-white dark:text-black font-bold text-[10px] tracking-widest uppercase">E-AUDIT</span>
        </div>
        <div class="flex flex-col leading-none">
          <span class="font-display font-bold text-sm sm:text-base tracking-tight text-slate-900 dark:text-white">Inspektorat</span>
          <span class="text-[8px] sm:text-[9px] uppercase tracking-[0.15em] text-slate-500 font-semibold">Kab. Rembang</span>
        </div>
      </a>

      <!-- Desktop Nav -->
      <div class="hidden md:flex items-center gap-8 text-sm font-medium">
        <a href="/" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-white transition-colors">Beranda</a>
        <a href="#audit-process" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-white transition-colors">Alur Kerja</a>
        <a href="/tracking" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-white transition-colors">Tracking</a>

        <div class="flex items-center gap-4 pl-4 border-l border-border-light dark:border-border-dark">
          <!-- Dark mode toggle -->
          <button
            onclick="toggleDark()"
            class="p-2 rounded-full border border-border-light dark:border-border-dark hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors"
            aria-label="Toggle dark mode"
          >
            <span class="material-icons-round text-sm block dark:hidden" aria-hidden="true">dark_mode</span>
            <span class="material-icons-round text-sm hidden dark:block" aria-hidden="true">light_mode</span>
          </button>

           <a href="/login" class="bg-blue-600 dark:bg-blue-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700 dark:hover:bg-blue-400 transition-all text-xs tracking-wider uppercase">
            Akses Portal
          </a>
        </div>
      </div>

      <!-- Mobile Controls -->
      <div class="md:hidden flex items-center gap-1">
        <button onclick="toggleDark()" class="p-2 text-slate-600 dark:text-slate-400" aria-label="Toggle dark mode">
          <span class="material-icons-round block dark:hidden" aria-hidden="true">dark_mode</span>
          <span class="material-icons-round hidden dark:block" aria-hidden="true">light_mode</span>
        </button>
        <button onclick="toggleMenu()" class="p-2 text-slate-600 dark:text-slate-400" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu" id="menu-btn">
          <span class="material-icons-round" id="menu-icon" aria-hidden="true">menu</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden md:hidden bg-bg-light dark:bg-bg-dark border-t border-border-light dark:border-border-dark px-4 py-6 space-y-4">
    <a href="#" class="block text-slate-700 dark:text-slate-300 font-semibold">Beranda</a>
    <a href="#audit-process" class="block text-slate-700 dark:text-slate-300 font-semibold">Alur Kerja</a>
    <a href="/tracking" class="block text-slate-700 dark:text-slate-300 font-semibold">Tracking</a>
    <a href="/login" class="block text-center bg-primary dark:bg-white dark:text-black text-white py-3 rounded-lg font-bold">Akses Portal</a>
  </div>
</nav>

  {{-- ═══════════════════════ MAIN ═══════════════════════ --}}
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-24">

    {{-- ── Hero ── --}}
    <div class="text-center mb-14 animate-fadeup delay-1 relative overflow-hidden">
      <div class="blob blob-tr"></div>
      <span class="inline-flex items-center gap-1.5 px-4 py-1.5 mb-6 text-[11px] font-extrabold tracking-[0.18em] text-accent uppercase bg-accent/10 rounded-full border border-accent/20">
        <span class="material-icons-round text-[14px]">public</span>
        Layanan Informasi Publik
      </span>
      <h1 class="font-display text-4xl md:text-6xl font-extrabold text-slate-900 dark:text-white mb-5 tracking-tight leading-[1.08]">
        Lacak Status<br class="hidden sm:block"/> LHP Anda
      </h1>
      <p class="text-base md:text-lg text-slate-500 dark:text-slate-400 max-w-xl mx-auto leading-relaxed font-medium">
        Pantau proses audit secara real-time dengan memasukkan nomor referensi resmi dari Inspektorat.
      </p>
    </div>

    {{-- ── Search Form ── --}}
    <div class="max-w-2xl mx-auto mb-16 animate-fadeup delay-2">
      <form action="{{ route('tracking.public') }}" method="POST">
        @csrf
        <div class="bg-white dark:bg-slate-900 p-1.5 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-800
                    focus-within:ring-2 ring-primary/20 dark:ring-accent/20 transition-all">
          <div class="flex flex-col sm:flex-row gap-2">
            <div class="flex-1 relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="material-icons-round text-slate-400 text-xl">search</span>
              </div>
              <input type="text"
                     name="nomor_lhp"
                     required
                     pattern="[A-Za-z0-9\/\-\.]+"
                     title="Hanya huruf, angka, garis miring (/), dan strip (-)"
                     value="{{ old('nomor_lhp', $search ?? '') }}"
                     autofocus="{{ isset($lhp) ? 'false' : 'true' }}"
                     class="block w-full pl-12 pr-4 py-3.5 bg-transparent border-none focus:ring-0 text-base text-slate-900 dark:text-white placeholder:text-slate-400 font-medium"
                     placeholder="Contoh: LHP/700/012/2024" />
            </div>
            <button type="submit"
              class="bg-primary dark:bg-accent hover:opacity-90 active:scale-[0.98] text-white font-bold py-3 px-7 rounded-xl transition-all flex items-center justify-center gap-2 text-sm shadow-lg shadow-primary/25 whitespace-nowrap">
              <span>Lacak LHP</span>
              <span class="material-icons-round text-lg">arrow_forward</span>
            </button>
          </div>
        </div>
        <p class="text-center text-xs text-slate-400 mt-3">
          Format: <span class="font-mono text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">LHP/700/012/2024</span>
        </p>
      </form>
    </div>

    {{-- ── Flash: Error (nomor tidak ditemukan) ── --}}
    @if(session('error'))
    <div class="max-w-2xl mx-auto mb-10 animate-fadeup delay-2">
      <div class="flex items-start gap-4 p-5 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400">
        <span class="material-icons-round text-2xl flex-shrink-0 mt-0.5">search_off</span>
        <div>
          <p class="font-bold text-sm mb-0.5">LHP Tidak Ditemukan</p>
          <p class="text-sm opacity-80">{{ session('error') }}</p>
        </div>
      </div>
    </div>
    @endif

    {{-- ═══════════════════════ RESULT BLOCK ═══════════════════════ --}}
    @if(isset($lhp) && $lhp)

    {{-- Hitung variabel lokal dari relasi yang sudah di-eager load --}}
    @php
      /* Temuans */
      $temuans        = $lhp->temuans ?? collect();
      $totalTemuan    = $temuans->count();

      /* Rekomendasi: via hasManyThrough $lhp->recommendations (eager loaded di controller via temuans.recommendations) */
      $totalRekomendasi = $temuans->flatMap(fn($t) => $t->recommendations ?? collect())->count();

      /* Progress — gunakan accessor getPersenSelesaiAttribute() dari model Lhp */
      $progress = (int) round($lhp->persen_selesai);

      /* Statistik tambahan (jika relation statistik ter-load) */
      $stat = $lhp->statistik;

      /* Label status */
      $statusSelesai = in_array($lhp->status, ['final','ditandatangani']);

      /* Jenis pemeriksaan */
      $jenisPemeriksaan = $lhp->jenis_pemeriksaan ?? 'Audit Kinerja';

      /* Meta assignment */
      $assignment  = $lhp->auditAssignment;
      $unitDiperiksa = $assignment?->unitDiperiksa?->nama ?? '-';
      $namaProgram   = $assignment?->auditProgram?->nama_program ?? 'Pemeriksaan Belanja Daerah';

      /* Penanggung jawab — dari relasi AuditAssignment.tim (string) atau creator */
      $penanggungJawab = $assignment?->tim ?? $lhp->creator?->name ?? '-';

      /* Tanggal LHP — sudah di-cast ke date di model */
      $tanggalLhp = $lhp->tanggal_lhp?->translatedFormat('d F Y') ?? '-';

      /* Total kerugian — accessor model */
      $totalKerugian = $lhp->total_kerugian;
    @endphp

    {{-- ── Header Card ── --}}
    <div class="bg-white dark:bg-slate-900 rounded-5xl p-8 md:p-10 mb-8 border border-slate-200 dark:border-slate-800
                shadow-2xl shadow-slate-200/40 dark:shadow-none overflow-hidden relative animate-fadeup delay-1">
      <div class="blob blob-tr"></div>
      <div class="blob blob-bl"></div>

      {{-- Breadcrumb --}}
      <nav class="flex items-center gap-1.5 text-xs text-slate-400 font-medium mb-8 relative z-10">
        <a href="{{ url('/') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <a href="{{ route('tracking.public') }}" class="hover:text-primary transition-colors">Tracking</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $lhp->nomor_lhp }}</span>
      </nav>

      <div class="relative z-10">
        {{-- Badges --}}
        <div class="flex flex-wrap items-center gap-3 mb-6">
          <span class="bg-primary/10 text-primary dark:text-blue-400 text-[11px] font-black uppercase tracking-[0.15em] px-5 py-2 rounded-full border border-primary/20">
            {{ $jenisPemeriksaan }}
          </span>

          <div class="flex items-center gap-2.5 px-5 py-2 rounded-full text-[11px] font-black tracking-wider uppercase
            {{ $statusSelesai ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $statusSelesai ? 'bg-emerald-400' : 'bg-amber-400' }} opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $statusSelesai ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
            </span>
            {{ ucfirst(str_replace('_',' ', $lhp->status)) }}
          </div>

          {{-- Semester --}}
          <span class="text-[11px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-4 py-2 rounded-full">
            Semester {{ $lhp->semester }} · {{ $lhp->tanggal_lhp?->year ?? '-' }}
          </span>

          @if($lhp->irban)
          <span class="text-[11px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-4 py-2 rounded-full">
            {{ $lhp->irban }}
          </span>
          @endif
        </div>

        {{-- Nomor & Program --}}
        <h2 class="font-display text-4xl md:text-5xl font-black text-slate-900 dark:text-white mb-3 tracking-tight">
          {{ $lhp->nomor_lhp }}
        </h2>
        <p class="text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl leading-relaxed">
          {{ $namaProgram }}
        </p>
      </div>

      {{-- Meta Grid --}}
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mt-10 pt-8 border-t border-slate-100 dark:border-slate-800 relative z-10">

        <div class="group">
          <div class="flex items-center gap-3 mb-2.5">
            <div class="bg-slate-50 dark:bg-slate-800 p-2.5 rounded-xl text-primary shadow-sm group-hover:scale-110 transition-transform">
              <span class="material-symbols-outlined text-xl">apartment</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Entitas</p>
          </div>
          <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $unitDiperiksa }}</p>
        </div>

        <div class="group">
          <div class="flex items-center gap-3 mb-2.5">
            <div class="bg-slate-50 dark:bg-slate-800 p-2.5 rounded-xl text-primary shadow-sm group-hover:scale-110 transition-transform">
              <span class="material-symbols-outlined text-xl">event_available</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal LHP</p>
          </div>
          <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $tanggalLhp }}</p>
        </div>

        <div class="group">
          <div class="flex items-center gap-3 mb-2.5">
            <div class="bg-slate-50 dark:bg-slate-800 p-2.5 rounded-xl text-primary shadow-sm group-hover:scale-110 transition-transform">
              <span class="material-symbols-outlined text-xl">badge</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Penanggung Jawab</p>
          </div>
          <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $penanggungJawab }}</p>
        </div>

        <div class="group">
          <div class="flex items-center gap-3 mb-2.5">
            <div class="bg-slate-50 dark:bg-slate-800 p-2.5 rounded-xl text-primary shadow-sm group-hover:scale-110 transition-transform">
              <span class="material-symbols-outlined text-xl">shield</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Data</p>
          </div>
          <p class="text-sm font-bold text-emerald-600 flex items-center gap-1">
            <span class="material-icons-round text-[15px]">verified</span>
            Terverifikasi
          </p>
        </div>
      </div>
    </div>

    {{-- ── Grid: Timeline + Stats ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

      {{-- Timeline --}}
      <div class="bg-white dark:bg-slate-900 rounded-4xl p-7 border border-slate-200 dark:border-slate-800 shadow-sm animate-fadeup delay-2">
        <h3 class="text-base font-black text-slate-900 dark:text-white mb-7 flex items-center gap-2.5">
          <span class="material-symbols-outlined text-primary text-xl">route</span>
          Timeline Progres
        </h3>

        @php
          /* Tentukan tahap aktif berdasarkan status LHP */
          $tlPemeriksaan = true;
          $tlDraft       = !in_array($lhp->status, ['draft']);
          $tlFinalisasi  = in_array($lhp->status, ['final','ditandatangani','selesai']);
          $tlTindakLanjut= true; /* selalu aktif jika LHP sudah ada */
        @endphp

        <div class="timeline-track space-y-7 pl-1">

          {{-- Step 1: Pemeriksaan Lapangan --}}
          <div class="relative flex items-start gap-4">
            <div class="mt-0.5 h-7 w-7 rounded-full {{ $tlPemeriksaan ? 'bg-emerald-500 shadow-md shadow-emerald-200' : 'bg-slate-200 dark:bg-slate-700' }} flex items-center justify-center text-white z-10 flex-shrink-0">
              <span class="material-icons-round text-[14px]">check</span>
            </div>
            <div class="pt-0.5">
              <p class="text-sm font-bold text-slate-900 dark:text-white">Pemeriksaan Lapangan</p>
              <p class="text-xs text-slate-400 mt-0.5 font-medium">
                Selesai
                @if($assignment?->tanggal_selesai)
                  · {{ \Carbon\Carbon::parse($assignment->tanggal_selesai)->translatedFormat('d M Y') }}
                @endif
              </p>
            </div>
          </div>

          {{-- Step 2: Draft LHP --}}
          <div class="relative flex items-start gap-4">
            <div class="mt-0.5 h-7 w-7 rounded-full {{ $tlDraft ? 'bg-emerald-500 shadow-md shadow-emerald-200' : 'bg-slate-200 dark:bg-slate-700' }} flex items-center justify-center text-white z-10 flex-shrink-0">
              <span class="material-icons-round text-[14px]">{{ $tlDraft ? 'check' : 'schedule' }}</span>
            </div>
            <div class="pt-0.5">
              <p class="text-sm font-bold text-slate-900 dark:text-white">Draft Laporan (LHP)</p>
              <p class="text-xs {{ $tlDraft ? 'text-slate-400' : 'text-amber-500 font-bold italic' }} mt-0.5 font-medium">
                {{ $tlDraft ? 'Disetujui' : 'Dalam Proses' }}
              </p>
            </div>
          </div>

          {{-- Step 3: Finalisasi --}}
          <div class="relative flex items-start gap-4">
            <div class="mt-0.5 h-7 w-7 rounded-full
              {{ $tlFinalisasi
                  ? 'bg-emerald-500 shadow-md shadow-emerald-200'
                  : 'bg-slate-200 dark:bg-slate-700' }}
              flex items-center justify-center text-white z-10 flex-shrink-0">
              <span class="material-icons-round text-[14px]">{{ $tlFinalisasi ? 'check' : 'schedule' }}</span>
            </div>
            <div class="pt-0.5">
              <p class="text-sm font-bold text-slate-900 dark:text-white">Finalisasi LHP</p>
              <p class="text-xs mt-0.5 font-medium {{ $tlFinalisasi ? 'text-slate-400' : 'text-amber-500 italic font-bold' }}">
                @if($tlFinalisasi)
                  Terbit · {{ $tanggalLhp }}
                @else
                  Menunggu Finalisasi
                @endif
              </p>
            </div>
          </div>

          {{-- Step 4: Tindak Lanjut --}}
          <div class="relative flex items-start gap-4">
            <div class="mt-0.5 h-7 w-7 rounded-full
              {{ $statusSelesai
                  ? 'bg-emerald-500 shadow-md shadow-emerald-200'
                  : 'bg-amber-500 shadow-md shadow-amber-200 ring-4 ring-amber-50 dark:ring-amber-900/30' }}
              flex items-center justify-center text-white z-10 flex-shrink-0">
              <span class="material-icons-round text-[14px]">{{ $statusSelesai ? 'check' : 'sync' }}</span>
            </div>
            <div class="pt-0.5">
              <p class="text-sm font-bold text-slate-900 dark:text-white">Pemantauan Tindak Lanjut</p>
              <p class="text-xs mt-0.5 font-bold {{ $statusSelesai ? 'text-emerald-500' : 'text-amber-500 italic' }}">
                {{ $statusSelesai ? 'Selesai' : 'Sedang Berlangsung' }}
              </p>
            </div>
          </div>

        </div>
      </div>

      {{-- Stats Cards --}}
      <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4 content-start animate-fadeup delay-3">

        {{-- Total Temuan --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-7 rounded-4xl shadow-sm card-lift">
          <div class="flex justify-between items-start mb-5">
            <div class="p-3.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-2xl">
              <span class="material-symbols-outlined text-2xl">fact_check</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Findings</span>
          </div>
          <p class="text-5xl font-black text-slate-900 dark:text-white mb-1">{{ $totalTemuan }}</p>
          <p class="text-xs font-bold text-slate-500 uppercase tracking-tight">Total Temuan Audit</p>
          @if($totalKerugian > 0)
          <p class="text-[11px] text-rose-500 font-semibold mt-2">
            Kerugian: Rp {{ number_format($totalKerugian, 0, ',', '.') }}
          </p>
          @endif
        </div>

        {{-- Total Rekomendasi --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-7 rounded-4xl shadow-sm card-lift">
          <div class="flex justify-between items-start mb-5">
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-2xl">
              <span class="material-symbols-outlined text-2xl">tips_and_updates</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Suggestions</span>
          </div>
          <p class="text-5xl font-black text-slate-900 dark:text-white mb-1">{{ $totalRekomendasi }}</p>
          <p class="text-xs font-bold text-slate-500 uppercase tracking-tight">Rekomendasi Diterbitkan</p>
        </div>

        {{-- Progres Tindak Lanjut --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-7 rounded-4xl shadow-sm card-lift">
          <div class="flex justify-between items-start mb-5">
            <div class="p-3.5 bg-slate-50 dark:bg-slate-800 text-slate-600 rounded-2xl">
              <span class="material-symbols-outlined text-2xl">donut_large</span>
            </div>
            <span class="text-xs font-black text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1 rounded-lg">
              {{ $progress }}%
            </span>
          </div>
          <p class="text-5xl font-black text-slate-900 dark:text-white mb-4">{{ $progress }}%</p>
          <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
            <div class="bg-primary dark:bg-accent h-full rounded-full progress-fill shadow-[0_0_12px_rgba(0,51,102,.3)]"
                 style="width:{{ $progress }}%"></div>
          </div>
          <p class="text-xs font-bold text-slate-500 uppercase mt-3 tracking-tight">Penyelesaian Tindak Lanjut</p>
        </div>

      </div>
    </div>

    {{-- ── Findings Table ── --}}
    <div class="bg-white dark:bg-slate-900 rounded-5xl border border-slate-200 dark:border-slate-800
                shadow-2xl shadow-slate-200/30 dark:shadow-none overflow-hidden mb-12 animate-fadeup delay-4">

      {{-- Table header --}}
      <div class="px-8 md:px-10 py-7 border-b border-slate-100 dark:border-slate-800
                  flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4
                  bg-slate-50/40 dark:bg-slate-800/30">
        <h4 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2.5">
          <span class="material-symbols-outlined text-primary text-xl">format_list_bulleted</span>
          Daftar Temuan &amp; Rekomendasi
          <span class="ml-1 text-[11px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full">
            {{ $totalTemuan }}
          </span>
        </h4>
        <div class="flex items-center gap-2">
          <span class="text-xs text-slate-400 font-medium hidden sm:block">
            Menampilkan {{ $temuans->count() }} temuan
          </span>
        </div>
      </div>

      {{-- Table body --}}
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/80">
              <th class="px-8 md:px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-20">#</th>
              <th class="px-4 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Uraian Temuan</th>
              <th class="px-4 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Rekomendasi Pertama</th>
              <th class="px-4 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Nilai Temuan</th>
              <th class="px-8 md:px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status TL</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

            @forelse($temuans as $index => $temuan)
            @php
              /* status_tl dari model Temuan */
              $statusTl  = $temuan->status_tl ?? 'belum_ditindaklanjuti';
              $badgeCls  = match($statusTl) {
                'selesai'               => 'badge-selesai',
                'dalam_proses'          => 'badge-proses',
                default                 => 'badge-belum',
              };
              $badgeLabel = match($statusTl) {
                'selesai'               => 'Selesai',
                'dalam_proses'          => 'Dalam Proses',
                default                 => 'Belum Ditindaklanjuti',
              };
              /* Rekomendasi pertama untuk temuan ini */
              $rekoPertama = $temuan->recommendations?->first()?->uraian
                          ?? $temuan->recommendations?->first()?->kondisi
                          ?? null;
              /* Nilai temuan */
              $nilaiTemuan = $temuan->nilai_temuan ?? 0;
              /* Kode temuan */
              $kode = $temuan->kodeTemuan?->kode ?? null;
            @endphp
            <tr class="finding-row group transition-all">

              {{-- No --}}
              <td class="px-8 md:px-10 py-7">
                <div class="flex flex-col gap-1.5">
                  <span class="inline-block px-3 py-1 bg-primary/5 text-primary dark:text-blue-400 text-[10px] font-black rounded-lg tracking-wider">
                    T.{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                  </span>
                  @if($kode)
                  <span class="text-[10px] text-slate-400 font-mono">{{ $kode }}</span>
                  @endif
                </div>
              </td>

              {{-- Uraian --}}
              <td class="px-4 py-7 max-w-xs">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 leading-relaxed line-clamp-2"
                   title="{{ $temuan->kondisi }}">
                  {{ $temuan->kondisi ?? '-' }}
                </p>
              </td>

              {{-- Rekomendasi --}}
              <td class="px-4 py-7 max-w-xs">
                @if($rekoPertama)
                <div class="relative pl-5 before:absolute before:left-0 before:top-0 before:bottom-0 before:w-[3px] before:bg-slate-100 dark:before:bg-slate-700 before:rounded-full">
                  <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed italic line-clamp-2"
                     title="{{ $rekoPertama }}">
                    "{{ $rekoPertama }}"
                  </p>
                  @if($temuan->recommendations?->count() > 1)
                  <span class="text-[10px] font-bold text-primary dark:text-accent mt-1 inline-block">
                    +{{ $temuan->recommendations->count() - 1 }} rekomendasi lainnya
                  </span>
                  @endif
                </div>
                @else
                <p class="text-sm text-slate-400 italic">Menunggu entry rekomendasi...</p>
                @endif
              </td>

              {{-- Nilai Temuan --}}
              <td class="px-4 py-7 text-right whitespace-nowrap">
                @if($nilaiTemuan > 0)
                <p class="text-sm font-bold text-rose-600 dark:text-rose-400">
                  Rp {{ number_format($nilaiTemuan, 0, ',', '.') }}
                </p>
                @else
                <p class="text-sm text-slate-400">—</p>
                @endif
              </td>

              {{-- Status TL --}}
              <td class="px-8 md:px-10 py-7 text-center">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm {{ $badgeCls }}">
                  {{ $badgeLabel }}
                </span>
              </td>

            </tr>
            @empty
            <tr>
              <td colspan="5" class="px-10 py-20 text-center">
                <div class="flex flex-col items-center justify-center text-slate-300 dark:text-slate-600">
                  <span class="material-symbols-outlined text-7xl mb-4">inventory_2</span>
                  <p class="text-base font-bold">Data Temuan Tidak Ditemukan</p>
                  <p class="text-sm mt-1">Silakan hubungi administrator untuk detail lebih lanjut.</p>
                </div>
              </td>
            </tr>
            @endforelse

          </tbody>
        </table>
      </div>

      {{-- Table footer --}}
      <div class="px-8 md:px-10 py-6 bg-slate-50/30 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800
                  flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[14px]">verified</span>
          Database Inspektorat Kabupaten Rembang — {{ now()->year }}
        </p>
        @if($totalTemuan > 0 && $stat)
        <div class="flex items-center gap-4 text-xs text-slate-400">
          <span>Selesai: <strong class="text-emerald-600">{{ $stat->temuan_selesai ?? 0 }}</strong></span>
          <span>Proses: <strong class="text-amber-600">{{ $stat->temuan_proses ?? 0 }}</strong></span>
          <span>Belum: <strong class="text-rose-600">{{ $stat->temuan_belum ?? 0 }}</strong></span>
        </div>
        @endif
      </div>
    </div>

    {{-- ── Catatan Umum (jika ada) ── --}}
    @if($lhp->catatan_umum)
    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-4xl p-7 mb-8 animate-fadeup delay-4">
      <h5 class="font-bold text-amber-800 dark:text-amber-300 mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">sticky_note_2</span>
        Catatan Umum
      </h5>
      <p class="text-sm text-amber-700 dark:text-amber-400 leading-relaxed">{{ $lhp->catatan_umum }}</p>
    </div>
    @endif

    {{-- ── Help Banner ── --}}
    <div class="bg-slate-900 dark:bg-slate-950 rounded-4xl p-8 md:p-10 text-white relative overflow-hidden shadow-xl animate-fadeup delay-5">
      <div class="absolute -top-20 -right-20 w-56 h-56 bg-accent/10 blur-[70px] rounded-full pointer-events-none"></div>
      <div class="absolute -bottom-20 -left-20 w-56 h-56 bg-primary/20 blur-[70px] rounded-full pointer-events-none"></div>
      <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="flex items-center gap-5 text-center md:text-left">
          <div class="bg-white/10 text-amber-300 p-4 rounded-2xl flex-shrink-0">
            <span class="material-symbols-outlined text-3xl">help_outline</span>
          </div>
          <div>
            <h5 class="font-display font-bold text-white text-lg mb-1">Butuh bantuan terkait LHP ini?</h5>
            <p class="text-sm text-slate-400">Hubungi Inspektorat Daerah Kabupaten Rembang atau gunakan fitur helpdesk.</p>
          </div>
        </div>
        <div class="flex gap-3 flex-shrink-0">
          <a href="#" class="px-6 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold hover:bg-white/20 transition-all">
            Pelajari Panduan
          </a>
          <a href="mailto:inspektorat@rembangkab.go.id" class="px-6 py-2.5 bg-accent text-white rounded-xl text-sm font-bold shadow-lg shadow-accent/30 hover:opacity-90 transition-all">
            Hubungi Admin
          </a>
        </div>
      </div>
    </div>

    @endif {{-- /isset($lhp) --}}

    {{-- ═══════════════════════ HOW TO SECTION ═══════════════════════ --}}
    {{-- Tampil hanya jika belum ada hasil pencarian --}}
    @if(!isset($lhp) || !$lhp)
    <section class="relative animate-fadeup delay-4 mt-4">
      <div class="bg-slate-900 dark:bg-slate-950 rounded-4xl p-8 md:p-12 text-white relative overflow-hidden shadow-xl">
        <div class="absolute -top-16 -right-16 w-48 h-48 bg-amber-500/10 blur-[60px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-slate-700/30 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10">
          <h3 class="text-xl md:text-2xl font-extrabold text-center mb-12 tracking-tight">Bagaimana Cara Melacak?</h3>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div class="hidden md:block absolute top-8 left-[16%] right-[16%] h-[1px] border-t border-dashed border-white/10"></div>

            @foreach([
              ['edit_note',     '1. Input Nomor',     'Masukkan nomor referensi LHP resmi dari Inspektorat.'],
              ['verified_user', '2. Validasi Sistem',  'Verifikasi otomatis pada database pusat secara aman.'],
              ['task_alt',      '3. Lihat Status',     'Dapatkan progres pemeriksaan secara mendetail.'],
            ] as [$icon, $title, $desc])
            <div class="flex flex-col items-center text-center group">
              <div class="w-16 h-16 bg-white/5 backdrop-blur-lg rounded-2xl flex items-center justify-center text-amber-400 mb-5 border border-white/10 group-hover:bg-amber-500 group-hover:text-slate-950 transition-all duration-300 shadow-lg">
                <span class="material-icons-round text-2xl">{{ $icon }}</span>
              </div>
              <h4 class="font-bold text-base mb-2">{{ $title }}</h4>
              <p class="text-xs text-slate-400 max-w-[180px] leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </section>
    @endif

  </main>

  {{-- ═══════════════════════ FOOTER ═══════════════════════ --}}
  <footer class="py-16 bg-white dark:bg-bg-dark border-t border-slate-100 dark:border-border-dark transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid md:grid-cols-4 gap-10 mb-14">

        <div class="col-span-2">
          <div class="flex items-center gap-3 mb-5">
            <img src="{{ asset('images/Simantab.png') }}" alt="Logo SIMANTAB" class="h-14 w-auto object-contain" onerror="this.style.display='none';">
          </div>
          <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm leading-relaxed">
            Standar profesional untuk audit digital dan manajemen integritas data di lingkungan Pemerintah Kabupaten Rembang.
          </p>
        </div>

        <nav aria-label="Layanan">
          <h4 class="font-bold text-slate-900 dark:text-white mb-5 text-xs uppercase tracking-widest">Layanan</h4>
          <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
            <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">E-Planning</a></li>
            <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">E-Execution</a></li>
            <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">E-Reporting</a></li>
          </ul>
        </nav>

        <nav aria-label="Tautan cepat">
          <h4 class="font-bold text-slate-900 dark:text-white mb-5 text-xs uppercase tracking-widest">Tautan Cepat</h4>
          <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
            <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Profil Instansi</a></li>
            <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Regulasi &amp; Hukum</a></li>
            <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Pusat Bantuan</a></li>
          </ul>
        </nav>
      </div>

      <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-slate-100 dark:border-border-dark gap-4 text-xs text-slate-400 dark:text-slate-500">
        <p>© {{ now()->year }} Inspektorat Kabupaten Rembang. Semua hak dilindungi.</p>
        <nav class="flex gap-6" aria-label="Legal links">
          <a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Kebijakan Privasi</a>
          <a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Syarat &amp; Ketentuan</a>
          <a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Kontak Kami</a>
        </nav>
      </div>
    </div>
  </footer>

  {{-- ═══════════════════════ SCRIPTS ═══════════════════════ --}}
  <script>
    /* ── Dark Mode ── */
    (function () {
      const saved = localStorage.getItem('theme');
      if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches))
        document.documentElement.classList.add('dark');
    })();
    function toggleDark() {
      const html = document.documentElement;
      html.classList.toggle('dark');
      localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    }
    function toggleMenu() {
      const menu = document.getElementById('mobile-menu');
      const icon = document.getElementById('menu-icon');
      const open = !menu.classList.toggle('hidden');
      icon.textContent = open ? 'close' : 'menu';
    }

    /* ── Scroll Progress ── */
    window.addEventListener('scroll', function () {
      const s = document.documentElement;
      document.getElementById('scroll-progress').style.width =
        (s.scrollTop / (s.scrollHeight - s.clientHeight) * 100) + '%';
    }, { passive: true });

    /* ── Auto scroll ke hasil jika ada LHP ── */
    @if(isset($lhp) && $lhp)
    document.addEventListener('DOMContentLoaded', function () {
      const result = document.querySelector('.animate-fadeup.delay-1');
      if (result) setTimeout(() => result.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
    });
    @endif
  </script>
</body>
</html>