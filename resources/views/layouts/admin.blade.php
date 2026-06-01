@php
    $brandingSettings = isset($branding) ? $branding : App\Models\Branding::getInstance();
    $userTheme = auth()->check() ? auth()->user()->theme : 'light';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandingSettings->app_name ?? 'Admin' }} · {{ request()->routeIs('admin.dashboard') ? 'Dashboard' : 'Admin' }}</title>
    @if ($faviconUrl = $brandingSettings->faviconUrl())
        <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">
    @endif
    <script>
        (function() {
            var theme = '{{ $userTheme }}';
            var isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) document.documentElement.classList.add('dark');
            window.__userTheme = theme;
        })();
    </script>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        html{scroll-behavior:smooth;}
        :root{
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
        body{
          font-family:'DM Sans',sans-serif;
          background:var(--bg);color:var(--text-1);
          font-size:14px;line-height:1.6;
          display:flex;min-height:100vh;
        }
        body::before{
          content:'';position:fixed;inset:0;
          background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
          pointer-events:none;z-index:9999;opacity:.6;
        }
        #overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:90;backdrop-filter:blur(2px);}
        #overlay.show{display:block;}
        aside{
          width:var(--sidebar-w);background:var(--bg-2);
          border-right:1px solid var(--border);
          height:100vh;position:fixed;top:0;left:0;
          display:flex;flex-direction:column;z-index:100;
          transition:transform .3s cubic-bezier(.4,0,.2,1);
        }
        .logo{
          height:var(--header-h);display:flex;align-items:center;gap:12px;
          padding:0 20px;border-bottom:1px solid var(--border);flex-shrink:0;
        }
        .logo-mark{
          width:34px;height:34px;border-radius:var(--radius);
          background:var(--gold);display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
        .logo-mark i{color:var(--bg-2);font-size:15px;}
        .logo-text{flex:1;}
        .logo-name{font-family:'Playfair Display',serif;font-size:14px;font-weight:600;color:var(--text-1);line-height:1.1;letter-spacing:0.01em;}
        .logo-sub{font-size:10px;color:var(--text-3);letter-spacing:0.06em;text-transform:uppercase;}
        #close-sidebar{display:none;background:none;border:none;color:var(--text-3);font-size:18px;cursor:pointer;padding:4px;}
        nav{flex:1;overflow-y:auto;padding:12px 0;}
        nav::-webkit-scrollbar{width:3px;}
        nav::-webkit-scrollbar-track{background:transparent;}
        nav::-webkit-scrollbar-thumb{background:var(--border);}
        .nav-section{padding:0 12px;margin-bottom:8px;}
        .nav-label{font-size:9px;font-weight:600;color:var(--text-4);letter-spacing:0.12em;text-transform:uppercase;padding:10px 8px 4px;}
        .nav-item{display:flex;align-items:center;gap:11px;padding:9px 10px;border-radius:var(--radius);cursor:pointer;color:var(--text-3);font-size:13.5px;font-weight:400;text-decoration:none;transition:all .18s;position:relative;}
        .nav-item:hover{background:var(--gold-subtle);color:var(--text-2);}
        .nav-item.active{background:var(--gold-glow);color:var(--gold-light);font-weight:500;}
        .nav-item.active::before{content:'';position:absolute;left:0;top:6px;bottom:6px;width:2px;border-radius:2px;background:var(--gold);}
        .nav-item i{width:17px;font-size:13px;text-align:center;flex-shrink:0;}
        .nav-item.active i{color:var(--gold);}
        .nav-chip{margin-left:auto;font-size:10px;font-weight:600;background:var(--gold-glow);color:var(--gold);padding:2px 8px;border-radius:99px;border:1px solid var(--border);}
        .nav-chip.alert{background:var(--red-bg);color:var(--red-text);border-color:transparent;}
        .sidebar-footer{padding:12px;border-top:1px solid var(--border);flex-shrink:0;}
        .venue-status{display:flex;align-items:center;gap:8px;padding:10px 10px;background:var(--bg-3);border-radius:var(--radius);border:1px solid var(--border);margin-bottom:8px;}
        .status-dot{width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);flex-shrink:0;}
        .status-label{font-size:12px;font-weight:500;color:var(--text-2);}
        .status-time{font-size:11px;color:var(--text-3);margin-left:auto;}
        .user-row{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--radius);cursor:pointer;transition:background .15s;}
        .user-row:hover{background:var(--gold-subtle);}
        .av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--gold-dim),var(--gold));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--bg-2);flex-shrink:0;}
        .user-name{font-size:13px;font-weight:500;color:var(--text-2);}
        .user-role{font-size:11px;color:var(--text-4);}
        .user-row>i{margin-left:auto;color:var(--text-4);font-size:12px;}
        main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;}
        .admin-header{
          height:var(--header-h);background:var(--bg-2);
          border-bottom:1px solid var(--border);
          display:flex;align-items:center;
          padding:0 24px;gap:14px;
          position:sticky;top:0;z-index:50;
        }
        #hamburger{display:none;background:none;border:none;color:var(--text-2);font-size:18px;cursor:pointer;padding:4px;}
        .breadcrumb{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--text-3);}
        .breadcrumb span:last-child{color:var(--text-2);font-weight:500;}
        .breadcrumb i{font-size:9px;}
        .header-right{margin-left:auto;display:flex;align-items:center;gap:8px;}
        .search{display:flex;align-items:center;gap:8px;background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius);padding:0 12px;height:36px;width:200px;color:var(--text-3);font-size:13px;font-family:inherit;cursor:text;transition:border .15s;}
        .search:hover{border-color:var(--border);}
        .search i{font-size:12px;}
        .hbtn{width:36px;height:36px;border-radius:var(--radius);border:1px solid var(--border-dim);background:var(--bg-3);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-3);font-size:14px;transition:all .15s;position:relative;text-decoration:none;}
        .hbtn:hover{border-color:var(--border);color:var(--text-2);}
        .ndot{width:7px;height:7px;border-radius:50%;background:var(--red);border:1.5px solid var(--bg-2);position:absolute;top:5px;right:5px;}
        .date-chip{display:flex;align-items:center;gap:6px;padding:0 12px;height:36px;background:var(--gold-glow);border:1px solid var(--border);border-radius:var(--radius);font-size:12px;font-weight:500;color:var(--gold-light);white-space:nowrap;}
        .date-chip i{font-size:12px;color:var(--gold);}
        .page{padding:24px 28px;flex:1;}
        .page-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:16px;flex-wrap:wrap;}
        .page-title{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--text-1);line-height:1.2;}
        .page-sub{font-size:13px;color:var(--text-3);margin-top:4px;}
        .btn-row{display:flex;gap:8px;flex-wrap:wrap;}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:var(--radius);font-size:13px;font-weight:500;cursor:pointer;border:none;font-family:inherit;transition:all .15s;text-decoration:none;}
        .btn-gold{background:var(--gold);color:var(--bg-2);}
        .btn-gold:hover{background:var(--gold-light);}
        .btn-ghost{background:var(--bg-3);color:var(--text-2);border:1px solid var(--border-dim);}
        .btn-ghost:hover{border-color:var(--border);color:var(--text-1);}
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
        .scard{background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);padding:18px 20px;position:relative;overflow:hidden;transition:border-color .2s;}
        .scard:hover{border-color:var(--border);}
        .scard::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--gold-subtle) 0%,transparent 60%);pointer-events:none;}
        .scard-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;}
        .scard-label{font-size:10px;font-weight:600;color:var(--text-3);letter-spacing:0.09em;text-transform:uppercase;}
        .scard-icon{width:34px;height:34px;border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:14px;}
        .si-gold{background:var(--gold-glow);color:var(--gold);}
        .si-green{background:var(--green-bg);color:var(--green-text);}
        .si-amber{background:var(--amber-bg);color:var(--amber-text);}
        .si-blue{background:var(--blue-bg);color:var(--blue-text);}
        .scard-val{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:var(--text-1);line-height:1;}
        .scard-meta{display:flex;align-items:center;gap:5px;margin-top:7px;font-size:11.5px;}
        .scard-meta.up{color:var(--green-text);}
        .scard-meta.down{color:var(--red-text);}
        .scard-meta.neutral{color:var(--text-3);}
        .scard-meta span{color:var(--text-3);margin-left:2px;}
        .mid-grid{display:grid;grid-template-columns:1fr 320px;gap:18px;margin-bottom:22px;}
        .card{background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);transition:border-color .2s;}
        .card:hover{border-color:var(--border);}
        .card-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px 14px;border-bottom:1px solid var(--border-dim);}
        .card-title{font-size:13.5px;font-weight:600;color:var(--text-1);}
        .card-sub{font-size:11px;color:var(--text-3);margin-top:2px;}
        .card-link{font-size:12px;color:var(--gold);font-weight:500;text-decoration:none;cursor:pointer;}
        .card-link:hover{color:var(--gold-light);}
        .tbl-wrap{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;}
        thead tr{background:var(--bg-4);}
        th{text-align:left;font-size:10px;font-weight:600;color:var(--text-3);letter-spacing:0.08em;text-transform:uppercase;padding:10px 16px;border-bottom:1px solid var(--border-dim);white-space:nowrap;}
        td{padding:11px 16px;border-bottom:1px solid var(--border-dim);font-size:13px;color:var(--text-2);vertical-align:middle;}
        tr:last-child td{border-bottom:none;}
        tbody tr{transition:background .12s;}
        tbody tr:hover td{background:var(--bg-4);}
        .ucell{display:flex;align-items:center;gap:10px;}
        .ucell strong{font-weight:500;color:var(--text-1);display:block;}
        .ucell small{font-size:11px;color:var(--text-3);}
        .av-sm{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;color:var(--bg-2);}
        .a1{background:linear-gradient(135deg,#c8922a,#e8b85c);}
        .a2{background:linear-gradient(135deg,#4caf7d,#7de0a4);}
        .a3{background:linear-gradient(135deg,#7c5cc8,#b89aee);}
        .a4{background:linear-gradient(135deg,#e05252,#f87171);}
        .a5{background:linear-gradient(135deg,#63a4d8,#93c4ee);}
        .badge{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:99px;white-space:nowrap;}
        .b-green{background:var(--green-bg);color:var(--green-text);}
        .b-amber{background:var(--amber-bg);color:var(--amber-text);}
        .b-red{background:var(--red-bg);color:var(--red-text);}
        .b-blue{background:var(--blue-bg);color:var(--blue-text);}
        .b-gray{background:rgba(255,255,255,0.05);color:var(--text-3);}
        .bdot{width:5px;height:5px;border-radius:50%;background:currentColor;}
        .amount-cell{font-family:'Playfair Display',serif;font-size:14px;font-weight:600;color:var(--gold-light);}
        .table-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;padding:16px;}
        .table-slot{aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;border-radius:var(--radius);border:1px solid var(--border-dim);cursor:pointer;transition:all .15s;font-size:12px;font-weight:500;position:relative;}
        .table-slot.occupied{background:var(--gold-glow);border-color:var(--gold-dim);color:var(--gold-light);}
        .table-slot.free{background:var(--bg-4);color:var(--text-3);}
        .table-slot.free:hover{border-color:var(--border);color:var(--text-2);}
        .table-slot.reserved{background:var(--blue-bg);border-color:rgba(99,164,216,0.2);color:var(--blue-text);}
        .ts-num{font-size:16px;font-weight:700;font-family:'Playfair Display',serif;}
        .ts-label{font-size:10px;letter-spacing:0.04em;opacity:.8;}
        .ts-pax{position:absolute;top:6px;right:7px;font-size:9px;color:currentColor;opacity:.6;}
        .ts-pax i{font-size:9px;}
        .map-legend{display:flex;gap:14px;padding:0 16px 14px;font-size:11px;color:var(--text-3);}
        .leg-item{display:flex;align-items:center;gap:5px;}
        .leg-dot{width:8px;height:8px;border-radius:2px;}
        .bottom-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;}
        .drinks-list{}
        .drink-row{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border-dim);}
        .drink-row:last-child{border-bottom:none;}
        .drink-rank{font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--text-4);width:18px;text-align:center;flex-shrink:0;}
        .drink-rank.top{color:var(--gold);}
        .drink-info{flex:1;min-width:0;}
        .drink-name{font-size:13px;font-weight:500;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .drink-cat{font-size:11px;color:var(--text-3);}
        .drink-right{text-align:right;flex-shrink:0;}
        .drink-count{font-size:13px;font-weight:600;color:var(--text-1);}
        .drink-rev{font-size:11px;color:var(--gold);}
        .inv-row{display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid var(--border-dim);}
        .inv-row:last-child{border-bottom:none;}
        .inv-icon{width:30px;height:30px;border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
        .inv-info{flex:1;min-width:0;}
        .inv-name{font-size:13px;font-weight:500;color:var(--text-1);}
        .inv-stock{font-size:11px;color:var(--text-3);}
        .inv-bar-wrap{margin-top:5px;height:3px;background:var(--bg-5);border-radius:99px;overflow:hidden;}
        .inv-bar{height:100%;border-radius:99px;}
        .staff-row{display:flex;align-items:center;gap:11px;padding:11px 20px;border-bottom:1px solid var(--border-dim);}
        .staff-row:last-child{border-bottom:none;}
        .staff-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;color:var(--bg-2);}
        .staff-info{flex:1;}
        .staff-name{font-size:13px;font-weight:500;color:var(--text-1);}
        .staff-role{font-size:11px;color:var(--text-3);}
        .staff-hours{font-size:11px;color:var(--text-3);text-align:right;}
        .staff-hours strong{display:block;font-size:13px;font-weight:500;color:var(--text-2);}
        /* ========== SHARED COMPONENTS ========== */
        .content-max{margin:0 auto;max-width:680px;}
        .content-wide{max-width:960px;}
        .page-section{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;}
        .page-section h2{font-family:"Playfair Display",serif;font-size:18px;font-weight:700;color:var(--text-1);}
        .page-section p{font-size:13px;color:var(--text-3);margin-top:2px;}
        .card-body{padding:20px;}
        .card-body.compact{padding:16px;}
        .card-section{border-bottom:1px solid var(--border-dim);padding:18px 20px;}
        .card-section:last-child{border-bottom:none;}
        .card-section-title{font-size:13px;font-weight:600;color:var(--text-1);margin-bottom:2px;}
        .card-section-sub{font-size:11px;color:var(--text-3);margin-bottom:10px;}
        .filter-bar{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-bottom:18px;}
        .filter-group{display:flex;flex-direction:column;gap:4px;min-width:140px;}
        .filter-group label{font-size:10px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:0.06em;}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .form-group{display:flex;flex-direction:column;gap:5px;}
        .form-group.full{grid-column:1/-1;}
        .form-label{font-size:12px;font-weight:500;color:var(--text-2);}
        .form-input,.form-select,.form-textarea{padding:9px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--bg-4);color:var(--text-1);font-size:13px;font-family:inherit;width:100%;transition:border-color .15s;outline:none;}
        .form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--gold);}
        .form-input::placeholder,.form-textarea::placeholder{color:var(--text-4);}
        .form-textarea{resize:vertical;min-height:72px;}
        .form-select{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="10" height="6" fill="%237a6a52"%3E%3Cpath d="M0 0l5 6 5-6z"/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:28px;}
        .form-select option{background:var(--bg-3);color:var(--text-1);}
        .form-hint{font-size:11px;color:var(--text-4);margin-top:2px;}
        .form-error{font-size:11px;color:var(--red-text);margin-top:2px;}
        .form-checkbox{display:flex;align-items:center;gap:8px;cursor:pointer;}
        .form-checkbox input[type="checkbox"]{appearance:none;width:16px;height:16px;border-radius:3px;border:1px solid var(--border);background:var(--bg-4);cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s;}
        .form-checkbox input[type="checkbox"]:checked{background:var(--gold);border-color:var(--gold);}
        .form-checkbox input[type="checkbox"]:checked::after{content:"\2713";font-size:11px;font-weight:700;color:var(--bg-2);}
        .form-checkbox label{font-size:13px;color:var(--text-2);cursor:pointer;}
        .form-actions{display:flex;align-items:center;justify-content:space-between;padding-top:14px;border-top:1px solid var(--border-dim);margin-top:14px;}
        .form-actions.left{justify-content:flex-start;gap:10px;}
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
        .list-items{display:flex;flex-direction:column;}
        .list-item{display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid var(--border-dim);}
        .list-item:last-child{border-bottom:none;}
        .list-item:hover{background:var(--bg-4);}
        .list-item-info{flex:1;min-width:0;}
        .list-item-title{font-size:13px;font-weight:500;color:var(--text-1);}
        .list-item-sub{font-size:11px;color:var(--text-3);}
        .list-item-actions{display:flex;align-items:center;gap:10px;flex-shrink:0;}
        .list-item-actions a{font-size:12px;color:var(--gold);text-decoration:none;}
        .list-item-actions a:hover{color:var(--gold-light);}
        .list-item-actions .btn-danger-link{color:var(--red-text);}
        .list-item-actions .btn-danger-link:hover{color:var(--red);}
        .pagination{display:flex;justify-content:center;padding:16px 0;}
        .pagination nav{display:flex;gap:4px;}
        .pagination a,.pagination span{padding:6px 12px;border-radius:var(--radius);font-size:12px;color:var(--text-2);background:var(--bg-4);border:1px solid var(--border-dim);text-decoration:none;transition:all .12s;}
        .pagination a:hover{background:var(--gold-subtle);border-color:var(--gold-dim);color:var(--gold-light);}
        .pagination .active{background:var(--gold);border-color:var(--gold);color:var(--bg-2);font-weight:600;}
        .pagination .disabled{opacity:.4;pointer-events:none;}
        .management-card .card-head .left-side h3{font-size:13.5px;font-weight:600;color:var(--text-1);}
        .btn-danger{background:var(--red);color:#fff;}
        .btn-danger:hover{background:var(--red-text);}
        .btn-sm{padding:6px 12px;font-size:12px;}
        .btn-link{background:none;border:none;color:var(--gold);cursor:pointer;font-size:12px;font-family:inherit;padding:0;transition:color .12s;text-decoration:none;}
        .btn-link:hover{color:var(--gold-light);}
        .btn-link.danger{color:var(--red-text);}
        .btn-link.danger:hover{color:var(--red);}
        /* ========== PLANO DE MESAS ========== */
        .plano-toolbar{display:flex;gap:16px;align-items:flex-end;padding:14px 18px;background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);margin-bottom:14px;flex-wrap:wrap;}
        .toolbar-group{display:flex;flex-direction:column;gap:4px;min-width:100px;}
        .toolbar-label{font-size:10px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:0.06em;}
        .shape-selector{display:flex;gap:4px;}
        .shape-btn{width:34px;height:34px;border-radius:var(--radius);border:1px solid var(--border-dim);background:var(--bg-4);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s;}
        .shape-btn:hover{border-color:var(--gold-dim);}
        .shape-btn.active{background:var(--gold);border-color:var(--gold);}
        .shape-btn.active .shape-preview{background:var(--bg-2);}
        .shape-preview{width:16px;height:16px;background:var(--text-3);}
        .shape-preview.circle{border-radius:50%;}
        .shape-preview.square{border-radius:2px;}
        .size-slider{width:80px;accent-color:var(--gold);cursor:pointer;}
        .size-input{width:50px;padding:4px 6px;border-radius:var(--radius);border:1px solid var(--border-dim);background:var(--bg-4);color:var(--text-1);font-size:12px;font-family:inherit;text-align:center;outline:none;}
        .size-input:focus{border-color:var(--gold);}
        .plano-wrapper{border:1px solid var(--border-dim);border-radius:var(--radius-lg);overflow:hidden;background:var(--bg-3);}
        .plano-canvas{position:relative;width:100%;min-height:600px;background-color:var(--bg-4);background-image:linear-gradient(rgba(0,0,0,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,0,0,0.04) 1px,transparent 1px);background-size:20px 20px;overflow:hidden;}
        .dark .plano-canvas{background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);}
        .mesa-shape{position:absolute;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:grab;border:1px solid var(--border-dim);transition:box-shadow .15s,width .2s,height .2s,border-radius .2s;user-select:none;}
        .mesa-shape:active{cursor:grabbing;}
        .mesa-shape.selected{box-shadow:0 0 0 3px var(--gold),0 0 16px rgba(200,146,42,0.3);z-index:10;}
        .mesa-shape.circle{border-radius:50%;}
        .mesa-shape.square{border-radius:6px;}
        .mesa-shape.occupied{background:var(--gold-glow);border-color:var(--gold-dim);color:var(--gold-light);}
        .mesa-shape.free{background:var(--bg-4);color:var(--text-3);border-color:var(--border-dim);}
        .mesa-shape.reserved{background:var(--blue-bg);border-color:rgba(99,164,216,0.2);color:var(--blue-text);}
        .mesa-label{font-family:'Playfair Display',serif;font-size:14px;font-weight:700;pointer-events:none;line-height:1;}
        .mesa-pax{position:absolute;top:5px;right:7px;font-size:9px;opacity:.7;pointer-events:none;}
        .mesa-pax i{font-size:8px;}
        .plano-status{margin-top:12px;padding:10px 14px;border-radius:var(--radius);font-size:13px;display:flex;align-items:center;gap:7px;}
        .plano-status.success{background:var(--green-bg);color:var(--green-text);}
        .plano-status.error{background:var(--red-bg);color:var(--red-text);}
        .plano-status.saving{color:var(--text-3);}
        .drag-handle{color:var(--text-4);cursor:grab;font-size:16px;}
        .drag-handle:active{cursor:grabbing;}
        .sort-item{cursor:move;}
        .sort-item.dragging{opacity:.5;}
        .theme-hbtn{width:36px;height:36px;border-radius:var(--radius);border:1px solid var(--border-dim);background:var(--bg-3);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-3);font-size:14px;transition:all .15s;}
        .theme-hbtn:hover{border-color:var(--border);color:var(--gold);}
        /* ========== PISOS / NIVELES ========== */
        .piso-tabs{display:flex;gap:4px;margin-bottom:14px;flex-wrap:wrap;}
        .piso-tab{display:inline-flex;align-items:center;padding:8px 16px;border-radius:var(--radius);font-size:13px;font-weight:500;color:var(--text-2);background:var(--bg-3);border:1px solid var(--border-dim);text-decoration:none;transition:all .12s;cursor:pointer;}
        .piso-tab:hover{border-color:var(--border);color:var(--text-1);}
        .piso-tab.active{background:var(--gold);border-color:var(--gold);color:var(--bg-2);}
        @media(max-width:1200px){.stats-grid{grid-template-columns:repeat(2,1fr);}.bottom-grid{grid-template-columns:1fr 1fr;}.bottom-grid .card:last-child{grid-column:1/-1;}.form-grid{grid-template-columns:1fr;}}
        @media(max-width:960px){aside{transform:translateX(-100%);}aside.open{transform:translateX(0);}main{margin-left:0;}#hamburger{display:flex;}#close-sidebar{display:flex;}.mid-grid{grid-template-columns:1fr;}.search{width:160px;}.date-chip{display:none;}.table-grid{grid-template-columns:repeat(3,1fr);}}
        @media(max-width:680px){.stats-grid{grid-template-columns:1fr 1fr;}.bottom-grid{grid-template-columns:1fr;}.page{padding:16px;}.admin-header{padding:0 16px;}.page-top{flex-direction:column;}.table-grid{grid-template-columns:repeat(2,1fr);}.filter-bar{flex-direction:column;align-items:stretch;}.filter-group{min-width:0;}.form-actions{flex-direction:column-reverse;gap:10px;}.form-actions a{margin:0 auto;}.search{display:none;}.category-header{flex-direction:column;align-items:flex-start;gap:8px;}}
        @media(max-width:420px){.stats-grid{grid-template-columns:1fr;}.table-grid{grid-template-columns:repeat(3,1fr);}}
    </style>
</head>
<body>
<div id="overlay" onclick="closeSidebar()"></div>

<aside id="sidebar">
    @include('admin.partials.sidebar')
</aside>

<main>
    <header class="admin-header">
        @include('admin.partials.header')
    </header>
    <div class="page">
        @yield('content')
    </div>
</main>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function openSidebar(){
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('overlay').classList.add('show');
    document.body.style.overflow='hidden';
}
function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
    document.body.style.overflow='';
}
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
    var icon = document.getElementById('theme-icon');
    if (icon) {
        var isCurrentlyDark = document.documentElement.classList.contains('dark');
        icon.className = isCurrentlyDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
}
function toggleTheme() {
    var isDark = document.documentElement.classList.contains('dark');
    var newTheme = isDark ? 'light' : 'dark';
    setTheme(newTheme);
    fetch('{{ route('profile.updateTheme') }}', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ theme: newTheme })
    });
}
document.addEventListener('alpine:init', function () {
    Alpine.data('themeManager', function () {
        var self = this;
        return {
            theme: window.__userTheme,
            systemDark: window.matchMedia('(prefers-color-scheme: dark)').matches,
            init: function () {
                var self = this;
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                    self.systemDark = e.matches;
                    if (self.theme === 'system') {
                        document.documentElement.classList.toggle('dark', e.matches);
                    }
                });
            }
        };
    });
});
document.addEventListener('DOMContentLoaded', function() {
    var dtInputs = document.querySelectorAll('input[type="text"][data-flatpickr]');
    dtInputs.forEach(function(inp){
        flatpickr(inp, {enableTime:true,dateFormat:"d-m-Y H:i",time_24hr:true,minDate:"today",locale:"es"});
    });
});
</script>
</body>
</html>