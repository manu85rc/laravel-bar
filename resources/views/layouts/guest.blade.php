<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>The Copper Still — @yield('page-title', 'Admin Console')</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

:root{
  --bg:          #0e0a05;
  --bg-2:        #16100a;
  --bg-3:        #1e160d;
  --bg-4:        #271d11;
  --bg-5:        #2f2415;

  --gold:        #c8922a;
  --gold-light:  #e8b85c;
  --gold-dim:    #7a5518;
  --gold-glow:   rgba(200,146,42,0.15);
  --gold-subtle: rgba(200,146,42,0.07);

  --text-1: #f0e6d3;
  --text-2: #b89e7a;
  --text-3: #7a6a52;
  --text-4: #4a3d2a;

  --border:     rgba(200,146,42,0.14);
  --border-dim: rgba(240,230,211,0.07);
  --border-focus: rgba(200,146,42,0.5);

  --red:     #e05252;
  --red-bg:  rgba(224,82,82,0.1);
  --green:   #4caf7d;

  --radius:    8px;
  --radius-lg: 14px;
  --radius-xl: 22px;
}

html,body{height:100%;}

body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  color:var(--text-1);
  font-size:14px;
  line-height:1.6;
  display:flex;
  min-height:100vh;
}

body::before{
  content:'';
  position:fixed;inset:0;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
  pointer-events:none;z-index:9999;opacity:.6;
}

.left{
  width:55%;
  position:relative;
  overflow:hidden;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  padding:48px;
}

.left::before{
  content:'';
  position:absolute;
  top:30%;left:50%;
  transform:translate(-50%,-50%);
  width:500px;height:500px;
  background:radial-gradient(ellipse at center, rgba(200,146,42,0.18) 0%, transparent 70%);
  pointer-events:none;
}

.left-bg{
  position:absolute;inset:0;
  background:
    linear-gradient(160deg, rgba(200,146,42,0.04) 0%, transparent 50%),
    linear-gradient(to bottom, var(--bg-2), var(--bg));
  border-right:1px solid var(--border);
  z-index:0;
}

.left-bg::after{
  content:'';
  position:absolute;inset:0;
  background-image:
    repeating-linear-gradient(
      0deg,
      transparent,
      transparent 59px,
      rgba(200,146,42,0.04) 60px
    ),
    repeating-linear-gradient(
      90deg,
      transparent,
      transparent 59px,
      rgba(200,146,42,0.04) 60px
    );
}

.left-content{position:relative;z-index:1;}

.brand{display:flex;align-items:center;gap:12px;margin-bottom:auto;}
.brand-mark{
  width:40px;height:40px;
  border-radius:10px;
  background:var(--gold);
  display:flex;align-items:center;justify-content:center;
}
.brand-mark i{color:var(--bg-2);font-size:17px;}
.brand-name{
  font-family:'Playfair Display',serif;
  font-size:17px;font-weight:600;
  color:var(--text-1);letter-spacing:0.01em;
}
.brand-sub{font-size:10px;color:var(--text-3);letter-spacing:0.08em;text-transform:uppercase;}

.illustration{
  flex:1;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  padding:40px 0;
  position:relative;z-index:1;
}

.glass-svg{
  width:220px;
  margin-bottom:32px;
  filter:drop-shadow(0 0 40px rgba(200,146,42,0.25));
  animation:float 4s ease-in-out infinite;
}

@keyframes float{
  0%,100%{transform:translateY(0);}
  50%{transform:translateY(-10px);}
}

.ill-headline{
  font-family:'Playfair Display',serif;
  font-size:32px;font-weight:700;
  color:var(--text-1);
  text-align:center;
  line-height:1.2;
  max-width:340px;
}
.ill-headline em{
  font-style:italic;
  color:var(--gold-light);
}
.ill-sub{
  font-size:14px;color:var(--text-3);
  text-align:center;margin-top:12px;
  max-width:300px;
  line-height:1.7;
}

