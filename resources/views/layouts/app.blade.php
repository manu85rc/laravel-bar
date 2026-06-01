<!DOCTYPE html>
@php
    $userTheme = auth()->check() ? auth()->user()->theme : 'light';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($branding) ? $branding->app_name : (App\Models\Branding::getInstance()->app_name ?? 'Laravel Bar') }}</title>
    <!-- Favicon -->
    @php
        $faviconUrl = null;
        if (isset($branding)) {
            $faviconUrl = $branding->faviconUrl();
        } else {
            $faviconUrl = App\Models\Branding::getInstance()->faviconUrl();
        }
    @endphp
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">
    @endif
    <script>
        (function() {
            var theme = '{{ $userTheme }}';
            var isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
            window.__userTheme = theme;
        })();
    </script>
    @vite('resources/css/app.css')
    <!-- Google Fonts for branding -->
    @php
        $brandingFont = isset($branding) ? $branding->font_family : (App\Models\Branding::getInstance()->font_family ?? null);
    @endphp
    @if ($brandingFont)
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ urlencode($brandingFont) }}:wght@400;600;700&display=swap">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @php
        $brandingSettings = isset($branding) ? $branding : App\Models\Branding::getInstance();
    @endphp

    <style>
        :root {
            @if ($brandingSettings->font_family)
                --branding-font: '{{ $brandingSettings->font_family }}', serif;
            @endif
            @if ($brandingSettings->primary_color)
                --branding-primary: {{ $brandingSettings->primary_color }};
            @endif
            @if ($brandingSettings->background_color)
                --branding-bg: {{ $brandingSettings->background_color }};
            @endif
            --sidebar-w:256px;--header-h:64px;
            --bg:#f8f6f1;--bg-2:#f0ece4;--bg-3:#ffffff;--bg-4:#e8e2d8;--bg-5:#ddd6ca;
            --gold:#c8922a;--gold-light:#dba34e;--gold-dim:#b07a1e;
            --gold-glow:rgba(200,146,42,0.12);--gold-subtle:rgba(200,146,42,0.06);
            --text-1:#1a1816;--text-2:#5a4e3c;--text-3:#8a7d68;--text-4:#b0a594;
            --border:rgba(200,146,42,0.2);--border-dim:rgba(0,0,0,0.08);
            --green:#2d8f5e;--green-bg:rgba(45,143,94,0.08);--green-text:#2d8f5e;
            --red:#c0392b;--red-bg:rgba(192,57,43,0.08);--red-text:#c0392b;
            --amber:#c8922a;--amber-bg:rgba(200,146,42,0.08);--amber-text:#8a6518;
            --blue:#3a7bbd;--blue-bg:rgba(58,123,189,0.08);--blue-text:#3a7bbd;
            --radius:8px;--radius-lg:14px;--radius-xl:20px;
        }
        .dark{
            --bg:#0e0a05;--bg-2:#16100a;--bg-3:#1e160d;--bg-4:#271d11;--bg-5:#2f2415;
            --gold-glow:rgba(200,146,42,0.15);--gold-subtle:rgba(200,146,42,0.08);
            --text-1:#f0e6d3;--text-2:#b89e7a;--text-3:#7a6a52;--text-4:#4a3d2a;
            --border:rgba(200,146,42,0.12);--border-dim:rgba(240,230,211,0.06);
            --green:#4caf7d;--green-bg:rgba(76,175,125,0.12);--green-text:#6fcf97;
            --red:#e05252;--red-bg:rgba(224,82,82,0.12);--red-text:#ff8080;
            --amber:#e8b85c;--amber-bg:rgba(232,184,92,0.12);--amber-text:#f0c97a;
            --blue:#63a4d8;--blue-bg:rgba(99,164,216,0.12);--blue-text:#7cbde8;
        }
        @if ($brandingSettings->font_family)
            body, .font-branding { font-family: var(--branding-font) !important; }
        @endif
        @if ($brandingSettings->primary_color)
            .text-branding { color: var(--branding-primary); }
            .bg-branding { background-color: var(--branding-primary); }
            .border-branding { border-color: var(--branding-primary); }
        @endif
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        html{scroll-behavior:smooth;}
        body{
          font-family:"DM Sans",sans-serif;
          background:var(--bg);color:var(--text-1);
          font-size:14px;line-height:1.6;min-height:100vh;
        }
        body::before{
          content:"";position:fixed;inset:0;
          background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
          pointer-events:none;z-index:9999;opacity:.6;
        }
        .app-wrap{min-height:100vh;position:relative;z-index:1;}
        .app-main{padding:32px 20px 40px;max-width:1200px;margin:0 auto;}
        /* ========== SHARED COMPONENTS (match admin layout) ========== */
        .page-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:16px;flex-wrap:wrap;}
        .page-title{font-family:"Playfair Display",serif;font-size:22px;font-weight:700;color:var(--text-1);line-height:1.2;}
        .page-sub{font-size:13px;color:var(--text-3);margin-top:4px;}
        .btn-row{display:flex;gap:8px;flex-wrap:wrap;}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:var(--radius);font-size:13px;font-weight:500;cursor:pointer;border:none;font-family:inherit;transition:all .15s;text-decoration:none;}
        .btn-gold{background:var(--gold);color:var(--bg-2);}
        .btn-gold:hover{background:var(--gold-light);}
        .btn-ghost{background:var(--bg-3);color:var(--text-2);border:1px solid var(--border-dim);}
        .btn-ghost:hover{border-color:var(--border);color:var(--text-1);}
        .btn-sm{padding:6px 12px;font-size:12px;}
        .btn-link{background:none;border:none;color:var(--gold);cursor:pointer;font-size:12px;font-family:inherit;padding:0;transition:color .12s;text-decoration:none;}
        .btn-link:hover{color:var(--gold-light);}
        .btn-link.danger{color:var(--red-text);}
        .btn-link.danger:hover{color:var(--red);}
        .content-max{margin:0 auto;max-width:680px;}
        .content-wide{max-width:960px;}
        .card{background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);transition:border-color .2s;}
        .card:hover{border-color:var(--border);}
        .card-body{padding:20px;}
        .card-body.compact{padding:16px;}
        .card-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px 14px;border-bottom:1px solid var(--border-dim);}
        .card-title{font-size:13.5px;font-weight:600;color:var(--text-1);}
        .card-sub{font-size:11px;color:var(--text-3);margin-top:2px;}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .form-group{display:flex;flex-direction:column;gap:5px;}
        .form-group.full{grid-column:1/-1;}
        .form-label{font-size:12px;font-weight:500;color:var(--text-2);}
        .form-input,.form-select,.form-textarea{padding:9px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--bg-4);color:var(--text-1);font-size:13px;font-family:inherit;width:100%;transition:border-color .15s;outline:none;}
        .form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--gold);}
        .form-input::placeholder,.form-textarea::placeholder{color:var(--text-4);}
        .form-textarea{resize:vertical;min-height:72px;}
        .form-select{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='%237a6a52'%3E%3Cpath d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:28px;}
        .form-select option{background:var(--bg-3);color:var(--text-1);}
        .form-hint{font-size:11px;color:var(--text-4);margin-top:2px;}
        .form-error{font-size:11px;color:var(--red-text);margin-top:2px;}
        .form-checkbox{display:flex;align-items:center;gap:8px;cursor:pointer;}
        .form-checkbox input[type="checkbox"]{appearance:none;width:16px;height:16px;border-radius:3px;border:1px solid var(--border);background:var(--bg-4);cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s;}
        .form-checkbox input[type="checkbox"]:checked{background:var(--gold);border-color:var(--gold);}
        .form-checkbox input[type="checkbox"]:checked::after{content:"\2713";font-size:11px;font-weight:700;color:var(--bg-2);}
        .form-checkbox label{font-size:13px;color:var(--text-2);cursor:pointer;}
        .form-actions{display:flex;align-items:center;justify-content:space-between;padding-top:14px;border-top:1px solid var(--border-dim);margin-top:14px;}
        .file-input-wrap{display:flex;flex-direction:column;gap:6px;}
        .file-input-wrap input[type="file"]{font-size:12px;color:var(--text-3);}
        .file-input-wrap input[type="file"]::file-selector-button{padding:5px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--bg-4);color:var(--text-2);font-size:12px;font-family:inherit;cursor:pointer;transition:all .12s;margin-right:8px;}
        .file-input-wrap input[type="file"]::file-selector-button:hover{background:var(--bg-5);border-color:var(--gold-dim);}
        .img-preview{margin-top:8px;width:120px;height:120px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border-dim);}
        .img-preview-sm{width:60px;height:60px;}
        .alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;display:flex;align-items:flex-start;gap:8px;margin-bottom:16px;}
        .alert i{margin-top:2px;flex-shrink:0;}
        .alert-success{background:var(--green-bg);border:1px solid var(--green);color:var(--green-text);}
        .alert-error{background:var(--red-bg);border:1px solid var(--red);color:var(--red-text);}
        .alert-warning{background:var(--amber-bg);border:1px solid var(--amber);color:var(--amber-text);}
        .alert ul{margin:0;padding-left:16px;}
        .empty-state{text-align:center;padding:28px 16px;color:var(--text-3);font-size:13px;}
        .badge{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:99px;white-space:nowrap;}
        .b-green{background:var(--green-bg);color:var(--green-text);}
        .b-amber{background:var(--amber-bg);color:var(--amber-text);}
        .b-red{background:var(--red-bg);color:var(--red-text);}
        .b-blue{background:var(--blue-bg);color:var(--blue-text);}
        .b-gray{background:rgba(255,255,255,0.05);color:var(--text-3);}
        .bdot{width:5px;height:5px;border-radius:50%;background:currentColor;}
        .ucell{display:flex;align-items:center;gap:10px;}
        .ucell strong{font-weight:500;color:var(--text-1);display:block;}
        .ucell small{font-size:11px;color:var(--text-3);}
        .tbl-wrap{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;}
        thead tr{background:var(--bg-4);}
        th{text-align:left;font-size:10px;font-weight:600;color:var(--text-3);letter-spacing:0.08em;text-transform:uppercase;padding:10px 16px;border-bottom:1px solid var(--border-dim);white-space:nowrap;}
        td{padding:11px 16px;border-bottom:1px solid var(--border-dim);font-size:13px;color:var(--text-2);vertical-align:middle;}
        tr:last-child td{border-bottom:none;}
        tbody tr{transition:background .12s;}
        tbody tr:hover td{background:var(--bg-4);}
        .list-item-actions{display:flex;align-items:center;gap:10px;flex-shrink:0;}
        .list-item-actions a{font-size:12px;color:var(--gold);text-decoration:none;}
        .list-item-actions a:hover{color:var(--gold-light);}
        .list-item-actions form{display:inline;}
        @media(max-width:680px){.app-main{padding:20px 12px 32px;}.form-grid{grid-template-columns:1fr;}.form-actions{flex-direction:column-reverse;gap:10px;}.form-actions a{margin:0 auto;}.page-top{flex-direction:column;}}
    </style>

