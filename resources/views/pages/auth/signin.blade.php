<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — SIPUAS Inspektorat Kabupaten Rembang</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#1558B0;
    --primary-dark:#0B3D82;
    --primary-deep:#062E68;
    --primary-light:#EAF3FF;

    --gold:#F6C343;
    --gold-dark:#E7A900;

    --text:#14213D;
    --muted:#64748B;
    --border:#E2E8F0;

    --white:#FFFFFF;
    --background:#F4F7FB;

    --danger:#DC4A4A;
    --success:#16A36A;
}

*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

html,
body{
    min-height:100%;
}

body{
    font-family:'Inter',sans-serif;
    background:var(--background);
    color:var(--text);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
    transition:background-color .3s ease, color .3s ease;
}

/* =========================================================
   DARK MODE OVERRIDES
========================================================= */
body.dark {
    --background: #0B132B;
    --text: #F1F5F9;
    --muted: #94A3B8;
    --border: #334155;
    background: #0B132B;
    color: #F1F5F9;
}

body.dark .login-shell {
    background: #1C2541;
    box-shadow: 0 30px 80px rgba(0,0,0,0.45);
}

body.dark .form-panel {
    background: #1C2541;
}

body.dark .form-header h2 {
    color: #FFFFFF;
}

body.dark .form-header p {
    color: #94A3B8;
}

body.dark .tenant-label {
    color: #E2E8F0;
}

body.dark .tenant-card {
    background: #111827;
    border-color: #374151;
}

body.dark .tenant-name {
    color: #F9FAFB;
}

body.dark .tenant-meta {
    color: #9CA3AF;
}

body.dark .field label {
    color: #E2E8F0;
}

body.dark .input-group {
    background: #111827;
    border-color: #374151;
}

body.dark .input-group input {
    color: #F9FAFB;
}

body.dark .input-group input::placeholder {
    color: #6B7280;
}

body.dark .security-note {
    background: #111827;
    color: #9CA3AF;
}

body.dark .form-footer {
    border-top-color: #374151;
    color: #6B7280;
}

body.dark .form-footer a {
    color: #9CA3AF;
}

body.dark .theme-switch {
    color: #9CA3AF;
}

/* =========================================================
   LOGIN SHELL
========================================================= */

.login-shell{
    width:min(1380px,100%);
    min-height:760px;

    display:grid;
    grid-template-columns:52% 48%;

    background:white;

    border-radius:30px;

    overflow:hidden;

    box-shadow:
        0 30px 80px rgba(15,35,70,.14),
        0 8px 30px rgba(15,35,70,.06);
    transition:background-color .3s ease, box-shadow .3s ease;
}


/* =========================================================
   LEFT BRAND PANEL
========================================================= */

.brand-panel{
    position:relative;
    overflow:hidden;

    display:flex;
    flex-direction:column;

    background:
        radial-gradient(
            circle at 20% 15%,
            rgba(255,255,255,.14),
            transparent 30%
        ),
        radial-gradient(
            circle at 85% 75%,
            rgba(246,195,67,.12),
            transparent 35%
        ),
        linear-gradient(
            145deg,
            #0B4EA2 0%,
            #0B55B5 45%,
            #063A83 100%
        );

    color:white;

    padding:44px 48px 34px;
}


/* decorative circles */

.brand-panel::before{
    content:"";

    position:absolute;

    width:520px;
    height:520px;

    border:1px solid rgba(255,255,255,.08);

    border-radius:50%;

    top:-220px;
    right:-180px;
}

.brand-panel::after{
    content:"";

    position:absolute;

    width:380px;
    height:380px;

    border:1px solid rgba(255,255,255,.06);

    border-radius:50%;

    bottom:-210px;
    left:-170px;
}


/* =========================================================
   BRAND HEADER
========================================================= */

.brand-header{
    position:relative;
    z-index:3;

    display:flex;
    align-items:center;
    gap:13px;
}

.brand-mini-logo{
    width:42px;
    height:42px;

    border-radius:12px;

    background:white;

    display:flex;
    align-items:center;
    justify-content:center;

    box-shadow:
        0 8px 20px rgba(0,0,0,.12);
}

.brand-mini-logo img{
    width:34px;
    height:34px;

    object-fit:contain;
}