.stats-strip{
  display:flex;gap:0;
  background:var(--bg-3);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  overflow:hidden;
  position:relative;z-index:1;
}
.stat-item{
  flex:1;
  padding:16px 20px;
  text-align:center;
  border-right:1px solid var(--border-dim);
}
.stat-item:last-child{border-right:none;}
.stat-num{
  font-family:'Playfair Display',serif;
  font-size:22px;font-weight:700;
  color:var(--gold-light);
  line-height:1;
}
.stat-desc{font-size:11px;color:var(--text-3);margin-top:3px;letter-spacing:0.03em;}

.right{
  flex:1;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:48px 40px;
  background:var(--bg);
}

.login-box{
  width:100%;
  max-width:400px;
}

.login-title{
  font-family:'Playfair Display',serif;
  font-size:26px;font-weight:700;
  color:var(--text-1);
  margin-bottom:6px;
}
.login-sub{
  font-size:13.5px;color:var(--text-3);
  margin-bottom:32px;
  line-height:1.6;
}

.field{margin-bottom:18px;}
.field-label{
  display:block;
  font-size:12px;font-weight:500;
  color:var(--text-2);
  margin-bottom:7px;
  letter-spacing:0.03em;
}
.input-wrap{position:relative;}
.input-icon{
  position:absolute;left:13px;top:50%;transform:translateY(-50%);
  color:var(--text-4);font-size:14px;
  pointer-events:none;
  transition:color .2s;
}
input{
  width:100%;
  background:var(--bg-3);
  border:1px solid var(--border-dim);
  border-radius:var(--radius);
  padding:11px 42px 11px 40px;
  font-size:14px;font-family:inherit;
  color:var(--text-1);
  outline:none;
  transition:border .2s, box-shadow .2s;
  -webkit-appearance:none;
}
input::placeholder{color:var(--text-4);}
input:hover{border-color:var(--border);}
input:focus{
  border-color:var(--border-focus);
  box-shadow:0 0 0 3px rgba(200,146,42,0.1);
}
input:focus + .focus-line,
.input-wrap:focus-within .input-icon{color:var(--gold);}

.pw-toggle{
  position:absolute;right:13px;top:50%;transform:translateY(-50%);
  color:var(--text-4);font-size:13px;
  cursor:pointer;
  background:none;border:none;padding:4px;
  transition:color .15s;
}
.pw-toggle:hover{color:var(--text-2);}

.field.error input{border-color:var(--red);box-shadow:0 0 0 3px var(--red-bg);}
.field-err{
  display:none;
  font-size:11.5px;color:var(--red);
  margin-top:6px;
}
.field.error .field-err{display:block;}

.form-meta{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:22px;
}
.checkbox-label{
  display:flex;align-items:center;gap:8px;
  cursor:pointer;
  font-size:13px;color:var(--text-3);
  user-select:none;
}
.checkbox-label input[type=checkbox]{
  width:16px;height:16px;padding:0;
  appearance:none;-webkit-appearance:none;
  background:var(--bg-3);
  border:1px solid var(--border);
  border-radius:4px;
  cursor:pointer;
  display:grid;place-content:center;
  flex-shrink:0;
}
.checkbox-label input[type=checkbox]:checked{
  background:var(--gold);border-color:var(--gold);
}
.checkbox-label input[type=checkbox]:checked::before{
  content:'';
  width:8px;height:8px;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 8'%3E%3Cpath d='M1 4l3 3 5-6' stroke='%230e0a05' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") center/contain no-repeat;
}
.forgot{
  font-size:13px;color:var(--gold);
  text-decoration:none;font-weight:500;
  transition:color .15s;
}
.forgot:hover{color:var(--gold-light);}