</head>
<body
      @php
          $brandingBody = isset($branding) ? $branding : App\Models\Branding::getInstance();
      @endphp
      @if ($brandingBody->background_image_path)
          style="background-image: url('{{ asset('storage/' . $brandingBody->background_image_path) }}'); background-size: cover; background-attachment: fixed; background-position: center;"
      @endif>
    <div class="app-wrap" @if ($brandingBody->background_color && !$brandingBody->background_image_path) style="background-color: {{ $brandingBody->background_color }};" @endif>
        @include('layouts.navbar')

        <!-- Main content -->
        <main class="app-main">
            @yield('content')
        </main>
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeManager', () => ({
                theme: window.__userTheme,
                systemDark: window.matchMedia('(prefers-color-scheme: dark)').matches,
                init() {
                    var self = this;
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                        self.systemDark = e.matches;
                        if (self.theme === 'system') {
                            document.documentElement.classList.toggle('dark', e.matches);
                        }
                    });
                }
            }));
        });

        function setTheme(theme) {
            window.__userTheme = theme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', isDark);
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const datetimeInputs = document.querySelectorAll('input[type="text"][data-flatpickr]');
            datetimeInputs.forEach(input => {
                flatpickr(input, {
                    enableTime: true,
                    dateFormat: "d-m-Y H:i",
                    time_24hr: true,
                    minDate: "today",
                    locale: "es",
                });
            });
        });
    </script>
</body>
</html>