.brand-header-text{
    display:flex;
    flex-direction:column;
    gap:2px;
}

.brand-header-text strong{
    font-family:'Plus Jakarta Sans',sans-serif;

    font-size:14px;
    font-weight:800;

    letter-spacing:.01em;
}

.brand-header-text span{
    font-size:11px;

    color:rgba(255,255,255,.68);

    letter-spacing:.04em;
}


/* =========================================================
   MASCOT
========================================================= */

.mascot-area{
    position:relative;
    z-index:2;

    flex:1;

    display:flex;
    flex-direction:column;

    align-items:center;
    justify-content:center;

    padding:25px 0 20px;
}

.logo-character{
    width:min(430px,82%);

    object-fit:contain;

    filter:
        drop-shadow(0 25px 30px rgba(0,0,0,.22));

    animation:
        mascotFloat 5s ease-in-out infinite;
}

@keyframes mascotFloat{

    0%,
    100%{
        transform:translateY(0);
    }

    50%{
        transform:translateY(-8px);
    }

}


/* =========================================================
   BRAND TEXT
========================================================= */

.brand-copy{
    position:relative;
    z-index:3;

    text-align:center;

    max-width:620px;

    margin:0 auto 26px;
}

.brand-copy .welcome{
    font-family:'Plus Jakarta Sans',sans-serif;

    font-size:22px;

    font-weight:700;

    color:white;

    margin-bottom:5px;
}

.brand-copy h1{
    font-family:'Plus Jakarta Sans',sans-serif;

    font-size:clamp(24px,2.5vw,34px);

    line-height:1.15;

    font-weight:800;

    color:var(--gold);

    margin-bottom:13px;
}

.brand-copy p{
    font-size:13.5px;

    line-height:1.65;

    color:rgba(255,255,255,.78);

    max-width:500px;

    margin:auto;
}


/* =========================================================
   VALUES
========================================================= */

.values{
    position:relative;
    z-index:3;

    display:grid;

    grid-template-columns:repeat(3,1fr);

    border:1px solid rgba(255,255,255,.18);

    background:rgba(255,255,255,.06);

    backdrop-filter:blur(12px);

    border-radius:18px;

    overflow:hidden;
}

.value{
    padding:16px 12px;

    text-align:center;

    border-right:1px solid rgba(255,255,255,.13);
}

.value:last-child{
    border-right:none;
}

.value-icon{
    width:34px;
    height:34px;

    margin:0 auto 8px;

    border-radius:10px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:rgba(255,255,255,.13);

    color:white;
}

.value-icon svg{
    width:18px;
    height:18px;
}

.value strong{
    display:block;

    font-size:12px;

    font-weight:700;

    margin-bottom:4px;
}

.value span{
    display:block;

    font-size:10px;

    line-height:1.4;

    color:rgba(255,255,255,.62);
}


/* =========================================================
   GOLD WAVE
========================================================= */

.wave{
    position:absolute;

    z-index:1;

    left:-5%;
    bottom:-1px;

    width:110%;

    height:45px;

    pointer-events:none;
}

.wave svg{
    width:100%;
    height:100%;
}


/* =========================================================
   RIGHT FORM PANEL
========================================================= */

.form-panel{
    position:relative;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:60px 70px;

    background:#FFFFFF;
    transition:background-color .3s ease;
}

.form-container{
    width:100%;

    max-width:430px;
}


/* =========================================================
   FORM HEADER
========================================================= */

.form-icon{
    width:52px;
    height:52px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:16px;

    background:var(--primary-light);

    color:var(--primary);

    margin-bottom:20px;
}

.form-icon svg{
    width:25px;
    height:25px;
}

.form-header h2{
    font-family:'Plus Jakarta Sans',sans-serif;

    font-size:30px;

    line-height:1.2;

    font-weight:800;

    letter-spacing:-.03em;

    color:var(--text);

    margin-bottom:8px;
    transition:color .3s ease;
}

.form-header p{
    font-size:14px;

    line-height:1.6;

    color:var(--muted);

    margin-bottom:32px;
    transition:color .3s ease;
}


/* =========================================================
   ERROR
========================================================= */

