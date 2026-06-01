@php
    $pageTitle = match(true) {
        request()->routeIs('admin.dashboard') => 'Dashboard',
        request()->routeIs('admin.menu.*') => 'Menú',
        request()->routeIs('admin.productos.*') => 'Productos',
        request()->routeIs('admin.categorias.*') => 'Categorías',
        request()->routeIs('admin.mesas.*') => 'Mesas',
        request()->routeIs('pedidos.*') => 'Pedidos',
        request()->routeIs('reservas.*') => 'Reservas',
        request()->routeIs('admin.users.*') => 'Usuarios',
        request()->routeIs('admin.branding.*') => 'Branding',
        default => 'Admin',
    };
    $user = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 1) . substr($user->name, strpos($user->name, ' ') + 1, 1));
    if (strlen($initials) < 2) $initials = strtoupper(substr($user->name, 0, 2));
    $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $hoy = $dias[(int)date('w')] . ', ' . (int)date('d') . ' de ' . $meses[(int)date('n')-1] . ' — ' . date('Y');
@endphp
<button id="hamburger" onclick="openSidebar()"><i class="fa-solid fa-bars"></i></button>
<div class="breadcrumb">
    <span>{{ $brandingSettings->app_name ?? 'Admin' }}</span>
    <i class="fa-solid fa-chevron-right"></i>
    <span>{{ $pageTitle }}</span>
</div>
<div class="header-right">
    <div class="date-chip">
        <i class="fa-regular fa-calendar"></i>
        {{ $hoy }}
    </div>
    <button class="hbtn theme-hbtn" onclick="toggleTheme()" title="Cambiar tema">
        <i class="fa-solid fa-moon" id="theme-icon"></i>
    </button>
    <a href="{{ route('profile.edit') }}" class="hbtn">
        <i class="fa-regular fa-circle-user"></i>
    </a>
    <div class="hbtn" style="border-radius:50%;background:linear-gradient(135deg,var(--gold-dim),var(--gold));border:none;color:var(--bg-2);font-size:11px;font-weight:700;">
        {{ $initials }}
    </div>
</div>
<script>
    (function() {
        var icon = document.getElementById('theme-icon');
        if (icon) {
            var isDark = document.documentElement.classList.contains('dark');
            icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
    })();
</script>
