@php
    $activeCategories = $categorias->filter(fn($cat) =>
        $cat->productos->isNotEmpty() ||
        $cat->children->filter(fn($child) => $child->productos->isNotEmpty())->isNotEmpty()
    );
    $isUser = auth()->check() && auth()->user()->isUser();
    $isStaff = auth()->check() && auth()->user()->puedeTomarPedidos();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branding->app_name ?? 'Mi Bar' }} · Menú</title>
    <link rel="manifest" href="{{ route('manifest') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($isStaff)
        <meta name="vapid-public-key" content="{{ config('webpush.public_key') }}">
    @endif
    <meta name="theme-color" content="{{ $branding->primary_color ?? '#c8922a' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <script>window.__isStaff = {{ $isStaff ? 'true' : 'false' }}; window.__pwaInstallDismissed = {{ session('pwa_install_dismissed') ? 'true' : 'false' }};</script>
    @vite(['resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @if ($faviconUrl = $branding->faviconUrl())
        <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">
    @endif
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

        /* ===== USER AVATAR ===== */
        .user-avatar-wrap {
            position: relative;
            margin-left: 12px;
        }
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid var(--gold-dim);
            transition: border-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #c8922a, #e8b85c);
        }
        .user-avatar:hover { border-color: var(--gold); }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .user-avatar .initials {
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #0e0d0b;
            line-height: 1;
        }
        .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            min-width: 180px;
            z-index: 500;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .user-dropdown.show { display: block; }
        .user-dropdown a,
        .user-dropdown button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 10px 16px;
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            color: var(--text);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .user-dropdown a:hover,
        .user-dropdown button:hover {
            background: rgba(184,150,74,0.1);
            color: var(--gold);
        }
        .user-dropdown hr {
            border: none;
            border-top: 1px solid var(--border);
            margin: 4px 0;
        }

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
        .modal-social-wrap { margin-top: 1.1rem; }
        .modal-sep {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.9rem;
            color: var(--text-muted);
            font-size: 11px;
            letter-spacing: 0.04em;
        }
        .modal-sep::before, .modal-sep::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border, rgba(255, 255, 255, 0.1));
        }
        .modal-social { display: flex; flex-direction: column; gap: 8px; }
        .modal-social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 11px;
            border: 1px solid var(--border, rgba(255, 255, 255, 0.12));
            border-radius: 8px;
            background: transparent;
            color: var(--text);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .modal-social-btn:hover { border-color: var(--gold); background: rgba(255, 255, 255, 0.04); }
        .modal-social-btn i { color: var(--gold); font-size: 15px; }
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
        .item:hover .item-img { transform: scale(1.18); }

        @media (min-width: 640px) {
            .item { padding-right: 1.75rem; }
        }

        .item-img {
            width: clamp(44px, 12vw, 56px);
            height: clamp(44px, 12vw, 56px);
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 6px;
            display: block;
            transition: transform 0.2s ease;
        }

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

        .social-links {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 14px;
        }
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 50%;
            color: var(--text-dim);
            font-size: 17px;
            text-decoration: none;
            transition: color 0.2s, border-color 0.2s, transform 0.2s;
        }
        .social-link:hover {
            color: var(--gold);
            border-color: var(--gold);
            transform: translateY(-2px);
        }

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

        .order-fab {
            position: fixed;
            bottom: 26px;
            right: 24px;
            z-index: 100000;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 22px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, #c8922a, #b8964a);
            color: #0e0d0b;
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            box-shadow: 0 6px 24px rgba(184,150,74,0.45);
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .order-fab:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 10px 32px rgba(184,150,74,0.6);
        }
        .order-fab:active {
            transform: scale(0.96);
        }

        /* ===== NOTIFICACIONES (campana integrada en la top-bar) ===== */
        body .notif-wrap {
            position: relative;
            margin-right: auto;
        }
        body .notif-btn {
            width: 34px;
            height: 34px;
            padding: 0;
            border-radius: 50%;
            border: 2px solid var(--gold-dim);
            background: var(--bg-hover);
            color: var(--text-muted);
            font-size: 14px;
            font-family: 'Jost', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: border-color 0.2s, color 0.2s;
            -webkit-tap-highlight-color: transparent;
        }
        body .notif-btn:hover {
            border-color: var(--gold);
            color: var(--gold);
        }
        body .notif-btn.ping {
            animation: notif-ping 1s ease 2;
        }
        @keyframes notif-ping {
            0% { transform: scale(1); }
            35% { transform: scale(1.18); }
            100% { transform: scale(1); }
        }
        body #notifBadge[hidden] { display: none !important; }
        body #notifBadge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 17px;
            height: 17px;
            padding: 0 4px;
            border-radius: 99px;
            background: var(--red);
            color: #fff;
            font-size: 9.5px;
            font-weight: 700;
            line-height: 17px;
            text-align: center;
            box-shadow: 0 0 0 2px var(--bg);
        }
        body .notif-panel {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: auto;
            width: 330px;
            max-width: calc(100vw - 24px);
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: 0 14px 42px rgba(0, 0, 0, 0.55);
            z-index: 1300;
            overflow: hidden;
            display: none;
            text-align: left;
            font-family: 'Jost', sans-serif;
        }
        body .notif-panel.open { display: block; }
        body .notif-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
        }
        body .notif-panel-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }
        body .notif-panel-count {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--gold);
            background: rgba(184, 150, 74, 0.12);
            padding: 2px 8px;
            border-radius: 99px;
        }
        body .notif-list {
            max-height: 320px;
            overflow-y: auto;
        }
        body .notif-item {
            display: flex;
            gap: 10px;
            padding: 11px 14px;
            border-bottom: 1px solid var(--border-item);
            cursor: pointer;
            transition: background 0.12s;
        }
        body .notif-item:last-child { border-bottom: none; }
        body .notif-item:hover { background: var(--bg-hover); }
        body .notif-item.unread { background: rgba(184, 150, 74, 0.06); }
        body .notif-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: rgba(184, 150, 74, 0.12);
            color: var(--gold);
        }
        body .notif-icon.verde {
            background: var(--green-bg);
            color: var(--green);
        }
        body .notif-body { flex: 1; min-width: 0; }
        body .notif-title {
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text-muted);
        }
        body .notif-item.unread .notif-title {
            font-weight: 700;
            color: var(--text);
        }
        body .notif-msg {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 1px;
            line-height: 1.35;
        }
        body .notif-meta {
            font-size: 10px;
            color: var(--text-dim);
            margin-top: 3px;
        }
        body .notif-empty {
            padding: 24px 14px;
            text-align: center;
            font-size: 12.5px;
            color: var(--text-muted);
        }
        body .notif-foot {
            display: flex;
            gap: 8px;
            padding: 10px 14px;
            border-top: 1px solid var(--border);
            background: var(--bg-hover);
        }
        body .notif-foot button {
            flex: 1;
            padding: 7px 10px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 500;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            transition: border-color 0.12s, color 0.12s, background 0.12s;
        }
        body .notif-foot button:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(184, 150, 74, 0.1);
        }
        body .notif-foot button:disabled {
            opacity: 0.5;
            cursor: default;
        }

        /* ===== TOAST CALENDARIO ===== */
        .cal-toast {
            position: fixed;
            bottom: 26px;
            left: 50%;
            transform: translateX(-50%) translateY(8px);
            background: var(--bg-hover);
            border: 1px solid var(--gold);
            color: var(--text);
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 13px;
            font-family: 'Jost', sans-serif;
            z-index: 100001;
            display: none;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.4);
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none;
            white-space: nowrap;
        }
        .cal-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .cal-toast i { color: var(--gold); }
    </style>