.alert-error{
    display:flex;

    align-items:flex-start;

    gap:10px;

    padding:13px 14px;

    margin-bottom:20px;

    border-radius:12px;

    background:#FFF3F3;

    border:1px solid #FFD7D7;

    color:var(--danger);

    font-size:13px;

    line-height:1.45;
}

.alert-error svg{
    flex-shrink:0;

    margin-top:1px;
}


/* =========================================================
   TENANT
========================================================= */

.tenant-label{
    display:block;

    font-size:12px;

    font-weight:700;

    color:var(--text);

    margin-bottom:8px;
    transition:color .3s ease;
}

.tenant-card{
    display:flex;

    align-items:center;

    gap:13px;

    padding:13px;

    border:1px solid var(--border);

    border-radius:14px;

    background:#F8FAFC;

    margin-bottom:23px;
    transition:background-color .3s ease, border-color .3s ease;
}

.tenant-icon{
    width:42px;
    height:42px;

    flex-shrink:0;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:11px;

    background:var(--primary-light);

    color:var(--primary);
}

.tenant-icon svg{
    width:21px;
    height:21px;
}

.tenant-info{
    min-width:0;
}

.tenant-name{
    font-size:13px;

    font-weight:700;

    color:var(--text);

    white-space:nowrap;

    overflow:hidden;

    text-overflow:ellipsis;
    transition:color .3s ease;
}

.tenant-meta{
    display:flex;

    align-items:center;

    gap:6px;

    font-size:11px;

    color:var(--muted);

    margin-top:3px;
    transition:color .3s ease;
}

.status-dot{
    width:7px;
    height:7px;

    border-radius:50%;

    background:var(--success);

    box-shadow:
        0 0 0 3px rgba(22,163,106,.10);
}


/* =========================================================
   FIELD
========================================================= */

.field{
    margin-bottom:19px;
}

.field label{
    display:block;

    font-size:13px;

    font-weight:700;

    color:var(--text);

    margin-bottom:8px;
    transition:color .3s ease;
}

.input-group{
    display:flex;

    align-items:center;

    gap:11px;

    width:100%;

    height:50px;

    padding:0 14px;

    border:1px solid var(--border);

    border-radius:12px;

    background:#FFFFFF;

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background-color .3s ease;
}

.input-group:hover{
    border-color:#CBD5E1;
}

.input-group:focus-within{
    border-color:var(--primary);

    box-shadow:
        0 0 0 4px rgba(21,88,176,.10);
}

.input-group.input-error{
    border-color:var(--danger);
}

.input-group svg{
    flex-shrink:0;

    color:#94A3B8;
}

.input-group input{
    flex:1;

    width:100%;

    border:none;

    outline:none;

    background:transparent;

    font-family:'Inter',sans-serif;

    font-size:13.5px;

    color:var(--text);
}

.input-group input::placeholder{
    color:#A0AEC0;
}

.eye-btn{
    display:flex;
    align-items:center;
    justify-content:center;

    border:none;

    background:none;

    padding:4px;

    color:#94A3B8;

    cursor:pointer;
}

.eye-btn:hover{
    color:var(--primary);
}


/* =========================================================
   FIELD ERROR
========================================================= */

.field-error{
    display:flex;

    align-items:center;

    gap:5px;

    margin-top:6px;

    font-size:11.5px;

    color:var(--danger);
}


/* =========================================================
   REMEMBER / FORGOT
========================================================= */

.row-between{
    display:flex;

    align-items:center;

    justify-content:space-between;

    margin-top:2px;

    margin-bottom:22px;
}

.remember{
    display:flex;

    align-items:center;

    gap:8px;

    cursor:pointer;

    font-size:12.5px;

    color:var(--muted);
}

.remember input{
    appearance:none;

    width:16px;
    height:16px;

    border:1px solid #CBD5E1;

    border-radius:5px;

    background:white;

    cursor:pointer;

    position:relative;
}

.remember input:checked{
    background:var(--primary);

    border-color:var(--primary);
}

.remember input:checked::after{
    content:"";

    position:absolute;

    width:4px;
    height:8px;

    border-right:2px solid white;
    border-bottom:2px solid white;

    transform:rotate(45deg);

    left:5px;
    top:2px;
}

