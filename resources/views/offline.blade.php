<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Copper Still · Sin conexión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/js/pos.js'])
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        :root {
            --bg:        #0e0d0b;
            --bg-hover:  #131210;
            --text:      #e8dfc8;
            --text-muted:#7a7060;
            --text-dim:  #555046;
            --gold:      #b8964a;
            --gold-dim:  #8a6e2a;
            --gold-glow: rgba(184,150,74,0.2);
            --gold-soft: rgba(184,150,74,0.08);
            --border:    #2e2b24;
            --radius: 6px;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            font-family: 'Jost', sans-serif;
            color: var(--text);
            padding: 1.5rem;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 0;
        }
        .card {
            position: relative; z-index: 1;
            text-align: center;
            max-width: 480px;
            width: 100%;
            padding: 3rem 2.5rem 2.5rem;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .icon-wrap {
            width: 96px; height: 96px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: var(--gold-soft);
            border: 1px solid rgba(184,150,74,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: var(--gold);
            position: relative;
        }
        .icon-wrap::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--gold-glow) 0%, transparent 70%);
            z-index: -1;
            animation: pulse 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.08); }
        }
        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.6rem;
            line-height: 1.15;
        }
        h1 i { color: var(--gold); font-style: normal; }
        .divider { width: 48px; height: 1px; background: var(--gold); margin: 1rem auto; }
        p {
            font-size: 14.5px; line-height: 1.7;
            color: var(--text-muted); margin-bottom: 2rem;
            max-width: 380px; margin-left: auto; margin-right: auto;
        }
        .status {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--gold); margin-bottom: 1.6rem;
        }
        .status .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); box-shadow: 0 0 8px var(--gold); animation: blink 1.6s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 32px; background: var(--gold); color: #0e0d0b;
            font-size: 14px; font-weight: 500; font-family: 'Jost', sans-serif;
            letter-spacing: 0.06em; border: none; border-radius: var(--radius);
            cursor: pointer; text-decoration: none; text-transform: uppercase;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn-home:hover { background: #d4a84e; box-shadow: 0 4px 20px rgba(184,150,74,0.35); }
        body.tiene-workspace { align-items: flex-start; }
        .workspace { position: relative; z-index: 1; width: 100%; max-width: 1100px; }
        .ws-head { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; justify-content: space-between; margin-bottom: 1.25rem; }
        .ws-head h1 { font-size: 26px; margin-bottom: .35rem; }
        .ws-head p { margin: 0; max-width: 620px; font-size: 13px; }
        .ws-head-acciones { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
        .ws-contador { font-size: 12px; color: var(--gold); margin-right: .25rem; }
        .ws-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 14px; font-family: 'Jost', sans-serif; font-size: 12px;
            letter-spacing: .04em; text-transform: uppercase; text-decoration: none;
            border: 1px solid rgba(184,150,74,.35); border-radius: var(--radius);
            background: transparent; color: var(--gold); cursor: pointer;
        }
        .ws-btn:hover { border-color: var(--gold); background: var(--gold-soft); }
        .ws-btn:disabled { opacity: .45; cursor: default; }
        .ws-btn-primario { background: var(--gold); border-color: var(--gold); color: #0e0d0b; font-weight: 500; }
        .ws-btn-primario:hover { background: #d4a84e; }
        .ws-tabla { background: var(--bg-hover); border: 1px solid var(--border); border-radius: 14px; overflow-x: auto; }
        .ws-tabla table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ws-tabla th {
            padding: 12px 14px; text-align: left; font-size: 11px; letter-spacing: .06em;
            text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border);
        }
        .ws-tabla td { padding: 14px; border-bottom: 1px solid var(--border); vertical-align: top; }
        .ws-tabla tr:last-child td { border-bottom: none; }
        .ws-vacio { text-align: center; color: var(--text-muted); padding: 32px 14px; }
        .ws-badge {
            display: inline-block; padding: 3px 8px; border-radius: 99px; font-size: 10px;
            letter-spacing: .03em; text-transform: uppercase; border: 1px solid var(--border);
        }
        .ws-badge.ame { color: #e8b45c; border-color: rgba(232,180,92,.35); }
        .ws-badge.azu { color: #79a9d6; border-color: rgba(121,169,214,.35); }
        .ws-badge.ver { color: #7cc49a; border-color: rgba(124,196,154,.35); }
        .ws-badge.gri { color: var(--text-muted); }
        .ws-pago { display: inline-block; margin-top: 5px; font-size: 11px; }
        .ws-pago.ok { color: #7cc49a; }
        .ws-pago.pend { color: var(--gold); }
        .ws-acciones { display: flex; flex-direction: column; gap: 8px; align-items: flex-end; }
        .ws-pedido { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; }
        .ws-pedido-id { color: var(--text-muted); font-size: 11px; }
        .ws-pedido select {
            background: var(--bg); color: var(--text); border: 1px solid var(--border);
            border-radius: 4px; padding: 4px 6px; font-family: 'Jost', sans-serif; font-size: 11px;
        }
        .ws-cola { font-size: 10px; color: var(--gold); }
        .ws-botones { display: flex; gap: 6px; }
        .ws-botones .ws-btn { padding: 5px 10px; font-size: 10px; }
        .ws-overlay {
            position: fixed; inset: 0; z-index: 20; display: flex; align-items: center; justify-content: center;
            background: rgba(5,5,4,.78); padding: 1.5rem;
        }
        .ws-modal {
            width: 100%; max-width: 420px; background: var(--bg-hover); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; gap: .9rem;
        }
        .ws-modal h3 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; }
        .ws-modal p { margin: 0; font-size: 12px; line-height: 1.55; }
        .ws-modal label { display: flex; flex-direction: column; gap: 5px; font-size: 12px; color: var(--text-muted); }
        .ws-modal select {
            background: var(--bg); color: var(--text); border: 1px solid var(--border);
            border-radius: 4px; padding: 8px; font-family: 'Jost', sans-serif; font-size: 13px;
        }
        .ws-lineas { max-height: 190px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; }
        .ws-lineas label { flex-direction: row; align-items: center; gap: 8px; color: var(--text); }
        .ws-modal-acciones { display: flex; gap: 8px; }
        @media (max-width: 500px) {
            .card { padding: 2.5rem 1.5rem 2rem; }
            h1 { font-size: 25px; }
            p { font-size: 13.5px; }
            .icon-wrap { width: 80px; height: 80px; font-size: 28px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
        </div>
        <h1>Sin <i>conexión</i></h1>
        <div class="divider"></div>
        <div class="status"><span class="dot"></span> Modo offline</div>
        <p>
            No hay acceso a la red en este momento. Los pedidos que crees en modo
            offline se guardarán localmente y se sincronizarán automáticamente en
            cuanto vuelva la conexión.
        </p>
        <div id="pending-wrap" style="display:none;margin-bottom:1.2rem;font-size:12.5px;color:var(--gold);">
            <span id="pending-count">0</span> pedido(s) esperando sincronización
        </div>
        <button class="btn-home" id="btn-pos" style="margin-bottom:0.75rem;width:100%;justify-content:center;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Nuevo pedido
        </button>
        <button class="btn-retry" id="btn-retry">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="retry-ico"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v5h-5"/></svg>
            Reintentar conexión
        </button>
    </div>
    <div id="offline-workspace" class="workspace" style="display:none;"></div>
    <style>
        .btn-retry {
            width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 20px; background: transparent; color: var(--gold);
            font-size: 13px; font-weight: 500; font-family: 'Jost', sans-serif;
            letter-spacing: 0.05em; border: 1px solid rgba(184,150,74,0.35); border-radius: var(--radius);
            cursor: pointer; text-transform: uppercase; transition: all 0.2s;
        }
        .btn-retry:hover { border-color: var(--gold); background: var(--gold-soft); }
        .retry-ico.rotate { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    <script>
        var btnRetry = document.getElementById('btn-retry');
        btnRetry.addEventListener('click', function () {
            btnRetry.disabled = true;
            var ico = btnRetry.querySelector('.retry-ico');
            ico.classList.add('rotate');
            window.OfflineApp.probeOnline().then(function (ok) {
                if (ok) { window.location.reload(); return; }
                btnRetry.disabled = false;
                ico.classList.remove('rotate');
            });
        });
        document.getElementById('btn-pos').addEventListener('click', function () {
            window.location.href = '/pedidos/create';
        });
        window.addEventListener('app:online', function () { window.location.reload(); });

        // Mostrar pedidos pendientes sin necesidad de red.
        function refreshPending() {
            if (window.OfflineApp && window.OfflineApp.countPending) {
                window.OfflineApp.countPending().then(function (n) {
                    var wrap = document.getElementById('pending-wrap');
                    if (n > 0) {
                        document.getElementById('pending-count').textContent = n;
                        wrap.style.display = 'block';
                    } else {
                        wrap.style.display = 'none';
                    }
                });
            }
        }
        var pa = 0;
        var ptimer = setInterval(function () {
            pa++;
            if (window.OfflineApp || pa > 20) {
                clearInterval(ptimer);
                refreshPending();
            }
        }, 50);
    </script>
</body>
</html>