</head>
<body>

    <div class="top-bar">
        @auth
            @if ($isStaff)
                <div class="notif-wrap" id="notifWrap">
                    <button type="button" class="notif-btn" id="notifBell" aria-label="Notificaciones" title="Notificaciones" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-bell"></i>
                        <span id="notifBadge" hidden>0</span>
                    </button>
                </div>
            @endif
            @if ($isUser)
                <button class="btn-reservar" onclick="openReservarModal()">Reservar</button>
            @else
                <a href="{{ route('reservas.index') }}" class="btn-reservar">Reservas</a>
            @endif
            <div class="user-avatar-wrap">
                <div class="user-avatar" onclick="toggleUserDropdown()">
                    @if (auth()->user()->profilePhotoUrl())
                        <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="Foto">
                    @else
                        <span class="initials">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="user-dropdown" id="userDropdown">
                    @if ($isUser)
                        <a href="javascript:void(0)" onclick="toggleUserDropdown(); openReservarModal();">Mis Reservas</a>
                        <a href="javascript:void(0)" onclick="toggleUserDropdown(); openProfileModal();">Editar Perfil</a>
                    @else
                        <a href="{{ route('reservas.index') }}">Mis Reservas</a>
                        <a href="{{ route('profile.edit') }}">Editar Perfil</a>
                    @endif
                    <hr>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Salir</button>
                    </form>
                </div>
            </div>
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
                                @if ($producto->mostrar_foto && $producto->fotoUrl())
                                    <img src="{{ $producto->fotoUrl() }}" alt="{{ $producto->nombre }}" class="item-img">
                                @endif
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
                                    @if ($producto->mostrar_foto && $producto->fotoUrl())
                                        <img src="{{ $producto->fotoUrl() }}" alt="{{ $producto->nombre }}" class="item-img">
                                    @endif
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
            @php
                $socials = [
                    ['key' => 'social_instagram', 'label' => 'Instagram', 'icon' => 'fa-instagram', 'value' => $branding->social_instagram],
                    ['key' => 'social_facebook', 'label' => 'Facebook', 'icon' => 'fa-facebook-f', 'value' => $branding->social_facebook],
                    ['key' => 'social_whatsapp', 'label' => 'WhatsApp', 'icon' => 'fa-whatsapp', 'value' => $branding->social_whatsapp],
                    ['key' => 'social_tiktok', 'label' => 'TikTok', 'icon' => 'fa-tiktok', 'value' => $branding->social_tiktok],
                    ['key' => 'social_twitter', 'label' => 'X', 'icon' => 'fa-x-twitter', 'value' => $branding->social_twitter],
                ];
                $socials = array_filter($socials, fn($s) => !empty($s['value']));
                $socialUrl = function ($key, $value) {
                    $value = trim($value);
                    if ($key === 'social_whatsapp' && preg_match('/^\+?[\d\s\-().]+$/', $value)) {
                        return 'https://wa.me/'.preg_replace('/[^\d]/', '', $value);
                    }
                    if (!preg_match('~^https?://~i', $value)) {
                        return 'https://'.$value;
                    }
                    return $value;
                };
            @endphp
            @if (!empty($socials))
                <div class="social-links">
                    @foreach ($socials as $social)
                        <a href="{{ $socialUrl($social['key'], $social['value']) }}" class="social-link" target="_blank" rel="noopener" title="{{ $social['label'] }}" aria-label="{{ $social['label'] }}">
                            <i class="fa-brands {{ $social['icon'] }}"></i>
                        </a>
                    @endforeach
                </div>
            @endif
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

            @if ($errors->any())
                <div class="alert-banner error" style="margin:0 0 16px;font-size:13px;">
                    <ul style="margin:0;padding-left:16px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

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

            @php
                $socialProviders = collect([
                    ['key' => 'google', 'label' => 'Google', 'icon' => 'fa-brands fa-google'],
                    ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f'],
                ])->filter(fn ($p) => filled(config('services.'.$p['key'].'.client_id')));
            @endphp

            @if ($socialProviders->isNotEmpty())
                <div class="modal-social-wrap">
                    <div class="modal-sep"><span>o continúa con</span></div>
                    <div class="modal-social">
                        @foreach ($socialProviders as $provider)
                            <a class="modal-social-btn" href="{{ route('social.redirect', ['provider' => $provider['key'], 'redirect_from' => 'reservar']) }}">
                                <i class="{{ $provider['icon'] }}"></i>
                                {{ $provider['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

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

    <!-- ===== ICS AUTO-DOWNLOAD (post-reserva) ===== -->
    @php
        $calReserva = session('nueva_reserva');
        $calIcs = null;
        if ($calReserva) {
            $calStart = \Carbon\Carbon::parse($calReserva['fecha'].' '.$calReserva['hora']);
            $calEnd = $calStart->copy()->addMinutes(15);
            $calTitle = 'Reserva - '.($branding->app_name ?? 'Mi Bar');
            $calPeople = (int) $calReserva['personas'];
            $calDesc = 'Reserva para '.$calPeople.($calPeople === 1 ? ' persona' : ' personas');
            if (!empty($calReserva['observaciones'])) {
                $calDesc .= '. '.$calReserva['observaciones'];
            }
            $calLocation = trim((string) ($branding->address ?? ''));
            $calStartUtc = $calStart->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
            $calEndUtc = $calEnd->copy()->setTimezone('UTC')->format('Ymd\THis\Z');

            $icsEscape = fn ($v) => str_replace(['\\', ';', ',', "\r", "\n"], ['\\\\', '\;', '\,', '', '\n'], $v);
            $calLines = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//'.$calTitle.'//ES',
                'CALSCALE:GREGORIAN',
                'BEGIN:VEVENT',
                'UID:reserva-'.uniqid('', true).'@'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'local'),
                'DTSTAMP:'.gmdate('Ymd\THis\Z'),
                'DTSTART:'.$calStartUtc,
                'DTEND:'.$calEndUtc,
                'SUMMARY:'.$icsEscape($calTitle),
                'DESCRIPTION:'.$icsEscape($calDesc),
            ];
            if ($calLocation !== '') {
                $calLines[] = 'LOCATION:'.$icsEscape($calLocation);
            }
            $calLines[] = 'END:VEVENT';
            $calLines[] = 'END:VCALENDAR';
            $calIcs = implode("\r\n", $calLines)."\r\n";
        }
    @endphp

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

    <div class="cal-toast" id="calToast">
        <i class="fa-solid fa-calendar-check"></i> Evento de calendario descargado
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

        // ===== ICS AUTO-DOWNLOAD (post-reserva) =====
        var icsContent = @json($calIcs);
        function downloadIcs() {
            if (!icsContent) return;
            var blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'reserva.ics';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
        }
        function showCalToast() {
            var t = document.getElementById('calToast');
            if (!t) return;
            t.style.display = 'block';
            requestAnimationFrame(function() { t.classList.add('show'); });
            setTimeout(function() {
                t.classList.remove('show');
                setTimeout(function() { t.style.display = 'none'; }, 300);
            }, 4000);
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

        // Auto-open auth modal on validation errors
        @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openModal();
            @if ($errors->has('name'))
            switchModalTab('register');
            @endif
        });
        @endif

        // Auto-open reservation modal after login/register
        @if (session('open_reservar'))
        document.addEventListener('DOMContentLoaded', function() {
            openReservarModal();
        });
        @endif

        // Auto-download .ics + toast after creating a reservation
        @if ($calReserva)
        document.addEventListener('DOMContentLoaded', function() {
            downloadIcs();
            showCalToast();
        });
        @endif

        // Close modals on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeReservarModal();
                var dd = document.getElementById('userDropdown');
                if (dd) dd.classList.remove('show');
            }
        });

        // User dropdown
        function toggleUserDropdown() {
            var dd = document.getElementById('userDropdown');
            if (dd) dd.classList.toggle('show');
        }
        document.addEventListener('click', function(e) {
            var wrap = document.querySelector('.user-avatar-wrap');
            var dd = document.getElementById('userDropdown');
            if (wrap && dd && !wrap.contains(e.target)) {
                dd.classList.remove('show');
            }
        });
    </script>

    @if ($isStaff)
        <a href="{{ route('pedidos.create') }}" class="order-fab" title="Tomar pedido">
            <i class="fa-solid fa-clipboard-list"></i>
            Tomar Pedido
        </a>
    @endif
</body>
</html>