.forgot{
    color:var(--primary);

    font-size:12.5px;

    font-weight:700;

    text-decoration:none;
}

.forgot:hover{
    text-decoration:underline;
}


/* =========================================================
   PRIMARY BUTTON
========================================================= */

.btn-primary{
    width:100%;

    height:52px;

    border:none;

    border-radius:12px;

    background:
        linear-gradient(
            135deg,
            var(--primary),
            #0D4CA1
        );

    color:white;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:9px;

    font-family:'Inter',sans-serif;

    font-size:14px;

    font-weight:700;

    cursor:pointer;

    box-shadow:
        0 8px 18px rgba(21,88,176,.18);

    transition:
        transform .15s ease,
        box-shadow .15s ease,
        filter .15s ease;
}

.btn-primary:hover{
    filter:brightness(1.05);

    box-shadow:
        0 12px 25px rgba(21,88,176,.25);

    transform:translateY(-1px);
}

.btn-primary:active{
    transform:translateY(0);
}

.btn-primary:disabled{
    opacity:.55;

    cursor:not-allowed;

    transform:none;
}


/* =========================================================
   SECURITY
========================================================= */

.security-note{
    display:flex;

    align-items:flex-start;

    gap:8px;

    margin-top:19px;

    padding:11px 12px;

    border-radius:10px;

    background:#F8FAFC;

    color:#64748B;

    font-size:10.5px;

    line-height:1.5;
    transition:background-color .3s ease, color .3s ease;
}

.security-note svg{
    flex-shrink:0;

    margin-top:1px;

    color:#64748B;
}


/* =========================================================
   FOOTER
========================================================= */

.form-footer{
    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:15px;

    padding-top:22px;

    margin-top:27px;

    border-top:1px solid #EEF2F7;

    font-size:11px;

    color:#94A3B8;
    transition:border-color .3s ease, color .3s ease;
}

.form-footer a{
    color:#64748B;

    text-decoration:none;

    font-weight:600;
}

.form-footer a:hover{
    color:var(--primary);
}


/* =========================================================
   THEME BUTTON
========================================================= */

.theme-switch{
    position:absolute;

    top:28px;
    right:32px;

    display:flex;

    align-items:center;

    gap:8px;

    border:none;

    background:transparent;

    color:#64748B;

    font-size:12px;

    cursor:pointer;
    transition:color .3s ease;
}

.theme-switch svg{
    width:18px;
    height:18px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1100px){

    body{
        padding:15px;
    }

    .login-shell{
        grid-template-columns:48% 52%;
    }

    .brand-panel{
        padding:35px 30px 28px;
    }

    .form-panel{
        padding:50px;
    }

    .logo-character{
        width:min(360px,90%);
    }

}


@media(max-width:850px){

    body{
        padding:0;

        display:block;

        background:white;
    }

    .login-shell{
        display:flex;

        flex-direction:column;

        width:100%;

        min-height:100vh;

        border-radius:0;

        box-shadow:none;
    }

    .brand-panel{
        min-height:570px;

        padding:28px 24px 25px;
    }

    .mascot-area{
        padding:15px 0;
    }

    .logo-character{
        width:min(320px,75%);
    }

    .brand-copy .welcome{
        font-size:19px;
    }

    .brand-copy h1{
        font-size:25px;
    }

    .values{
        max-width:560px;

        width:100%;

        margin:auto;
    }

    .form-panel{
        padding:45px 24px 55px;

        align-items:flex-start;
    }

    .form-container{
        max-width:520px;
    }

    .theme-switch{
        display:none;
    }

}


@media(max-width:520px){

    .brand-panel{
        min-height:530px;
    }

    .brand-header{
        gap:9px;
    }

    .brand-mini-logo{
        width:37px;
        height:37px;
    }

    .brand-mini-logo img{
        width:30px;
        height:30px;
    }

    .brand-header-text strong{
        font-size:12px;
    }

    .brand-header-text span{
        font-size:9px;
    }

    .logo-character{
        width:270px;
    }

    .brand-copy{
        margin-bottom:20px;
    }

    .brand-copy .welcome{
        font-size:17px;
    }

    .brand-copy h1{
        font-size:21px;
    }

    .brand-copy p{
        font-size:11.5px;
    }

    .value{
        padding:12px 6px;
    }

    .value-icon{
        width:30px;
        height:30px;
    }

    .value strong{
        font-size:10px;
    }

    .value span{
        display:none;
    }

    .form-header h2{
        font-size:26px;
    }

    .form-header p{
        font-size:13px;
    }

    .tenant-name{
        font-size:12px;
    }

    .form-footer{
        flex-direction:column;

        align-items:flex-start;

        gap:8px;
    }

}


