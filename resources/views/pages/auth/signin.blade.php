<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — SIWAS Inspektorat Kabupaten Rembang</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --ink-black:#0A0D12;
    --ink-panel:#11151C;
    --teal:#22D3B8;
    --amber:#F0A93B;
    --coral:#E8674F;
    --paper:#F7F7F5;
    --slate-900:#171B22;
    --slate-600:#5B6472;
    --slate-300:#D7DAE0;
    --slate-100:#EEEFF1;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  html,body{height:100%;}
  body{
    font-family:'Inter', sans-serif;
    display:flex;
    min-height:100vh;
    background:var(--paper);
    color:var(--slate-900);
    overflow:hidden;
  }

  /* ===== LEFT: 3D STAGE ===== */
  .stage{
    position:relative;
    flex:1 1 56%;
    background:
      radial-gradient(ellipse at 30% 20%, rgba(34,211,184,0.10), transparent 55%),
      radial-gradient(ellipse at 75% 80%, rgba(240,169,59,0.08), transparent 50%),
      var(--ink-black);
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    min-height:100vh;
  }

  .scene3d{
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    perspective:1200px;
    z-index:1;
    pointer-events:none;
  }
  .gem{
    position:relative;
    width:260px; height:260px;
    transform-style:preserve-3d;
    animation:spinGem 14s linear infinite;
  }
  @keyframes spinGem{
    from{ transform:rotateY(0deg) rotateX(-18deg); }
    to{ transform:rotateY(360deg) rotateX(-18deg); }
  }
  .facet{
    position:absolute;
    top:50%; left:50%;
    width:150px; height:150px;
    margin:-75px 0 0 -75px;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    opacity:0.92;
    backface-visibility:visible;
    box-shadow: 0 0 40px rgba(34,211,184,0.15);
  }
  .facet.f1{ background:linear-gradient(135deg, #2FE3C6, #1AA98F); transform: rotateY(0deg)   translateZ(90px); }
  .facet.f2{ background:linear-gradient(135deg, #23B39C, #14806D); transform: rotateY(60deg)  translateZ(90px); }
  .facet.f3{ background:linear-gradient(135deg, #F2B75A, #D98F1F); transform: rotateY(120deg) translateZ(90px); }
  .facet.f4{ background:linear-gradient(135deg, #EF8B62, #D85C36); transform: rotateY(180deg) translateZ(90px); }
  .facet.f5{ background:linear-gradient(135deg, #23B39C, #14806D); transform: rotateY(240deg) translateZ(90px); }
  .facet.f6{ background:linear-gradient(135deg, #2FE3C6, #1AA98F); transform: rotateY(300deg) translateZ(90px); }
  .facet.top{
    background:linear-gradient(135deg, #F6D488, #F0A93B);
    clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);
    transform: rotateX(90deg) translateZ(90px);
  }
  .facet.bottom{
    background:linear-gradient(135deg, #163B36, #0C2422);
    clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);
    transform: rotateX(-90deg) translateZ(90px);
  }
  .ring{
    position:absolute; top:50%; left:50%;
    width:340px; height:340px;
    margin:-170px 0 0 -170px;
    border:1px solid rgba(34,211,184,0.35);
    border-radius:50%;
    transform-style:preserve-3d;
    animation:spinRing 22s linear infinite reverse;
  }
  @keyframes spinRing{
    from{ transform:rotateX(78deg) rotateZ(0deg); }
    to{ transform:rotateX(78deg) rotateZ(360deg); }
  }
  .ring::before, .ring::after{
    content:'';
    position:absolute;
    width:10px; height:10px;
    border-radius:50%;
    background:var(--amber);
    box-shadow:0 0 12px var(--amber);
    top:-5px; left:calc(50% - 5px);
  }
  .ring::after{
    background:var(--teal);
    box-shadow:0 0 12px var(--teal);
    top:auto; bottom:-5px;
    left:calc(50% - 5px);
  }
  .glow-floor{
    position:absolute; bottom:-40px; left:50%;
    width:380px; height:90px;
    transform:translateX(-50%);
    background:radial-gradient(ellipse at center, rgba(34,211,184,0.28), transparent 70%);
    filter:blur(6px);
  }

  .stage-top{
    position:relative; z-index:2;
    display:flex; align-items:center; gap:10px;
    padding:36px 44px 0;
  }
  .mark{
    width:28px; height:28px;
    background:linear-gradient(135deg, var(--teal), var(--amber));
    border-radius:7px;
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
  }
  .brand-tag{
    font-family:'JetBrains Mono', monospace;
    font-size:12px; letter-spacing:.14em; color:var(--slate-300);
    text-transform:uppercase;
  }

  .stage-bottom{
    position:relative; z-index:2;
    padding:0 44px 48px;
  }
  .stage-bottom h1{
    font-family:'Space Grotesk', sans-serif;
    font-weight:600;
    font-size:clamp(30px, 3.6vw, 46px);
    line-height:1.08;
    color:#fff;
    letter-spacing:-0.01em;
    max-width:9.5ch;
  }

  .title-3d-wrap{
    perspective:900px;
    margin-bottom:2px;
  }
  .title-3d{
    display:inline-block;
    font-family:'Space Grotesk', sans-serif;
    font-weight:700;
    font-size:clamp(26px, 2.7vw, 38px);
    line-height:1.14;
    letter-spacing:0.01em;
    color:#F4FBFA;
    max-width:11.5ch;
    min-height:2.3em;
    transform:rotateX(10deg) rotateY(-4deg);
    transform-style:preserve-3d;
    text-shadow:
      1px 1px 0   #1AA98F,
      2px 2px 0   #17967F,
      3px 3px 0   #14806D,
      4px 4px 0   #116C5C,
      5px 5px 0   #0E5A4D,
      6px 6px 0   #0B4A3F,
      7px 7px 0   #093C33,
      8px 8px 10px rgba(0,0,0,0.55),
      0 0 26px rgba(34,211,184,0.35);
    animation:titleFloat 6s ease-in-out infinite;
  }
  @keyframes titleFloat{
    0%, 100%{ transform:rotateX(10deg) rotateY(-4deg) translateY(0px); }
    50%{ transform:rotateX(10deg) rotateY(-4deg) translateY(-5px); }
  }
  .title-eyebrow{
    font-family:'JetBrains Mono', monospace;
    font-size:11px;
    letter-spacing:.16em;
    text-transform:uppercase;
    color:var(--amber);
    margin-bottom:10px;
    min-height:14px;
  }

  .tw-cursor{
    display:inline-block;
    width:2px;
    margin-left:2px;
    background:currentColor;
    animation:twBlink 0.85s steps(1) infinite;
    vertical-align:-0.05em;
  }
  .tw-cursor.on-eyebrow{ height:11px; background:var(--amber); }
  .tw-cursor.on-title{ height:0.85em; background:var(--teal); }
  .tw-cursor.on-sub{ height:14px; background:var(--slate-300); }
  .tw-cursor.done{ animation:twBlink 0.85s steps(1) infinite; }
  @keyframes twBlink{
    0%, 49%{ opacity:1; }
    50%, 100%{ opacity:0; }
  }
  .stage-bottom .sub{
    margin-top:14px;
    font-size:15px;
    color:var(--slate-300);
    max-width:34ch;
    line-height:1.55;
  }
  .stage-foot{
    margin-top:28px;
    display:flex; gap:22px;
    font-family:'JetBrains Mono', monospace;
    font-size:11px;
    color:#586071;
    letter-spacing:.04em;
  }
  .stage-foot span{ display:flex; align-items:center; gap:6px; }
  .dot{width:6px;height:6px;border-radius:50%;background:var(--teal); box-shadow:0 0 8px var(--teal);}

  /* ===== RIGHT: FORM ===== */
  .panel{
    flex:1 1 44%;
    max-width:560px;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px;
    background:var(--paper);
    position:relative;
  }
  .form-wrap{ width:100%; max-width:380px; }

  .eyebrow{
    font-family:'JetBrains Mono', monospace;
    font-size:11px;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:var(--coral);
    margin-bottom:10px;
  }
  .form-wrap h2{
    font-family:'Space Grotesk', sans-serif;
    font-size:28px;
    font-weight:600;
    letter-spacing:-0.01em;
    margin-bottom:8px;
  }
  .form-wrap p.desc{
    font-size:14px;
    color:var(--slate-600);
    line-height:1.5;
    margin-bottom:32px;
  }

  .field{ margin-bottom:20px; }
  .field label{
    display:block;
    font-size:12.5px;
    font-weight:600;
    color:var(--slate-900);
    margin-bottom:8px;
  }

  .tenant-card{
    display:flex; align-items:center; gap:12px;
    border:1.5px solid var(--slate-300);
    border-radius:12px;
    padding:13px 14px;
    cursor:default;
    transition:border-color .15s ease, background .15s ease;
    background:#fff;
  }
  .tenant-card:hover{ border-color:var(--teal); }
  .tenant-icon{
    width:36px; height:36px; border-radius:9px;
    background:linear-gradient(135deg, #E8FBF7, #FDF3E3);
    display:flex; align-items:center; justify-content:center;
    font-size:16px;
  }
  .tenant-info{ flex:1; }
  .tenant-info .name{ font-size:14px; font-weight:600; color:var(--slate-900); }
  .tenant-info .meta{ display:flex; align-items:center; gap:6px; font-size:12px; color:var(--slate-600); margin-top:2px; }
  .tenant-info .meta .status-dot{ width:6px; height:6px; border-radius:50%; background:var(--teal); }

  .input-group{
    display:flex; align-items:center; gap:10px;
    border:1.5px solid var(--slate-300);
    border-radius:12px;
    padding:12px 14px;
    background:#fff;
    transition:border-color .15s ease, box-shadow .15s ease;
  }
  .input-group:focus-within{
    border-color:var(--teal);
    box-shadow:0 0 0 4px rgba(34,211,184,0.12);
  }
  .input-group.input-error{
    border-color:var(--coral);
    box-shadow:0 0 0 4px rgba(232,103,79,0.12);
  }
  .input-group svg{ flex-shrink:0; color:var(--slate-600); }
  .input-group input{
    border:none; outline:none; flex:1;
    font-family:'Inter', sans-serif;
    font-size:14px; color:var(--slate-900);
    background:transparent;
  }
  .input-group input::placeholder{ color:#A7ACB6; }
  .eye-btn{ background:none; border:none; cursor:pointer; color:var(--slate-600); display:flex; padding:0; }

  .field-error{
    font-size:12px;
    color:var(--coral);
    margin-top:6px;
    display:flex;
    align-items:center;
    gap:5px;
  }

  .row-between{ display:flex; align-items:center; justify-content:space-between; margin:2px 0 24px; }
  .remember{ display:flex; align-items:center; gap:8px; font-size:13px; color:var(--slate-600); cursor:pointer; }
  .remember input{ accent-color:var(--teal); width:14px; height:14px; cursor:pointer; }
  .forgot{ font-size:13px; color:var(--coral); text-decoration:none; font-weight:600; }
  .forgot:hover{ text-decoration:underline; }

  .alert-error{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 14px;
    border-radius:12px;
    background:#FEF2F0;
    border:1px solid #FADBD4;
    font-size:13px;
    color:var(--coral);
    margin-bottom:20px;
    line-height:1.4;
  }
  .alert-error svg{ flex-shrink:0; }

  .btn-primary{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background:var(--slate-900);
    color:#fff;
    font-family:'Inter', sans-serif;
    font-size:14.5px;
    font-weight:600;
    cursor:pointer;
    transition:background .15s ease, transform .1s ease;
    display:flex; align-items:center; justify-content:center; gap:8px;
  }
  .btn-primary:hover{ background:#000; }
  .btn-primary:active{ transform:scale(0.99); }
  .btn-primary:disabled{ opacity:0.5; cursor:not-allowed; }

  .security-note{
    display:flex; align-items:flex-start; gap:8px;
    margin-top:22px;
    font-size:12px;
    color:var(--slate-600);
    line-height:1.5;
  }
  .security-note svg{ flex-shrink:0; margin-top:1px; color:var(--slate-600); }

  .divider-foot{
    margin-top:34px;
    padding-top:20px;
    border-top:1px solid var(--slate-100);
    font-size:12px;
    color:#9CA1AC;
    display:flex; justify-content:space-between;
  }
  .divider-foot a{ color:var(--slate-600); text-decoration:none; font-weight:600; }
  .divider-foot a:hover{ color:var(--slate-900); }

  @media (max-width: 880px){
    body{ flex-direction:column; overflow:auto; }
    .stage{ min-height:280px; flex:none; }
    .stage-bottom h1{ font-size:clamp(24px, 5vw, 36px); }
    .panel{ max-width:100%; padding:32px 24px 60px; }
  }

  @media (prefers-reduced-motion: reduce){
    .scene3d{ display:none; }
    .title-3d{ animation:none; }
  }
</style>
</head>
<body>

  <div class="stage">
    <div class="scene3d">
      <div class="gem">
        <div class="facet f1"></div>
        <div class="facet f2"></div>
        <div class="facet f3"></div>
        <div class="facet f4"></div>
        <div class="facet f5"></div>
        <div class="facet f6"></div>
        <div class="facet top"></div>
        <div class="facet bottom"></div>
        <div class="ring"></div>
      </div>
      <div class="glow-floor"></div>
    </div>
    <div class="stage-top">
      <div class="mark"></div>
      <div class="brand-tag">SIMANTAB · Kab. Rembang</div>
    </div>
    <div class="stage-bottom">
      <div class="title-eyebrow" id="twEyebrow"></div>
      <div class="title-3d-wrap">
        <span class="title-3d"><span class="tw-line" id="twLine1"></span><br><span class="tw-line" id="twLine2"></span></span>
      </div>
      <p class="sub" id="twSub"></p>
      <div class="stage-foot">
        <span><span class="dot"></span> Sistem beroperasi normal</span>
        <span>v1.0.0</span>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="form-wrap">
      <div class="eyebrow">Masuk ke Sistem</div>
      <h2>Selamat datang kembali</h2>
      <p class="desc">Masukkan detail akun Anda untuk melanjutkan ke SIWAS Inspektorat Daerah Kabupaten Rembang.</p>

      {{-- Error Alert --}}
      @if (isset($errors) && $errors->any())
      <div class="alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
        </svg>
        <span>Email atau password salah. Silakan coba lagi.</span>
      </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
          <label>Unit Kerja</label>
          <div class="tenant-card">
            <div class="tenant-icon">🏛️</div>
            <div class="tenant-info">
              <div class="name">Inspektorat Daerah — Kab. Rembang</div>
              <div class="meta"><span class="status-dot"></span> Periode Aktif · 2026</div>
            </div>
          </div>
        </div>

        <div class="field">
          <label>Email</label>
          <div class="input-group @error('email') input-error @enderror">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email Anda" required autofocus autocomplete="email">
          </div>
          @error('email')
            <p class="field-error">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
              </svg>
              {{ $message }}
            </p>
          @enderror
        </div>

        <div class="field">
          <label>Kata Sandi</label>
          <div class="input-group @error('password') input-error @enderror">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input id="pwd" type="password" name="password" placeholder="Minimal 6 karakter" required autocomplete="current-password">
            <button class="eye-btn" onclick="togglePwd()" type="button" aria-label="Tampilkan kata sandi">
              <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          @error('password')
            <p class="field-error">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
              </svg>
              {{ $message }}
            </p>
          @enderror
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
            Ingat saya
          </label>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="forgot">Lupa kata sandi?</a>
          @endif
        </div>

        <button type="submit" class="btn-primary">
          Masuk
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M5 12h14M13 6l6 6-6 6"/>
          </svg>
        </button>
      </form>

      <div class="security-note">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        Koneksi Anda dienkripsi end-to-end. Kami tidak pernah meminta kata sandi lewat email atau telepon.
      </div>

      <div class="divider-foot">
        <span>&copy; {{ date('Y') }} Inspektorat Kab. Rembang</span>
        <a href="#">Butuh bantuan?</a>
      </div>
    </div>
  </div>

<script>
  function togglePwd(){
    const pwd = document.getElementById('pwd');
    const icon = document.getElementById('eyeIcon');
    const isPwd = pwd.type === 'password';
    pwd.type = isPwd ? 'text' : 'password';
    icon.innerHTML = isPwd
      ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/>'
      : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }

  (function initParallax(){
    const stage = document.querySelector('.stage');
    const gem = document.querySelector('.gem');
    if(!stage || !gem) return;
    stage.addEventListener('mousemove', (e)=>{
      const rect = stage.getBoundingClientRect();
      const px = (e.clientX - rect.left) / rect.width - 0.5;
      const py = (e.clientY - rect.top) / rect.height - 0.5;
      gem.style.setProperty('--tiltX', (py * -14) + 'deg');
      gem.style.setProperty('--tiltY', (px * 14) + 'deg');
    });
  })();

  (function initTypewriter(){
    const sequence = [
      { el: document.getElementById('twEyebrow'), text: 'Sistem Informasi Pengawasan', speed: 32, cursorClass: 'on-eyebrow' },
      { el: document.getElementById('twLine1'),   text: 'INSPEKTORAT DAERAH',          speed: 52, cursorClass: 'on-title' },
      { el: document.getElementById('twLine2'),   text: 'KABUPATEN REMBANG',           speed: 52, cursorClass: 'on-title' },
      { el: document.getElementById('twSub'),     text: 'Portal internal untuk pengelolaan pengawasan, tindak lanjut hasil audit, dan pelaporan kinerja perangkat daerah.', speed: 16, cursorClass: 'on-sub' }
    ].filter(step => step.el);

    if(!sequence.length) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(reduceMotion){
      sequence.forEach(step => { step.el.textContent = step.text; });
      return;
    }

    const cursor = document.createElement('span');
    cursor.className = 'tw-cursor';

    function typeStep(step){
      return new Promise(resolve => {
        step.el.textContent = '';
        step.el.parentNode.insertBefore(cursor, step.el.nextSibling);
        cursor.className = 'tw-cursor ' + step.cursorClass;

        let charIndex = 0;
        let lastTime = null;

        function frame(now){
          if(lastTime === null) lastTime = now;
          const elapsed = now - lastTime;
          if(elapsed >= step.speed){
            charIndex++;
            step.el.textContent = step.text.slice(0, charIndex);
            lastTime = now;
          }
          if(charIndex < step.text.length){
            requestAnimationFrame(frame);
          } else {
            resolve();
          }
        }
        requestAnimationFrame(frame);
      });
    }

    function pause(ms){
      return new Promise(resolve => setTimeout(resolve, ms));
    }

    async function runSequence(){
      for(const step of sequence){
        await typeStep(step);
        await pause(180);
      }
      cursor.classList.add('done');
    }

    if(document.fonts && document.fonts.ready){
      document.fonts.ready.then(runSequence);
    } else {
      runSequence();
    }
  })();
</script>
</body>
</html>