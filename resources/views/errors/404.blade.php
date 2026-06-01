<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Copper Still · 404</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

        .plate-svg {
            position: absolute;
            bottom: -12px;
            right: -12px;
            font-size: 48px;
            color: rgba(184,150,74,0.1);
            transform: rotate(15deg);
            pointer-events: none;
        }

        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.6rem;
            line-height: 1.15;
        }

        h1 i { color: var(--gold); font-style: normal; }

        .divider {
            width: 48px; height: 1px;
            background: var(--gold);
            margin: 1rem auto;
        }

        p {
            font-size: 14.5px;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 2rem;
            max-width: 380px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 32px;
            background: var(--gold);
            color: #0e0d0b;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Jost', sans-serif;
            letter-spacing: 0.06em;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            text-decoration: none;
            text-transform: uppercase;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
        }

        .btn-home:hover {
            background: #d4a84e;
            box-shadow: 0 4px 20px rgba(184,150,74,0.35);
        }

        .btn-home:active { transform: scale(0.97); }

        .status-code {
            font-family: 'Cormorant Garamond', serif;
            font-size: 13px;
            letter-spacing: 0.25em;
            color: var(--text-dim);
            margin-top: 1.8rem;
            text-transform: uppercase;
        }

        @media (max-width: 500px) {
            .card { padding: 2.5rem 1.5rem 2rem; }
            h1 { font-size: 26px; }
            p { font-size: 13.5px; }
            .icon-wrap { width: 80px; height: 80px; font-size: 28px; }
            .plate-svg { font-size: 36px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <i class="fa-solid fa-utensils"></i>
            <i class="fa-solid fa-plate-wheat plate-svg"></i>
        </div>

        <h1>Esta página <i>no está en la carta</i></h1>
        <div class="divider"></div>
        <p>
            El staff ya fue a preguntar a cocina, pero esta página no figura en nuestro menú.
            Parece que alguien se la comió. ¿Por qué no pruebas con algo de nuestra selección principal?
        </p>

        <a href="{{ url('/') }}" class="btn-home">
            <i class="fa-solid fa-arrow-left"></i> Volver al comedor
        </a>

        <div class="status-code">Error 404 · Página no encontrada</div>
    </div>
</body>
</html>