/* =========================================================
   ACCESSIBILITY
========================================================= */

@media(prefers-reduced-motion:reduce){

    *,
    *::before,
    *::after{
        animation:none!important;

        transition:none!important;
    }

}

</style>

</head>

<body>

<div class="login-shell">

<!-- =====================================================
     LEFT : BRANDING
====================================================== -->

<section class="brand-panel">


    <!-- HEADER -->

    <div class="brand-header">

        <div class="brand-mini-logo">

            <img
                src="{{ asset('images/icon.png') }}"
                alt="Logo Inspektorat Kabupaten Rembang"
            >

        </div>

        <div class="brand-header-text">

            <strong>
                SIPUAS
            </strong>

            <span>
                Sistem Informasi Pengawasan
            </span>

        </div>

    </div>


    <!-- CHARACTER -->

    <div class="mascot-area">

        <img
            class="logo-character"
            src="{{ asset('images/icon.png') }}"
            alt="Karakter Inspektorat Kabupaten Rembang"
        >

    </div>


    <!-- BRAND COPY -->

    <div class="brand-copy">

        <div class="welcome">
            Selamat Datang di
        </div>

        <h1>
            Inspektorat Kabupaten Rembang
        </h1>

        <p>
            Sistem informasi untuk mendukung pengawasan
            yang transparan, akuntabel, dan berintegritas.
        </p>

    </div>


    <!-- VALUES -->

    <div class="values">


        <div class="value">

            <div class="value-icon">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >

                    <path d="M12 3l7 4v5c0 4.5-3 7.8-7 9-4-1.2-7-4.5-7-9V7l7-4z"/>

                    <path d="M9 12l2 2 4-4"/>

                </svg>

            </div>

            <strong>
                Transparan
            </strong>

            <span>
                Keterbukaan dalam setiap proses
            </span>

        </div>


        <div class="value">

            <div class="value-icon">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >

                    <rect
                        x="5"
                        y="3"
                        width="14"
                        height="18"
                        rx="2"
                    />

                    <path d="M9 8h6"/>
                    <path d="M9 12h6"/>
                    <path d="M9 16h4"/>

                </svg>

            </div>

            <strong>
                Akuntabel
            </strong>

            <span>
                Bertanggung jawab atas setiap tindakan
            </span>

        </div>


        <div class="value">

            <div class="value-icon">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >

                    <path d="M12 3l7 4v5c0 4.5-3 7.8-7 9-4-1.2-7-4.5-7-9V7l7-4z"/>

                    <path d="M12 8v4"/>

                    <circle
                        cx="12"
                        cy="16"
                        r=".7"
                        fill="currentColor"
                    />

                </svg>

            </div>

            <strong>
                Berintegritas
            </strong>

            <span>
                Menjunjung tinggi nilai kejujuran
            </span>

        </div>


    </div>


    <!-- DECORATIVE WAVE -->

    <div class="wave">

        <svg
            viewBox="0 0 1200 80"
            preserveAspectRatio="none"
        >

            <path
                d="M0,55 C180,15 300,75 470,42 C650,8 760,60 920,35 C1040,16 1120,35 1200,10 L1200,80 L0,80 Z"
                fill="#F6C343"
            />

            <path
                d="M0,65 C180,25 300,85 470,52 C650,18 760,70 920,45 C1040,26 1120,45 1200,20 L1200,80 L0,80 Z"
                fill="#0B3D82"
                opacity=".7"
            />

        </svg>

    </div>


</section>



<!-- =====================================================
     RIGHT : LOGIN
====================================================== -->

