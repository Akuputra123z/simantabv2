<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">

    <title>Verifikasi Alamat Email · SIAPTUNTAS</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta name="theme-color" content="#0F2A5C">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIAPTUNTAS">

    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap"
    >

    <style>

        /* =========================================================
           ROOT
        ========================================================= */

        :root {
            --navy: #0F2A5C;
            --blue: #1E4FA8;
            --green: #2F8F3E;
            --gold: #E8B93A;
            --amber: #D97706;

            --bg: #F5F6F2;
            --panel: #FFFFFF;
            --ink: #14213D;
            --muted: #5B667A;
            --line: #D9DDE5;
            --field: #FFFFFF;
            --err: #B3261E;

            --display: "Bricolage Grotesque", "Segoe UI", system-ui, sans-serif;
            --body: "Figtree", "Segoe UI", system-ui, sans-serif;

            box-sizing: border-box;

            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-padding-top: env(safe-area-inset-top, 0px);
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);

            font-family: var(--body);
            font-size: 16px;
            line-height: 1.5;

            min-height: 100vh;

            display: grid;
            grid-template-columns: minmax(0, 5fr) minmax(0, 6fr);
        }

        a {
            color: inherit;
        }

        :focus-visible {
            outline: 3px solid var(--gold);
            outline-offset: 2px;
        }


        /* =========================================================
           DESKTOP LAYOUT
        ========================================================= */

        @media (min-width: 901px) {

            .logo {
                transform: translateY(-30px);
            }

            body {
                height: 100vh;
                min-height: 640px;
                overflow: hidden;

                --content-top: clamp(
                    20px,
                    calc(50vh - 290px),
                    90px
                );
            }

            .track-content {
                margin-top: calc(34px + var(--content-top));
                margin-bottom: auto;
            }

            main {
                margin-top: var(--content-top);
                margin-bottom: auto;
            }

        }


        /* =========================================================
           PANEL KIRI
        ========================================================= */

        .track {
            position: relative;
            height: 100%;

            background:
                linear-gradient(
                    160deg,
                    #E8F1FA 0%,
                    #F1F7F3 100%
                );

            color: var(--navy);
            padding: 24px clamp(28px, 4vw, 56px);

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;

            overflow: hidden;
            text-align: center;
        }

        .track > * {
            position: relative;
            z-index: 1;
        }

        .track-back-btn {
            position: absolute;
            top: 24px;
            left: clamp(20px, 3.5vw, 48px);
            z-index: 10;

            display: inline-flex;
            align-items: center;
            gap: 8px;

            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(15, 42, 92, 0.16);
            color: #0F2A5C;
            border-radius: 999px;

            padding: 0 16px;
            height: 34px;
            font: 600 13px var(--body);
            text-decoration: none;
            line-height: 1;
            cursor: pointer;

            box-shadow: 0 1px 3px rgba(15, 42, 92, 0.06);
            transition: all .18s ease;
        }

        .track-back-btn:hover {
            background: #FFFFFF;
            border-color: rgba(15, 42, 92, 0.32);
            color: #081B3A;
            box-shadow: 0 3px 8px rgba(15, 42, 92, 0.12);
            transform: translateX(-2px);
        }

        .track-back-btn svg {
            transition: transform .18s ease;
        }

        .track-back-btn:hover svg {
            transform: translateX(-2px);
        }


        /* =========================================================
           KONTEN KIRI
        ========================================================= */

        .track-content {
            width: 100%;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .center {
            width: 100%;
            max-width: 500px;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            text-align: center;
            gap: 14px;
        }

        .logo {
            display: block;
            width: 440px;
            max-width: 90%;
            height: auto;
            max-height: 290px;
            object-fit: contain;
            margin: 0 auto;

            filter: drop-shadow(0 10px 20px rgba(15, 42, 92, 0.08));
            transition: transform .3s ease;
        }

        .center h1 {
            margin: 0;
            font-family: var(--display);
            font-weight: 800;
            font-size: clamp(22px, 2.2vw, 28px);
            line-height: 1.2;
            letter-spacing: -.02em;
            color: var(--navy);
        }

        .center > p {
            max-width: 38ch;
            margin: 0 auto;
            color: #4A5A78;
            font-size: 14px;
            line-height: 1.45;
        }

        .stages {
            list-style: none;
            padding: 0;
            margin: 2px 0 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            width: 100%;
            max-width: 390px;
        }

        .stages li {
            position: relative;
            padding-top: 26px;
            font-size: 13px;
            font-weight: 600;
            color: #556987;
            letter-spacing: -.01em;
        }

        .stages li:not(:last-child)::before {
            content: "";
            position: absolute;
            top: 7px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #B7C6DE;
        }

        .stages i {
            position: absolute;
            z-index: 1;
            top: 0;
            left: 50%;
            margin-left: -8px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #7C93BC;
            background: #EAF2FA;
            box-shadow: 0 0 0 3px rgba(234, 242, 250, .8);
            transition: all .2s ease;
        }

        .stages li:last-child i {
            background: var(--green);
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(47, 143, 62, .2);
        }

        .stages li:last-child i::after {
            content: "";
            position: absolute;
            left: 4px;
            top: 1px;
            width: 4px;
            height: 8px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .stages li:last-child {
            color: var(--green);
            font-weight: 700;
        }


        /* =========================================================
           PANEL KANAN
        ========================================================= */

        .form-side {
            height: 100%;
            background: var(--panel);
            display: flex;
            flex-direction: column;
            padding: 24px clamp(28px, 4vw, 56px);
            border-left: 1px solid var(--line);
            overflow-y: auto;
        }

        .top {
            height: 34px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-shrink: 0;
        }

        .theme {
            background: none;
            border: 1px solid var(--line);
            color: var(--muted);
            border-radius: 999px;
            padding: 6px 14px;
            font: 500 13px var(--body);
            cursor: pointer;
            transition: all .15s;
            line-height: 1.4;
        }

        .theme:hover {
            color: var(--ink);
            border-color: var(--muted);
        }

        main {
            width: 100%;
            max-width: 440px;
            margin: var(--content-top) auto auto;
            padding: 0 0 20px;
        }

        .badge-header {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(30, 79, 168, 0.08);
            color: var(--blue);
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        h2 {
            margin: 0 0 8px;
            font-family: var(--display);
            font-weight: 700;
            font-size: 28px;
            letter-spacing: -.015em;
            line-height: 1.15;
        }

        .lead {
            color: var(--muted);
            margin: 0 0 20px;
            font-size: 15px;
            line-height: 1.5;
        }


        /* =========================================================
           KARTU AKUN / EMAIL USER (VERIFICATION SPECIFIC)
        ========================================================= */

        .account-card {
            background: #F8FAFC;
            border: 1.5px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            transition: all .2s ease;
        }

        .account-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #E8F1FA;
            color: var(--blue);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .account-info {
            flex: 1;
            min-width: 0;
        }

        .account-name {
            display: block;
            font-weight: 700;
            font-size: 14px;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .account-email {
            display: block;
            font-size: 13px;
            color: var(--muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #FDE68A;
            flex-shrink: 0;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #D97706;
            animation: pulse-dot 1.8s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        /* INSTRUCTION STEPS */
        .instruction-box {
            background: #F1F5F9;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
            font-size: 13.5px;
            color: var(--muted);
            line-height: 1.5;
        }

        .instruction-box strong {
            color: var(--ink);
        }

        .instruction-list {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .instruction-list li {
            margin-bottom: 4px;
        }


        /* =========================================================
           ALERTS
        ========================================================= */

        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.45;
        }

        .alert-ok {
            background: #DEF7EC;
            border: 1px solid #BCF0DA;
            color: var(--green);
        }

        .alert-err {
            background: #FDF2F2;
            border: 1px solid #F8B4B4;
            color: var(--err);
        }

        .alert svg {
            flex-shrink: 0;
            margin-top: 2px;
        }


        /* =========================================================
           BUTTONS & ACTIONS
        ========================================================= */

        .go {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 10px;
            background: var(--blue);
            color: #fff;
            font: 700 15px var(--body);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            box-shadow: 0 2px 4px rgba(30, 79, 168, 0.2);
            transition: background .15s, transform .1s, opacity .15s;
        }

        .go:hover:not([disabled]) {
            background: color-mix(in srgb, var(--blue) 86%, #000);
            box-shadow: 0 4px 10px rgba(30, 79, 168, 0.28);
        }

        .go:active:not([disabled]) {
            transform: translateY(1px);
        }

        .go[disabled] {
            opacity: .7;
            cursor: not-allowed;
            background: #64748B;
            box-shadow: none;
        }

        .btn-link-action {
            background: none;
            border: none;
            padding: 0;
            color: #0284c7;
            font: 600 13px var(--body);
            cursor: pointer;
            text-decoration: none;
            transition: color .15s;
            margin-left: 4px;
        }

        .btn-link-action:hover {
            text-decoration: underline;
        }

        .activation-link-wrapper {
            margin-top: 18px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        .note {
            margin-top: 22px;
            font-size: 12.5px;
            color: var(--muted);
            text-align: center;
            line-height: 1.5;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .bottom {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--muted);
            border-top: 1px solid var(--line);
            padding-top: 16px;
        }

        .bottom a {
            font-weight: 600;
            color: var(--ink);
            text-decoration: none;
        }

        .bottom a:hover {
            text-decoration: underline;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }


        /* =========================================================
           DARK MODE
        ========================================================= */

        :root[data-theme="dark"] {
            --bg: #0F2A5C;
            --panel: #0F2A5C;
            --ink: #FFFFFF;
            --muted: #D6E0F0;
            --line: rgba(255, 255, 255, .20);
            --field: #FFFFFF;
            --blue: #5B8CE6;
            --err: #FF8A80;
        }

        :root[data-theme="dark"] .track {
            background:
                linear-gradient(
                    160deg,
                    #E8F1FA 0%,
                    #F1F7F3 100%
                );
            color: #0F2A5C;
        }

        :root[data-theme="dark"] .account-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
        }

        :root[data-theme="dark"] .account-icon {
            background: rgba(91, 140, 230, 0.2);
            color: #93C5FD;
        }

        :root[data-theme="dark"] .account-name {
            color: #FFFFFF;
        }

        :root[data-theme="dark"] .account-email {
            color: #CBD5E1;
        }

        :root[data-theme="dark"] .instruction-box {
            background: rgba(255, 255, 255, 0.06);
            color: #CBD5E1;
        }

        :root[data-theme="dark"] .instruction-box strong {
            color: #FFFFFF;
        }

        :root[data-theme="dark"] .badge-header {
            background: rgba(91, 140, 230, 0.18);
            color: #93C5FD;
        }

        :root[data-theme="dark"] .status-pill {
            background: rgba(217, 119, 6, 0.2);
            color: #FCD34D;
            border-color: rgba(217, 119, 6, 0.35);
        }

        :root[data-theme="dark"] .status-dot {
            background: #F59E0B;
        }

        :root[data-theme="dark"] .btn-link-action {
            color: #38bdf8;
        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 900px) {
            body {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                overflow-y: auto;
            }

            .track {
                min-height: 380px;
                padding: 40px 24px 28px;
            }

            .track-back-btn {
                top: 16px;
                left: 16px;
            }

            .logo {
                width: 240px;
                transform: none;
            }

            .form-side {
                border-left: none;
                border-top: 1px solid var(--line);
                padding: 28px 20px 32px;
                height: auto;
                overflow: visible;
            }

            main {
                margin-top: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }

    </style>
</head>


<body>

    <!-- =========================================================
         PANEL KIRI
    ========================================================== -->

    <section class="track" aria-label="Tentang SIAPTUNTAS">

        <!-- TOMBOL KELUAR (POJOK KIRI ATAS SCREEN KIRI) -->
        <button
            type="button"
            class="track-back-btn"
            title="Keluar dari Akun"
            aria-label="Keluar dari akun"
            onclick="document.getElementById('logout-form').submit();"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Keluar</span>
        </button>

        <div class="track-content">

            <div class="center">

                <img
                    class="logo"
                    src="{{ asset('images/logo/coba.png') }}"
                    alt="Logo SIAPTUNTAS"
                >

                <h1>
                    Tindak Lanjut Tuntas, Tata Kelola Berkualitas
                </h1>

                <p>
                    Sistem pemantauan tindak lanjut hasil pengawasan
                    Inspektorat Daerah Kabupaten Rembang.
                </p>

            </div>

            <ol class="stages">
                <li>
                    <i></i>
                    Temuan
                </li>
                <li>
                    <i></i>
                    Rekomendasi
                </li>
                <li>
                    <i></i>
                    Tindak lanjut
                </li>
                <li>
                    <i></i>
                    Tuntas
                </li>
            </ol>

        </div>

    </section>


    <!-- =========================================================
         PANEL KANAN
    ========================================================== -->

    <section class="form-side">

        <!-- THEME -->
        <div class="top">
            <button
                class="theme"
                id="theme"
                type="button"
                aria-label="Ganti tema"
            >
                Tema: Terang
            </button>
        </div>


        <!-- FORM CONTENT -->
        <main>

            <span class="badge-header">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Perlindungan Akun
            </span>

            <h2>
                Verifikasi Email Anda
            </h2>

            <p class="lead">
                Satu langkah lagi sebelum Anda dapat mengakses sistem. Silakan verifikasi alamat email dinas Anda untuk memastikan keamanan akun.
            </p>

            {{-- KARTU INFO AKUN TERDAFTAR --}}
            <div class="account-card">
                <div class="account-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <div class="account-info">
                    <span class="account-name">{{ auth()->user()?->name ?? 'Pengguna' }}</span>
                    <span class="account-email" title="{{ auth()->user()?->email }}">{{ auth()->user()?->email }}</span>
                </div>
                <span class="status-pill" title="Status verifikasi email">
                    <span class="status-dot"></span>
                    Menunggu Verifikasi
                </span>
            </div>

            {{-- ALERT NOTIFIKASI --}}
            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-ok" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <div>
                        <strong>Tautan Verifikasi Terkirim!</strong>
                        <div style="font-size: 13px; margin-top: 2px;">
                            Tautan verifikasi baru telah dikirim ke alamat email <strong>{{ auth()->user()?->email }}</strong>. Silakan periksa Kotak Masuk (Inbox) atau folder Spam Anda.
                        </div>
                    </div>
                </div>
            @elseif (session('status'))
                <div class="alert alert-ok" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-err" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- INSTRUKSI LANGKAH --}}
            <div class="instruction-box">
                <strong>Langkah Verifikasi:</strong>
                <ol class="instruction-list">
                    <li>Buka aplikasi email Anda pada alamat email di atas.</li>
                    <li>Cari pesan dari <strong>SIAPTUNTAS Inspektorat Rembang</strong>.</li>
                    <li>Klik tautan atau tombol <strong>"Verifikasi Alamat Email"</strong> di dalam email tersebut.</li>
                </ol>
            </div>

            <!-- FORM RESEND VERIFICATION EMAIL -->
            <form id="resend-form" method="POST" action="{{ route('verification.store') }}">
                @csrf

                <button class="go" id="resend-btn" type="submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <span id="resend-btn-text">Kirim Ulang Email Verifikasi</span>
                </button>
            </form>

            <!-- ACTION LOGOUT / GANTI AKUN -->
            <div class="activation-link-wrapper">
                <span>Bukan akun Anda atau ingin menggunakan akun lain?</span>
                <button type="button" class="btn-link-action" onclick="document.getElementById('logout-form').submit();">
                    Keluar & Ganti Akun
                </button>
            </div>

            <!-- HIDDEN LOGOUT FORM -->
            <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                @csrf
            </form>

            <p class="note">
                Belum menerima email dalam beberapa menit? Periksa folder Spam atau Hubungi Tim Helpdesk Inspektorat Daerah Kabupaten Rembang.
            </p>

        </main>


        <!-- FOOTER -->
        <div class="bottom">
            <span>
                &copy; {{ date('Y') }}
                Inspektorat Daerah Kabupaten Rembang
            </span>

            <a href="mailto:inspektorat@rembangkab.go.id">
                Butuh bantuan?
            </a>
        </div>

    </section>


    <!-- =========================================================
         JAVASCRIPT
    ========================================================== -->

    <script>

        (function () {

            /* =====================================================
               THEME MANAGEMENT
            ===================================================== */

            var root = document.documentElement;
            var tb = document.getElementById('theme');

            function isDarkTheme() {
                return root.getAttribute('data-theme') === 'dark';
            }

            function updateThemeButton() {
                var dark = isDarkTheme();
                if (tb) {
                    tb.textContent = 'Tema: ' + (dark ? 'Gelap' : 'Terang');
                }
            }

            try {
                var savedTheme = localStorage.getItem('st-theme') || localStorage.getItem('theme');
                if (savedTheme === 'dark' || savedTheme === 'light') {
                    root.setAttribute('data-theme', savedTheme);
                } else {
                    root.setAttribute('data-theme', 'light');
                }
            } catch (e) {
                root.setAttribute('data-theme', 'light');
            }

            updateThemeButton();

            if (tb) {
                tb.onclick = function () {
                    var next = isDarkTheme() ? 'light' : 'dark';
                    root.setAttribute('data-theme', next);

                    try {
                        localStorage.setItem('st-theme', next);
                        localStorage.setItem('theme', next);
                    } catch (e) {}

                    updateThemeButton();
                };
            }


            /* =====================================================
               RESEND BUTTON COOLDOWN / COUNTDOWN
            ===================================================== */

            var form = document.getElementById('resend-form');
            var btn = document.getElementById('resend-btn');
            var btnText = document.getElementById('resend-btn-text');
            var justSent = @json(session('status') == 'verification-link-sent');

            function startCooldown(seconds) {
                if (!btn || !btnText) return;

                btn.disabled = true;
                var remaining = seconds;

                var timer = setInterval(function () {
                    remaining--;
                    if (remaining > 0) {
                        btnText.textContent = 'Kirim Ulang (' + remaining + 's)';
                    } else {
                        clearInterval(timer);
                        btn.disabled = false;
                        btnText.textContent = 'Kirim Ulang Email Verifikasi';
                    }
                }, 1000);

                btnText.textContent = 'Kirim Ulang (' + remaining + 's)';
            }

            // Jika baru saja terkirim dari server, aktifkan countdown 60 detik
            if (justSent) {
                startCooldown(60);
            }

            if (form) {
                form.onsubmit = function () {
                    if (btn && !btn.disabled) {
                        btn.disabled = true;
                        if (btnText) {
                            btnText.innerHTML =
                                '<svg class="spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
                                '<circle cx="12" cy="12" r="10" stroke-opacity=".25"/>' +
                                '<path d="M12 2a10 10 0 0 1 10 10"/>' +
                                '</svg> Mengirim tautan verifikasi...';
                        }
                    }
                    return true;
                };
            }

        })();

        /* =========================================================
           PWA SERVICE WORKER
        ========================================================= */

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js')
                    .then(function (reg) {
                        console.log('PWA ServiceWorker registered:', reg.scope);
                    })
                    .catch(function (err) {
                        console.warn('PWA ServiceWorker registration failed:', err);
                    });
            });
        }

    </script>

</body>

</html>
