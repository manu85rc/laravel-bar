@php
    $activeCategories = $categorias->filter(fn($cat) =>
        $cat->productos->isNotEmpty() ||
        $cat->children->filter(fn($child) => $child->productos->isNotEmpty())->isNotEmpty()
    );
    $isUser = auth()->check() && auth()->user()->isUser();
    $isStaff = auth()->check() && auth()->user()->isStaff();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branding->app_name ?? 'Mi Bar' }} · Menú</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0e0d0b;
            --bg-hover:  #131210;
            --text:      #e8dfc8;
            --text-muted:#7a7060;
            --text-dim:  #555046;
            --gold:      #b8964a;
            --gold-dim:  #8a6e2a;
            --border:    #2e2b24;
            --border-item:#1e1c18;
            --green:     #4caf7d;
            --green-bg:  rgba(76,175,125,0.15);
            --red:       #e05252;
            --red-bg:    rgba(224,82,82,0.15);
            --amber:     #e8b85c;
            --amber-bg:  rgba(232,184,92,0.15);
            --blue:      #63a4d8;
            --blue-bg:   rgba(99,164,216,0.15);
            --radius: 8px;
            --radius-lg: 12px;
        }

        html { font-size: 16px; }
        body { background: var(--bg); color: var(--text); font-family: 'Jost', sans-serif; min-height: 100vh; }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0.6rem clamp(1rem, 4vw, 2rem);
            border-bottom: 1px solid var(--border);
        }

        .btn-reservar {
            font-family: 'Jost', sans-serif;
            font-size: clamp(11px, 2vw, 13px);
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: clamp(8px, 1.5vw, 12px) clamp(18px, 3vw, 28px);
            background: var(--gold);
            color: #0e0d0b;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-reservar:hover { background: #d4a84e; }
        .btn-reservar:active { transform: scale(0.97); }

        .menu-header {
            text-align: center;
            padding: clamp(1.5rem, 5vw, 3rem) 1rem clamp(1rem, 3vw, 1.5rem);
            border-bottom: 1px solid var(--border);
        }
        .menu-header::before,
        .menu-header::after {
            content: '';
            display: block;
            width: 48px;
            height: 1px;
            background: var(--gold);
            margin: 0 auto;
        }
        .menu-header::before { margin-bottom: clamp(0.6rem, 2vw, 1rem); }
        .menu-header::after  { margin-top:    clamp(0.6rem, 2vw, 1rem); }

        .menu-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(28px, 7vw, 48px);
            font-weight: 600;
            letter-spacing: 0.12em;
            color: var(--text);
            line-height: 1;
        }
        .menu-subtitle {
            font-size: clamp(9px, 2vw, 11px);
            letter-spacing: 0.3em;
            color: var(--gold);
            margin-top: 8px;
            text-transform: uppercase;
        }

        /* ===== AUTH MODAL ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 420px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem 1.8rem 1.8rem;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 12px;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            padding: 4px;
            transition: color 0.2s;
        }
        .modal-close:hover { color: var(--text); }
        .modal-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }
        .modal-tab {
            flex: 1;
            text-align: center;
            padding: 0.7rem 0.5rem;
            font-size: clamp(12px, 2.5vw, 14px);
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            font-family: 'Jost', sans-serif;
            transition: color 0.2s, border-color 0.2s;
            -webkit-tap-highlight-color: transparent;
        }
        .modal-tab:hover { color: var(--text); }
        .modal-tab.active { color: var(--gold); border-bottom-color: var(--gold); }
        .modal-form { display: none; }
        .modal-form.active { display: block; }
        .modal-field { margin-bottom: 1rem; }
        .modal-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 5px;
            letter-spacing: 0.03em;
        }
        .modal-input, .modal-select {
            width: 100%;
            padding: 10px 12px;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-size: 14px;
            font-family: 'Jost', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .modal-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='%237a7060'%3E%3Cpath d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 28px;
        }
        .modal-select option { background: var(--bg); color: var(--text); }
        .modal-input:focus, .modal-select:focus { border-color: var(--gold); }
        .modal-input::placeholder { color: var(--text-dim); }
        .modal-submit {
            width: 100%;
            padding: 12px;
            background: var(--gold);
            color: #0e0d0b;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Jost', sans-serif;
            cursor: pointer;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            transition: background 0.2s, transform 0.1s;
            margin-top: 0.5rem;
        }
        .modal-submit:hover { background: #d4a84e; }
        .modal-submit:active { transform: scale(0.98); }
        .modal-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .modal-submit-secondary {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .modal-submit-secondary:hover {
            border-color: var(--text-muted);
            color: var(--text);
            background: var(--bg-hover);
        }
        .modal-submit-danger {
            background: var(--red);
            color: #fff;
        }
        .modal-submit-danger:hover { background: #c0392b; }
        .modal-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 1.2rem 0;
            color: var(--text-dim);
            font-size: 11px;
        }
        .modal-divider::before,
        .modal-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .modal-err {
            display: none;
            font-size: 11.5px;
            color: #e05252;
            margin-top: 5px;
        }
        .modal-field.error .modal-err { display: block; }
        .modal-field.error .modal-input,
        .modal-field.error .modal-select { border-color: #e05252; }

        /* ===== RESERVATION MODAL ===== */
        .modal-wide {
            max-width: 520px;
        }

        .reserva-card {
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem 1.4rem;
            margin-bottom: 1rem;
        }
        .reserva-card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 0.7rem;
        }
        .reserva-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.35rem 0;
            font-size: 13px;
            color: var(--text);
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .reserva-card-row .label {
            color: var(--text-muted);
            font-size: 12px;
        }
        .reserva-card-row .value {
            font-weight: 500;
            text-align: right;
        }
        .reserva-card-actions {
            display: flex;
            gap: 8px;
            margin-top: 1rem;
            padding-top: 0.8rem;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }
        .reserva-card-actions .btn-sm {
            padding: 7px 16px;
            font-size: 12px;
            font-family: 'Jost', sans-serif;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.15s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .reserva-card-actions .btn-sm-gold {
            background: var(--gold);
            color: #0e0d0b;
        }
        .reserva-card-actions .btn-sm-gold:hover { background: #d4a84e; }
        .reserva-card-actions .btn-sm-danger {
            background: transparent;
            border: 1px solid var(--red);
            color: var(--red);
        }
        .reserva-card-actions .btn-sm-danger:hover {
            background: var(--red-bg);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 99px;
        }
        .badge-pendiente { background: var(--amber-bg); color: var(--amber); }
        .badge-confirmada { background: var(--green-bg); color: var(--green); }
        .badge-completada { background: var(--blue-bg); color: var(--blue); }
        .badge-cancelada { background: var(--red-bg); color: var(--red); }

        .reserva-empty {
            text-align: center;
            padding: 2rem 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .btn-link-modal {
            background: none;
            border: none;
            color: var(--gold);
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            padding: 0;
            transition: color 0.2s;
        }
        .btn-link-modal:hover { color: #d4a84e; }
        .btn-link-modal.danger { color: var(--red); }
        .btn-link-modal.danger:hover { color: #ff6b6b; }

        .confirm-dialog {
            text-align: center;
            padding: 1.5rem 0;
        }
        .confirm-dialog p {
            font-size: 14px;
            color: var(--text);
            margin-bottom: 1.2rem;
        }
        .confirm-dialog .btn-row {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 540px) {
            .top-bar { padding: 0.5rem 0.8rem; }
            .modal { padding: 1.5rem 1rem 1.2rem; }
            .modal-wide { max-width: 100%; }
            .reserva-card { padding: 1rem; }
            .reserva-card-row { font-size: 12px; }
        }

        .empty-state {
            text-align: center;
            padding: clamp(3rem, 10vw, 6rem) 1rem;
        }
        .empty-state h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(24px, 5vw, 36px);
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: var(--text-muted);
            font-size: clamp(13px, 2.5vw, 15px);
        }

        .tabs-wrapper {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--bg);
            border-bottom: 1px solid var(--border);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .tabs-wrapper::-webkit-scrollbar { display: none; }

        .tabs {
            display: flex;
            min-width: max-content;
            width: 100%;
            justify-content: center;
        }

        @media (max-width: 540px) {
            .tabs { justify-content: flex-start; padding: 0 0.5rem; }
        }

        .tab {
            font-size: clamp(9px, 2.2vw, 11px);
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: clamp(0.7rem, 2vw, 1rem) clamp(0.8rem, 2.5vw, 1.4rem);
            cursor: pointer;
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            font-family: 'Jost', sans-serif;
            white-space: nowrap;
            transition: color 0.2s, border-color 0.2s;
            -webkit-tap-highlight-color: transparent;
            min-height: 44px;
        }
        .tab:hover  { color: var(--text); }
        .tab.active { color: var(--gold); border-bottom-color: var(--gold); }

        .section { display: none; padding: clamp(1.2rem, 4vw, 2rem) clamp(1rem, 4vw, 1.5rem); max-width: 820px; margin-left: auto; margin-right: auto; }
        .section.active { display: block; }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(10px, 2.5vw, 13px);
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--gold);
            margin: 0 0 1.2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
            gap: 0;
        }

        .item {
            padding: clamp(0.75rem, 2.5vw, 1rem) 8px clamp(0.75rem, 2.5vw, 1rem) 0;
            border-bottom: 1px solid var(--border-item);
            transition: background 0.15s, padding-left 0.15s;
            cursor: default;
        }
        .item:hover { background: var(--bg-hover); padding-left: 8px; }

        .item-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .item-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(15px, 4vw, 18px);
            font-weight: 600;
            color: var(--text);
            line-height: 1.25;
        }
        .item-desc {
            font-size: clamp(11px, 2.5vw, 12px);
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.55;
        }
        .item-price {
            font-size: clamp(12px, 3vw, 13px);
            color: var(--gold);
            white-space: nowrap;
            font-weight: 500;
            padding-top: 2px;
            flex-shrink: 0;
        }

        .section-gap { margin-top: clamp(1.2rem, 4vw, 2rem); }

        .menu-footer {
            text-align: center;
            padding: clamp(1rem, 4vw, 2rem) 1rem;
            border-top: 1px solid var(--border);
            margin-top: clamp(1rem, 4vw, 2rem);
        }
        .menu-footer p {
            font-size: 11px;
            letter-spacing: 0.15em;
            color: var(--text-dim);
            text-transform: uppercase;
        }
        .menu-footer span { color: var(--gold); }

        .reserva-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .reserva-form-grid .full { grid-column: 1 / -1; }

        @media (max-width: 480px) {
            .reserva-form-grid {
                grid-template-columns: 1fr;
            }
        }

        .alert-banner {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-banner.success {
            background: var(--green-bg);
            border: 1px solid var(--green);
            color: var(--green);
        }
        .alert-banner.error {
            background: var(--red-bg);
            border: 1px solid var(--red);
            color: var(--red);
        }

        .modal-field .help-text {
            font-size: 11px;
            color: var(--text-dim);
            margin-top: 3px;
        }

        .fab {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--gold);
            color: #0e0d0b;
            border: none;
            font-size: 22px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(184,150,74,0.4);
            transition: transform 0.15s, box-shadow 0.15s;
            text-decoration: none;
            z-index: 100;
        }
        .fab:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 28px rgba(184,150,74,0.55);
        }
        .fab:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>

    <div class="top-bar">
        @auth
            @if ($isUser)
                <button class="btn-reservar" onclick="openReservarModal()">Reservar</button>
            @else
                <a href="{{ route('reservas.create') }}" class="btn-reservar">Reservar</a>
            @endif
        @else
            <button class="btn-reservar" onclick="openModal()">Reservar</button>
        @endauth
    </div>

    <header class="menu-header">
        <div class="menu-logo">{{ $branding->app_name ?? 'Mi Bar' }}</div>
        @if ($branding->tagline)
            <div class="menu-subtitle">{{ $branding->tagline }}</div>
        @endif
    </header>

    @if ($activeCategories->isEmpty())
        <div class="empty-state">
            <h2>Próximamente</h2>
            <p>Estamos preparando nuestra carta para ti.</p>
        </div>
    @else
        <div class="tabs-wrapper" role="tablist" aria-label="Secciones del menú">
            <div class="tabs">
                @foreach ($activeCategories as $i => $categoria)
                    <button class="tab {{ $loop->first ? 'active' : '' }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="cat-{{ $categoria->id }}" onclick="switchTab('cat-{{ $categoria->id }}', this)">{{ $categoria->name }}</button>
                @endforeach
            </div>
        </div>

        @foreach ($activeCategories as $categoria)
            @php
                $hasDirectProducts = $categoria->productos->isNotEmpty();
                $subcategoriesWithProducts = $categoria->children->filter(fn($child) => $child->productos->isNotEmpty());
            @endphp
            <section id="cat-{{ $categoria->id }}" class="section {{ $loop->first ? 'active' : '' }}" role="tabpanel">

                @if ($categoria->description)
                    <h2 class="section-title">{{ $categoria->description }}</h2>
                @endif

                @if ($hasDirectProducts)
                    <div class="items-grid">
                        @foreach ($categoria->productos as $producto)
                            <div class="item">
                                <div class="item-row">
                                    <span class="item-name">{{ $producto->nombre }}</span>
                                    <span class="item-price">${{ number_format($producto->precio) }}</span>
                                </div>
                                @if ($producto->descripcion)
                                    <div class="item-desc">{{ $producto->descripcion }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @foreach ($subcategoriesWithProducts as $subcategoria)
                    <div class="{{ $hasDirectProducts ? 'section-gap' : '' }}">
                        <h2 class="section-title">{{ $subcategoria->name }}</h2>
                        <div class="items-grid">
                            @foreach ($subcategoria->productos as $producto)
                                <div class="item">
                                    <div class="item-row">
                                        <span class="item-name">{{ $producto->nombre }}</span>
                                        <span class="item-price">${{ number_format($producto->precio) }}</span>
                                    </div>
                                    @if ($producto->descripcion)
                                        <div class="item-desc">{{ $producto->descripcion }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>
        @endforeach

        <footer class="menu-footer">
            <p>
                @if ($branding->address)
                    {{ $branding->address }} &nbsp;·&nbsp;
                @endif
                <span>{{ $branding->phone ?? '' }}</span>
            </p>
        </footer>
    @endif

    <!-- ===== MODAL LOGIN / REGISTER ===== -->
    <div class="modal-overlay" id="authModal">
        <div class="modal">
            <button class="modal-close" onclick="closeModal()">&times;</button>

            <div class="modal-tabs">
                <button class="modal-tab active" id="tabLogin" onclick="switchModalTab('login')">Iniciar sesión</button>
                <button class="modal-tab" id="tabRegister" onclick="switchModalTab('register')">Registrarse</button>
            </div>

            <form class="modal-form active" id="formLogin" method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <input type="hidden" name="redirect_from" value="reservar">
                <div class="modal-field" id="mfield-email">
                    <label class="modal-label" for="memail">Correo electrónico</label>
                    <input class="modal-input" type="email" name="email" id="memail" placeholder="tucorreo@ejemplo.com" autocomplete="email">
                    <div class="modal-err">Ingresa un correo válido.</div>
                </div>
                <div class="modal-field" id="mfield-password">
                    <label class="modal-label" for="mpassword">Contraseña</label>
                    <input class="modal-input" type="password" name="password" id="mpassword" placeholder="••••••••" autocomplete="current-password">
                    <div class="modal-err">La contraseña debe tener al menos 6 caracteres.</div>
                </div>
                <button class="modal-submit" type="submit">Iniciar sesión</button>
            </form>

            <form class="modal-form" id="formRegister" method="POST" action="{{ route('register') }}" novalidate>
                @csrf
                <input type="hidden" name="redirect_from" value="reservar">
                <div class="modal-field" id="mfield-name">
                    <label class="modal-label" for="mname">Nombre</label>
                    <input class="modal-input" type="text" name="name" id="mname" placeholder="Tu nombre" autocomplete="name">
                    <div class="modal-err">El nombre es obligatorio.</div>
                </div>
                <div class="modal-field" id="mfield-email2">
                    <label class="modal-label" for="memail2">Correo electrónico</label>
                    <input class="modal-input" type="email" name="email" id="memail2" placeholder="tucorreo@ejemplo.com" autocomplete="email">
                    <div class="modal-err">Ingresa un correo válido.</div>
                </div>
                <div class="modal-field" id="mfield-pass">
                    <label class="modal-label" for="mpass">Contraseña</label>
                    <input class="modal-input" type="password" name="password" id="mpass" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    <div class="modal-err">Mínimo 8 caracteres.</div>
                </div>
                <div class="modal-field" id="mfield-pass-confirm">
                    <label class="modal-label" for="mpass-confirm">Confirmar contraseña</label>
                    <input class="modal-input" type="password" name="password_confirmation" id="mpass-confirm" placeholder="Repite la contraseña" autocomplete="new-password">
                    <div class="modal-err">Las contraseñas no coinciden.</div>
                </div>
                <button class="modal-submit" type="submit">Crear cuenta</button>
            </form>
        </div>
    </div>

    <!-- ===== MODAL RESERVAS ===== -->
    <div class="modal-overlay" id="reservarModal">
        <div class="modal modal-wide">
            <button class="modal-close" onclick="closeReservarModal()">&times;</button>

            @if (session('success'))
                <div class="alert-banner success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert-banner error"><i class="fa-solid fa-circle-exclamation"></i>
                    <ul style="margin:0;padding-left:16px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div id="reservaCurrentView">
                @if ($activeReserva)
                    <div class="reserva-card">
                        <div class="reserva-card-title">Tu reserva actual</div>
                        @php
                            $dt = \Carbon\Carbon::parse($activeReserva->fecha_hora);
                            $estados = [
                                'pendiente' => ['label' => 'Pendiente', 'class' => 'badge-pendiente'],
                                'confirmada' => ['label' => 'Confirmada', 'class' => 'badge-confirmada'],
                                'completada' => ['label' => 'Completada', 'class' => 'badge-completada'],
                                'cancelada' => ['label' => 'Cancelada', 'class' => 'badge-cancelada'],
                            ];
                            $estInfo = $estados[$activeReserva->estado] ?? ['label' => $activeReserva->estado, 'class' => ''];
                        @endphp
                        <div class="reserva-card-row">
                            <span class="label">Fecha</span>
                            <span class="value">{{ $dt->format('d/m/Y') }}</span>
                        </div>
                        <div class="reserva-card-row">
                            <span class="label">Hora</span>
                            <span class="value">{{ $dt->format('H:i') }}</span>
                        </div>
                        <div class="reserva-card-row">
                            <span class="label">Personas</span>
                            <span class="value">{{ $activeReserva->numero_personas }}</span>
                        </div>
                        @if ($activeReserva->observaciones)
                        <div class="reserva-card-row">
                            <span class="label">Observaciones</span>
                            <span class="value">{{ $activeReserva->observaciones }}</span>
                        </div>
                        @endif
                        <div class="reserva-card-row">
                            <span class="label">Estado</span>
                            <span class="value"><span class="badge {{ $estInfo['class'] }}">{{ $estInfo['label'] }}</span></span>
                        </div>
                        <div class="reserva-card-actions">
                            <button class="btn-sm btn-sm-gold" onclick="editReserva()">Editar</button>
                            <button class="btn-sm btn-sm-danger" onclick="confirmDelete({{ $activeReserva->id }})">Cancelar reserva</button>
                        </div>
                    </div>

                    <form id="deleteForm" method="POST" action="{{ route('reservas.destroy', $activeReserva->id) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="home">
                    </form>
                @else
                    <div id="reservaCreateView">
                        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--gold);margin-bottom:1.2rem;">Nueva Reserva</h3>
                        <form method="POST" action="{{ route('reservas.store') }}" onsubmit="return validateReservaForm()">
                            @csrf
                            <input type="hidden" name="redirect_to" value="home">
                            <div class="reserva-form-grid">
                                <div class="modal-field full">
                                    <label class="modal-label" for="resFecha">Fecha</label>
                                    <input class="modal-input" type="date" name="fecha" id="resFecha"
                                        value="{{ $defaultDate->format('Y-m-d') }}"
                                        min="{{ date('Y-m-d') }}" required>
                                    <div class="help-text">Selecciona el día de tu reserva</div>
                                </div>
                                <div class="modal-field full">
                                    <label class="modal-label" for="resHora">Horario</label>
                                    <select class="modal-select" name="hora" id="resHora" required>
                                        <option value="">Selecciona un horario</option>
                                    </select>
                                </div>
                                <div class="modal-field">
                                    <label class="modal-label" for="resPersonas">Personas</label>
                                    <input class="modal-input" type="number" name="numero_personas" id="resPersonas"
                                        min="1" max="{{ $reservationSettings->max_personas }}"
                                        placeholder="1" required>
                                </div>
                                <div class="modal-field full">
                                    <label class="modal-label" for="resObs">Observaciones</label>
                                    <textarea class="modal-input" name="observaciones" id="resObs" rows="2"
                                        placeholder="Alergias, ocasión especial..." style="resize:vertical;min-height:44px;"></textarea>
                                </div>
                            </div>
                            <button class="modal-submit" type="submit" id="btnCrearReserva">Crear Reserva</button>
                        </form>
                    </div>
                @endif
            </div>

            <div id="reservaEditView" style="display:none;">
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--gold);margin-bottom:1.2rem;">Editar Reserva</h3>
                <form method="POST" action="" id="editReservaForm" onsubmit="return validateEditForm()">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to" value="home">
                    <div class="reserva-form-grid">
                        <div class="modal-field full">
                            <label class="modal-label" for="editFecha">Fecha</label>
                            <input class="modal-input" type="date" name="fecha" id="editFecha"
                                value="{{ $defaultDate->format('Y-m-d') }}"
                                min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="modal-field full">
                            <label class="modal-label" for="editHora">Horario</label>
                            <select class="modal-select" name="hora" id="editHora" required>
                                <option value="">Selecciona un horario</option>
                            </select>
                        </div>
                        <div class="modal-field">
                            <label class="modal-label" for="editPersonas">Personas</label>
                            <input class="modal-input" type="number" name="numero_personas" id="editPersonas"
                                min="1" max="{{ $reservationSettings->max_personas }}" required>
                        </div>
                        <div class="modal-field full">
                            <label class="modal-label" for="editObs">Observaciones</label>
                            <textarea class="modal-input" name="observaciones" id="editObs" rows="2"
                                style="resize:vertical;min-height:44px;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;margin-top:0.5rem;flex-wrap:wrap;">
                        <button class="modal-submit" type="submit" style="flex:1;">Guardar Cambios</button>
                        <button class="modal-submit modal-submit-secondary" type="button" onclick="cancelEdit()" style="flex:1;">Cancelar</button>
                    </div>
                </form>
            </div>

            <div id="reservaDeleteConfirm" style="display:none;">
                <div class="confirm-dialog">
                    <p>¿Estás seguro de que deseas cancelar esta reserva?</p>
                    <div class="btn-row">
                        <button class="modal-submit modal-submit-danger" onclick="submitDelete()" style="flex:0 1 auto;padding:10px 24px;">Sí, cancelar</button>
                        <button class="modal-submit modal-submit-secondary" onclick="cancelDelete()" style="flex:0 1 auto;padding:10px 24px;">Volver</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(id, el) {
            document.querySelectorAll('.section').forEach(function(s) {
                s.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(function(t) {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            document.getElementById(id).classList.add('active');
            el.classList.add('active');
            el.setAttribute('aria-selected', 'true');
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }

        // ===== AUTH MODAL =====
        function openModal() {
            document.getElementById('authModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('authModal').classList.remove('show');
            document.body.style.overflow = '';
        }
        document.getElementById('authModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        function switchModalTab(tab) {
            document.getElementById('tabLogin').classList.toggle('active', tab === 'login');
            document.getElementById('tabRegister').classList.toggle('active', tab === 'register');
            document.getElementById('formLogin').classList.toggle('active', tab === 'login');
            document.getElementById('formRegister').classList.toggle('active', tab === 'register');
        }

        function validateLogin() {
            let ok = true;
            const email = document.getElementById('memail').value.trim();
            const pass  = document.getElementById('mpassword').value;
            document.getElementById('mfield-email').classList.remove('error');
            document.getElementById('mfield-password').classList.remove('error');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('mfield-email').classList.add('error'); ok = false;
            }
            if (pass.length < 6) {
                document.getElementById('mfield-password').classList.add('error'); ok = false;
            }
            return ok;
        }
        function validateRegister() {
            let ok = true;
            const name = document.getElementById('mname').value.trim();
            const email = document.getElementById('memail2').value.trim();
            const pass = document.getElementById('mpass').value;
            const conf = document.getElementById('mpass-confirm').value;
            document.getElementById('mfield-name').classList.remove('error');
            document.getElementById('mfield-email2').classList.remove('error');
            document.getElementById('mfield-pass').classList.remove('error');
            document.getElementById('mfield-pass-confirm').classList.remove('error');
            if (name.length < 2) {
                document.getElementById('mfield-name').classList.add('error'); ok = false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('mfield-email2').classList.add('error'); ok = false;
            }
            if (pass.length < 8) {
                document.getElementById('mfield-pass').classList.add('error'); ok = false;
            }
            if (pass !== conf) {
                document.getElementById('mfield-pass-confirm').classList.add('error'); ok = false;
            }
            return ok;
        }

        document.getElementById('formLogin').addEventListener('submit', function(e) {
            if (!validateLogin()) e.preventDefault();
        });
        document.getElementById('formRegister').addEventListener('submit', function(e) {
            if (!validateRegister()) e.preventDefault();
        });

        // ===== RESERVATION MODAL =====
        function openReservarModal() {
            document.getElementById('reservarModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeReservarModal() {
            document.getElementById('reservarModal').classList.remove('show');
            document.body.style.overflow = '';
            cancelEdit();
            cancelDelete();
        }
        document.getElementById('reservarModal').addEventListener('click', function(e) {
            if (e.target === this) closeReservarModal();
        });

        function editReserva() {
            @if ($activeReserva)
            var dt = @json(\Carbon\Carbon::parse($activeReserva->fecha_hora)->format('Y-m-d'));
            var hr = @json(\Carbon\Carbon::parse($activeReserva->fecha_hora)->format('H:i'));
            var pax = {{ $activeReserva->numero_personas }};
            var obs = @json($activeReserva->observaciones ?? '');
            document.getElementById('editFecha').value = dt;
            var dow = new Date(dt + 'T12:00:00').getDay();
            populateTimeSlots(document.getElementById('editHora'), dow === 0 ? 7 : dow, hr, isToday(dt));
            document.getElementById('editPersonas').value = pax;
            document.getElementById('editObs').value = obs;
            document.getElementById('editReservaForm').action = '{{ route("reservas.update", $activeReserva->id) }}';
            document.getElementById('reservaCurrentView').style.display = 'none';
            document.getElementById('reservaEditView').style.display = 'block';
            @endif
        }
        function cancelEdit() {
            document.getElementById('reservaEditView').style.display = 'none';
            document.getElementById('reservaCurrentView').style.display = '';
        }

        var deleteId = null;
        function confirmDelete(id) {
            deleteId = id;
            document.getElementById('reservaCurrentView').style.display = 'none';
            document.getElementById('reservaDeleteConfirm').style.display = 'block';
        }
        function cancelDelete() {
            deleteId = null;
            document.getElementById('reservaDeleteConfirm').style.display = 'none';
            document.getElementById('reservaCurrentView').style.display = '';
        }
        function submitDelete() {
            document.getElementById('deleteForm').submit();
        }

        // ===== DYNAMIC TIME SLOTS BY DAY =====
        var daySlots = @json($daySlotsJson);

        function isToday(dateStr) {
            var d = new Date(dateStr + 'T12:00:00');
            var t = new Date();
            return d.getFullYear() === t.getFullYear() &&
                   d.getMonth() === t.getMonth() &&
                   d.getDate() === t.getDate();
        }

        function populateTimeSlots(selectEl, dayOfWeek, selectedValue, filterPast) {
            selectEl.innerHTML = '<option value="">Selecciona un horario</option>';
            var data = daySlots[dayOfWeek];
            if (!data || !data.activo) {
                selectEl.disabled = true;
                return;
            }
            selectEl.disabled = false;
            var nowMin = filterPast ? new Date().getHours() * 60 + new Date().getMinutes() : -1;
            data.groups.forEach(function(group) {
                var appendTo = selectEl;
                var og = null;
                if (group.label) {
                    og = document.createElement('optgroup');
                    og.label = group.label;
                    appendTo = og;
                }
                var hasSlots = false;
                group.slots.forEach(function(slot) {
                    if (filterPast) {
                        var parts = slot.split(':');
                        var slotMin = parseInt(parts[0]) * 60 + parseInt(parts[1]);
                        if (slotMin <= nowMin) return;
                    }
                    var opt = document.createElement('option');
                    opt.value = slot;
                    opt.textContent = slot;
                    if (selectedValue && slot === selectedValue) opt.selected = true;
                    appendTo.appendChild(opt);
                    hasSlots = true;
                });
                if (og && hasSlots) selectEl.appendChild(og);
            });
        }

        function updateCreateSlots() {
            var val = document.getElementById('resFecha').value;
            if (!val) return;
            var dow = new Date(val + 'T12:00:00').getDay();
            populateTimeSlots(document.getElementById('resHora'), dow === 0 ? 7 : dow, '', isToday(val));
        }

        function updateEditSlots() {
            var val = document.getElementById('editFecha').value;
            if (!val) return;
            var dow = new Date(val + 'T12:00:00').getDay();
            populateTimeSlots(document.getElementById('editHora'), dow === 0 ? 7 : dow, '', isToday(val));
            var editHora = document.getElementById('editHora');
            if (editHora.options.length === 1) editHora.disabled = true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCreateSlots();
            updateEditSlots();
            document.getElementById('resFecha').addEventListener('change', updateCreateSlots);
            document.getElementById('editFecha').addEventListener('change', updateEditSlots);
        });

        function validateReservaForm() {
            var fecha = document.getElementById('resFecha').value;
            var hora = document.getElementById('resHora').value;
            var pers = document.getElementById('resPersonas').value;
            if (!fecha || !hora || !pers) {
                alert('Completa todos los campos requeridos.');
                return false;
            }
            return true;
        }
        function validateEditForm() {
            var fecha = document.getElementById('editFecha').value;
            var hora = document.getElementById('editHora').value;
            var pers = document.getElementById('editPersonas').value;
            if (!fecha || !hora || !pers) {
                alert('Completa todos los campos requeridos.');
                return false;
            }
            return true;
        }

        // Auto-open reservation modal after login/register
        @if (session('open_reservar'))
        document.addEventListener('DOMContentLoaded', function() {
            openReservarModal();
        });
        @endif

        // Close modals on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeReservarModal();
            }
        });
    </script>

    @if ($isStaff)
        <a href="{{ route('pedidos.create') }}" class="fab" title="Nuevo Pedido">
            <i class="fa-solid fa-plus"></i>
        </a>
    @endif
</body>
</html>