<section class="form-panel">


    <!-- THEME -->

    <button
        type="button"
        class="theme-switch"
        aria-label="Mode tampilan"
    >

        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >

            <circle
                cx="12"
                cy="12"
                r="4"
            />

            <path d="M12 2v2"/>
            <path d="M12 20v2"/>
            <path d="M4.93 4.93l1.42 1.42"/>
            <path d="M17.65 17.65l1.42 1.42"/>
            <path d="M2 12h2"/>
            <path d="M20 12h2"/>
            <path d="M4.93 19.07l1.42-1.42"/>
            <path d="M17.65 6.35l1.42-1.42"/>

        </svg>

        Light

    </button>



    <div class="form-container">


        <!-- FORM HEADER -->

        <div class="form-icon">

            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <rect
                    x="4"
                    y="10"
                    width="16"
                    height="11"
                    rx="2"
                />

                <path d="M8 10V7a4 4 0 018 0v3"/>

            </svg>

        </div>


        <div class="form-header">

            <h2>
                Masuk ke Akun Anda
            </h2>

            <p>
                Silakan masuk untuk melanjutkan ke
                dashboard SIPUAS Inspektorat Kabupaten Rembang.
            </p>

        </div>



        <!-- ERROR ALERT -->

        @if (isset($errors) && $errors->any())

            <div class="alert-error">

                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >

                    <circle
                        cx="12"
                        cy="12"
                        r="10"
                    />

                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>

                </svg>

                <span>
                    Email atau password salah.
                    Silakan coba lagi.
                </span>

            </div>

        @endif



        <!-- FORM -->

        <form
            method="POST"
            action="{{ route('login') }}"
        >

            @csrf



            <!-- UNIT KERJA -->

            <div class="tenant-label">
                Unit Kerja
            </div>

            <div class="tenant-card">

                <div class="tenant-icon">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >

                        <path d="M3 21h18"/>
                        <path d="M5 21V7l7-4 7 4v14"/>
                        <path d="M9 21v-5h6v5"/>
                        <path d="M9 9h.01"/>
                        <path d="M15 9h.01"/>
                        <path d="M9 12h.01"/>
                        <path d="M15 12h.01"/>

                    </svg>

                </div>

                <div class="tenant-info">

                    <div class="tenant-name">
                        Inspektorat Daerah — Kab. Rembang
                    </div>

                    <div class="tenant-meta">

                        <span class="status-dot"></span>

                        Periode Aktif · {{ date('Y') }}

                    </div>

                </div>

            </div>



            <!-- EMAIL -->

            <div class="field">

                <label for="email">
                    Email atau Username
                </label>

                <div
                    class="input-group
                    @error('email') input-error @enderror"
                >

                    <svg
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>

                        <circle
                            cx="12"
                            cy="7"
                            r="4"
                        />

                    </svg>


                    <input
                        id="email"
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Masukkan email atau username"
                        required
                        autofocus
                        autocomplete="username email"
                    >

                </div>


                @error('email')

                    <p class="field-error">

                        <svg
                            width="12"
                            height="12"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >

                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                            />

                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>

                        </svg>

                        {{ $message }}

                    </p>

                @enderror

            </div>



            <!-- PASSWORD -->

            <div class="field">

                <label for="pwd">
                    Kata Sandi
                </label>

                <div
                    class="input-group
                    @error('password') input-error @enderror"
                >

                    <svg
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <rect
                            x="3"
                            y="11"
                            width="18"
                            height="11"
                            rx="2"
                        />

                        <path d="M7 11V7a5 5 0 0110 0v4"/>

                    </svg>


                    <input
                        id="pwd"
                        type="password"
                        name="password"
                        placeholder="Masukkan kata sandi"
                        required
                        autocomplete="current-password"
                    >


                    <button
                        class="eye-btn"
                        onclick="togglePwd()"
                        type="button"
                        aria-label="Tampilkan kata sandi"
                    >

                        <svg
                            id="eyeIcon"
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >

                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>

                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            />

                        </svg>

                    </button>

                </div>


                @error('password')

                    <p class="field-error">

                        <svg
                            width="12"
                            height="12"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >

                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                            />

                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>

                        </svg>

                        {{ $message }}

                    </p>

                @enderror

            </div>



            <!-- OPTIONS -->

            <div class="row-between">

                <label class="remember">

                    <input
                        type="checkbox"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                    >

                    Ingat saya

                </label>


                @if (Route::has('password.request'))

                    <a
                        href="{{ route('password.request') }}"
                        class="forgot"
                    >
                        Lupa kata sandi?
                    </a>

                @endif

            </div>



            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="btn-primary"
            >

                Masuk

                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                >

                    <path d="M5 12h14"/>
                    <path d="M13 6l6 6-6 6"/>

                </svg>

            </button>


        </form>



        <!-- SECURITY -->

        <div class="security-note">

            <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <rect
                    x="3"
                    y="11"
                    width="18"
                    height="11"
                    rx="2"
                />

                <path d="M7 11V7a5 5 0 0110 0v4"/>

            </svg>

            <span>
                Koneksi Anda aman dan terenkripsi.
                Jangan pernah membagikan kata sandi kepada pihak lain.
            </span>

        </div>



        <!-- FOOTER -->

        <div class="form-footer">

            <span>
                &copy; {{ date('Y') }}
                Inspektorat Kabupaten Rembang
            </span>

            <a href="#">
                Butuh bantuan?
            </a>

        </div>


    </div>

