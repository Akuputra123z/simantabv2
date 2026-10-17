<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">

    <title>Verifikasi & Aktivasi Akun · SIAPTUNTAS</title>

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

                /*
                 * SATU PATOKAN POSISI UNTUK KIRI DAN KANAN
                 */
                --content-top: clamp(
                    20px,
                    calc(50vh - 280px),
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

       .lanes,
.finish {
        display: none;

}
.lanes {
    z-index: 0;
}

        .lanes path {
            fill: none;
            stroke: #0F2A5C;
            stroke-opacity: .07;
            stroke-width: 1.5;
        }

        .track > * {
            position: relative;
            z-index: 1;
        }

        /* TOMBOL KEMBALI DI SCREEN KIRI (ATAS KIRI) */
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


        /* =========================================================
           LOGO
        ========================================================= */

        .logo {
            display: block;

            width: 440px;
            max-width: 90%;

            height: auto;
            max-height: 290px;

            object-fit: contain;

            margin: 0 auto;

            filter:
                drop-shadow(
                    0 10px 20px rgba(15, 42, 92, 0.08)
                );

            transition: transform .3s ease;
        }

        


        /* =========================================================
           TEKS PANEL KIRI
        ========================================================= */

        .center h1 {
            margin: 0;

            font-family: var(--display);

            font-weight: 800;

            font-size: clamp(
                22px,
                2.2vw,
                28px
            );

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


        /* =========================================================
           TAHAPAN
        ========================================================= */

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

            box-shadow:
                0 0 0 3px rgba(234, 242, 250, .8);

            transition: all .2s ease;
        }

        .stages li:last-child i {
            background: var(--green);
            border-color: var(--green);

            box-shadow:
                0 0 0 3px rgba(47, 143, 62, .2);
        }

        .stages li:last-child i::after {
            content: "";

            position: absolute;

            left: 4px;
            top: 1px;

            width: 4px;
            height: 8px;

            border:
                solid #fff;

            border-width:
                0 2px 2px 0;

            transform: rotate(45deg);
        }

        .stages li:last-child {
            color: var(--green);
            font-weight: 700;
        }


        /* =========================================================
           GARIS BAWAH
        ========================================================= */

        .finish {
              display: none;

            left: 0;
            right: 0;
            bottom: 0;

            height: 6px;

            background:
                repeating-linear-gradient(
                    90deg,
                    var(--gold) 0 16px,
                    transparent 16px 26px
                );

            transform-origin: left;

            animation:
                run 1.4s .2s
                cubic-bezier(.2, .7, .2, 1)
                both;
        }

        @keyframes run {
            from {
                transform: scaleX(0);
            }

            to {
                transform: scaleX(1);
            }
        }


        /* =========================================================
           PANEL KANAN
        ========================================================= */

        .form-side {
            height: 100%;

            background: var(--panel);

            display: flex;
            flex-direction: column;

            padding:
                24px
                clamp(28px, 4vw, 56px);

            border-left:
                1px solid var(--line);

            overflow-y: auto;
        }


        /* =========================================================
           TOMBOL TEMA
        ========================================================= */

        .top {
            height: 34px;

            display: flex;

            justify-content: flex-end;
            align-items: center;

            flex-shrink: 0;
        }

        .theme {
            background: none;

            border:
                1px solid var(--line);

            color: var(--muted);

            border-radius: 999px;

            padding:
                6px 14px;

            font:
                500 13px var(--body);

            cursor: pointer;

            transition:
                all .15s;

            line-height: 1.4;
        }

        .theme:hover {
            color: var(--ink);
            border-color: var(--muted);
        }


        /* =========================================================
           FORM
        ========================================================= */

        main {
            width: 100%;
            max-width: 420px;

            /*
             * POSISI FORM
             */
            margin:
                var(--content-top)
                auto
                auto;

            padding:
                0 0 20px;
        }

        h2 {
            margin: 0 0 6px;

            font-family: var(--display);

            font-weight: 700;

            font-size: 28px;

            letter-spacing: -.015em;

            line-height: 1.15;
        }

        .lead {
            color: var(--muted);

            margin:
                0 0 20px;

            font-size: 15px;
        }


        /* =========================================================
           FIELD
        ========================================================= */

        label,
        .lbl {
            display: block;

            font-weight: 600;
            font-size: 14px;

            margin-bottom: 6px;
        }

        .field {
            margin-bottom: 16px;
        }

        .input,
        select {
            width: 100%;
            height: 46px;

            border:
                1.5px solid var(--line);

            background: var(--field);

            color: var(--ink);

            border-radius: 10px;

            padding:
                0 14px;

            font:
                400 16px var(--body);

            transition:
                border-color .15s,
                box-shadow .15s;
        }

        .input:hover,
        select:hover {
            border-color: var(--muted);
        }

        .input:focus,
        select:focus {
            outline: none;

            border-color: var(--blue);

            box-shadow:
                0 0 0 4px
                color-mix(
                    in srgb,
                    var(--blue) 22%,
                    transparent
                );
        }

        .input[aria-invalid="true"] {
            border-color: var(--err);
        }

        .msg {
            color: var(--err);

            font-size: 13px;

            margin-top: 6px;

            min-height: 0;

            line-height: 1.35;
        }


        /* =========================================================
           UNIT KERJA
        ========================================================= */

        .unit {
            position: relative;
        }

        .unit select {
            appearance: none;

            padding-right: 38px;

            cursor: default;
        }

        .unit .dot {
            position: absolute;

            right: 18px;
            top: 50%;

            width: 8px;
            height: 8px;

            border-radius: 50%;

            background: var(--green);

            box-shadow:
                0 0 0 3px
                rgba(47, 143, 62, .2);

            transform:
                translateY(-50%);

            pointer-events: none;
        }

        .hint {
            font-size: 13px;

            color: var(--muted);

            margin-top: 6px;
        }


        /* =========================================================
           PASSWORD
        ========================================================= */

        .pw {
            position: relative;
        }

        .pw .input {
            padding-right: 76px;
        }

        .pw button {
            position: absolute;

            right: 6px;
            top: 6px;

            height: 34px;

            padding:
                0 12px;

            border: 0;

            background: none;

            color: var(--blue);

            font:
                600 14px var(--body);

            cursor: pointer;

            border-radius: 8px;

            transition:
                background .15s;
        }

        .pw button:hover {
            background:
                color-mix(
                    in srgb,
                    var(--blue) 10%,
                    transparent
                );
        }


        /* =========================================================
           CAPTCHA
        ========================================================= */

        .cap {
            display: grid;

            grid-template-columns:
                auto 1fr;

            gap: 10px;

            align-items: stretch;
        }

        .cap-box {
            display: flex;

            align-items: center;

            gap: 2px;

            border:
                1.5px solid var(--line);

            border-radius: 10px;

            padding:
                0 4px 0 0;

            background: #EEF2FA;

            overflow: hidden;

            height: 46px;
        }

        .cap-box img,
        .cap-box svg {
            display: block;

            height: 44px;
            width: 130px;

            object-fit: contain;

            cursor: pointer;

            user-select: none;
        }

        .cap-box button {
            border: 0;

            background: none;

            width: 34px;
            height: 34px;

            border-radius: 8px;

            cursor: pointer;

            color: #35507E;

            font-size: 18px;

            line-height: 1;

            display: grid;
            place-items: center;

            transition:
                transform .2s ease,
                background .15s;
        }

        .cap-box button:hover {
            background:
                rgba(0, 0, 0, .06);
        }

        .cap .input {
            letter-spacing: .3em;

            font-weight: 600;

            text-transform: uppercase;
        }

        .cap .input::placeholder {
            letter-spacing: 0;

            font-weight: 400;

            text-transform: none;
        }


        /* =========================================================
           REMEMBER
        ========================================================= */

        .row {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin:
                2px 0 20px;

            font-size: 14px;
        }

        .chk {
            display: flex;

            gap: 8px;

            align-items: center;

            font-weight: 400;

            margin: 0;

            cursor: pointer;

            user-select: none;
        }

        .chk input {
            width: 18px;
            height: 18px;

            accent-color: var(--blue);

            cursor: pointer;
        }

        .row a {
            color: var(--blue);

            font-weight: 600;

            text-decoration: none;
        }

        .row a:hover {
            text-decoration: underline;
        }


        /* =========================================================
           BUTTON MASUK
        ========================================================= */

        .go {
            width: 100%;
            height: 48px;

            border: 0;

            border-radius: 10px;

            background: var(--blue);

            color: #fff;

            font:
                700 16px var(--body);

            cursor: pointer;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 8px;

            transition:
                background .15s,
                transform .1s;
        }

        .go:hover {
            background:
                color-mix(
                    in srgb,
                    var(--blue) 86%,
                    #000
                );
        }

        .go:active {
            transform:
                translateY(1px);
        }

        .go[disabled] {
            opacity: .75;
            cursor: progress;
        }

        .oauth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 18px 0 14px;
            color: var(--muted);
        }

        .oauth-divider::before,
        .oauth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--line);
        }

        .oauth-divider span {
            padding: 0 12px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 11px;
        }

        .btn-google {
            width: 100%;
            height: 48px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--ink);
            font: 600 15px var(--body);
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: background .15s, border-color .15s, box-shadow .15s;
        }

        .btn-google:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        :root[data-theme="dark"] .btn-google {
            background: #1e293b;
            color: #f1f5f9;
            border-color: #334155;
        }

        :root[data-theme="dark"] .btn-google:hover {
            background: #334155;
        }

        .activation-link-wrapper {
            margin-top: 16px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        .activation-link-wrapper a {
            color: #0284c7;
            font-weight: 600;
            text-decoration: none;
            margin-left: 4px;
            transition: color .15s;
        }

        .activation-link-wrapper a:hover {
            text-decoration: underline;
        }

        :root[data-theme="dark"] .activation-link-wrapper a {
            color: #38bdf8;
        }


        /* =========================================================
           NOTE
        ========================================================= */

        .note {
            margin-top: 20px;

            font-size: 13px;

            color: var(--muted);

            text-align: center;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .bottom {
            display: flex;

            justify-content: space-between;

            font-size: 13px;

            color: var(--muted);

            border-top:
                1px solid var(--line);

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


        /* =========================================================
           ALERT
        ========================================================= */

        .alert {
            border-radius: 10px;

            padding:
                12px 14px;

            font-size: 14px;

            margin-bottom: 18px;

            display: flex;

            align-items: center;

            gap: 10px;

            line-height: 1.4;
        }

        .alert-err {
            background: #FDF2F2;

            border:
                1px solid #F8B4B4;

            color: var(--err);
        }

        .alert-ok {
            background: #DEF7EC;

            border:
                1px solid #BCF0DA;

            color: var(--green);
        }


        /* =========================================================
           DARK MODE
           
           PENTING:
           PANEL KIRI TIDAK BERUBAH.
           HANYA PANEL KANAN.
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


        /* PANEL KIRI TETAP TERANG */

        :root[data-theme="dark"] .track {
            background:
                linear-gradient(
                    160deg,
                    #E8F1FA 0%,
                    #F1F7F3 100%
                );

            color: #0F2A5C;
        }

        :root[data-theme="dark"] .track .center h1 {
            color: #0F2A5C;
        }

        :root[data-theme="dark"] .track .center > p {
            color: #4A5A78;
        }

        :root[data-theme="dark"] .track .stages li {
            color: #556987;
        }

        :root[data-theme="dark"]
        .track .stages li:not(:last-child)::before {
            background: #B7C6DE;
        }

        :root[data-theme="dark"]
        .track .stages i {
            border-color: #7C93BC;

            background: #EAF2FA;

            box-shadow:
                0 0 0 3px
                rgba(234, 242, 250, .8);
        }

        :root[data-theme="dark"]
        .track .lanes path {
            stroke: #0F2A5C;
            stroke-opacity: .07;
        }


        /* PANEL KANAN NAVY */

        :root[data-theme="dark"] .form-side {
            background: #0F2A5C;

            color: #FFFFFF;

            border-left-color:
                rgba(255, 255, 255, .15);
        }

        :root[data-theme="dark"] h2 {
            color: #FFFFFF;
        }

        :root[data-theme="dark"] .lead {
            color: #D6E0F0;
        }

        :root[data-theme="dark"] label,
        :root[data-theme="dark"] .lbl {
            color: #FFFFFF;
        }


        /* INPUT TETAP PUTIH */

        :root[data-theme="dark"] .input,
        :root[data-theme="dark"] select {
            background: #FFFFFF;

            color: #14213D;

            border-color:
                rgba(255, 255, 255, .30);
        }

        :root[data-theme="dark"]
        .input::placeholder {
            color: #7A8496;
        }


        /* HINT */

        :root[data-theme="dark"] .hint {
            color: #D6E0F0;
        }


        /* TOMBOL TEMA */

        :root[data-theme="dark"] .theme {
            background:
                rgba(255, 255, 255, .10);

            color: #FFFFFF;

            border-color:
                rgba(255, 255, 255, .25);
        }

        :root[data-theme="dark"] .theme:hover {
            background:
                rgba(255, 255, 255, .16);

            color: #FFFFFF;
        }

        :root[data-theme="dark"] .track-back-btn {
            background: rgba(255, 255, 255, 0.88);
            border-color: rgba(15, 42, 92, 0.22);
            color: #0F2A5C;
        }

        :root[data-theme="dark"] .track-back-btn:hover {
            background: #FFFFFF;
            border-color: rgba(15, 42, 92, 0.4);
            color: #081B3A;
        }


        /* PASSWORD BUTTON */

        :root[data-theme="dark"] .pw button {
            color: #1E4FA8;
        }


        /* LUPA PASSWORD */

        :root[data-theme="dark"] .row a {
            color: #FFFFFF;
        }


        /* CHECKBOX */

        :root[data-theme="dark"] .chk {
            color: #FFFFFF;
        }


        /* CAPTCHA */

        :root[data-theme="dark"] .cap-box {
            background: #FFFFFF;

            border-color:
                rgba(255, 255, 255, .30);
        }

        :root[data-theme="dark"] .cap-box button {
            color: #35507E;
        }


        /* BUTTON LOGIN */

       :root[data-theme="dark"] .go {
    background: var(--gold);
    color: #0F2A5C;
    box-shadow: 0 6px 18px rgba(232, 185, 58, .22);
}

        :root[data-theme="dark"] .go:hover {
            background: #4D7ED0;
        }


        /* NOTE */

        :root[data-theme="dark"] .note {
            color: #D6E0F0;
        }


        /* FOOTER */

        :root[data-theme="dark"] .bottom {
            color: #D6E0F0;

            border-top-color:
                rgba(255, 255, 255, .18);
        }

        :root[data-theme="dark"] .bottom a {
            color: #FFFFFF;
        }


        /* ALERT */

        :root[data-theme="dark"] .alert-err {
            background:
                rgba(179, 38, 30, .15);

            border-color:
                rgba(255, 138, 128, .25);

            color: #FF8A80;
        }

        :root[data-theme="dark"] .alert-ok {
            background:
                rgba(47, 143, 62, .15);

            border-color:
                rgba(47, 143, 62, .30);

            color: #4ADE80;
        }


        /* =========================================================
           SPINNER
        ========================================================= */

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation:
                spin .75s linear infinite;
        }


        /* =========================================================
           MOBILE
        ========================================================= */

      @media (min-width: 901px) {

    /* ---------- PANEL KIRI: logo di tengah & lebih besar ---------- */

    .track {
        justify-content: center;
        padding: 32px clamp(28px, 4vw, 72px);
    }

    .track-back-btn {
        top: 24px;
        left: clamp(28px, 4vw, 72px);
    }

    .track-content {
        margin: 0;
        max-width: 640px;
        gap: 28px;
    }

    .center {
        max-width: 640px;
        gap: 18px;
    }

    .logo {
        width: clamp(380px, 32vw, 640px);
        max-width: 100%;
        max-height: 44vh;
        object-fit: contain;
    }

    .center h1 {
        font-size: clamp(26px, 2.3vw, 38px);
    }

    .center > p {
        font-size: clamp(15px, 1.1vw, 18px);
        max-width: 44ch;
    }

    .stages {
        max-width: 460px;
    }

    .stages li {
        font-size: 14px;
    }


    /* ---------- PANEL KANAN: form lebih besar & di tengah ---------- */

    .form-side {
        padding: 24px clamp(28px, 5vw, 88px);
    }

    main {
        margin: auto;
        max-width: clamp(420px, 27vw, 580px);
        padding: 0 0 12px;
    }

    h2 {
        font-size: clamp(30px, 2.3vw, 40px);
        margin-bottom: 8px;
    }

    .lead {
        font-size: clamp(15px, 1.1vw, 18px);
        margin-bottom: clamp(20px, 3vh, 32px);
    }

    label,
    .lbl {
        font-size: 15px;
        margin-bottom: 8px;
    }

    .field {
        margin-bottom: clamp(14px, 2.2vh, 22px);
    }

    .input,
    select {
        height: clamp(46px, 5.4vh, 58px);
        font-size: 17px;
        border-radius: 12px;
    }

    .cap-box {
        height: clamp(46px, 5.4vh, 58px);
    }

    .cap-box img,
    .cap-box svg {
        height: calc(100% - 2px);
        width: auto;
    }

    .pw button {
        height: calc(100% - 12px);
    }

    .row {
        font-size: 15px;
    }

    .go {
        height: clamp(48px, 5.8vh, 60px);
        font-size: 17px;
        border-radius: 12px;
    }

    .note,
    .hint {
        font-size: 14px;
    }
}


        /* =========================================================
           REDUCED MOTION
        ========================================================= */

        @media (prefers-reduced-motion: reduce) {

            .finish {
                animation: none;
            }

            * {
                transition: none !important;
            }
        }

    </style>
</head>


<body>


    <!-- =========================================================
         PANEL KIRI
    ========================================================== -->

    <section
        class="track"
        aria-label="Tentang SIAPTUNTAS"
    >

        <!-- TOMBOL KEMBALI (POJOK KIRI ATAS SCREEN KIRI) -->
        <a
            href="{{ route('home') }}"
            class="track-back-btn"
            title="Kembali ke Landing Page"
            aria-label="Kembali ke landing page"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5"/>
                <path d="M12 19l-7-7 7-7"/>
            </svg>
            <span>Kembali</span>
        </a>

        <svg
            class="lanes"
            viewBox="0 0 600 900"
            preserveAspectRatio="xMidYMid slice"
            aria-hidden="true"
        >

            <path
                d="M-200 620 C 80 620, 300 560, 300 380 S 120 140, 700 140"
            />

            <path
                d="M-200 668 C 110 668, 360 590, 360 380 S 140 92, 700 92"
            />

            <path
                d="M-200 716 C 140 716, 420 620, 420 380 S 160 44, 700 44"
            />

            <path
                d="M-200 764 C 170 764, 480 650, 480 380 S 180 -4, 700 -4"
            />

        </svg>


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


        <div
            class="finish"
            aria-hidden="true"
        ></div>

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


        <!-- FORM -->

        <main>

            <h2>
                Verifikasi & Aktivasi Akun
            </h2>

            <p class="lead">
                Ketik email kedinasan, NIP, atau username Anda untuk menerima tautan verifikasi akun resmi.
            </p>

            @if (session('status'))
                <div class="alert alert-ok" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-ok" role="alert" style="border-left-color: var(--blue);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                    <span>{{ session('info') }}</span>
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

            <!-- REQUEST VERIFICATION FORM -->
            <form id="f" method="POST" action="{{ route('account.verify.send') }}" novalidate>
                @csrf

                <!-- IDENTIFIER -->
                <div class="field">
                    <label for="identifier">Email, NIP, atau Username</label>
                    <input
                        class="input"
                        id="identifier"
                        type="text"
                        name="identifier"
                        value="{{ old('identifier') }}"
                        autocomplete="username email"
                        autofocus
                        required
                        placeholder="Contoh: nama@rembangkab.go.id atau NIP"
                        @error('identifier') aria-invalid="true" @enderror
                    >
                    <p class="msg" id="e_identifier" role="alert">
                        @error('identifier') {{ $message }} @enderror
                    </p>
                    <p class="hint">
                        Sistem akan mengirimkan surat elektronik ke alamat email resmi Anda yang telah didaftarkan.
                    </p>
                </div>

                <!-- SUBMIT -->
                <button class="go" id="go" type="submit">
                    <span id="go-text">Kirim Tautan Verifikasi</span>
                </button>

                <div class="activation-link-wrapper">
                    <span>Sudah memiliki akun aktif?</span>
                    <a href="{{ route('login') }}">
                        Kembali ke Halaman Masuk
                    </a>
                </div>

                <p class="note">
                    Pastikan memeriksa folder Kotak Masuk (Inbox) atau Spam pada email kedinasan / Gmail Anda setelah mengirim permintaan.
                </p>

            </form>

        </main>


        <!-- FOOTER -->

        <div class="bottom">

            <span>
                &copy; {{ date('Y') }}
                Inspektorat Kabupaten Rembang
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
               THEME
            ===================================================== */

            var root =
                document.documentElement;

            var tb =
                document.getElementById('theme');


            /*
             * Hanya membaca data-theme.
             *
             * Tidak lagi mengikuti prefers-color-scheme.
             * Jadi browser dark mode tidak otomatis mengubah
             * tampilan sebelum tombol diklik.
             */
            function isDarkTheme() {

                return root.getAttribute('data-theme') === 'dark';

            }


            function updateThemeButton() {

                var dark =
                    isDarkTheme();

                if (tb) {

                    tb.textContent =
                        'Tema: ' +
                        (
                            dark
                                ? 'Gelap'
                                : 'Terang'
                        );

                }

            }


            /*
             * LOAD THEME
             */

            try {

                var savedTheme =
                    localStorage.getItem('st-theme')
                    ||
                    localStorage.getItem('theme');


                if (
                    savedTheme === 'dark'
                    ||
                    savedTheme === 'light'
                ) {

                    root.setAttribute(
                        'data-theme',
                        savedTheme
                    );

                } else {

                    /*
                     * DEFAULT SELALU TERANG
                     */
                    root.setAttribute(
                        'data-theme',
                        'light'
                    );

                }

            } catch (e) {

                root.setAttribute(
                    'data-theme',
                    'light'
                );

            }


            updateThemeButton();


            /*
             * TOGGLE THEME
             */

            if (tb) {

                tb.onclick =
                    function () {

                        var next =
                            isDarkTheme()
                                ? 'light'
                                : 'dark';


                        root.setAttribute(
                            'data-theme',
                            next
                        );


                        try {

                            localStorage.setItem(
                                'st-theme',
                                next
                            );

                            localStorage.setItem(
                                'theme',
                                next
                            );

                        } catch (e) {}


                        updateThemeButton();

                    };

            }



            /* =====================================================
               FORM VALIDATION
            ===================================================== */

            var form = document.getElementById('f');
            var goBtn = document.getElementById('go');
            var goText = document.getElementById('go-text');
            var inpId = document.getElementById('identifier');

            function setErrMsg(id, inputId, msg) {
                var el = document.getElementById(id);
                var inp = document.getElementById(inputId);
                if (el) { el.textContent = msg; }
                if (inp) {
                    if (msg) { inp.setAttribute('aria-invalid', 'true'); }
                    else { inp.removeAttribute('aria-invalid'); }
                }
            }

            if (form) {
                form.onsubmit = function (e) {
                    var val = inpId ? inpId.value.trim() : '';

                    if (!val) {
                        setErrMsg('e_identifier', 'identifier', 'Email, NIP, atau username wajib diisi.');
                        e.preventDefault();
                        if (inpId) { inpId.focus(); }
                        return false;
                    }

                    setErrMsg('e_identifier', 'identifier', '');

                    if (goBtn) {
                        goBtn.disabled = true;
                        if (goText) {
                            goText.innerHTML =
                                '<svg class="spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
                                '<circle cx="12" cy="12" r="10" stroke-opacity=".25"/>' +
                                '<path d="M12 2a10 10 0 0 1 10 10"/>' +
                                '</svg> Mengirim tautan...';
                        }
                    }
                    return true;
                };
            }

        })();

        /* =========================================================
           PWA SERVICE WORKER
        ========================================================= */

        if (
            'serviceWorker'
            in navigator
        ) {

            window.addEventListener(
                'load',
                function () {

                    navigator.serviceWorker
                        .register('/sw.js')

                        .then(
                            function (reg) {

                                console.log(
                                    'PWA ServiceWorker registered with scope:',
                                    reg.scope
                                );

                            }
                        )

                        .catch(
                            function (err) {

                                console.warn(
                                    'PWA ServiceWorker registration failed:',
                                    err
                                );

                            }
                        );

                }
            );

        }

    </script>

</body>

</html>