.btn-submit{
  width:100%;
  padding:13px;
  background:var(--gold);
  color:var(--bg-2);
  border:none;border-radius:var(--radius);
  font-size:14px;font-weight:600;font-family:inherit;
  cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:8px;
  transition:background .2s, transform .1s, box-shadow .2s;
  letter-spacing:0.02em;
  position:relative;
  overflow:hidden;
}
.btn-submit::after{
  content:'';
  position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,0.1),transparent);
  pointer-events:none;
}
.btn-submit:hover{
  background:var(--gold-light);
  box-shadow:0 4px 20px rgba(200,146,42,0.35);
}
.btn-submit:active{transform:scale(0.98);}
.btn-submit.loading{pointer-events:none;opacity:.8;}

.spinner{
  display:none;
  width:16px;height:16px;
  border:2px solid rgba(14,10,5,0.3);
  border-top-color:var(--bg-2);
  border-radius:50%;
  animation:spin .7s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg);}}
.btn-submit.loading .spinner{display:block;}
.btn-submit.loading .btn-text{opacity:0.7;}

.divider{
  display:flex;align-items:center;gap:12px;
  margin:22px 0;
  color:var(--text-4);font-size:12px;
}
.divider::before,.divider::after{
  content:'';flex:1;
  height:1px;background:var(--border-dim);
}

.btn-sso{
  width:100%;
  padding:11px;
  background:var(--bg-3);
  color:var(--text-2);
  border:1px solid var(--border-dim);
  border-radius:var(--radius);
  font-size:13px;font-weight:500;font-family:inherit;
  cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:9px;
  transition:all .15s;
}
.btn-sso:hover{border-color:var(--border);color:var(--text-1);background:var(--bg-4);}

.login-footer{
  margin-top:28px;
  padding-top:22px;
  border-top:1px solid var(--border-dim);
  text-align:center;
  font-size:12px;color:var(--text-4);
}
.login-footer a{color:var(--gold);text-decoration:none;font-weight:500;}
.login-footer a:hover{color:var(--gold-light);}

#toast{
  position:fixed;bottom:28px;left:50%;
  transform:translateX(-50%) translateY(20px);
  background:var(--bg-3);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  padding:12px 20px;
  display:flex;align-items:center;gap:10px;
  font-size:13px;color:var(--text-1);
  box-shadow:0 8px 32px rgba(0,0,0,0.5);
  opacity:0;
  transition:opacity .3s, transform .3s;
  pointer-events:none;
  z-index:1000;
  white-space:nowrap;
}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
#toast i{color:var(--red);}
#toast.success i{color:var(--green);}

@media(max-width:800px){
  .left{display:none;}
  .right{padding:40px 24px;}
  body{justify-content:center;}
}
@media(max-width:420px){
  .right{padding:32px 16px;align-items:flex-start;padding-top:60px;}
}
</style>
</head>
<body>