</section>

</div>

<script>

/* =========================================================
   PASSWORD TOGGLE
========================================================= */

function togglePwd(){

    const pwd =
        document.getElementById('pwd');

    const icon =
        document.getElementById('eyeIcon');

    if(!pwd || !icon){
        return;
    }

    const isPassword =
        pwd.type === 'password';

    pwd.type =
        isPassword
            ? 'text'
            : 'password';


    icon.innerHTML = isPassword

        ? `
            <path d="M3 3l18 18"/>
            <path d="M10.6 10.6a2 2 0 002.8 2.8"/>
            <path d="M9.9 4.2A10.7 10.7 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-3.1 4.3"/>
            <path d="M6.2 6.2C3.1 8.4 1 12 1 12s4 8 11 8a10.7 10.7 0 004.1-.8"/>
          `

        : `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          `;
}


/* =========================================================
   BUTTON LOADING
========================================================= */

const loginForm =
    document.querySelector('form');

if(loginForm){

    loginForm.addEventListener('submit', function(){

        const button =
            this.querySelector('.btn-primary');

        if(!button){
            return;
        }

        button.disabled = true;

        button.innerHTML = `
            <svg
                width="17"
                height="17"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                style="animation:spin .8s linear infinite"
            >
                <path d="M12 2v4"/>
                <path d="M12 18v4"/>
                <path d="M4.93 4.93l2.83 2.83"/>
                <path d="M16.24 16.24l2.83 2.83"/>
                <path d="M2 12h4"/>
                <path d="M18 12h4"/>
                <path d="M4.93 19.07l2.83-2.83"/>
                <path d="M16.24 7.76l2.83-2.83"/>
            </svg>

            Memproses...
        `;

    });

}

/* =========================================================
   DARK / LIGHT THEME TOGGLE
========================================================= */

document.addEventListener('DOMContentLoaded', function() {
    const themeBtn = document.querySelector('.theme-switch');
    if (!themeBtn) return;

    function updateThemeUI(isDark) {
        themeBtn.innerHTML = isDark
            ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
               </svg> Dark`
            : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2"/><path d="M12 20v2"/>
                <path d="M4.93 4.93l1.42 1.42"/><path d="M17.65 17.65l1.42 1.42"/>
                <path d="M2 12h2"/><path d="M20 12h2"/>
                <path d="M4.93 19.07l1.42-1.42"/><path d="M17.65 6.35l1.42-1.42"/>
               </svg> Light`;
    }

    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

    if (isDark) {
        document.body.classList.add('dark');
        updateThemeUI(true);
    } else {
        document.body.classList.remove('dark');
        updateThemeUI(false);
    }

    themeBtn.addEventListener('click', function() {
        const currentlyDark = document.body.classList.contains('dark');
        if (currentlyDark) {
            document.body.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            updateThemeUI(false);
        } else {
            document.body.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            updateThemeUI(true);
        }
    });
});


/* =========================================================
   SPINNER
========================================================= */

const style =
    document.createElement('style');

style.innerHTML = `

@keyframes spin{

    from{
        transform:rotate(0deg);
    }

    to{
        transform:rotate(360deg);
    }

}

`;

document.head.appendChild(style);

</script>

</body>
</html>