<div class="left">
  <div class="left-bg"></div>

  <div class="left-content brand">
    <div class="brand-mark"><i class="fa-solid fa-whiskey-glass"></i></div>
    <div>
      <div class="brand-name">The Copper Still</div>
      <div class="brand-sub">Admin Console</div>
    </div>
  </div>

  <div class="illustration">
    <svg class="glass-svg" viewBox="0 0 200 240" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="74" y="88" width="22" height="20" rx="4" fill="rgba(200,146,42,0.12)" stroke="rgba(200,146,42,0.25)" stroke-width="1"/>
      <rect x="100" y="95" width="20" height="18" rx="4" fill="rgba(200,146,42,0.1)" stroke="rgba(200,146,42,0.2)" stroke-width="1"/>
      <rect x="83" y="100" width="18" height="16" rx="4" fill="rgba(200,146,42,0.08)" stroke="rgba(200,146,42,0.18)" stroke-width="1"/>
      <path d="M62 75 Q65 115 80 118 L120 118 Q135 115 138 75 Z" fill="rgba(200,146,42,0.22)"/>
      <ellipse cx="100" cy="75" rx="38" ry="5" fill="rgba(232,184,92,0.18)"/>
      <path d="M56 42 L68 118 Q70 128 80 130 L120 130 Q130 128 132 118 L144 42 Z"
        fill="rgba(255,255,255,0.03)" stroke="rgba(200,146,42,0.35)" stroke-width="1.5"/>
      <ellipse cx="100" cy="42" rx="44" ry="7" fill="rgba(200,146,42,0.08)" stroke="rgba(200,146,42,0.4)" stroke-width="1.5"/>
      <ellipse cx="100" cy="130" rx="20" ry="4" fill="rgba(200,146,42,0.1)" stroke="rgba(200,146,42,0.25)" stroke-width="1"/>
      <rect x="97" y="130" width="6" height="48" rx="3" fill="rgba(200,146,42,0.2)" stroke="rgba(200,146,42,0.3)" stroke-width="1"/>
      <ellipse cx="100" cy="178" rx="28" ry="5" fill="rgba(200,146,42,0.15)" stroke="rgba(200,146,42,0.3)" stroke-width="1"/>
      <ellipse cx="100" cy="180" rx="30" ry="4" fill="rgba(200,146,42,0.08)" stroke="rgba(200,146,42,0.2)" stroke-width="1"/>
      <path d="M136 50 Q150 38 148 52 Q146 62 136 58 Q130 54 136 50Z"
        fill="rgba(232,130,50,0.5)" stroke="rgba(232,130,50,0.7)" stroke-width="1"/>
      <line x1="136" y1="42" x2="118" y2="65" stroke="rgba(200,146,42,0.6)" stroke-width="1.2" stroke-linecap="round"/>
      <circle cx="136" cy="40" r="4" fill="var(--gold)" opacity=".8"/>
      <path d="M65 50 Q67 65 70 80" stroke="rgba(255,255,255,0.08)" stroke-width="2" stroke-linecap="round"/>
      <circle cx="38" cy="60" r="1.5" fill="rgba(232,184,92,0.5)"/>
      <circle cx="164" cy="78" r="1" fill="rgba(232,184,92,0.4)"/>
      <circle cx="48" cy="110" r="1" fill="rgba(232,184,92,0.3)"/>
      <circle cx="160" cy="45" r="1.5" fill="rgba(232,184,92,0.4)"/>
    </svg>

    <h1 class="ill-headline">@yield('left-headline', 'Bienvenido de nuevo a <em>The Still</em>')</h1>
    <p class="ill-sub">@yield('left-subtitle', 'Tu venue de un vistazo — reservas, cuentas, inventario y personal en un solo lugar.')</p>
  </div>

  <div class="stats-strip">
    <div class="stat-item">
      <div class="stat-num">$4.8k</div>
      <div class="stat-desc">Ingresos de hoy</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">47</div>
      <div class="stat-desc">Comensales atendidos</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">8</div>
      <div class="stat-desc">Cuentas abiertas</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">6</div>
      <div class="stat-desc">Staff en turno</div>
    </div>
  </div>
</div>

<div class="right">
  <div class="login-box">
    @yield('content')
  </div>
</div>

<div id="toast">
  <i class="fa-solid fa-circle-xmark"></i>
  <span id="toast-msg">Error.</span>
</div>

<script>
function togglePassword(){
  const input = document.getElementById('password');
  const icon  = document.getElementById('pw-icon');
  const shown = input.type === 'text';
  input.type  = shown ? 'password' : 'text';
  icon.className = shown ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
}

function showToast(msg, success){
  const toast = document.getElementById('toast');
  const icon  = toast.querySelector('i');
  document.getElementById('toast-msg').textContent = msg;
  icon.className = success
    ? 'fa-solid fa-circle-check'
    : 'fa-solid fa-circle-xmark';
  toast.className = success ? 'show success' : 'show';
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => toast.className = '', 3500);
}

document.addEventListener('DOMContentLoaded', function() {
    @if ($errors->any())
    showToast(@json($errors->first()), false);
    @endif
    @if (session('status'))
    showToast(@json(session('status')), true);
    @endif
});
</script>

@stack('scripts')

</body>
</html>
